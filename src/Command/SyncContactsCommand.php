<?php

namespace Tourze\WechatBotBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Repository\WeChatAccountRepository;
use Tourze\WechatBotBundle\Service\WeChatContactService;

/**
 * 同步微信联系人命令
 * 用于定时同步所有在线微信账号的联系人信息
 */
#[AsCommand(name: self::NAME, description: '同步微信联系人信息', help: <<<'TXT'

    此命令用于同步微信联系人信息。支持以下功能：
    - 同步所有在线账号的联系人信息
    - 指定特定账号进行同步
    - 批量处理和请求限流
    - 增量同步和全量同步

    使用示例：
      php bin/console wechat:sync-contacts                    # 同步所有在线账号
      php bin/console wechat:sync-contacts 123               # 同步指定账号
      php bin/console wechat:sync-contacts --force           # 强制同步所有账号
      php bin/console wechat:sync-contacts --only-online     # 只同步在线账号
      php bin/console wechat:sync-contacts --batch-size=5    # 设置批次大小

    TXT)]
class SyncContactsCommand extends Command
{
    public const NAME = 'wechat:sync-contacts';

    public function __construct(
        private readonly WeChatAccountRepository $accountRepository,
        private readonly WeChatContactService $contactService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('account-id', InputArgument::OPTIONAL, '指定要同步的微信账号ID')
            ->addOption('force', 'f', InputOption::VALUE_NONE, '强制同步，即使账号离线')
            ->addOption('only-online', 'o', InputOption::VALUE_NONE, '只同步在线账号')
            ->addOption('batch-size', 'b', InputOption::VALUE_REQUIRED, '批次大小', 10)
            ->addOption('delay', 'd', InputOption::VALUE_REQUIRED, '请求间隔（秒）', 1)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $accountId = $input->getArgument('account-id');
        $force = (bool) $input->getOption('force');
        $onlyOnlineOption = $input->getOption('only-online');
        $onlyOnline = is_bool($onlyOnlineOption) ? $onlyOnlineOption : (bool) $onlyOnlineOption;
        $batchSizeOption = $input->getOption('batch-size');
        $batchSize = is_int($batchSizeOption) ? $batchSizeOption : (is_string($batchSizeOption) || is_numeric($batchSizeOption) ? (int) $batchSizeOption : 10);
        $delayOption = $input->getOption('delay');
        $delay = is_int($delayOption) ? $delayOption : (is_string($delayOption) || is_numeric($delayOption) ? (int) $delayOption : 1);

        $io->title('微信联系人同步');

        try {
            if (null !== $accountId) {
                return $this->syncSingleAccountById($io, $accountId, $force);
            }

            return $this->syncMultipleAccounts($io, $force, $onlyOnline, $batchSize, $delay);
        } catch (\Exception $e) {
            return $this->handleExecuteError($io, $output, $e);
        }
    }

    private function syncSingleAccountById(SymfonyStyle $io, mixed $accountId, bool $force): int
    {
        $account = $this->accountRepository->find($accountId);
        if (null === $account) {
            $accountIdStr = is_string($accountId) || is_int($accountId) ? (string) $accountId : 'UNKNOWN';
            $io->error("未找到ID为 {$accountIdStr} 的微信账号");

            return Command::FAILURE;
        }

        // 账号已通过 Repository::find() 返回，类型确保正确
        // $account 已经由 PHPDoc 声明为 WeChatAccount|null，检查 null 后确保类型

        return $this->executeSingleAccountSync($io, $account, $force);
    }

    private function executeSingleAccountSync(SymfonyStyle $io, WeChatAccount $account, bool $force): int
    {
        $nickname = $account->getNickname() ?? '未知';
        $wechatId = $account->getWechatId() ?? '未知';

        $io->section("同步账号：{$nickname} ({$wechatId})");

        if (!$force && !$account->isOnline()) {
            $io->warning("账号 {$wechatId} 当前离线，跳过同步（使用 --force 强制同步）");

            return Command::SUCCESS;
        }

        try {
            $success = $this->contactService->syncContacts($account);

            if ($success) {
                $io->success("账号 {$wechatId} 联系人同步完成");
            } else {
                $io->warning("账号 {$wechatId} 联系人同步可能有部分失败");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error("同步账号 {$wechatId} 时发生错误：{$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    private function handleExecuteError(SymfonyStyle $io, OutputInterface $output, \Exception $e): int
    {
        $io->error('同步过程中发生错误：' . $e->getMessage());
        if ($output->isVerbose()) {
            $io->writeln($e->getTraceAsString());
        }

        return Command::FAILURE;
    }

    /**
     * 同步多个账号的联系人
     */
    private function syncMultipleAccounts(
        SymfonyStyle $io,
        bool $force,
        bool $onlyOnline,
        int $batchSize,
        int $delay,
    ): int {
        $accounts = $this->getAccountsToSync($io, $onlyOnline, $force);

        if ([] === $accounts) {
            $io->warning('没有找到可同步的账号');

            return Command::FAILURE;
        }

        $io->progressStart(count($accounts));
        $result = $this->syncAccountsInBatches($io, $accounts, $batchSize, $delay, $force);
        $io->progressFinish();

        $this->displaySyncResults($io, $result);

        return $result['failureCount'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * @return WeChatAccount[]
     */
    private function getAccountsToSync(SymfonyStyle $io, bool $onlyOnline, bool $force): array
    {
        if ($onlyOnline || !$force) {
            $accounts = $this->accountRepository->findOnlineAccounts();
            $io->writeln('找到 ' . count($accounts) . ' 个在线账号');
        } else {
            $accounts = $this->accountRepository->findAllValidAccounts();
            $io->writeln('找到 ' . count($accounts) . ' 个有效账号');
        }

        return $accounts;
    }

    /**
     * @param WeChatAccount[] $accounts
     * @return array<string, mixed>
     */
    private function syncAccountsInBatches(
        SymfonyStyle $io,
        array $accounts,
        int $batchSize,
        int $delay,
        bool $force,
    ): array {
        $successCount = 0;
        $failureCount = 0;
        $batches = array_chunk($accounts, max(1, $batchSize));

        foreach ($batches as $batchIndex => $batch) {
            $io->section('处理第 ' . ($batchIndex + 1) . ' 批账号（共 ' . count($batches) . ' 批）');

            $batchResult = $this->syncAccountBatch($io, $batch, $delay, $force);
            $successCount += $batchResult['successCount'];
            $failureCount += $batchResult['failureCount'];

            $this->delayBetweenBatches($io, $batchIndex, count($batches), $delay);
        }

        return ['successCount' => $successCount, 'failureCount' => $failureCount];
    }

    /**
     * @param WeChatAccount[] $batch
     * @return array<string, int>
     */
    private function syncAccountBatch(
        SymfonyStyle $io,
        array $batch,
        int $delay,
        bool $force,
    ): array {
        $successCount = 0;
        $failureCount = 0;

        foreach ($batch as $account) {
            try {
                $result = $this->syncSingleAccount($io, $account, $force);
                if ($result) {
                    ++$successCount;
                } else {
                    ++$failureCount;
                }

                $this->delayBetweenRequests($delay);
            } catch (\Exception $e) {
                $io->writeln("  <error>同步失败：{$e->getMessage()}</error>");
                ++$failureCount;
            } finally {
                $io->progressAdvance();
            }
        }

        return ['successCount' => $successCount, 'failureCount' => $failureCount];
    }

    private function syncSingleAccount(SymfonyStyle $io, WeChatAccount $account, bool $force): bool
    {
        $nickname = $account->getNickname() ?? '未知';
        $wechatId = $account->getWechatId() ?? '未知';
        $io->writeln("同步账号：{$nickname} ({$wechatId})");

        // 检查账号状态
        if (!$force && !$account->isOnline()) {
            $io->writeln('  账号离线，跳过同步');

            return true; // 跳过不算失败
        }

        // 执行同步
        $success = $this->contactService->syncContacts($account);
        $io->writeln($success ? '  同步完成' : '  同步可能有部分失败');

        return $success;
    }

    private function delayBetweenRequests(int $delay): void
    {
        if ($delay > 0) {
            sleep($delay);
        }
    }

    private function delayBetweenBatches(SymfonyStyle $io, int $batchIndex, int $totalBatches, int $delay): void
    {
        if ($delay > 0 && $batchIndex < $totalBatches - 1) {
            $io->writeln("批次间隔等待 {$delay} 秒...");
            sleep($delay);
        }
    }

    /**
     * @param array<string, mixed> $result
     */
    private function displaySyncResults(SymfonyStyle $io, array $result): void
    {
        $io->section('同步结果统计');
        $successCount = is_int($result['successCount'] ?? null) ? $result['successCount'] : 0;
        $failureCount = is_int($result['failureCount'] ?? null) ? $result['failureCount'] : 0;
        $io->writeln("成功同步：{$successCount} 个账号");
        $io->writeln("同步失败：{$failureCount} 个账号");

        if ($failureCount > 0) {
            $io->warning('部分账号同步失败，请检查日志获取详细信息');
        } else {
            $io->success('所有账号同步完成！');
        }
    }
}

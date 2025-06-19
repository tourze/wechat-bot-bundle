<?php

namespace Tourze\WechatBotBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
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
#[AsCommand(
    name: self::NAME,
    description: '同步微信联系人信息'
)]
class SyncContactsCommand extends Command
{
    public const NAME = 'wechat:sync-contacts';
public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WeChatAccountRepository $accountRepository,
        private readonly WeChatContactService $contactService
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
            ->setHelp('
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
');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $accountId = $input->getArgument('account-id');
        $force = (bool) $input->getOption('force');
        $onlyOnline = $input->getOption('only-online');
        $batchSize = (int) $input->getOption('batch-size');
        $delay = (int) $input->getOption('delay');

        $io->title('微信联系人同步');

        try {
            if ((bool) $accountId) {
                // 同步指定账号
                $account = $this->accountRepository->find($accountId);
                if (!$account) {
                    $io->error("未找到ID为 {$accountId} 的微信账号");
                    return Command::FAILURE;
                }

                return $this->syncSingleAccount($io, $account, $force, $delay);
            } else {
                // 同步多个账号
                return $this->syncMultipleAccounts($io, $force, $onlyOnline, $batchSize, $delay);
            }
        } catch (\Exception $e) {
            $io->error('同步过程中发生错误：' . $e->getMessage());
            if ($output->isVerbose()) {
                $io->writeln($e->getTraceAsString());
            }
            return Command::FAILURE;
        }
    }

    /**
     * 同步单个账号的联系人
     */
    private function syncSingleAccount(
        SymfonyStyle $io,
        WeChatAccount $account,
        bool $force,
        int $delay
    ): int {
        $io->section("同步账号：{$account->getNickname()} ({$account->getWechatId()})");

        // 检查账号状态
        if (!$force && !$account->isOnline()) {
            $io->warning("账号 {$account->getWechatId()} 当前离线，跳过同步（使用 --force 强制同步）");
            return Command::SUCCESS;
        }

        $io->progressStart();

        try {
            // 执行同步
            $success = $this->contactService->syncContacts($account);

            $io->progressFinish();
            if ((bool) $success) {
                $io->success("账号 {$account->getWechatId()} 联系人同步完成");
            } else {
                $io->warning("账号 {$account->getWechatId()} 联系人同步可能有部分失败");
            }

            // 请求间隔
            if ($delay > 0) {
                $io->writeln("等待 {$delay} 秒...");
                sleep($delay);
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->progressFinish();
            $io->error("同步账号 {$account->getWechatId()} 时发生错误：{$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * 同步多个账号的联系人
     */
    private function syncMultipleAccounts(
        SymfonyStyle $io,
        bool $force,
        bool $onlyOnline,
        int $batchSize,
        int $delay
    ): int {
        // 获取要同步的账号列表
        if ($onlyOnline || !$force) {
            $accounts = $this->accountRepository->findOnlineAccounts();
            $io->writeln("找到 " . count($accounts) . " 个在线账号");
        } else {
            $accounts = $this->accountRepository->findAllValidAccounts();
            $io->writeln("找到 " . count($accounts) . " 个有效账号");
        }

        if ((bool) empty($accounts)) {
            $io->warning('没有找到可同步的账号');
            return Command::SUCCESS;
        }

        $io->progressStart(count($accounts));

        $successCount = 0;
        $failureCount = 0;
        $batches = array_chunk($accounts, $batchSize);

        foreach ($batches as $batchIndex => $batch) {
            $io->section("处理第 " . ($batchIndex + 1) . " 批账号（共 " . count($batches) . " 批）");

            foreach ($batch as $account) {
                try {
                    $io->writeln("同步账号：{$account->getNickname()} ({$account->getWechatId()})");

                    // 检查账号状态
                    if (!$force && !$account->isOnline()) {
                        $io->writeln("  账号离线，跳过同步");
                        $io->progressAdvance();
                        continue;
                    }

                    // 执行同步
                    $success = $this->contactService->syncContacts($account);

                    $io->writeln($success ? "  同步完成" : "  同步可能有部分失败");
                    $successCount++;

                    // 请求间隔
                    if ($delay > 0) {
                        sleep($delay);
                    }
                } catch (\Exception $e) {
                    $io->writeln("  <error>同步失败：{$e->getMessage()}</error>");
                    $failureCount++;
                } finally {
                    $io->progressAdvance();
                }
            }

            // 批次间隔
            if ($batchIndex < count($batches) - 1 && $delay > 0) {
                $io->writeln("批次间等待 " . ($delay * 2) . " 秒...");
                sleep($delay * 2);
            }
        }

        $io->progressFinish();

        // 显示统计结果
        $io->success([
            '联系人同步完成',
            "成功同步：{$successCount} 个账号",
            "同步失败：{$failureCount} 个账号",
            "总计处理：" . count($accounts) . " 个账号"
        ]);

        return $failureCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}

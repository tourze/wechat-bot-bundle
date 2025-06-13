<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\WechatBotBundle\Repository\WeChatAccountRepository;
use Tourze\WechatBotBundle\Service\WeChatAccountService;

/**
 * 检查微信账号在线状态命令
 * 
 * 定期检查所有微信账号的在线状态，用于：
 * - 及时发现掉线账号
 * - 更新账号状态信息
 * - 记录在线状态变化日志
 * - 发送状态变化通知
 * 
 * @author AI Assistant
 */
#[AsCommand(
    name: 'wechat-bot:check-online-status',
    description: '检查所有微信账号的在线状态'
)]
class CheckOnlineStatusCommand extends Command
{
    public function __construct(
        private readonly WeChatAccountRepository $accountRepository,
        private readonly WeChatAccountService $accountService,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('检查所有微信账号的在线状态')
            ->setHelp('此命令检查数据库中所有微信账号的在线状态，更新状态信息并记录日志。建议每5-10分钟执行一次。')
            ->addOption(
                'only-online',
                'o',
                InputOption::VALUE_NONE,
                '只检查当前状态为在线的账号'
            )
            ->addOption(
                'account-id',
                'a',
                InputOption::VALUE_OPTIONAL,
                '只检查指定ID的账号'
            )
            ->addOption(
                'timeout',
                't',
                InputOption::VALUE_OPTIONAL,
                '设置检查超时时间（秒）',
                30
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $startTime = microtime(true);

        $io->title('微信账号在线状态检查');

        try {
            // 获取要检查的账号列表
            $accounts = $this->getAccountsToCheck($input);
            
            if (empty($accounts)) {
                $io->info('没有找到需要检查的账号');
                return Command::SUCCESS;
            }

            $io->info(sprintf('开始检查 %d 个账号的在线状态...', count($accounts)));

            // 统计数据
            $stats = [
                'total' => count($accounts),
                'online' => 0,
                'offline' => 0,
                'pending' => 0,
                'expired' => 0,
                'errors' => 0,
                'status_changed' => 0
            ];

            // 逐个检查账号状态
            foreach ($accounts as $account) {
                $this->checkAccountStatus($account, $io, $stats);
            }

            // 输出统计结果
            $this->outputStatistics($io, $stats, $startTime);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->logger->error('Check online status command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $io->error('检查在线状态失败: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * 获取要检查的账号列表
     */
    private function getAccountsToCheck(InputInterface $input): array
    {
        $accountId = $input->getOption('account-id');
        $onlyOnline = $input->getOption('only-online');

        if ($accountId) {
            // 检查指定账号
            $account = $this->accountRepository->find($accountId);
            return $account ? [$account] : [];
        }

        if ($onlyOnline) {
            // 只检查在线账号
            return $this->accountRepository->findBy(['status' => 'online']);
        }

        // 检查所有有效账号（排除已删除的）
        return $this->accountRepository->findActiveAccounts();
    }

    /**
     * 检查单个账号状态
     */
    private function checkAccountStatus($account, SymfonyStyle $io, array &$stats): void
    {
        $accountId = $account->getId();
        $deviceId = $account->getDeviceId();
        $previousStatus = $account->getStatus();

        try {
            $io->writeln(sprintf(
                '检查账号 [%d] %s (设备ID: %s, 当前状态: %s)',
                $accountId,
                $account->getWechatId() ?: '未登录',
                $deviceId,
                $previousStatus
            ));

            // 检查在线状态
            $deviceStatus = $this->accountService->checkOnlineStatus($account);
            $currentStatus = $account->getStatus(); // 服务已更新状态

            // 统计状态
            $this->updateStats($stats, $currentStatus);

            // 检查状态是否变化
            if ($previousStatus !== $currentStatus) {
                $stats['status_changed']++;
                
                $io->writeln(sprintf(
                    '  ✓ 状态变化: %s → %s',
                    $previousStatus,
                    $currentStatus
                ), OutputInterface::VERBOSITY_VERBOSE);

                $this->logger->info('WeChat account status changed', [
                    'accountId' => $accountId,
                    'deviceId' => $deviceId,
                    'previousStatus' => $previousStatus,
                    'currentStatus' => $currentStatus,
                    'isOnline' => $deviceStatus->isOnline,
                    'lastActiveTime' => $deviceStatus->lastActiveTime?->format('Y-m-d H:i:s')
                ]);
            } else {
                $io->writeln(sprintf(
                    '  状态未变化: %s (在线: %s)',
                    $currentStatus,
                    $deviceStatus->isOnline ? '是' : '否'
                ), OutputInterface::VERBOSITY_VERBOSE);
            }

        } catch (\Exception $e) {
            $stats['errors']++;
            
            $io->writeln(sprintf(
                '  ✗ 检查失败: %s',
                $e->getMessage()
            ));

            $this->logger->error('Failed to check account status', [
                'accountId' => $accountId,
                'deviceId' => $deviceId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 更新统计数据
     */
    private function updateStats(array &$stats, string $status): void
    {
        switch ($status) {
            case 'online':
                $stats['online']++;
                break;
            case 'offline':
                $stats['offline']++;
                break;
            case 'pending_login':
                $stats['pending']++;
                break;
            case 'expired':
                $stats['expired']++;
                break;
        }
    }

    /**
     * 输出统计结果
     */
    private function outputStatistics(SymfonyStyle $io, array $stats, float $startTime): void
    {
        $duration = round(microtime(true) - $startTime, 2);

        $io->section('检查结果统计');

        $table = $io->createTable();
        $table->setHeaders(['状态', '数量', '百分比']);
        
        $total = $stats['total'];
        if ($total > 0) {
            $table->addRows([
                ['在线', $stats['online'], sprintf('%.1f%%', ($stats['online'] / $total) * 100)],
                ['离线', $stats['offline'], sprintf('%.1f%%', ($stats['offline'] / $total) * 100)],
                ['等待登录', $stats['pending'], sprintf('%.1f%%', ($stats['pending'] / $total) * 100)],
                ['已过期', $stats['expired'], sprintf('%.1f%%', ($stats['expired'] / $total) * 100)],
                new TableSeparator(),
                ['检查失败', $stats['errors'], sprintf('%.1f%%', ($stats['errors'] / $total) * 100)],
                ['状态变化', $stats['status_changed'], sprintf('%.1f%%', ($stats['status_changed'] / $total) * 100)],
            ]);
        }

        $table->render();

        $io->success(sprintf(
            '检查完成! 总计: %d 个账号, 耗时: %s 秒',
            $total,
            $duration
        ));

        // 记录汇总日志
        $this->logger->info('Online status check completed', [
            'stats' => $stats,
            'duration' => $duration
        ]);
    }
} 
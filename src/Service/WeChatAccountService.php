<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\DTO\WeChatDeviceStatus;
use Tourze\WechatBotBundle\DTO\WeChatLoginResult;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Exception\InvalidArgumentException;
use Tourze\WechatBotBundle\Exception\LoginException;
use Tourze\WechatBotBundle\Repository\WeChatAccountRepository;
use Tourze\WechatBotBundle\Request\CheckOnlineStatusRequest;
use Tourze\WechatBotBundle\Request\ConfirmLoginRequest;
use Tourze\WechatBotBundle\Request\CreateDeviceRequest;
use Tourze\WechatBotBundle\Request\GetLoginQrCodeRequest;
use Tourze\WechatBotBundle\Request\InitContactListRequest;
use Tourze\WechatBotBundle\Request\LogoutRequest;
use Tourze\WechatBotBundle\Request\SetDeviceProxyRequest;

/**
 * 微信账号管理服务
 *
 * 提供微信账号的完整生命周期管理，包括：
 * - 设备创建和登录流程
 * - 在线状态监控和管理
 * - 账号信息同步
 * - 登录状态维护
 *
 * @author AI Assistant
 */
class WeChatAccountService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WeChatApiClient $apiClient,
        private readonly WeChatAccountRepository $accountRepository,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * 为现有账号重新开始登录流程
     */
    public function startLogin(
        WeChatAccount $account,
        string $province = '广东',
        string $city = '深圳', 
        ?string $proxy = null
    ): WeChatLoginResult {
        try {
            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();

            // 设置代理（如果提供）
            if ((bool) $proxy) {
                $this->setDeviceProxy($apiAccount, $deviceId, $proxy);
                $account->setProxy($proxy);
            }

            // 获取登录二维码
            $qrCodeRequest = new GetLoginQrCodeRequest($apiAccount, $deviceId, $province, $city);
            $qrCodeResponse = $this->apiClient->request($qrCodeRequest);

            if (!isset($qrCodeResponse['data']['qrCodeUrl'])) {
                throw new LoginException('Failed to get QR code URL');
            }

            // 更新账号信息
            $account->setQrCodeUrl($qrCodeResponse['data']['qrCodeUrl'])
                ->setStatus('pending_login');

            $this->entityManager->flush();

            $this->logger->info('WeChat account login QR code regenerated', [
                'accountId' => $account->getId(),
                'deviceId' => $deviceId
            ]);

            return new WeChatLoginResult(
                account: $account,
                qrCodeUrl: $qrCodeResponse['data']['qrCodeUrl'],
                success: true,
                message: '二维码生成成功，请扫码登录'
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to start login for existing account', [
                'accountId' => $account->getId(),
                'error' => $e->getMessage()
            ]);

            return new WeChatLoginResult(
                account: $account,
                qrCodeUrl: null,
                success: false,
                message: '生成二维码失败：' . $e->getMessage()
            );
        }
    }

    /**
     * 创建新的微信设备并开始登录流程
     */
    public function createDeviceAndStartLogin(
        WeChatApiAccount $apiAccount,
        ?string $remark = null,
        ?string $proxy = null,
        string $province = '北京',
        ?string $city = null
    ): WeChatLoginResult {
        try {
            // 生成唯一设备ID
            $deviceId = $this->generateDeviceId();

            // 1. 创建设备
            $createDeviceRequest = new CreateDeviceRequest($apiAccount, $deviceId);
            $createResponse = $this->apiClient->request($createDeviceRequest);

            $this->logger->info('WeChat device created', [
                'deviceId' => $deviceId,
                'apiAccount' => $apiAccount->getName()
            ]);

            // 2. 设置代理（如果提供）
            if ((bool) $proxy) {
                $this->setDeviceProxy($apiAccount, $deviceId, $proxy);
            }

            // 3. 获取登录二维码
            $qrCodeRequest = new GetLoginQrCodeRequest($apiAccount, $deviceId, $province, $city);
            $qrCodeResponse = $this->apiClient->request($qrCodeRequest);

            if (!isset($qrCodeResponse['data']['qrCodeUrl'])) {
                throw new LoginException('Failed to get QR code URL');
            }

            // 4. 创建账号记录
            $account = new WeChatAccount();
            $account->setApiAccount($apiAccount)
                ->setDeviceId($deviceId)
                ->setQrCodeUrl($qrCodeResponse['data']['qrCodeUrl'])
                ->setStatus('pending_login')
                ->setProxy($proxy)
                ->setRemark($remark);

            $this->entityManager->persist($account);
            $this->entityManager->flush();

            $this->logger->info('WeChat account created and QR code generated', [
                'accountId' => $account->getId(),
                'deviceId' => $deviceId
            ]);

            return new WeChatLoginResult(
                account: $account,
                qrCodeUrl: $qrCodeResponse['data']['qrCodeUrl'],
                success: true,
                message: '设备创建成功，请扫码登录'
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to create device and start login', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return new WeChatLoginResult(
                account: null,
                qrCodeUrl: null,
                success: false,
                message: '创建设备失败：' . $e->getMessage()
            );
        }
    }

    /**
     * 确认登录状态
     */
    public function confirmLogin(WeChatAccount $account): WeChatLoginResult
    {
        try {
            $confirmRequest = new ConfirmLoginRequest($account->getApiAccount(), $account->getDeviceId());
            $response = $this->apiClient->request($confirmRequest);

            if ((bool) isset($response['data']['login']) && $response['data']['login'] === true) {
                // 登录成功，更新账号信息
                $this->updateAccountFromLoginResponse($account, $response['data']);

                $this->logger->info('WeChat account login confirmed', [
                    'accountId' => $account->getId(),
                    'deviceId' => $account->getDeviceId(),
                    'wechatId' => $account->getWechatId()
                ]);

                return new WeChatLoginResult(
                    account: $account,
                    qrCodeUrl: null,
                    success: true,
                    message: '登录成功'
                );
            }

            return new WeChatLoginResult(
                account: $account,
                qrCodeUrl: $account->getQrCodeUrl(),
                success: false,
                message: '等待扫码登录'
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to confirm login', [
                'accountId' => $account->getId(),
                'error' => $e->getMessage()
            ]);

            return new WeChatLoginResult(
                account: $account,
                qrCodeUrl: $account->getQrCodeUrl(),
                success: false,
                message: '确认登录失败：' . $e->getMessage()
            );
        }
    }

    /**
     * 检查在线状态
     */
    public function checkOnlineStatus(WeChatAccount $account): WeChatDeviceStatus
    {
        try {
            $statusRequest = new CheckOnlineStatusRequest($account->getApiAccount(), $account->getDeviceId());
            $response = $this->apiClient->request($statusRequest);

            $isOnline = isset($response['data']['online']) && $response['data']['online'] === true;
            $status = $isOnline ? 'online' : 'offline';

            // 更新账号状态
            if ($account->getStatus() !== $status) {
                $account->setStatus($status);
                if ((bool) $isOnline) {
                    $account->updateLastActiveTime();
                }
                $this->entityManager->flush();
            }

            return new WeChatDeviceStatus(
                deviceId: $account->getDeviceId(),
                isOnline: $isOnline,
                status: $status,
                lastActiveTime: $account->getLastActiveTime()
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to check online status', [
                'accountId' => $account->getId(),
                'error' => $e->getMessage()
            ]);

            // 检查失败时标记为离线
            $account->markAsOffline();
            $this->entityManager->flush();

            return new WeChatDeviceStatus(
                deviceId: $account->getDeviceId(),
                isOnline: false,
                status: 'offline',
                lastActiveTime: $account->getLastActiveTime(),
                error: $e->getMessage()
            );
        }
    }

    /**
     * 初始化联系人列表
     */
    public function initContactList(WeChatAccount $account): bool
    {
        try {
            $initRequest = new InitContactListRequest($account->getApiAccount(), $account->getDeviceId());
            $response = $this->apiClient->request($initRequest);

            $this->logger->info('Contact list initialization started', [
                'accountId' => $account->getId(),
                'deviceId' => $account->getDeviceId()
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to init contact list', [
                'accountId' => $account->getId(),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * 退出登录
     */
    public function logout(WeChatAccount $account): bool
    {
        try {
            $logoutRequest = new LogoutRequest($account->getApiAccount(), $account->getDeviceId());
            $response = $this->apiClient->request($logoutRequest);

            // 更新账号状态
            $account->markAsOffline();
            $this->entityManager->flush();

            $this->logger->info('WeChat account logged out', [
                'accountId' => $account->getId(),
                'deviceId' => $account->getDeviceId()
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to logout', [
                'accountId' => $account->getId(),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * 设置设备代理
     */
    public function setDeviceProxy(WeChatApiAccount $apiAccount, string $deviceId, string $proxy): bool
    {
        try {
            // 解析代理格式：host:port:username:password
            $proxyParts = explode(':', $proxy);
            if ((bool) count($proxyParts) < 2) {
                throw new InvalidArgumentException('Invalid proxy format, expected: host:port[:username:password]');
            }

            $proxyIp = $proxyParts[0] . ':' . $proxyParts[1];
            $proxyRequest = new SetDeviceProxyRequest(
                $apiAccount,
                $deviceId,
                $proxyIp,
                $proxyParts[2] ?? null,
                $proxyParts[3] ?? null
            );

            $response = $this->apiClient->request($proxyRequest);

            $this->logger->info('Device proxy set', [
                'deviceId' => $deviceId,
                'proxyIp' => $proxyIp
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to set device proxy', [
                'deviceId' => $deviceId,
                'proxy' => $proxy,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * 批量检查所有账号的在线状态
     */
    public function checkAllAccountsStatus(): array
    {
        $accounts = $this->accountRepository->findValid();
        $results = [];

        foreach ($accounts as $account) {
            $results[$account->getId()] = $this->checkOnlineStatus($account);
        }

        return $results;
    }

    /**
     * 获取账号统计信息
     */
    public function getAccountsStatistics(): array
    {
        $statusCounts = $this->accountRepository->countByStatus();

        return [
            'total' => array_sum($statusCounts),
            'online' => $statusCounts['online'] ?? 0,
            'offline' => $statusCounts['offline'] ?? 0,
            'pending_login' => $statusCounts['pending_login'] ?? 0,
            'expired' => $statusCounts['expired'] ?? 0,
        ];
    }

    /**
     * 生成唯一设备ID
     */
    private function generateDeviceId(): string
    {
        $timestamp = time();
        $random = random_int(1000, 9999);
        return sprintf('device_%d_%d', $timestamp, $random);
    }

    /**
     * 从登录响应更新账号信息
     */
    private function updateAccountFromLoginResponse(WeChatAccount $account, array $data): void
    {
        if ((bool) isset($data['wxId'])) {
            $account->setWechatId($data['wxId']);
        }

        if ((bool) isset($data['nickname'])) {
            $account->setNickname($data['nickname']);
        }

        if ((bool) isset($data['avatar'])) {
            $account->setAvatar($data['avatar']);
        }

        $account->markAsOnline()
            ->setLastLoginTime(new \DateTime())
            ->updateLastActiveTime();

        $this->entityManager->flush();
    }

    public function __toString(): string
    {
        return 'WeChatAccountService';
    }
}

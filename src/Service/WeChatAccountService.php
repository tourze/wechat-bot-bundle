<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
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
#[WithMonologChannel(channel: 'wechat_bot')]
#[Autoconfigure(public: true)]
readonly class WeChatAccountService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WeChatApiClient $apiClient,
        private WeChatAccountRepository $accountRepository,
        private LoggerInterface $logger,
        private WeChatDeviceProxyManager $proxyManager,
    ) {
    }

    /**
     * 为现有账号重新开始登录流程
     */
    public function startLogin(
        WeChatAccount $account,
        string $province = '广东',
        string $city = '深圳',
        ?string $proxy = null,
    ): WeChatLoginResult {
        try {
            [$apiAccount, $deviceId] = $this->validateAccount($account);

            // 设置代理（如果提供）
            if (null !== $proxy && '' !== $proxy) {
                $this->applyDeviceProxy($apiAccount, $deviceId, $proxy);
                $account->setProxy($proxy);
            }

            // 获取登录二维码
            $qrCodeRequest = new GetLoginQrCodeRequest($apiAccount, $deviceId, $province, $city);
            $qrCodeResponse = $this->apiClient->request($qrCodeRequest);

            // 验证响应类型
            if (!is_array($qrCodeResponse)) {
                throw new LoginException('API response must be an array');
            }

            /** @var array<string, mixed> $qrCodeResponse */

            // 提取二维码URL
            $qrCodeUrl = $this->extractQrCodeUrl($qrCodeResponse);

            // 更新账号信息
            $account->setQrCodeUrl($qrCodeUrl);
            $this->updateAccountStatus($account, 'pending_login');

            $this->logger->info('WeChat account login QR code regenerated', [
                'accountId' => $account->getId(),
                'deviceId' => $deviceId,
            ]);

            return $this->createSuccessLoginResult($account, $qrCodeUrl, '二维码生成成功，请扫码登录');
        } catch (\Exception $e) {
            $this->logger->error('Failed to start login for existing account', [
                'accountId' => $account->getId(),
                'error' => $e->getMessage(),
            ]);

            return $this->createFailureLoginResult($account, null, '生成二维码失败：' . $e->getMessage());
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
        ?string $city = null,
    ): WeChatLoginResult {
        try {
            // 生成唯一设备ID
            $deviceId = $this->generateDeviceId();

            // 1. 创建设备
            $createDeviceRequest = new CreateDeviceRequest($apiAccount, $deviceId);
            $createResponse = $this->apiClient->request($createDeviceRequest);

            $this->logger->info('WeChat device created', [
                'deviceId' => $deviceId,
                'apiAccount' => $apiAccount->getName(),
            ]);

            // 2. 设置代理（如果提供）
            if (null !== $proxy && '' !== $proxy) {
                $this->applyDeviceProxy($apiAccount, $deviceId, $proxy);
            }

            // 3. 获取登录二维码
            $qrCodeRequest = new GetLoginQrCodeRequest($apiAccount, $deviceId, $province, $city);
            $qrCodeResponse = $this->apiClient->request($qrCodeRequest);

            // 验证响应数据类型
            if (!is_array($qrCodeResponse) || !isset($qrCodeResponse['data']) || !is_array($qrCodeResponse['data']) || !isset($qrCodeResponse['data']['qrCodeUrl'])) {
                throw new LoginException('Failed to get QR code URL');
            }

            /** @var array<string, mixed> $qrCodeResponse */

            // 4. 提取二维码URL并创建账号记录
            $qrCodeUrl = $this->extractQrCodeUrl($qrCodeResponse);
            $account = $this->createWeChatAccount($apiAccount, $deviceId, $qrCodeUrl, $proxy, $remark);

            $this->logger->info('WeChat account created and QR code generated', [
                'accountId' => $account->getId(),
                'deviceId' => $deviceId,
            ]);

            return $this->createSuccessLoginResult($account, $qrCodeUrl, '设备创建成功，请扫码登录');
        } catch (\Exception $e) {
            $this->logger->error('Failed to create device and start login', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->createFailureLoginResult(null, null, '创建设备失败：' . $e->getMessage());
        }
    }

    /**
     * 确认登录状态
     */
    public function confirmLogin(WeChatAccount $account): WeChatLoginResult
    {
        try {
            [$apiAccount, $deviceId] = $this->validateAccount($account);

            $confirmRequest = new ConfirmLoginRequest($apiAccount, $deviceId);
            $response = $this->apiClient->request($confirmRequest);

            $responseData = $this->validateApiResponse($response);
            if (isset($responseData['login']) && true === $responseData['login']) {
                // 登录成功，更新账号信息
                $this->updateAccountFromLoginResponse($account, $responseData);

                $this->logger->info('WeChat account login confirmed', [
                    'accountId' => $account->getId(),
                    'deviceId' => $account->getDeviceId(),
                    'wechatId' => $account->getWechatId(),
                ]);

                return $this->createSuccessLoginResult($account, null, '登录成功');
            }

            return $this->createFailureLoginResult($account, $account->getQrCodeUrl(), '等待扫码登录');
        } catch (\Exception $e) {
            $this->logger->error('Failed to confirm login', [
                'accountId' => $account->getId(),
                'error' => $e->getMessage(),
            ]);

            return $this->createFailureLoginResult($account, $account->getQrCodeUrl(), '确认登录失败：' . $e->getMessage());
        }
    }

    /**
     * 检查在线状态
     */
    public function checkOnlineStatus(WeChatAccount $account): WeChatDeviceStatus
    {
        [$apiAccount, $deviceId] = $this->validateAccount($account);

        try {
            $statusRequest = new CheckOnlineStatusRequest($apiAccount, $deviceId);
            $response = $this->apiClient->request($statusRequest);

            $responseData = $this->validateApiResponse($response);
            $isOnline = isset($responseData['online']) && true === $responseData['online'];
            $status = $isOnline ? 'online' : 'offline';

            // 更新账号状态
            if ($account->getStatus() !== $status) {
                $account->setStatus($status);
                if ($isOnline) {
                    $account->updateLastActiveTime();
                }
                $this->entityManager->flush();
            }

            return new WeChatDeviceStatus(
                deviceId: $deviceId,
                isOnline: $isOnline,
                status: $status,
                lastActiveTime: $account->getLastActiveTime()
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to check online status', [
                'accountId' => $account->getId(),
                'error' => $e->getMessage(),
            ]);

            // 检查失败时标记为离线
            $account->markAsOffline();
            $this->entityManager->flush();

            return new WeChatDeviceStatus(
                deviceId: $deviceId,
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
            [$apiAccount, $deviceId] = $this->validateAccount($account);

            $initRequest = new InitContactListRequest($apiAccount, $deviceId);
            $response = $this->apiClient->request($initRequest);

            $this->logger->info('Contact list initialization started', [
                'accountId' => $account->getId(),
                'deviceId' => $account->getDeviceId(),
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to init contact list', [
                'accountId' => $account->getId(),
                'error' => $e->getMessage(),
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
            [$apiAccount, $deviceId] = $this->validateAccount($account);

            $logoutRequest = new LogoutRequest($apiAccount, $deviceId);
            $response = $this->apiClient->request($logoutRequest);

            // 更新账号状态
            $account->markAsOffline();
            $this->entityManager->flush();

            $this->logger->info('WeChat account logged out', [
                'accountId' => $account->getId(),
                'deviceId' => $account->getDeviceId(),
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to logout', [
                'accountId' => $account->getId(),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * 设置设备代理
     */
    public function applyDeviceProxy(WeChatApiAccount $apiAccount, string $deviceId, string $proxy): bool
    {
        return $this->proxyManager->applyDeviceProxy($apiAccount, $deviceId, $proxy);
    }

    /**
     * 批量检查所有账号的在线状态
     */
    /**
     * @return array<int, WeChatDeviceStatus>
     */
    public function checkAllAccountsStatus(): array
    {
        $accounts = $this->accountRepository->findValid();
        $results = [];

        foreach ($accounts as $account) {
            $id = $account->getId();
            // 确保ID不为null，新创建的实体可能还没有ID
            if (null !== $id) {
                $results[$id] = $this->checkOnlineStatus($account);
            }
        }

        return $results;
    }

    /**
     * 获取账号统计信息
     */
    /**
     * @return array<string, mixed>
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
     *
     * @param array<string, mixed> $data
     */
    private function updateAccountFromLoginResponse(WeChatAccount $account, array $data): void
    {
        if (isset($data['wxId']) && is_string($data['wxId'])) {
            $account->setWechatId($data['wxId']);
        }

        if (isset($data['nickname']) && is_string($data['nickname'])) {
            $account->setNickname($data['nickname']);
        }

        if (isset($data['avatar']) && is_string($data['avatar'])) {
            $account->setAvatar($data['avatar']);
        }

        $account->markAsOnline();
        $account->setLastLoginTime(new \DateTimeImmutable());
        $account->updateLastActiveTime();

        $this->entityManager->flush();
    }

    /**
     * 执行账号操作前的统一验证
     *
     * @return array{0: WeChatApiAccount, 1: string}
     */
    private function validateAccount(WeChatAccount $account): array
    {
        $apiAccount = $account->getApiAccount();
        $deviceId = $account->getDeviceId();

        if (null === $apiAccount) {
            throw new InvalidArgumentException('API账号不能为null');
        }

        if (null === $deviceId) {
            throw new InvalidArgumentException('设备ID不能为null');
        }

        return [$apiAccount, $deviceId];
    }

    
    /**
     * 从二维码响应中提取URL
     *
     * @param array<string, mixed> $response
     */
    private function extractQrCodeUrl(array $response): string
    {
        if (!isset($response['data']) || !is_array($response['data']) || !isset($response['data']['qrCodeUrl'])) {
            throw new LoginException('Failed to get QR code URL');
        }

        /** @var array<string, mixed> $responseData */
        $responseData = $response['data'];
        $qrCodeUrl = $responseData['qrCodeUrl'];

        if (!is_string($qrCodeUrl)) {
            throw new LoginException('Invalid QR code URL format');
        }

        return $qrCodeUrl;
    }

    /**
     * 更新账号状态并持久化
     */
    private function updateAccountStatus(WeChatAccount $account, string $status, bool $flush = true): void
    {
        $account->setStatus($status);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    
    public function __toString(): string
    {
        return 'WeChatAccountService';
    }

    /**
     * 验证API响应数据格式
     *
     * @param mixed $response
     * @return array<string, mixed>
     */
    private function validateApiResponse($response): array
    {
        if (!is_array($response) || !isset($response['data']) || !is_array($response['data'])) {
            throw new InvalidArgumentException('Invalid API response format');
        }

        /** @var array<string, mixed> $responseData */
        $responseData = $response['data'];
        return $responseData;
    }

    /**
     * 创建新的微信账号记录
     */
    private function createWeChatAccount(
        WeChatApiAccount $apiAccount,
        string $deviceId,
        string $qrCodeUrl,
        ?string $proxy = null,
        ?string $remark = null
    ): WeChatAccount {
        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId($deviceId);
        $account->setQrCodeUrl($qrCodeUrl);
        $account->setStatus('pending_login');
        $account->setProxy($proxy);
        $account->setRemark($remark);

        $this->entityManager->persist($account);
        $this->entityManager->flush();

        return $account;
    }

    /**
     * 创建成功的登录结果
     */
    private function createSuccessLoginResult(WeChatAccount $account, ?string $qrCodeUrl, string $message): WeChatLoginResult
    {
        return new WeChatLoginResult(
            account: $account,
            qrCodeUrl: $qrCodeUrl,
            success: true,
            message: $message
        );
    }

    /**
     * 创建失败的登录结果
     */
    private function createFailureLoginResult(?WeChatAccount $account, ?string $qrCodeUrl, string $message): WeChatLoginResult
    {
        return new WeChatLoginResult(
            account: $account,
            qrCodeUrl: $qrCodeUrl,
            success: false,
            message: $message
        );
    }
}

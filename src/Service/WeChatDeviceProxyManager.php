<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Service;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Exception\InvalidArgumentException;
use Tourze\WechatBotBundle\Request\SetDeviceProxyRequest;

/**
 * 微信设备代理管理服务
 *
 * 负责代理配置的解析、验证和设置：
 * - 代理格式解析（host:port:username:password）
 * - 代理配置验证（host/port/凭证）
 * - 设备代理应用
 *
 * @author AI Assistant
 */
#[WithMonologChannel(channel: 'wechat_bot')]
#[Autoconfigure(public: true)]
readonly class WeChatDeviceProxyManager
{
    public function __construct(
        private WeChatApiClient $apiClient,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * 设置设备代理
     */
    public function applyDeviceProxy(WeChatApiAccount $apiAccount, string $deviceId, string $proxy): bool
    {
        try {
            $this->validateProxyConfiguration($proxy);

            // 移除 scheme（如 http:// 或 https://）
            $cleanProxy = preg_replace('|^https?://|i', '', $proxy);
            if (null === $cleanProxy) {
                throw new InvalidArgumentException('Invalid proxy format: regex error');
            }

            $proxyParts = explode(':', $cleanProxy);

            $host = $proxyParts[0];
            $port = (int) $proxyParts[1];
            $proxyIp = $host . ':' . $port;

            $proxyRequest = new SetDeviceProxyRequest(
                $apiAccount,
                $deviceId,
                $proxyIp,
                $proxyParts[2] ?? null,
                $proxyParts[3] ?? null
            );

            $this->apiClient->request($proxyRequest);

            $this->logger->info('Device proxy set', [
                'deviceId' => $deviceId,
                'proxyHost' => $host,
                'proxyPort' => $port,
            ]);

            return true;
        } catch (InvalidArgumentException $e) {
            $this->logger->warning('Invalid proxy configuration', [
                'deviceId' => $deviceId,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            $this->logger->error('Failed to set device proxy', [
                'deviceId' => $deviceId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * 验证代理配置格式和值的有效性
     *
     * @throws InvalidArgumentException 当代理配置无效时
     */
    private function validateProxyConfiguration(string $proxy): void
    {
        // 移除 scheme（如 http:// 或 https://）
        $cleanProxy = preg_replace('|^https?://|i', '', $proxy);
        if (null === $cleanProxy) {
            throw new InvalidArgumentException('Invalid proxy format: regex error');
        }

        // 解析代理格式：host:port:username:password
        $proxyParts = explode(':', $cleanProxy);
        if (count($proxyParts) < 2) {
            throw new InvalidArgumentException('Invalid proxy format, expected: [scheme://]host:port[:username:password]');
        }

        $this->validateProxyHost($proxyParts[0]);
        $this->validateProxyPort((int) $proxyParts[1]);
        $this->validateProxyCredentials($proxyParts);
    }

    /**
     * 验证代理 host 有效性
     *
     * @throws InvalidArgumentException 当代理 host 无效时
     */
    private function validateProxyHost(string $host): void
    {
        if ('' === $host || strlen($host) > 255) {
            throw new InvalidArgumentException('Invalid proxy host: empty or too long');
        }

        if (!$this->isValidProxyHost($host)) {
            throw new InvalidArgumentException(sprintf('Invalid proxy host format: %s', $host));
        }
    }

    /**
     * 验证代理端口有效性
     *
     * @throws InvalidArgumentException 当代理端口无效时
     */
    private function validateProxyPort(int $port): void
    {
        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException(sprintf('Invalid proxy port: %d (must be 1-65535)', $port));
        }
    }

    /**
     * 验证代理凭证（用户名和密码）有效性
     *
     * @param array<string> $proxyParts 代理配置部分
     *
     * @throws InvalidArgumentException 当凭证无效时
     */
    private function validateProxyCredentials(array $proxyParts): void
    {
        if (isset($proxyParts[2]) && '' !== $proxyParts[2]) {
            if (strlen($proxyParts[2]) > 255) {
                throw new InvalidArgumentException('Proxy username too long');
            }
        }
        if (isset($proxyParts[3]) && '' !== $proxyParts[3]) {
            if (strlen($proxyParts[3]) > 255) {
                throw new InvalidArgumentException('Proxy password too long');
            }
        }
    }

    /**
     * 验证代理 host 格式（IPv4 / IPv6 / 域名）
     */
    private function isValidProxyHost(string $host): bool
    {
        // IPv4 验证
        $ipv4Check = filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        if (false !== $ipv4Check) {
            return true;
        }

        // IPv6 验证
        $ipv6Check = filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        if (false !== $ipv6Check) {
            return true;
        }

        // 域名验证：仅允许字母、数字、点、连字符
        $domainMatches = preg_match('/^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/i', $host);
        if (1 === $domainMatches) {
            return true;
        }

        return false;
    }
}

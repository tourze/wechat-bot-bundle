<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Service;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\DTO\FileDownloadResult;
use Tourze\WechatBotBundle\DTO\FileInfo;
use Tourze\WechatBotBundle\DTO\FileStorageStats;
use Tourze\WechatBotBundle\DTO\FileUploadResult;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Exception\InvalidArgumentException;
use Tourze\WechatBotBundle\Request\File\DownloadCdnResourceRequest;
use Tourze\WechatBotBundle\Request\Upload\UploadImageToCdnRequest;

/**
 * 微信文件管理服务
 *
 * 提供文件上传、下载、管理等业务功能
 */
#[WithMonologChannel(channel: 'wechat_bot')]
#[Autoconfigure(public: true)]
readonly class WeChatFileService
{
    public function __construct(
        private WeChatApiClient $apiClient,
        private LoggerInterface $logger,
        private string $fileStoragePath = '/tmp/wechat_files',
    ) {
        // 确保存储目录存在
        if (!is_dir($this->fileStoragePath)) {
            mkdir($this->fileStoragePath, 0o755, true);
        }
    }

    /**
     * 下载CDN资源
     */
    public function downloadCdnResource(WeChatAccount $account, string $cdnUrl, string $fileName = ''): ?FileDownloadResult
    {
        try {
            $this->validateAccount($account);
            [$apiAccount, $deviceId] = $this->requireApiAndDevice($account);
            $request = new DownloadCdnResourceRequest($apiAccount, $deviceId, $cdnUrl);
            $data = $this->apiClient->request($request);

            // 验证API响应类型
            if (!is_array($data)) {
                throw new \InvalidArgumentException('Invalid API response format: expected array, got ' . gettype($data));
            }

            $content = is_string($data['content'] ?? null) ? $data['content'] : '';
            $originalName = is_string($data['file_name'] ?? null) ? $data['file_name'] : $fileName;
            $size = is_int($data['file_size'] ?? null) ? $data['file_size'] : 0;
            $mimeType = is_string($data['mime_type'] ?? null) ? $data['mime_type'] : 'application/octet-stream';

            // 生成本地文件路径
            $localFileName = $this->generateLocalFileName($originalName, 'cdn');
            $localPath = $this->fileStoragePath . '/cdn/' . $localFileName;

            // 确保CDN目录存在
            $cdnDir = dirname($localPath);
            if (!is_dir($cdnDir)) {
                mkdir($cdnDir, 0o755, true);
            }

            // 保存文件到本地
            if (false === file_put_contents($localPath, base64_decode($content, true))) {
                $this->logger->error('保存CDN资源到本地失败', [
                    'device_id' => $deviceId,
                    'local_path' => $localPath,
                ]);

                return null;
            }

            $this->logger->info('下载CDN资源成功', [
                'device_id' => $deviceId,
                'cdn_url' => $cdnUrl,
                'local_path' => $localPath,
                'file_size' => $size,
            ]);

            return new FileDownloadResult(
                $localPath,
                $originalName,
                $size,
                $mimeType,
                $cdnUrl
            );
        } catch (\Exception $e) {
            $this->logger->error('下载CDN资源异常', [
                'device_id' => $account->getDeviceId() ?? '',
                'cdn_url' => $cdnUrl,
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * 生成本地文件名
     */
    private function generateLocalFileName(string $originalName, string $type = 'file'): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $timestamp = time();
        $random = substr(md5(uniqid()), 0, 8);

        return "{$type}_{$timestamp}_{$random}" . ('' !== $extension ? ".{$extension}" : '');
    }

    /**
     * 上传图片到CDN
     */
    public function uploadImageToCdn(WeChatAccount $account, string $imageFilePath): ?FileUploadResult
    {
        try {
            $this->validateAccount($account);
            [$apiAccount, $deviceId] = $this->requireApiAndDevice($account);
            $request = new UploadImageToCdnRequest($apiAccount, $deviceId, $imageFilePath);
            $data = $this->apiClient->request($request);

            // 验证API响应类型
            if (!is_array($data)) {
                throw new \InvalidArgumentException('Invalid API response format: expected array, got ' . gettype($data));
            }

            $cdnUrl = is_string($data['cdn_url'] ?? null) ? $data['cdn_url'] : '';
            $imageId = is_string($data['image_id'] ?? null) ? $data['image_id'] : '';

            $this->logger->info('上传图片到CDN成功', [
                'device_id' => $deviceId,
                'image_file_path' => $imageFilePath,
                'cdn_url' => $cdnUrl,
                'image_id' => $imageId,
            ]);

            return new FileUploadResult(
                $cdnUrl,
                $imageId,
                basename($imageFilePath),
                false === filesize($imageFilePath) ? 0 : filesize($imageFilePath)
            );
        } catch (\Exception $e) {
            $this->logger->error('上传图片到CDN异常', [
                'device_id' => $account->getDeviceId() ?? '',
                'image_file_path' => $imageFilePath,
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * 获取文件信息
     */
    public function getFileInfo(string $filePath): ?FileInfo
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $size = filesize($filePath);
        $mimeType = mime_content_type($filePath);
        if (false === $mimeType) {
            $mimeType = 'application/octet-stream';
        }
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $fileName = basename($filePath);
        $modifyTime = filemtime($filePath);

        return new FileInfo(
            $filePath,
            $fileName,
            false === $size ? 0 : $size,
            $mimeType,
            $extension,
            false === $modifyTime ? 0 : $modifyTime
        );
    }

    /**
     * 删除本地文件
     */
    public function deleteLocalFile(string $filePath): bool
    {
        try {
            if (file_exists($filePath)) {
                $result = unlink($filePath);

                if ($result) {
                    $this->logger->info('删除本地文件成功', ['file_path' => $filePath]);
                } else {
                    $this->logger->warning('删除本地文件失败', ['file_path' => $filePath]);
                }

                return $result;
            }

            return true; // 文件不存在视为删除成功
        } catch (\Exception $e) {
            $this->logger->error('删除本地文件异常', [
                'file_path' => $filePath,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * 清理过期文件
     */
    public function cleanExpiredFiles(int $expireDays = 7): int
    {
        $expireTime = time() - ($expireDays * 24 * 3600);
        $deletedCount = 0;

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->fileStoragePath)
            );

            foreach ($iterator as $file) {
                if ($file instanceof \SplFileInfo && $file->isFile() && $file->getMTime() < $expireTime) {
                    if (unlink($file->getPathname())) {
                        ++$deletedCount;
                    }
                }
            }

            $this->logger->info('清理过期文件完成', [
                'expire_days' => $expireDays,
                'deleted_count' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('清理过期文件异常', [
                'expire_days' => $expireDays,
                'exception' => $e->getMessage(),
            ]);
        }

        return $deletedCount;
    }

    /**
     * 获取存储统计信息
     */
    public function getStorageStats(): FileStorageStats
    {
        $totalSize = 0;
        $totalFiles = 0;
        $typeStats = [];

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->fileStoragePath)
            );

            foreach ($iterator as $file) {
                if ($file instanceof \SplFileInfo && $file->isFile()) {
                    $size = $file->getSize();
                    $extension = strtolower($file->getExtension());

                    $totalSize += $size;
                    ++$totalFiles;

                    if (!isset($typeStats[$extension])) {
                        $typeStats[$extension] = ['count' => 0, 'size' => 0];
                    }
                    ++$typeStats[$extension]['count'];
                    $typeStats[$extension]['size'] += $size;
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('获取存储统计异常', [
                'exception' => $e->getMessage(),
            ]);
        }

        return new FileStorageStats($totalFiles, $totalSize, $typeStats);
    }

    /**
     * 验证账号的API账号和设备ID不为null
     */
    private function validateAccount(WeChatAccount $account): void
    {
        if (null === $account->getApiAccount()) {
            throw new InvalidArgumentException('API账号不能为null');
        }

        if (null === $account->getDeviceId()) {
            throw new InvalidArgumentException('设备ID不能为null');
        }
    }

    /**
     * @return array{0: WeChatApiAccount, 1: string}
     */
    private function requireApiAndDevice(WeChatAccount $account): array
    {
        $apiAccount = $account->getApiAccount();
        $deviceId = $account->getDeviceId();

        if (null === $apiAccount || null === $deviceId) {
            throw new InvalidArgumentException('API账号或设备ID不能为null');
        }

        return [$apiAccount, $deviceId];
    }
}

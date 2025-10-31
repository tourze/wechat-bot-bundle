<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Service;

use HttpClientBundle\Request\RequestInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\DTO\MomentInfo;
use Tourze\WechatBotBundle\DTO\MomentsResult;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Exception\InvalidArgumentException;
use Tourze\WechatBotBundle\Request\Moment\CommentMomentRequest;
use Tourze\WechatBotBundle\Request\Moment\DeleteMomentRequest;
use Tourze\WechatBotBundle\Request\Moment\DownloadMomentVideoRequest;
use Tourze\WechatBotBundle\Request\Moment\ForwardMomentRequest;
use Tourze\WechatBotBundle\Request\Moment\GetFriendMomentsRequest;
use Tourze\WechatBotBundle\Request\Moment\GetMomentDetailRequest;
use Tourze\WechatBotBundle\Request\Moment\GetMomentsRequest;
use Tourze\WechatBotBundle\Request\Moment\HideMomentRequest;
use Tourze\WechatBotBundle\Request\Moment\LikeMomentRequest;
use Tourze\WechatBotBundle\Request\Moment\PublishImageMomentRequest;
use Tourze\WechatBotBundle\Request\Moment\PublishLinkMomentRequest;
use Tourze\WechatBotBundle\Request\Moment\PublishTextMomentRequest;
use Tourze\WechatBotBundle\Request\Moment\PublishVideoMomentRequest;
use Tourze\WechatBotBundle\Request\Moment\ShowMomentRequest;
use Tourze\WechatBotBundle\Request\Moment\UploadMomentImageFileRequest;
use Tourze\WechatBotBundle\Request\Moment\UploadMomentImageRequest;

/**
 * 微信朋友圈管理服务
 *
 * 提供朋友圈动态获取、发布、互动等业务功能
 */
#[WithMonologChannel(channel: 'wechat_bot')]
#[Autoconfigure(public: true)]
readonly class WeChatMomentService
{
    public function __construct(
        private WeChatApiClient $apiClient,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * 获取朋友圈动态
     */
    public function getMoments(WeChatAccount $account, int $page = 1, int $limit = 20): ?MomentsResult
    {
        return $this->executeApiCall(
            $account,
            '获取朋友圈动态',
            fn (WeChatApiAccount $api, string $did) => $this->performGetMoments($api, $did, $page, $limit),
            ['page' => $page, 'limit' => $limit]
        );
    }

    private function performGetMoments(WeChatApiAccount $apiAccount, string $deviceId, int $page, int $limit): MomentsResult
    {
        $request = new GetMomentsRequest($apiAccount, $deviceId, $page, $limit);
        $data = $this->apiClient->request($request);

        if (!is_array($data)) {
            throw new \InvalidArgumentException('Invalid API response format');
        }
        /** @var array<string, mixed> $data */

        return $this->buildMomentsResult($data);
    }

    /**
     * 获取好友朋友圈
     */
    public function getFriendMoments(WeChatAccount $account, string $friendWxid, int $page = 1, int $limit = 20): ?MomentsResult
    {
        return $this->executeApiCall(
            $account,
            '获取好友朋友圈',
            fn (WeChatApiAccount $api, string $did) => $this->performGetFriendMoments($api, $did, $friendWxid, $page, $limit),
            ['friend_wxid' => $friendWxid, 'page' => $page, 'limit' => $limit]
        );
    }

    private function performGetFriendMoments(WeChatApiAccount $apiAccount, string $deviceId, string $friendWxid, int $page, int $limit): MomentsResult
    {
        $request = new GetFriendMomentsRequest($apiAccount, $deviceId, $friendWxid, $page, $limit);
        $data = $this->apiClient->request($request);

        if (!is_array($data)) {
            throw new \InvalidArgumentException('Invalid API response format');
        }
        /** @var array<string, mixed> $data */

        return $this->buildMomentsResult($data);
    }

    /**
     * 获取朋友圈详情
     */
    public function getMomentDetail(WeChatAccount $account, string $momentId): ?MomentInfo
    {
        return $this->executeApiCall(
            $account,
            '获取朋友圈详情',
            fn (WeChatApiAccount $api, string $did) => $this->performGetMomentDetail($api, $did, $momentId),
            ['moment_id' => $momentId]
        );
    }

    private function performGetMomentDetail(WeChatApiAccount $apiAccount, string $deviceId, string $momentId): MomentInfo
    {
        $request = new GetMomentDetailRequest($apiAccount, $deviceId, $momentId);
        $data = $this->apiClient->request($request);

        if (!is_array($data)) {
            throw new \InvalidArgumentException('Invalid API response format');
        }

        /** @var array<string, mixed> $dataTyped */
        $dataTyped = $data;

        return $this->buildMomentInfo($dataTyped);
    }

    /**
     * 朋友圈点赞
     */
    public function likeMoment(WeChatAccount $account, string $momentId): bool
    {
        return $this->executeSimpleApiCall(
            $account,
            '朋友圈点赞',
            $momentId,
            fn (WeChatApiAccount $api, string $did) => new LikeMomentRequest($api, $did, $momentId)
        );
    }

    /**
     * 朋友圈评论
     */
    public function commentMoment(WeChatAccount $account, string $momentId, string $comment): bool
    {
        return $this->executeSimpleApiCall(
            $account,
            '朋友圈评论',
            $momentId,
            fn (WeChatApiAccount $api, string $did) => new CommentMomentRequest($api, $did, $momentId, $comment)
        );
    }

    /**
     * 发布文本朋友圈
     */
    public function publishTextMoment(WeChatAccount $account, string $content): ?string
    {
        return $this->executePublishApiCall(
            $account,
            '发布文本朋友圈',
            fn (WeChatApiAccount $api, string $did) => new PublishTextMomentRequest($api, $did, $content)
        );
    }

    /**
     * 发布链接朋友圈
     */
    public function publishLinkMoment(WeChatAccount $account, string $linkUrl, string $title, string $description = '', string $content = ''): ?string
    {
        return $this->executePublishApiCall(
            $account,
            '发布链接朋友圈',
            fn (WeChatApiAccount $api, string $did) => new PublishLinkMomentRequest($api, $did, $linkUrl, $title, $description, $content)
        );
    }

    /**
     * 发布视频朋友圈
     */
    public function publishVideoMoment(WeChatAccount $account, string $videoPath, string $content = ''): ?string
    {
        return $this->executePublishApiCall(
            $account,
            '发布视频朋友圈',
            fn (WeChatApiAccount $api, string $did) => new PublishVideoMomentRequest($api, $did, $videoPath, $content)
        );
    }

    /**
     * 下载朋友圈视频
     */
    public function downloadMomentVideo(WeChatAccount $account, string $videoUrl): ?string
    {
        return $this->executeApiCall(
            $account,
            '下载朋友圈视频',
            fn (WeChatApiAccount $api, string $did) => $this->performDownloadMomentVideo($api, $did, $videoUrl),
            ['video_url' => $videoUrl]
        );
    }

    private function performDownloadMomentVideo(WeChatApiAccount $apiAccount, string $deviceId, string $videoUrl): ?string
    {
        $request = new DownloadMomentVideoRequest($apiAccount, $deviceId, $videoUrl);
        $data = $this->apiClient->request($request);

        if (!is_array($data)) {
            throw new \InvalidArgumentException('Invalid API response format: expected array, got ' . gettype($data));
        }

        $filePath = is_string($data['file_path'] ?? null) ? $data['file_path'] : null;

        $this->logger->info('下载朋友圈视频成功', [
            'device_id' => $deviceId,
            'video_url' => $videoUrl,
            'file_path' => $filePath,
        ]);

        return $filePath;
    }

    /**
     * 转发朋友圈
     */
    public function forwardMoment(WeChatAccount $account, string $momentId, string $content = ''): ?string
    {
        return $this->executePublishApiCall(
            $account,
            '转发朋友圈',
            fn (WeChatApiAccount $api, string $did) => new ForwardMomentRequest($api, $did, $momentId, $content)
        );
    }

    /**
     * 删除朋友圈
     */
    public function deleteMoment(WeChatAccount $account, string $momentId): bool
    {
        return $this->executeSimpleApiCall(
            $account,
            '删除朋友圈',
            $momentId,
            fn (WeChatApiAccount $api, string $did) => new DeleteMomentRequest($api, $did, $momentId)
        );
    }

    /**
     * 隐藏朋友圈
     */
    public function hideMoment(WeChatAccount $account, string $momentId): bool
    {
        return $this->executeSimpleApiCall(
            $account,
            '隐藏朋友圈',
            $momentId,
            fn (WeChatApiAccount $api, string $did) => new HideMomentRequest($api, $did, $momentId)
        );
    }

    /**
     * 公开朋友圈
     */
    public function showMoment(WeChatAccount $account, string $momentId): bool
    {
        return $this->executeSimpleApiCall(
            $account,
            '公开朋友圈',
            $momentId,
            fn (WeChatApiAccount $api, string $did) => new ShowMomentRequest($api, $did, $momentId)
        );
    }

    /**
     * 批量上传图片并发布朋友圈
     *
     * @param list<string> $imageFilePaths
     */
    public function publishImageMomentFromFiles(WeChatAccount $account, array $imageFilePaths, string $content = ''): ?string
    {
        $uploadedImageIds = $this->uploadMultipleImages($account, $imageFilePaths, 'file');

        if ([] === $uploadedImageIds) {
            $this->logger->error('所有图片上传失败', [
                'device_id' => $account->getDeviceId(),
                'file_paths' => $imageFilePaths,
            ]);

            return null;
        }

        return $this->publishImageMoment($account, $uploadedImageIds, $content);
    }

    /**
     * 上传朋友圈图片文件
     */
    public function uploadMomentImageFile(WeChatAccount $account, string $imageFilePath): ?string
    {
        return $this->executePublishApiCall(
            $account,
            '上传朋友圈图片文件',
            fn (WeChatApiAccount $api, string $did) => new UploadMomentImageFileRequest($api, $did, $imageFilePath)
        );
    }

    /**
     * 发布图片朋友圈
     *
     * @param list<string> $imageIds
     */
    public function publishImageMoment(WeChatAccount $account, array $imageIds, string $content = ''): ?string
    {
        return $this->executePublishApiCall(
            $account,
            '发布图片朋友圈',
            fn (WeChatApiAccount $api, string $did) => new PublishImageMomentRequest($api, $did, $imageIds, $content)
        );
    }

    /**
     * 批量上传base64图片并发布朋友圈
     *
     * @param list<string> $imageBase64List
     */
    public function publishImageMomentFromBase64(WeChatAccount $account, array $imageBase64List, string $content = ''): ?string
    {
        $uploadedImageIds = $this->uploadMultipleImages($account, $imageBase64List, 'base64');

        if ([] === $uploadedImageIds) {
            $this->logger->error('所有base64图片上传失败', [
                'device_id' => $account->getDeviceId(),
                'image_count' => count($imageBase64List),
            ]);

            return null;
        }

        return $this->publishImageMoment($account, $uploadedImageIds, $content);
    }

    /**
     * 上传朋友圈图片（base64）
     */
    public function uploadMomentImage(WeChatAccount $account, string $imageBase64): ?string
    {
        return $this->executePublishApiCall(
            $account,
            '上传朋友圈图片',
            fn (WeChatApiAccount $api, string $did) => new UploadMomentImageRequest($api, $did, $imageBase64)
        );
    }

    /**
     * 批量上传图片
     *
     * @param list<string> $images
     * @return list<string>
     */
    private function uploadMultipleImages(WeChatAccount $account, array $images, string $type): array
    {
        $uploadedImageIds = [];

        foreach ($images as $image) {
            // PHPDoc 已声明 $images 为 list<string>，无需额外类型检查
            if ('' === $image) {
                $this->logger->warning($type . '数据无效，跳过', ['device_id' => $account->getDeviceId()]);
                continue;
            }

            $imageId = ('base64' === $type)
                ? $this->uploadMomentImage($account, $image)
                : $this->uploadMomentImageFile($account, $image);

            if ((bool) $imageId) {
                $uploadedImageIds[] = $imageId;
            } else {
                $this->logger->warning($type . '图片上传失败，跳过', ['device_id' => $account->getDeviceId()]);
            }
        }

        return $uploadedImageIds;
    }

    /**
     * 从API数据构建MomentInfo对象
     *
     * @param array<string, mixed> $data
     */
    private function buildMomentInfo(array $data): MomentInfo
    {
        $rawImages = $this->extractArray($data, 'images');
        /** @var list<string> $images */
        $images = array_values(array_filter($rawImages, 'is_string'));

        /** @var array<string, mixed> $likes */
        $likes = $this->extractArray($data, 'likes');

        /** @var array<string, mixed> $comments */
        $comments = $this->extractArray($data, 'comments');

        return new MomentInfo(
            $this->extractString($data, 'moment_id'),
            $this->extractString($data, 'wxid'),
            $this->extractString($data, 'nickname'),
            $this->extractString($data, 'content'),
            $this->extractInt($data, 'type'),
            $this->extractInt($data, 'create_time'),
            $images,
            $this->extractString($data, 'video_url'),
            $this->extractString($data, 'link_title'),
            $this->extractString($data, 'link_desc'),
            $this->extractString($data, 'link_url'),
            $this->extractInt($data, 'like_count'),
            $this->extractInt($data, 'comment_count'),
            $likes,
            $comments
        );
    }

    /**
     * 验证并获取API账号和设备ID
     *
     * @return array{0: WeChatApiAccount, 1: string}
     */
    private function requireApiAndDevice(WeChatAccount $account): array
    {
        $apiAccount = $account->getApiAccount();
        $deviceId = $account->getDeviceId();

        if (null === $apiAccount || null === $deviceId) {
            throw new InvalidArgumentException('API账号或设备ID不可用');
        }

        return [$apiAccount, $deviceId];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractString(array $data, string $key): string
    {
        return is_string($data[$key] ?? null) ? $data[$key] : '';
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractInt(array $data, string $key): int
    {
        return is_int($data[$key] ?? null) ? $data[$key] : 0;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<mixed>
     */
    private function extractArray(array $data, string $key): array
    {
        return is_array($data[$key] ?? null) ? $data[$key] : [];
    }

    /**
     * 构建朋友圈列表结果
     *
     * @param array<string, mixed> $data
     */
    private function buildMomentsResult(array $data): MomentsResult
    {
        $rawMoments = $data['moments'] ?? [];
        $moments = is_array($rawMoments) ? $rawMoments : [];

        /** @var array<int, array<string, mixed>> $momentsTyped */
        $momentsTyped = array_values(array_filter($moments, 'is_array'));

        return new MomentsResult(
            $momentsTyped,
            is_string($data['next_max_id'] ?? null) ? $data['next_max_id'] : '',
            (bool) ($data['has_more'] ?? false)
        );
    }

    /**
     * 统一的API调用包装器，处理异常和日志
     *
     * @template T
     * @param callable(WeChatApiAccount, string): T $operation
     * @param array<string, mixed> $context
     * @return T|null
     */
    private function executeApiCall(WeChatAccount $account, string $operationName, callable $operation, array $context = []): mixed
    {
        try {
            [$apiAccount, $deviceId] = $this->requireApiAndDevice($account);

            return $operation($apiAccount, $deviceId);
        } catch (\Exception $e) {
            $this->logger->error($operationName . '异常', array_merge(
                ['device_id' => $account->getDeviceId(), 'exception' => $e->getMessage()],
                $context
            ));

            return null;
        }
    }

    /**
     * 简单API调用（返回布尔值，记录成功日志）
     */
    private function executeSimpleApiCall(WeChatAccount $account, string $operationName, string $momentId, callable $requestFactory): bool
    {
        return $this->executeApiCall(
            $account,
            $operationName,
            function (WeChatApiAccount $apiAccount, string $deviceId) use ($requestFactory, $operationName, $momentId) {
                $request = $requestFactory($apiAccount, $deviceId);
                assert($request instanceof RequestInterface);
                $this->apiClient->request($request);

                $this->logger->info($operationName . '成功', [
                    'device_id' => $deviceId,
                    'moment_id' => $momentId,
                ]);

                return true;
            },
            ['moment_id' => $momentId]
        ) ?? false;
    }

    /**
     * 发布类API调用（返回moment_id或image_id）
     */
    private function executePublishApiCall(WeChatAccount $account, string $operationName, callable $requestFactory): ?string
    {
        return $this->executeApiCall(
            $account,
            $operationName,
            function (WeChatApiAccount $apiAccount, string $deviceId) use ($requestFactory, $operationName) {
                $request = $requestFactory($apiAccount, $deviceId);
                assert($request instanceof RequestInterface);
                $data = $this->apiClient->request($request);

                if (!is_array($data)) {
                    throw new \InvalidArgumentException('Invalid API response format: expected array, got ' . gettype($data));
                }

                $id = is_string($data['moment_id'] ?? null) ? $data['moment_id'] : (is_string($data['image_id'] ?? null) ? $data['image_id'] : null);

                $this->logger->info($operationName . '成功', [
                    'device_id' => $deviceId,
                    'result_id' => $id,
                ]);

                return $id;
            },
            []
        );
    }
}

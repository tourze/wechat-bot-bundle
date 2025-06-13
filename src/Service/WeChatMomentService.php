<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\DTO\MomentInfo;
use Tourze\WechatBotBundle\DTO\MomentsResult;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
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
class WeChatMomentService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WeChatApiClient $apiClient,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * 获取朋友圈动态
     */
    public function getMoments(WeChatAccount $account, int $page = 1, int $limit = 20): ?MomentsResult
    {
        try {
            $request = new GetMomentsRequest($account->getApiAccount(), $account->getDeviceId(), $page, $limit);
            $response = $this->apiClient->request($request);

            if (!$response->isSuccess()) {
                $this->logger->error('获取朋友圈动态失败', [
                    'device_id' => $account->getDeviceId(),
                    'page' => $page,
                    'limit' => $limit,
                    'error' => $response->getErrorMessage()
                ]);
                return null;
            }

            $data = $response->getData();
            $moments = [];

            foreach ($data['moments'] ?? [] as $momentData) {
                $moments[] = new MomentInfo(
                    $momentData['moment_id'] ?? '',
                    $momentData['wxid'] ?? '',
                    $momentData['nickname'] ?? '',
                    $momentData['content'] ?? '',
                    $momentData['type'] ?? 0,
                    $momentData['create_time'] ?? 0,
                    $momentData['images'] ?? [],
                    $momentData['video_url'] ?? '',
                    $momentData['link_title'] ?? '',
                    $momentData['link_desc'] ?? '',
                    $momentData['link_url'] ?? '',
                    $momentData['like_count'] ?? 0,
                    $momentData['comment_count'] ?? 0,
                    $momentData['likes'] ?? [],
                    $momentData['comments'] ?? []
                );
            }

            return new MomentsResult(
                $moments,
                $data['next_max_id'] ?? '',
                (bool) ($data['has_more'] ?? false)
            );
        } catch (\Exception $e) {
            $this->logger->error('获取朋友圈动态异常', [
                'device_id' => $account->getDeviceId(),
                'page' => $page,
                'limit' => $limit,
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 获取好友朋友圈
     */
    public function getFriendMoments(WeChatAccount $account, string $friendWxid, int $page = 1, int $limit = 20): ?MomentsResult
    {
        try {
            $request = new GetFriendMomentsRequest($account->getApiAccount(), $account->getDeviceId(), $friendWxid, $page, $limit);
            $response = $this->apiClient->request($request);

            if (!$response->isSuccess()) {
                $this->logger->error('获取好友朋友圈失败', [
                    'device_id' => $account->getDeviceId(),
                    'friend_wxid' => $friendWxid,
                    'page' => $page,
                    'limit' => $limit,
                    'error' => $response->getErrorMessage()
                ]);
                return null;
            }

            $data = $response->getData();
            $moments = [];

            foreach ($data['moments'] ?? [] as $momentData) {
                $moments[] = new MomentInfo(
                    $momentData['moment_id'] ?? '',
                    $momentData['wxid'] ?? '',
                    $momentData['nickname'] ?? '',
                    $momentData['content'] ?? '',
                    $momentData['type'] ?? 0,
                    $momentData['create_time'] ?? 0,
                    $momentData['images'] ?? [],
                    $momentData['video_url'] ?? '',
                    $momentData['link_title'] ?? '',
                    $momentData['link_desc'] ?? '',
                    $momentData['link_url'] ?? '',
                    $momentData['like_count'] ?? 0,
                    $momentData['comment_count'] ?? 0,
                    $momentData['likes'] ?? [],
                    $momentData['comments'] ?? []
                );
            }

            return new MomentsResult(
                $moments,
                $data['next_max_id'] ?? '',
                (bool) ($data['has_more'] ?? false)
            );
        } catch (\Exception $e) {
            $this->logger->error('获取好友朋友圈异常', [
                'device_id' => $account->getDeviceId(),
                'friend_wxid' => $friendWxid,
                'page' => $page,
                'limit' => $limit,
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 获取朋友圈详情
     */
    public function getMomentDetail(WeChatAccount $account, string $momentId): ?MomentInfo
    {
        try {
            $request = new GetMomentDetailRequest($account->getApiAccount(), $account->getDeviceId(), $momentId);
            $response = $this->apiClient->request($request);

            if (!$response->isSuccess()) {
                $this->logger->error('获取朋友圈详情失败', [
                    'device_id' => $account->getDeviceId(),
                    'moment_id' => $momentId,
                    'error' => $response->getErrorMessage()
                ]);
                return null;
            }

            $data = $response->getData();
            return new MomentInfo(
                $data['moment_id'] ?? '',
                $data['wxid'] ?? '',
                $data['nickname'] ?? '',
                $data['content'] ?? '',
                $data['type'] ?? 0,
                $data['create_time'] ?? 0,
                $data['images'] ?? [],
                $data['video_url'] ?? '',
                $data['link_title'] ?? '',
                $data['link_desc'] ?? '',
                $data['link_url'] ?? '',
                $data['like_count'] ?? 0,
                $data['comment_count'] ?? 0,
                $data['likes'] ?? [],
                $data['comments'] ?? []
            );
        } catch (\Exception $e) {
            $this->logger->error('获取朋友圈详情异常', [
                'device_id' => $account->getDeviceId(),
                'moment_id' => $momentId,
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 朋友圈点赞
     */
    public function likeMoment(WeChatAccount $account, string $momentId): bool
    {
        try {
            $request = new LikeMomentRequest($account->getApiAccount(), $account->getDeviceId(), $momentId);
            $response = $this->apiClient->request($request);

            if (!$response->isSuccess()) {
                $this->logger->error('朋友圈点赞失败', [
                    'device_id' => $account->getDeviceId(),
                    'moment_id' => $momentId,
                    'error' => $response->getErrorMessage()
                ]);
                return false;
            }

            $this->logger->info('朋友圈点赞成功', [
                'device_id' => $account->getDeviceId(),
                'moment_id' => $momentId
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('朋友圈点赞异常', [
                'device_id' => $account->getDeviceId(),
                'moment_id' => $momentId,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 朋友圈评论
     */
    public function commentMoment(WeChatAccount $account, string $momentId, string $comment): bool
    {
        try {
            $request = new CommentMomentRequest($account->getApiAccount(), $account->getDeviceId(), $momentId, $comment);
            $response = $this->apiClient->request($request);

            if (!$response->isSuccess()) {
                $this->logger->error('朋友圈评论失败', [
                    'device_id' => $account->getDeviceId(),
                    'moment_id' => $momentId,
                    'comment' => $comment,
                    'error' => $response->getErrorMessage()
                ]);
                return false;
            }

            $this->logger->info('朋友圈评论成功', [
                'device_id' => $account->getDeviceId(),
                'moment_id' => $momentId
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('朋友圈评论异常', [
                'device_id' => $account->getDeviceId(),
                'moment_id' => $momentId,
                'comment' => $comment,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 发布文本朋友圈
     */
    public function publishTextMoment(WeChatAccount $account, string $content): ?string
    {
        try {
            $request = new PublishTextMomentRequest($account->getApiAccount(), $account->getDeviceId(), $content);
            $response = $this->apiClient->request($request);

            if (!$response->isSuccess()) {
                $this->logger->error('发布文本朋友圈失败', [
                    'device_id' => $account->getDeviceId(),
                    'content' => $content,
                    'error' => $response->getErrorMessage()
                ]);
                return null;
            }

            $data = $response->getData();
            $momentId = $data['moment_id'] ?? '';

            $this->logger->info('发布文本朋友圈成功', [
                'device_id' => $account->getDeviceId(),
                'moment_id' => $momentId
            ]);

            return $momentId;
        } catch (\Exception $e) {
            $this->logger->error('发布文本朋友圈异常', [
                'device_id' => $account->getDeviceId(),
                'content' => $content,
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 发布链接朋友圈
     */
    public function publishLinkMoment(WeChatAccount $account, string $linkUrl, string $title, string $description = '', string $content = ''): ?string
    {
        try {
            $request = new PublishLinkMomentRequest($account->getApiAccount(), $account->getDeviceId(), $linkUrl, $title, $description, $content);
            $response = $this->apiClient->request($request);

            if (!$response->isSuccess()) {
                $this->logger->error('发布链接朋友圈失败', [
                    'device_id' => $account->getDeviceId(),
                    'link_url' => $linkUrl,
                    'title' => $title,
                    'error' => $response->getErrorMessage()
                ]);
                return null;
            }

            $data = $response->getData();
            $momentId = $data['moment_id'] ?? '';

            $this->logger->info('发布链接朋友圈成功', [
                'device_id' => $account->getDeviceId(),
                'moment_id' => $momentId,
                'link_url' => $linkUrl
            ]);

            return $momentId;
        } catch (\Exception $e) {
            $this->logger->error('发布链接朋友圈异常', [
                'device_id' => $account->getDeviceId(),
                'link_url' => $linkUrl,
                'title' => $title,
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 发布视频朋友圈
     */
    public function publishVideoMoment(WeChatAccount $account, string $videoPath, string $content = ''): ?string
    {
        try {
            $request = new PublishVideoMomentRequest($account->getApiAccount(), $account->getDeviceId(), $videoPath, $content);
            $response = $this->apiClient->request($request);

            if (!$response->isSuccess()) {
                $this->logger->error('发布视频朋友圈失败', [
                    'device_id' => $account->getDeviceId(),
                    'video_path' => $videoPath,
                    'content' => $content,
                    'error' => $response->getErrorMessage()
                ]);
                return null;
            }

            $data = $response->getData();
            $momentId = $data['moment_id'] ?? '';

            $this->logger->info('发布视频朋友圈成功', [
                'device_id' => $account->getDeviceId(),
                'moment_id' => $momentId,
                'video_path' => $videoPath
            ]);

            return $momentId;
        } catch (\Exception $e) {
            $this->logger->error('发布视频朋友圈异常', [
                'device_id' => $account->getDeviceId(),
                'video_path' => $videoPath,
                'content' => $content,
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 下载朋友圈视频
     */
    public function downloadMomentVideo(WeChatAccount $account, string $videoUrl): ?string
    {
        try {
            $request = new DownloadMomentVideoRequest($account->getApiAccount(), $account->getDeviceId(), $videoUrl);
            $response = $this->apiClient->request($request);

            if (!$response->isSuccess()) {
                $this->logger->error('下载朋友圈视频失败', [
                    'device_id' => $account->getDeviceId(),
                    'video_url' => $videoUrl,
                    'error' => $response->getErrorMessage()
                ]);
                return null;
            }

            $data = $response->getData();
            $filePath = $data['file_path'] ?? '';

            $this->logger->info('下载朋友圈视频成功', [
                'device_id' => $account->getDeviceId(),
                'video_url' => $videoUrl,
                'file_path' => $filePath
            ]);

            return $filePath;
        } catch (\Exception $e) {
            $this->logger->error('下载朋友圈视频异常', [
                'device_id' => $account->getDeviceId(),
                'video_url' => $videoUrl,
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 转发朋友圈
     */
    public function forwardMoment(WeChatAccount $account, string $momentId, string $content = ''): ?string
    {
        try {
            $request = new ForwardMomentRequest($account->getApiAccount(), $account->getDeviceId(), $momentId, $content);
            $response = $this->apiClient->request($request);

            if (!$response->isSuccess()) {
                $this->logger->error('转发朋友圈失败', [
                    'device_id' => $account->getDeviceId(),
                    'moment_id' => $momentId,
                    'content' => $content,
                    'error' => $response->getErrorMessage()
                ]);
                return null;
            }

            $data = $response->getData();
            $newMomentId = $data['moment_id'] ?? '';

            $this->logger->info('转发朋友圈成功', [
                'device_id' => $account->getDeviceId(),
                'original_moment_id' => $momentId,
                'new_moment_id' => $newMomentId
            ]);

            return $newMomentId;
        } catch (\Exception $e) {
            $this->logger->error('转发朋友圈异常', [
                'device_id' => $account->getDeviceId(),
                'moment_id' => $momentId,
                'content' => $content,
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 删除朋友圈
     */
    public function deleteMoment(WeChatAccount $account, string $momentId): bool
    {
        try {
            $request = new DeleteMomentRequest($account->getApiAccount(), $account->getDeviceId(), $momentId);
            $response = $this->apiClient->request($request);

            if (!$response->isSuccess()) {
                $this->logger->error('删除朋友圈失败', [
                    'device_id' => $account->getDeviceId(),
                    'moment_id' => $momentId,
                    'error' => $response->getErrorMessage()
                ]);
                return false;
            }

            $this->logger->info('删除朋友圈成功', [
                'device_id' => $account->getDeviceId(),
                'moment_id' => $momentId
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('删除朋友圈异常', [
                'device_id' => $account->getDeviceId(),
                'moment_id' => $momentId,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 隐藏朋友圈
     */
    public function hideMoment(WeChatAccount $account, string $momentId): bool
    {
        try {
            $request = new HideMomentRequest($account->getApiAccount(), $account->getDeviceId(), $momentId);
            $response = $this->apiClient->request($request);

            if (!$response->isSuccess()) {
                $this->logger->error('隐藏朋友圈失败', [
                    'device_id' => $account->getDeviceId(),
                    'moment_id' => $momentId,
                    'error' => $response->getErrorMessage()
                ]);
                return false;
            }

            $this->logger->info('隐藏朋友圈成功', [
                'device_id' => $account->getDeviceId(),
                'moment_id' => $momentId
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('隐藏朋友圈异常', [
                'device_id' => $account->getDeviceId(),
                'moment_id' => $momentId,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 公开朋友圈
     */
    public function showMoment(WeChatAccount $account, string $momentId): bool
    {
        try {
            $request = new ShowMomentRequest($account->getApiAccount(), $account->getDeviceId(), $momentId);
            $response = $this->apiClient->request($request);

            if (!$response->isSuccess()) {
                $this->logger->error('公开朋友圈失败', [
                    'device_id' => $account->getDeviceId(),
                    'moment_id' => $momentId,
                    'error' => $response->getErrorMessage()
                ]);
                return false;
            }

            $this->logger->info('公开朋友圈成功', [
                'device_id' => $account->getDeviceId(),
                'moment_id' => $momentId
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('公开朋友圈异常', [
                'device_id' => $account->getDeviceId(),
                'moment_id' => $momentId,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 批量上传图片并发布朋友圈
     */
    public function publishImageMomentFromFiles(WeChatAccount $account, array $imageFilePaths, string $content = ''): ?string
    {
        $uploadedImageIds = [];

        // 批量上传图片
        foreach ($imageFilePaths as $filePath) {
            $imageId = $this->uploadMomentImageFile($account, $filePath);
            if ($imageId) {
                $uploadedImageIds[] = $imageId;
            } else {
                $this->logger->warning('图片上传失败，跳过', [
                    'device_id' => $account->getDeviceId(),
                    'file_path' => $filePath
                ]);
            }
        }

        if (empty($uploadedImageIds)) {
            $this->logger->error('所有图片上传失败', [
                'device_id' => $account->getDeviceId(),
                'file_paths' => $imageFilePaths
            ]);
            return null;
        }

        // 发布朋友圈
        return $this->publishImageMoment($account, $uploadedImageIds, $content);
    }

    /**
     * 上传朋友圈图片文件
     */
    public function uploadMomentImageFile(WeChatAccount $account, string $imageFilePath): ?string
    {
        try {
            $request = new UploadMomentImageFileRequest($account->getApiAccount(), $account->getDeviceId(), $imageFilePath);
            $response = $this->apiClient->request($request);

            if (!$response->isSuccess()) {
                $this->logger->error('上传朋友圈图片文件失败', [
                    'device_id' => $account->getDeviceId(),
                    'image_file_path' => $imageFilePath,
                    'error' => $response->getErrorMessage()
                ]);
                return null;
            }

            $data = $response->getData();
            $imageId = $data['image_id'] ?? '';

            $this->logger->info('上传朋友圈图片文件成功', [
                'device_id' => $account->getDeviceId(),
                'image_id' => $imageId,
                'image_file_path' => $imageFilePath
            ]);

            return $imageId;
        } catch (\Exception $e) {
            $this->logger->error('上传朋友圈图片文件异常', [
                'device_id' => $account->getDeviceId(),
                'image_file_path' => $imageFilePath,
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 发布图片朋友圈
     */
    public function publishImageMoment(WeChatAccount $account, array $imageIds, string $content = ''): ?string
    {
        try {
            $request = new PublishImageMomentRequest($account->getApiAccount(), $account->getDeviceId(), $imageIds, $content);
            $response = $this->apiClient->request($request);

            if (!$response->isSuccess()) {
                $this->logger->error('发布图片朋友圈失败', [
                    'device_id' => $account->getDeviceId(),
                    'image_ids' => $imageIds,
                    'content' => $content,
                    'error' => $response->getErrorMessage()
                ]);
                return null;
            }

            $data = $response->getData();
            $momentId = $data['moment_id'] ?? '';

            $this->logger->info('发布图片朋友圈成功', [
                'device_id' => $account->getDeviceId(),
                'moment_id' => $momentId,
                'image_count' => count($imageIds)
            ]);

            return $momentId;
        } catch (\Exception $e) {
            $this->logger->error('发布图片朋友圈异常', [
                'device_id' => $account->getDeviceId(),
                'image_ids' => $imageIds,
                'content' => $content,
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 批量上传base64图片并发布朋友圈
     */
    public function publishImageMomentFromBase64(WeChatAccount $account, array $imageBase64List, string $content = ''): ?string
    {
        $uploadedImageIds = [];

        // 批量上传图片
        foreach ($imageBase64List as $base64) {
            $imageId = $this->uploadMomentImage($account, $base64);
            if ($imageId) {
                $uploadedImageIds[] = $imageId;
            } else {
                $this->logger->warning('base64图片上传失败，跳过', [
                    'device_id' => $account->getDeviceId()
                ]);
            }
        }

        if (empty($uploadedImageIds)) {
            $this->logger->error('所有base64图片上传失败', [
                'device_id' => $account->getDeviceId(),
                'image_count' => count($imageBase64List)
            ]);
            return null;
        }

        // 发布朋友圈
        return $this->publishImageMoment($account, $uploadedImageIds, $content);
    }

    /**
     * 上传朋友圈图片（base64）
     */
    public function uploadMomentImage(WeChatAccount $account, string $imageBase64): ?string
    {
        try {
            $request = new UploadMomentImageRequest($account->getApiAccount(), $account->getDeviceId(), $imageBase64);
            $response = $this->apiClient->request($request);

            if (!$response->isSuccess()) {
                $this->logger->error('上传朋友圈图片失败', [
                    'device_id' => $account->getDeviceId(),
                    'error' => $response->getErrorMessage()
                ]);
                return null;
            }

            $data = $response->getData();
            $imageId = $data['image_id'] ?? '';

            $this->logger->info('上传朋友圈图片成功', [
                'device_id' => $account->getDeviceId(),
                'image_id' => $imageId
            ]);

            return $imageId;
        } catch (\Exception $e) {
            $this->logger->error('上传朋友圈图片异常', [
                'device_id' => $account->getDeviceId(),
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }
}

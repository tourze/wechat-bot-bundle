<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 朋友圈信息DTO
 */
class MomentInfo
{
    public function __construct(
        public readonly string $momentId,
        public readonly string $wxid,
        public readonly string $nickname,
        public readonly string $content,
        public readonly int $type,
        public readonly int $createTime,
        public readonly array $images,
        public readonly string $videoUrl,
        public readonly string $linkTitle,
        public readonly string $linkDesc,
        public readonly string $linkUrl,
        public readonly int $likeCount,
        public readonly int $commentCount,
        public readonly array $likes,
        public readonly array $comments
    ) {}

    /**
     * 获取朋友圈类型描述
     */
    public function getTypeDescription(): string
    {
        return match ($this->type) {
            1 => '文本',
            2 => '图片',
            3 => '视频',
            4 => '链接',
            default => '未知'
        };
    }

    /**
     * 是否有图片
     */
    public function hasImages(): bool
    {
        return !empty($this->images);
    }

    /**
     * 是否有视频
     */
    public function hasVideo(): bool
    {
        return !empty($this->videoUrl);
    }

    /**
     * 是否是链接类型
     */
    public function isLink(): bool
    {
        return $this->type === 4 && !empty($this->linkUrl);
    }

    /**
     * 获取格式化的创建时间
     */
    public function getFormattedCreateTime(): string
    {
        return date('Y-m-d H:i:s', $this->createTime);
    }
}

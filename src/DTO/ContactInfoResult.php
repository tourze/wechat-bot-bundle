<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 联系人详情结果DTO
 */
class ContactInfoResult
{
    public function __construct(
        public readonly string $wxid,
        public readonly string $nickname,
        public readonly string $avatar,
        public readonly string $remark,
        public readonly int $sex,
        public readonly string $signature,
        public readonly string $phone,
        public readonly string $city,
        public readonly string $province,
        public readonly string $country,
        public readonly array $tags,
        public readonly bool $isFriend,
        public readonly string $corpName = '',
        public readonly string $position = ''
    ) {}
}

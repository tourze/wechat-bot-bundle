<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 联系人搜索结果DTO
 */
class ContactSearchResult
{
    public function __construct(
        public readonly string $wxid,
        public readonly string $nickname,
        public readonly string $avatar,
        public readonly string $sex,
        public readonly string $signature,
        public readonly string $phone,
        public readonly string $city,
        public readonly string $province,
        public readonly string $country
    ) {}
}

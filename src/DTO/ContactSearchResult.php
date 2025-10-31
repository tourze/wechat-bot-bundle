<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 联系人搜索结果DTO
 */
readonly class ContactSearchResult
{
    public function __construct(
        public string $wxid,
        public string $nickname,
        public string $avatar,
        public string $sex,
        public string $signature,
        public string $phone,
        public string $city,
        public string $province,
        public string $country,
    ) {
    }
}

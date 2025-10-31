<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 联系人详情结果DTO
 */
readonly class ContactInfoResult
{
    /**
     * @param string[] $tags
     */
    public function __construct(
        public string $wxid,
        public string $nickname,
        public string $avatar,
        public string $remark,
        public int $sex,
        public string $signature,
        public string $phone,
        public string $city,
        public string $province,
        public string $country,
        public array $tags,
        public bool $isFriend,
        public string $corpName = '',
        public string $position = '',
    ) {
    }
}

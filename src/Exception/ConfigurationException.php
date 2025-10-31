<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Exception;

/**
 * 配置异常
 * 当 Bundle 配置出现问题时抛出此异常
 */
class ConfigurationException extends WeChatException
{
    public static function entityDirectoryNotFound(string $entityDir): self
    {
        return new self(sprintf('Entity directory "%s" does not exist', $entityDir));
    }
}

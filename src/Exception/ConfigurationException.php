<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Exception;

class ConfigurationException extends TencentMeetingException
{
    public static function invalidConfigKey(string $key): self
    {
        return new self("无效的配置项: {$key}");
    }

    public static function invalidCacheTtl(): self
    {
        return new self('缓存TTL必须是非负数');
    }

    public static function invalidRetryAttempts(): self
    {
        return new self('重试次数必须是非负数');
    }

    public static function invalidTimeout(): self
    {
        return new self('超时时间必须是正数');
    }
}

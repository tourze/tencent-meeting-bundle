<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Service;

class ConfigService implements ConfigServiceInterface
{
    /**
     * 获取字符串类型的环境变量
     */
    private function getEnvString(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? null;

        if (null !== $value && '' !== $value) {
            if (is_string($value)) {
                return $value;
            }
            if (is_scalar($value)) {
                return (string) $value;
            }

            return $default;
        }

        return $default;
    }

    /**
     * 获取整数类型的环境变量
     */
    private function getEnvInt(string $key, int $default): int
    {
        $value = $_ENV[$key] ?? null;

        if (null !== $value && '' !== $value) {
            if (is_int($value)) {
                return $value;
            }
            if (is_numeric($value)) {
                return (int) $value;
            }

            return $default;
        }

        return $default;
    }

    public function getApiUrl(): string
    {
        return $this->getEnvString('TENCENT_MEETING_API_URL', 'https://api.meeting.qq.com') ?? 'https://api.meeting.qq.com';
    }

    public function getTimeout(): int
    {
        return $this->getEnvInt('TENCENT_MEETING_TIMEOUT', 30);
    }

    public function getRetryTimes(): int
    {
        return $this->getEnvInt('TENCENT_MEETING_RETRY_TIMES', 3);
    }

    public function getLogLevel(): string
    {
        return $this->getEnvString('TENCENT_MEETING_LOG_LEVEL', 'info') ?? 'info';
    }

    public function isDebugEnabled(): bool
    {
        $value = $this->getEnvString('TENCENT_MEETING_DEBUG_ENABLED');

        return null !== $value && filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function getCacheTtl(): int
    {
        return $this->getEnvInt('TENCENT_MEETING_CACHE_TTL', 3600);
    }

    public function getWebhookSecret(): ?string
    {
        return $this->getEnvString('TENCENT_MEETING_WEBHOOK_SECRET');
    }

    public function getCacheDriver(): string
    {
        return $this->getEnvString('TENCENT_MEETING_CACHE_DRIVER', 'file') ?? 'file';
    }

    public function getRedisHost(): ?string
    {
        return $this->getEnvString('TENCENT_MEETING_REDIS_HOST');
    }

    public function getRedisPort(): ?int
    {
        $value = $this->getEnvString('TENCENT_MEETING_REDIS_PORT');

        return null !== $value ? (int) $value : null;
    }

    public function getRedisPassword(): ?string
    {
        return $this->getEnvString('TENCENT_MEETING_REDIS_PASSWORD');
    }

    public function getAuthToken(): ?string
    {
        return $this->getEnvString('TENCENT_MEETING_AUTH_TOKEN');
    }

    public function getSecretKey(): ?string
    {
        return $this->getEnvString('TENCENT_MEETING_SECRET_KEY');
    }

    public function getProxyHost(): ?string
    {
        return $this->getEnvString('TENCENT_MEETING_PROXY_HOST');
    }

    public function getProxyPort(): ?int
    {
        $value = $this->getEnvString('TENCENT_MEETING_PROXY_PORT');

        return null !== $value ? (int) $value : null;
    }

    public function getVerifySsl(): bool
    {
        $value = $this->getEnvString('TENCENT_MEETING_VERIFY_SSL', 'true');

        return null !== $value && filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return array<string, mixed>
     */
    public function getAllConfig(): array
    {
        return [
            'api_url' => $this->getApiUrl(),
            'timeout' => $this->getTimeout(),
            'retry_times' => $this->getRetryTimes(),
            'log_level' => $this->getLogLevel(),
            'debug_enabled' => $this->isDebugEnabled(),
            'cache_ttl' => $this->getCacheTtl(),
            'webhook_secret' => $this->getWebhookSecret(),
            'cache_driver' => $this->getCacheDriver(),
            'redis_host' => $this->getRedisHost(),
            'redis_port' => $this->getRedisPort(),
            'redis_password' => $this->getRedisPassword(),
            'auth_token' => $this->getAuthToken(),
            'proxy_host' => $this->getProxyHost(),
            'proxy_port' => $this->getProxyPort(),
            'verify_ssl' => $this->getVerifySsl(),
        ];
    }
}

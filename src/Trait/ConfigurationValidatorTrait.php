<?php

namespace Tourze\TencentMeetingBundle\Trait;

use Tourze\TencentMeetingBundle\Exception\ConfigurationException;

/**
 * 配置验证trait
 *
 * 提供配置验证的通用方法，降低验证逻辑的复杂度
 */
trait ConfigurationValidatorTrait
{
    /**
     * 验证配置
     *
     * @param array<string, mixed> $configuration 配置数组
     */
    private function validateConfiguration(array $configuration): void
    {
        $this->validateConfigurationKeys($configuration);
        $this->validateConfigurationValues($configuration);
    }

    /**
     * 验证配置键
     *
     * @param array<string, mixed> $configuration 配置数组
     */
    private function validateConfigurationKeys(array $configuration): void
    {
        $validConfigKeys = $this->getValidConfigurationKeys();

        foreach (array_keys($configuration) as $key) {
            if (!in_array($key, $validConfigKeys, true)) {
                throw ConfigurationException::invalidConfigKey($key);
            }
        }
    }

    /**
     * 验证配置值
     *
     * @param array<string, mixed> $configuration 配置数组
     */
    private function validateConfigurationValues(array $configuration): void
    {
        if (isset($configuration['cache_ttl'])) {
            $this->validateCacheTtl($configuration['cache_ttl']);
        }

        if (isset($configuration['retry_attempts'])) {
            $this->validateRetryAttempts($configuration['retry_attempts']);
        }

        if (isset($configuration['timeout'])) {
            $this->validateTimeout($configuration['timeout']);
        }
    }

    /**
     * 验证缓存TTL
     *
     * @param mixed $cacheTtl 缓存TTL值
     */
    private function validateCacheTtl(mixed $cacheTtl): void
    {
        if (!is_numeric($cacheTtl) || $cacheTtl < 0) {
            throw ConfigurationException::invalidCacheTtl();
        }
    }

    /**
     * 验证重试次数
     *
     * @param mixed $retryAttempts 重试次数值
     */
    private function validateRetryAttempts(mixed $retryAttempts): void
    {
        if (!is_numeric($retryAttempts) || $retryAttempts < 0) {
            throw ConfigurationException::invalidRetryAttempts();
        }
    }

    /**
     * 验证超时时间
     *
     * @param mixed $timeout 超时时间值
     */
    private function validateTimeout(mixed $timeout): void
    {
        if (!is_numeric($timeout) || $timeout <= 0) {
            throw ConfigurationException::invalidTimeout();
        }
    }

    /**
     * 获取有效的配置键列表
     *
     * @return array<string> 有效配置键列表
     */
    private function getValidConfigurationKeys(): array
    {
        return [
            'cache_enabled', 'cache_ttl', 'retry_attempts', 'timeout',
            'debug_mode', 'log_level', 'auto_refresh',
        ];
    }
}

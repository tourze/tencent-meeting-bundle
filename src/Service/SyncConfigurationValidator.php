<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Service;

use Tourze\TencentMeetingBundle\Exception\ApiException;

/**
 * 同步配置验证器
 *
 * 负责验证同步配置的有效性
 */
class SyncConfigurationValidator
{
    /** @var array<string> */
    private const VALID_CONFIG_KEYS = [
        'auto_sync', 'sync_interval', 'sync_types', 'batch_size',
        'retry_attempts', 'timeout', 'parallel_sync',
    ];

    /** @var array<string> */
    private const VALID_SYNC_TYPES = ['users', 'rooms', 'meetings', 'recordings'];

    private const MIN_SYNC_INTERVAL = 60;

    /**
     * 验证同步配置
     *
     * @param array<string, mixed> $configuration 同步配置
     */
    public function validate(array $configuration): void
    {
        $this->validateConfigurationKeys($configuration);
        $this->validateSyncInterval($configuration);
        $this->validateSyncTypes($configuration);
    }

    /**
     * 验证配置键名
     *
     * @param array<string, mixed> $configuration
     */
    private function validateConfigurationKeys(array $configuration): void
    {
        foreach (array_keys($configuration) as $key) {
            if (!in_array($key, self::VALID_CONFIG_KEYS, true)) {
                throw new ApiException("无效的同步配置项: {$key}");
            }
        }
    }

    /**
     * 验证同步间隔
     *
     * @param array<string, mixed> $configuration
     */
    private function validateSyncInterval(array $configuration): void
    {
        if (!isset($configuration['sync_interval'])) {
            return;
        }

        $interval = $configuration['sync_interval'];
        if (!is_numeric($interval) || (int) $interval < self::MIN_SYNC_INTERVAL) {
            throw new ApiException('同步间隔必须至少为60秒');
        }
    }

    /**
     * 验证同步类型
     *
     * @param array<string, mixed> $configuration
     */
    private function validateSyncTypes(array $configuration): void
    {
        if (!isset($configuration['sync_types'])) {
            return;
        }

        $syncTypes = $configuration['sync_types'];
        if (!is_array($syncTypes)) {
            throw new ApiException('sync_types必须是数组');
        }

        foreach ($syncTypes as $type) {
            if (!is_string($type) || !in_array($type, self::VALID_SYNC_TYPES, true)) {
                $typeStr = is_string($type) ? $type : gettype($type);
                throw new ApiException("无效的同步类型: {$typeStr}");
            }
        }
    }
}

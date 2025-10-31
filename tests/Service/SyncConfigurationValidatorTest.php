<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TencentMeetingBundle\Exception\ApiException;
use Tourze\TencentMeetingBundle\Service\SyncConfigurationValidator;

/**
 * @internal
 */
#[CoversClass(SyncConfigurationValidator::class)]
final class SyncConfigurationValidatorTest extends TestCase
{
    private SyncConfigurationValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new SyncConfigurationValidator();
    }

    public function testValidateValidConfiguration(): void
    {
        $config = [
            'auto_sync' => true,
            'sync_interval' => 3600,
            'sync_types' => ['users', 'rooms'],
        ];

        // 验证通过 - 如果没有抛出异常，测试就通过
        $this->validator->validate($config);
        // No assertion needed - if validate() doesn't throw, the test passes
        $this->expectNotToPerformAssertions();
    }

    public function testValidateInvalidConfigKey(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('无效的同步配置项: invalid_key');

        $config = ['invalid_key' => 'value'];
        $this->validator->validate($config);
    }

    public function testValidateInvalidSyncInterval(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('同步间隔必须至少为60秒');

        $config = ['sync_interval' => 30];
        $this->validator->validate($config);
    }

    public function testValidateInvalidSyncTypes(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('无效的同步类型');

        $config = ['sync_types' => ['invalid_type']];
        $this->validator->validate($config);
    }

    public function testValidateSyncTypesNotArray(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('sync_types必须是数组');

        $config = ['sync_types' => 'not_array'];
        $this->validator->validate($config);
    }
}

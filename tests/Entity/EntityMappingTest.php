<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

/**
 * @internal
 */
#[CoversClass(TencentMeetingConfig::class)]
final class EntityMappingTest extends AbstractEntityTestCase
{
    protected function createEntity(): TencentMeetingConfig
    {
        return new TencentMeetingConfig();
    }

    public function testConfigEntityExists(): void
    {
        // 这个测试会失败，因为实体类还不存在
        $config = new TencentMeetingConfig();
        $this->assertInstanceOf(TencentMeetingConfig::class, $config);
    }

    public function testConfigEntityHasRequiredFields(): void
    {
        $config = new TencentMeetingConfig();

        $this->assertInstanceOf(TencentMeetingConfig::class, $config);
        $this->assertSame(0, $config->getId()); // New entities have ID = 0 before persistence
        $this->assertObjectHasProperty('secretKey', $config);
        $this->assertObjectHasProperty('authType', $config);
        $this->assertObjectHasProperty('enabled', $config);
        $this->assertObjectHasProperty('webhookToken', $config);
        $this->assertObjectHasProperty('createTime', $config);
        $this->assertObjectHasProperty('updateTime', $config);
    }

    public function testConfigEntityHasGetterSetters(): void
    {
        $config = new TencentMeetingConfig();

        // 测试getter/setter方法
        $config->setAppId('test_app_id');
        $this->assertEquals('test_app_id', $config->getAppId());

        $config->setEnabled(true);
        $this->assertTrue($config->isEnabled());
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'appId' => ['appId', 'test_app_id_123'],
            'secretId' => ['secretId', 'test_secret_id_456'],
            'secretKey' => ['secretKey', 'test_secret_key_789'],
            'authType' => ['authType', 'JWT'],
            'webhookToken' => ['webhookToken', 'webhook_token_123'],
            'enabled' => ['enabled', true],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
            'updateTime' => ['updateTime', new \DateTimeImmutable('2024-01-01 09:30:00')],
        ];
    }
}

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
final class TencentMeetingConfigTest extends AbstractEntityTestCase
{
    protected function createEntity(): TencentMeetingConfig
    {
        return new TencentMeetingConfig();
    }

    public function testEntityCreation(): void
    {
        $config = new TencentMeetingConfig();
        $this->assertInstanceOf(TencentMeetingConfig::class, $config);
    }

    public function testAppId(): void
    {
        $config = new TencentMeetingConfig();
        $config->setAppId('test_app');
        $this->assertEquals('test_app', $config->getAppId());
    }

    public function testEnabled(): void
    {
        $config = new TencentMeetingConfig();
        $this->assertTrue($config->isEnabled());

        $config->setEnabled(false);
        $this->assertFalse($config->isEnabled());
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

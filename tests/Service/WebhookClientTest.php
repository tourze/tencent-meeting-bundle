<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TencentMeetingBundle\Service\WebhookClient;

/**
 * @internal
 */
#[CoversClass(WebhookClient::class)]
#[RunTestsInSeparateProcesses]
final class WebhookClientTest extends AbstractIntegrationTestCase
{
    private WebhookClient $webhookClient;

    protected function onSetUp(): void
    {
        $this->webhookClient = self::getService(WebhookClient::class);
    }

    public function testWebhookClientCanBeInstantiated(): void
    {
        $this->assertInstanceOf(WebhookClient::class, $this->webhookClient);
    }

    public function testGetWebhookWithValidId(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testCreateWebhookWithValidData(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testCreateWebhookWithMissingRequiredFields(): void
    {
        $webhookData = [
            'url' => 'https://example.com/webhook',
            // missing events
        ];

        $result = $this->webhookClient->createWebhook($webhookData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('Webhook数据缺少必需字段: events', $result['error']);
    }

    public function testCreateWebhookWithInvalidUrl(): void
    {
        $webhookData = [
            'url' => 'invalid-url',
            'events' => ['meeting.created'],
        ];

        $result = $this->webhookClient->createWebhook($webhookData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('无效的URL格式', $result['error']);
    }

    public function testCreateWebhookWithInvalidEventsFormat(): void
    {
        $webhookData = [
            'url' => 'https://example.com/webhook',
            'events' => 'not_an_array',
        ];

        $result = $this->webhookClient->createWebhook($webhookData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('事件列表必须是数组', $result['error']);
    }

    public function testCreateWebhookWithInvalidEventType(): void
    {
        $webhookData = [
            'url' => 'https://example.com/webhook',
            'events' => ['invalid.event'],
        ];

        $result = $this->webhookClient->createWebhook($webhookData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('无效的事件类型: invalid.event', $result['error']);
    }

    public function testCreateWebhookWithInvalidSecret(): void
    {
        $webhookData = [
            'url' => 'https://example.com/webhook',
            'events' => ['meeting.created'],
            'secret' => '123', // too short
        ];

        $result = $this->webhookClient->createWebhook($webhookData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('Webhook密钥必须是至少8个字符的字符串', $result['error']);
    }

    public function testUpdateWebhookWithValidData(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testDeleteWebhookWithValidId(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testListWebhooksWithFilters(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testTestWebhookWithValidId(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testGetWebhookEventsWithValidId(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testEnableWebhookWithValidId(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testDisableWebhookWithValidId(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testRetryWebhookWithValidData(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testGetWebhookLogsWithFilters(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testGetWebhookStatusWithValidId(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testHandleApiExceptionInGetWebhook(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testFormatWebhookResponseWithInvalidResponse(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testCreateWebhookWithValidEventTypes(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }
}

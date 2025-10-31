<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\TencentMeetingBundle\Exception\ApiException;
use Tourze\TencentMeetingBundle\Service\ConfigService;
use Tourze\TencentMeetingBundle\Service\HttpClientService;
use Tourze\TencentMeetingBundle\Service\WebhookClient;

/**
 * @internal
 */
#[CoversClass(WebhookClient::class)]
final class WebhookClientTest extends TestCase
{
    private WebhookClient $webhookClient;

    private ConfigService&MockObject $configService;

    private HttpClientService&MockObject $httpClientService;

    private LoggerInterface&MockObject $loggerService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configService = $this->createMock(ConfigService::class);
        $this->httpClientService = $this->createMock(HttpClientService::class);
        $this->loggerService = $this->createMock(LoggerInterface::class);

        // 配置默认的ConfigService方法返回值
        $this->configService->method('getApiUrl')->willReturn('https://api.meeting.qq.com');
        $this->configService->method('getTimeout')->willReturn(30);
        $this->configService->method('getRetryTimes')->willReturn(3);

        $this->webhookClient = new WebhookClient(
            $this->configService,
            $this->httpClientService,
            $this->loggerService
        );
    }

    public function testWebhookClientCanBeInstantiated(): void
    {
        $this->assertInstanceOf(WebhookClient::class, $this->webhookClient);
    }

    public function testGetWebhookWithValidId(): void
    {
        $webhookId = 'webhook123';
        $expectedResponse = [
            'success' => true,
            'webhook_id' => 'webhook123',
            'url' => 'https://example.com/webhook',
            'events' => ['meeting.created', 'meeting.started'],
            'enabled' => true,
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.meeting.qq.com/v1/webhooks/webhook123')
            ->willReturn($expectedResponse)
        ;

        $result = $this->webhookClient->getWebhook($webhookId);

        $this->assertTrue($result['success']);
        $this->assertSame('webhook123', $result['webhook_id']);
        $this->assertSame('https://example.com/webhook', $result['url']);
    }

    public function testCreateWebhookWithValidData(): void
    {
        $webhookData = [
            'url' => 'https://example.com/webhook',
            'events' => ['meeting.created', 'meeting.started'],
            'secret' => 'webhook_secret_key',
            'description' => 'Test webhook',
        ];

        $expectedResponse = [
            'success' => true,
            'webhook_id' => 'webhook456',
            'url' => 'https://example.com/webhook',
            'events' => ['meeting.created', 'meeting.started'],
            'enabled' => true,
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->webhookClient->createWebhook($webhookData);

        $this->assertTrue($result['success']);
        $this->assertSame('webhook456', $result['webhook_id']);
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
        $webhookId = 'webhook123';
        $updateData = [
            'url' => 'https://example.com/new-webhook',
            'events' => ['meeting.created', 'meeting.ended'],
        ];

        $expectedResponse = [
            'success' => true,
            'webhook_id' => 'webhook123',
            'url' => 'https://example.com/new-webhook',
            'events' => ['meeting.created', 'meeting.ended'],
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->webhookClient->updateWebhook($webhookId, $updateData);

        $this->assertTrue($result['success']);
        $this->assertSame('https://example.com/new-webhook', $result['url']);
    }

    public function testDeleteWebhookWithValidId(): void
    {
        $webhookId = 'webhook123';
        $expectedResponse = [
            'success' => true,
            'webhook_id' => 'webhook123',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('DELETE', 'https://api.meeting.qq.com/v1/webhooks/webhook123')
            ->willReturn($expectedResponse)
        ;

        $result = $this->webhookClient->deleteWebhook($webhookId);

        $this->assertTrue($result['success']);
    }

    public function testListWebhooksWithFilters(): void
    {
        $filters = [
            'page' => 1,
            'page_size' => 10,
            'enabled' => true,
            'event_type' => 'meeting.created',
        ];

        $expectedResponse = [
            'success' => true,
            'webhooks' => [],
            'pagination' => [],
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->webhookClient->listWebhooks($filters);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('webhooks', $result);
    }

    public function testTestWebhookWithValidId(): void
    {
        $webhookId = 'webhook123';
        $expectedResponse = [
            'success' => true,
            'webhook_id' => 'webhook123',
            'test_result' => 'success',
            'response_time' => 150,
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.meeting.qq.com/v1/webhooks/webhook123/test', [])
            ->willReturn($expectedResponse)
        ;

        $result = $this->webhookClient->testWebhook($webhookId);

        $this->assertTrue($result['success']);
        $this->assertSame('success', $result['test_result'] ?? null);
    }

    public function testGetWebhookEventsWithValidId(): void
    {
        $webhookId = 'webhook123';
        $expectedResponse = [
            'success' => true,
            'webhook_id' => 'webhook123',
            'events' => [
                'meeting.created',
                'meeting.started',
                'meeting.ended',
            ],
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.meeting.qq.com/v1/webhooks/webhook123/events')
            ->willReturn($expectedResponse)
        ;

        $result = $this->webhookClient->getWebhookEvents($webhookId);

        $this->assertTrue($result['success']);
        $this->assertCount(3, (array) $result['events']);
    }

    public function testEnableWebhookWithValidId(): void
    {
        $webhookId = 'webhook123';
        $expectedResponse = [
            'success' => true,
            'webhook_id' => 'webhook123',
            'enabled' => true,
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.meeting.qq.com/v1/webhooks/webhook123/enable', [])
            ->willReturn($expectedResponse)
        ;

        $result = $this->webhookClient->enableWebhook($webhookId);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['enabled']);
    }

    public function testDisableWebhookWithValidId(): void
    {
        $webhookId = 'webhook123';
        $expectedResponse = [
            'success' => true,
            'webhook_id' => 'webhook123',
            'enabled' => false,
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.meeting.qq.com/v1/webhooks/webhook123/disable', [])
            ->willReturn($expectedResponse)
        ;

        $result = $this->webhookClient->disableWebhook($webhookId);

        $this->assertTrue($result['success']);
        $this->assertFalse($result['enabled']);
    }

    public function testRetryWebhookWithValidData(): void
    {
        $webhookId = 'webhook123';
        $eventId = 'event456';

        $expectedResponse = [
            'success' => true,
            'webhook_id' => 'webhook123',
            'event_id' => 'event456',
            'retry_result' => 'success',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.meeting.qq.com/v1/webhooks/webhook123/retry', ['event_id' => 'event456'])
            ->willReturn($expectedResponse)
        ;

        $result = $this->webhookClient->retryWebhook($webhookId, $eventId);

        $this->assertTrue($result['success']);
        $this->assertSame('success', $result['retry_result'] ?? null);
    }

    public function testGetWebhookLogsWithFilters(): void
    {
        $webhookId = 'webhook123';
        $filters = [
            'page' => 1,
            'page_size' => 20,
            'status' => 'success',
            'event_type' => 'meeting.created',
            'start_time' => 1704067200,
            'end_time' => 1704070800,
        ];

        $expectedResponse = [
            'success' => true,
            'webhook_id' => 'webhook123',
            'logs' => [],
            'pagination' => [],
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->webhookClient->getWebhookLogs($webhookId, $filters);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('logs', $result);
    }

    public function testGetWebhookStatusWithValidId(): void
    {
        $webhookId = 'webhook123';
        $expectedResponse = [
            'success' => true,
            'webhook_id' => 'webhook123',
            'status' => 'active',
            'last_delivery' => '2024-01-01 10:00:00',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.meeting.qq.com/v1/webhooks/webhook123/status')
            ->willReturn($expectedResponse)
        ;

        $result = $this->webhookClient->getWebhookStatus($webhookId);

        $this->assertTrue($result['success']);
        $this->assertSame('active', $result['status']);
    }

    public function testHandleApiExceptionInGetWebhook(): void
    {
        $webhookId = 'webhook123';

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new ApiException('Webhook not found', 404))
        ;

        $result = $this->webhookClient->getWebhook($webhookId);

        $this->assertFalse($result['success']);
        $this->assertSame('API请求失败: Webhook not found', $result['error']);
        $this->assertSame(500, $result['code']);
        $this->assertSame('getWebhook', $result['operation']);
    }

    public function testFormatWebhookResponseWithInvalidResponse(): void
    {
        $webhookId = 'webhook123';

        $invalidResponse = [
            'success' => false,
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($invalidResponse)
        ;

        $result = $this->webhookClient->getWebhook($webhookId);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('Webhook操作失败', $result['error']);
    }

    public function testCreateWebhookWithValidEventTypes(): void
    {
        $validEvents = [
            'meeting.created',
            'meeting.updated',
            'meeting.deleted',
            'meeting.started',
            'meeting.ended',
            'meeting.cancelled',
            'user.joined',
            'user.left',
            'recording.started',
            'recording.ended',
            'recording.ready',
        ];

        $webhookData = [
            'url' => 'https://example.com/webhook',
            'events' => $validEvents,
            'secret' => 'valid_secret_key',
        ];

        $expectedResponse = [
            'success' => true,
            'webhook_id' => 'webhook789',
            'url' => 'https://example.com/webhook',
            'events' => $validEvents,
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->webhookClient->createWebhook($webhookData);

        $this->assertTrue($result['success']);
        $this->assertSame('webhook789', $result['webhook_id']);
        $this->assertCount(11, (array) $result['events']);
    }
}

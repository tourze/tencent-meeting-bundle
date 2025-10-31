<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\WebhookEvent;

/**
 * @internal
 */
#[CoversClass(WebhookEvent::class)]
final class WebhookEventTest extends AbstractEntityTestCase
{
    protected function createEntity(): WebhookEvent
    {
        return new WebhookEvent();
    }

    public function testWebhookEventEntity(): void
    {
        $webhookEvent = new WebhookEvent();
        $this->assertInstanceOf(WebhookEvent::class, $webhookEvent);

        $webhookEvent->setEventId('event_123');
        $webhookEvent->setEventType('meeting.started');
        $webhookEvent->setPayload('{"meeting_id": "123"}');

        $this->assertEquals('event_123', $webhookEvent->getEventId());
        $this->assertEquals('meeting.started', $webhookEvent->getEventType());
        $this->assertEquals('{"meeting_id": "123"}', $webhookEvent->getPayload());
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'eventId' => ['eventId', 'event_123'],
            'eventType' => ['eventType', 'meeting.started'],
            'payload' => ['payload', '{"meeting_id": "123", "user_id": "user_456"}'],
            'meetingId' => ['meetingId', 'meeting_789'],
            'userId' => ['userId', 'user_456'],
            'eventTime' => ['eventTime', new \DateTimeImmutable('2024-01-01 10:00:00')],
            'processStatus' => ['processStatus', 'pending'],
            'processResult' => ['processResult', 'Processing completed successfully'],
            'processingTime' => ['processingTime', new \DateTimeImmutable('2024-01-01 10:01:00')],
            'retryCount' => ['retryCount', 0],
            'nextRetryTime' => ['nextRetryTime', new \DateTimeImmutable('2024-01-01 10:05:00')],
            'errorMessage' => ['errorMessage', 'Test error message'],
            'signatureVerified' => ['signatureVerified', true],
            'sourceIp' => ['sourceIp', '192.168.1.100'],
            'userAgent' => ['userAgent', 'TencentMeeting-Webhook/1.0'],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
            'updateTime' => ['updateTime', new \DateTimeImmutable('2024-01-01 09:30:00')],
        ];
    }
}

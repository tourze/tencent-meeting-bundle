<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Entity\WebhookEvent;

class WebhookEventFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var TencentMeetingConfig $config */
        $config = $this->getReference(TencentMeetingConfigFixtures::CONFIG_REFERENCE_1, TencentMeetingConfig::class);

        $eventTypes = ['meeting.started', 'meeting.ended', 'participant.joined', 'participant.left'];
        $statuses = ['pending', 'processed', 'failed'];

        for ($i = 0; $i < 4; ++$i) {
            $webhookEvent = new WebhookEvent();
            $webhookEvent->setEventId('event_' . ($i + 1));
            $webhookEvent->setEventType($eventTypes[$i]);
            $payload = json_encode([
                'meeting_id' => 'meeting_' . ($i + 1),
                'timestamp' => time(),
                'data' => ['key' => 'value_' . ($i + 1)],
            ]);
            $webhookEvent->setPayload(false !== $payload ? $payload : '{}');
            $webhookEvent->setMeetingId('meeting_' . ($i + 1));
            $webhookEvent->setUserId('user_' . ($i + 1));
            $webhookEvent->setEventTime(new \DateTimeImmutable('-1 hour'));
            $webhookEvent->setProcessStatus($statuses[$i % 3]);
            $webhookEvent->setProcessResult(0 === $i % 2 ? 'success' : 'error');
            $webhookEvent->setProcessingTime(new \DateTimeImmutable('-30 minutes'));
            $webhookEvent->setRetryCount($i);

            if (3 === $i) {
                $webhookEvent->setNextRetryTime(new \DateTimeImmutable('+10 minutes'));
                $webhookEvent->setErrorMessage('测试错误消息');
            }

            $webhookEvent->setSignatureVerified(0 === $i % 2);
            $webhookEvent->setSourceIp('192.168.1.' . ($i + 1));
            $webhookEvent->setUserAgent('TencentMeeting-Webhook/1.0');
            $webhookEvent->setConfig($config);

            $manager->persist($webhookEvent);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TencentMeetingConfigFixtures::class,
        ];
    }
}

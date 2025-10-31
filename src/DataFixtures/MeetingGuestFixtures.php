<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\MeetingGuest;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Enum\MeetingStatus;

class MeetingGuestFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 使用已存在的配置实体
        /** @var TencentMeetingConfig $config */
        $config = $this->getReference(TencentMeetingConfigFixtures::CONFIG_REFERENCE_1, TencentMeetingConfig::class);

        $meeting = new Meeting();
        $meeting->setMeetingId('test-meeting-001');
        $meeting->setMeetingCode('123456789');
        $meeting->setSubject('测试会议');
        $meeting->setStartTime(new \DateTimeImmutable('+1 hour'));
        $meeting->setDuration(60);
        $meeting->setUserId('test-user-001');
        $meeting->setStatus(MeetingStatus::SCHEDULED);
        $meeting->setConfig($config);
        $manager->persist($meeting);

        $guest = new MeetingGuest();
        $guest->setGuestName('测试嘉宾');
        $guest->setEmail('guest@test.local');
        $guest->setPhone('13800138000');
        $guest->setMeeting($meeting);
        $guest->setInviteStatus('invited');
        $guest->setConfig($config);
        $manager->persist($guest);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TencentMeetingConfigFixtures::class,
        ];
    }
}

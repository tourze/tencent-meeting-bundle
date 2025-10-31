<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Enum\MeetingStatus;

class MeetingFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 获取之前创建的配置
        $config = $this->getReference(TencentMeetingConfigFixtures::CONFIG_REFERENCE_1, TencentMeetingConfig::class);

        // 创建测试用的会议数据
        for ($i = 1; $i <= 5; ++$i) {
            $meeting = new Meeting();
            $meeting->setMeetingId('fixture-meeting-' . $i . '-' . uniqid());
            $meeting->setMeetingCode('fixture-code-' . $i);
            $meeting->setSubject('Fixture Test Meeting ' . $i);
            $meeting->setStartTime(new \DateTimeImmutable('+' . $i . ' hours'));
            $meeting->setEndTime(new \DateTimeImmutable('+' . ($i + 1) . ' hours'));
            $meeting->setStatus(MeetingStatus::SCHEDULED);
            $meeting->setDuration(60);
            $meeting->setUserId('fixture-user-' . $i);
            $meeting->setConfig($config);

            $this->addReference('meeting-' . $i, $meeting);
            $manager->persist($meeting);
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

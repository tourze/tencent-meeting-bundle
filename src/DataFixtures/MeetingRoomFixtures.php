<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\MeetingRoom;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

class MeetingRoomFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var TencentMeetingConfig $config */
        $config = $this->getReference(TencentMeetingConfigFixtures::CONFIG_REFERENCE_1, TencentMeetingConfig::class);

        // 创建测试用的会议室数据
        for ($i = 1; $i <= 3; ++$i) {
            $meetingRoom = new MeetingRoom();
            $meetingRoom->setRoomId("room_test_{$i}");
            $meetingRoom->setName("测试会议室{$i}");
            $meetingRoom->setDescription("这是第{$i}个测试会议室");
            $meetingRoom->setCapacity(10);
            $meetingRoom->setConfig($config);

            $manager->persist($meetingRoom);
        }

        $manager->flush();
    }

    /**
     * @return array<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            TencentMeetingConfigFixtures::class,
        ];
    }
}

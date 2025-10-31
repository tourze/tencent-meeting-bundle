<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\Room;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

class RoomFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 使用已存在的配置实体
        /** @var TencentMeetingConfig $config */
        $config = $this->getReference(TencentMeetingConfigFixtures::CONFIG_REFERENCE_1, TencentMeetingConfig::class);

        // 创建测试用的会议室数据
        $roomTypes = ['physical', 'virtual', 'hybrid'];
        $roomData = [
            [
                'name' => '大会议室',
                'description' => '适合大型会议和培训',
                'capacity' => 50,
                'location' => '3楼301',
                'equipment' => '投影仪,音响,白板',
            ],
            [
                'name' => '小会议室',
                'description' => '适合小组讨论',
                'capacity' => 10,
                'location' => '2楼201',
                'equipment' => '显示器,白板',
            ],
            [
                'name' => '虚拟会议室',
                'description' => '纯线上会议',
                'capacity' => 100,
                'location' => null,
                'equipment' => '屏幕共享,录制功能',
            ],
        ];

        for ($i = 0; $i < 3; ++$i) {
            $room = new Room();

            $room->setRoomId('room_' . ($i + 1));
            $room->setName($roomData[$i]['name']);
            $room->setDescription($roomData[$i]['description']);
            $room->setRoomType($roomTypes[$i]);
            $room->setStatus('available');
            $room->setCapacity($roomData[$i]['capacity']);
            $room->setLocation($roomData[$i]['location']);
            $room->setEquipment($roomData[$i]['equipment']);
            $room->setRoomConfig([
                'allow_recording' => true,
                'waiting_room' => $i > 0,
                'breakout_rooms' => 0 === $i,
                'virtual_background' => true,
            ]);
            $room->setBookingRules('提前30分钟预订');
            $room->setOrderWeight($i + 1);
            $room->setRequiresApproval(0 === $i);
            $room->setExpirationTime(new \DateTimeImmutable('2025-12-31 23:59:59'));
            $room->setConfig($config);

            $manager->persist($room);
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

<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\Device;

class DeviceFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 创建测试用的设备数据
        for ($i = 1; $i <= 3; ++$i) {
            $device = new Device();
            $device->setDeviceId("device_test_{$i}");
            $device->setName("测试设备{$i}");
            $device->setDeviceType('camera');
            $device->setBrand("TestBrand{$i}");
            $device->setModel("TestModel{$i}");
            $device->setStatus('online');

            $manager->persist($device);
        }

        $manager->flush();
    }
}

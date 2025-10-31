<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Device;

/**
 * @internal
 */
#[CoversClass(Device::class)]
final class DeviceTest extends AbstractEntityTestCase
{
    protected function createEntity(): Device
    {
        return new Device();
    }

    public function testDeviceCreation(): void
    {
        $device = new Device();

        $this->assertInstanceOf(Device::class, $device);
        $this->assertSame(0, $device->getId()); // New entities have ID = 0 before persistence
        $this->assertEquals('', $device->getDeviceId());
        $this->assertEquals('', $device->getName());
        $this->assertEquals('other', $device->getDeviceType());
        $this->assertEquals('offline', $device->getStatus());
        $this->assertNull($device->getBrand());
        $this->assertNull($device->getModel());
        $this->assertNull($device->getSerialNumber());
        $this->assertNull($device->getActivationCode());
        $this->assertNull($device->getActivationTime());
        $this->assertNull($device->getExpirationTime());
        $this->assertNull($device->getLastOnlineTime());
        $this->assertNull($device->getIpAddress());
        $this->assertNull($device->getMacAddress());
        $this->assertNull($device->getFirmwareVersion());
        $this->assertNull($device->getSoftwareVersion());
        $this->assertNull($device->getDeviceConfig());
        $this->assertNull($device->getRemark());
        $this->assertNull($device->getRoom());
        // Initially, times should be null as they are set automatically by the system
        $this->assertNull($device->getCreateTime());
        $this->assertNull($device->getUpdateTime());
    }

    public function testDeviceSettersAndGetters(): void
    {
        $device = new Device();

        $device->setDeviceId('device_123');
        $device->setName('测试设备');
        $device->setDeviceType('camera');
        $device->setBrand('测试品牌');
        $device->setModel('测试型号');
        $device->setSerialNumber('SN123456');
        $device->setStatus('online');
        $device->setActivationCode('ACT123456');
        $device->setActivationTime(new \DateTimeImmutable('2024-01-01'));
        $device->setExpirationTime(new \DateTimeImmutable('2025-01-01'));
        $device->setLastOnlineTime(new \DateTimeImmutable('2024-06-01'));
        $device->setIpAddress('192.168.1.100');
        $device->setMacAddress('00:11:22:33:44:55');
        $device->setFirmwareVersion('1.0.0');
        $device->setSoftwareVersion('2.0.0');
        $device->setDeviceConfig(['resolution' => '1920x1080']);
        $device->setRemark('测试备注');

        $this->assertEquals('device_123', $device->getDeviceId());
        $this->assertEquals('测试设备', $device->getName());
        $this->assertEquals('camera', $device->getDeviceType());
        $this->assertEquals('测试品牌', $device->getBrand());
        $this->assertEquals('测试型号', $device->getModel());
        $this->assertEquals('SN123456', $device->getSerialNumber());
        $this->assertEquals('online', $device->getStatus());
        $this->assertEquals('ACT123456', $device->getActivationCode());
        $this->assertInstanceOf(\DateTimeImmutable::class, $device->getActivationTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $device->getExpirationTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $device->getLastOnlineTime());
        $this->assertEquals('192.168.1.100', $device->getIpAddress());
        $this->assertEquals('00:11:22:33:44:55', $device->getMacAddress());
        $this->assertEquals('1.0.0', $device->getFirmwareVersion());
        $this->assertEquals('2.0.0', $device->getSoftwareVersion());
        $this->assertEquals(['resolution' => '1920x1080'], $device->getDeviceConfig());
        $this->assertEquals('测试备注', $device->getRemark());
    }

    public function testDeviceToString(): void
    {
        $device = new Device();
        $device->setName('测试设备');

        $this->assertEquals('测试设备', (string) $device);

        $device2 = new Device();
        $device2->setDeviceId('device_456');
        $device2->setName('');

        $this->assertEquals('device_456', (string) $device2);

        $device3 = new Device();
        $this->assertEquals('', (string) $device3);
    }

    public function testDeviceTimeMethods(): void
    {
        $device = new Device();

        // Initially, times should be null as they are set automatically by the system
        $this->assertNull($device->getCreateTime());
        $this->assertNull($device->getUpdateTime());

        $newTime = new \DateTimeImmutable('2024-01-01');
        $device->setCreateTime($newTime);
        $this->assertEquals($newTime, $device->getCreateTime());

        $device->setUpdateTime($newTime);
        $this->assertEquals($newTime, $device->getUpdateTime());
    }

    public function testDeviceRoomAssociation(): void
    {
        $device = new Device();
        $this->assertNull($device->getRoom());

        // Room 关联测试会在实际 Room 类可用时进行
    }

    public function testDeviceTypeChoices(): void
    {
        $device = new Device();

        $validTypes = ['camera', 'microphone', 'speaker', 'display', 'touch_screen', 'whiteboard', 'other'];

        foreach ($validTypes as $type) {
            $device->setDeviceType($type);
            $this->assertEquals($type, $device->getDeviceType());
        }
    }

    public function testDeviceStatusChoices(): void
    {
        $device = new Device();

        $validStatuses = ['online', 'offline', 'maintenance', 'error'];

        foreach ($validStatuses as $status) {
            $device->setStatus($status);
            $this->assertEquals($status, $device->getStatus());
        }
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'deviceId' => ['deviceId', 'device_123'],
            'name' => ['name', 'Test Device'],
            'deviceType' => ['deviceType', 'camera'],
            'brand' => ['brand', 'Test Brand'],
            'model' => ['model', 'Test Model'],
            'serialNumber' => ['serialNumber', 'SN123456'],
            'status' => ['status', 'online'],
            'activationCode' => ['activationCode', 'ACT123456'],
            'activationTime' => ['activationTime', new \DateTimeImmutable('2024-01-01 10:00:00')],
            'expirationTime' => ['expirationTime', new \DateTimeImmutable('2025-01-01 10:00:00')],
            'lastOnlineTime' => ['lastOnlineTime', new \DateTimeImmutable('2024-06-01 10:00:00')],
            'ipAddress' => ['ipAddress', '192.168.1.100'],
            'macAddress' => ['macAddress', '00:11:22:33:44:55'],
            'firmwareVersion' => ['firmwareVersion', '1.0.0'],
            'softwareVersion' => ['softwareVersion', '2.0.0'],
            'deviceConfig' => ['deviceConfig', ['resolution' => '1920x1080', 'fps' => 30]],
            'remark' => ['remark', 'Test device remark'],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
            'updateTime' => ['updateTime', new \DateTimeImmutable('2024-01-01 09:30:00')],
        ];
    }
}

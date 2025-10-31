<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Room;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

/**
 * @internal
 */
#[CoversClass(Room::class)]
final class RoomTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Room();
    }

    public function testRoomCreation(): void
    {
        $room = new Room();

        $this->assertInstanceOf(Room::class, $room);
        $this->assertSame(0, $room->getId()); // New entities have ID = 0 before persistence
        $this->assertNull($room->getDescription());
        $this->assertEquals('virtual', $room->getRoomType());
        $this->assertEquals('available', $room->getStatus());
        $this->assertEquals(10, $room->getCapacity());
        $this->assertNull($room->getLocation());
        $this->assertNull($room->getEquipment());
        $this->assertNull($room->getRoomConfig());
        $this->assertNull($room->getBookingRules());
        $this->assertEquals(0, $room->getOrderWeight());
        $this->assertFalse($room->isRequiresApproval());
        $this->assertNull($room->getExpirationTime());
        // Initially, times should be null as they are set automatically by the system
        $this->assertNull($room->getCreateTime());
        $this->assertNull($room->getUpdateTime());
    }

    public function testRoomSettersAndGetters(): void
    {
        $room = new Room();
        $config = new TencentMeetingConfig();

        $createTime = new \DateTimeImmutable('2024-05-01 09:00:00');
        $updateTime = new \DateTimeImmutable('2024-05-01 09:30:00');
        $expireTime = new \DateTimeImmutable('2025-05-01 09:00:00');
        $roomConfig = [
            'audio_quality' => 'hd',
            'video_quality' => '1080p',
            'max_duration' => 7200,
            'features' => ['screen_share', 'whiteboard', 'recording'],
        ];

        $room->setRoomId('room_123');
        $room->setName('大会议室');
        $room->setDescription('这是一个大型会议室，适合大型会议使用');
        $room->setRoomType('physical');
        $room->setStatus('occupied');
        $room->setCapacity(50);
        $room->setLocation('A座3楼');
        $room->setEquipment('投影仪、音响、视频设备');
        $room->setRoomConfig($roomConfig);
        $room->setBookingRules('需提前预约，管理员审批');
        $room->setOrderWeight(10);
        $room->setRequiresApproval(true);
        $room->setExpirationTime($expireTime);
        $room->setCreateTime($createTime);
        $room->setUpdateTime($updateTime);
        $room->setConfig($config);

        $this->assertEquals('room_123', $room->getRoomId());
        $this->assertEquals('大会议室', $room->getName());
        $this->assertEquals('这是一个大型会议室，适合大型会议使用', $room->getDescription());
        $this->assertEquals('physical', $room->getRoomType());
        $this->assertEquals('occupied', $room->getStatus());
        $this->assertEquals(50, $room->getCapacity());
        $this->assertEquals('A座3楼', $room->getLocation());
        $this->assertEquals('投影仪、音响、视频设备', $room->getEquipment());
        $this->assertEquals($roomConfig, $room->getRoomConfig());
        $this->assertEquals('需提前预约，管理员审批', $room->getBookingRules());
        $this->assertEquals(10, $room->getOrderWeight());
        $this->assertTrue($room->isRequiresApproval());
        $this->assertEquals($expireTime, $room->getExpirationTime());
        $this->assertEquals($createTime, $room->getCreateTime());
        $this->assertEquals($updateTime, $room->getUpdateTime());
        $this->assertSame($config, $room->getConfig());
    }

    public function testRoomToString(): void
    {
        $room = new Room();
        $room->setRoomId('room_001');
        $room->setName('Conference Room A');

        $this->assertEquals('Conference Room A', (string) $room);

        $room2 = new Room();
        $room2->setRoomId('room_002');
        $room2->setName('会议室B');

        $this->assertEquals('会议室B', (string) $room2);
    }

    public function testRoomTypeChoices(): void
    {
        $room = new Room();

        $validTypes = ['physical', 'virtual', 'hybrid'];

        foreach ($validTypes as $type) {
            $room->setRoomType($type);
            $this->assertEquals($type, $room->getRoomType());
        }
    }

    public function testRoomStatusChoices(): void
    {
        $room = new Room();

        $validStatuses = ['available', 'occupied', 'maintenance', 'inactive'];

        foreach ($validStatuses as $status) {
            $room->setStatus($status);
            $this->assertEquals($status, $room->getStatus());
        }
    }

    public function testRoomCapacity(): void
    {
        $room = new Room();

        $capacities = [1, 5, 10, 25, 50, 100, 500];

        foreach ($capacities as $capacity) {
            $room->setCapacity($capacity);
            $this->assertEquals($capacity, $room->getCapacity());
        }
    }

    public function testRoomConfigHandling(): void
    {
        $room = new Room();

        // 测试空配置
        $room->setRoomConfig(null);
        $this->assertNull($room->getRoomConfig());

        // 测试简单配置
        $simpleConfig = ['max_participants' => 25];
        $room->setRoomConfig($simpleConfig);
        $this->assertEquals($simpleConfig, $room->getRoomConfig());

        // 测试复杂配置
        $complexConfig = [
            'audio' => [
                'quality' => 'hd',
                'noise_suppression' => true,
                'echo_cancellation' => true,
            ],
            'video' => [
                'resolution' => '1920x1080',
                'frame_rate' => 30,
                'codec' => 'h264',
            ],
            'features' => [
                'screen_sharing' => true,
                'whiteboard' => true,
                'recording' => true,
                'breakout_rooms' => false,
            ],
            'limits' => [
                'max_participants' => 100,
                'max_duration' => 7200,
                'file_upload_size' => 104857600,
            ],
        ];

        $room->setRoomConfig($complexConfig);
        $this->assertEquals($complexConfig, $room->getRoomConfig());
    }

    public function testRoomLocation(): void
    {
        $room = new Room();

        // 测试初始值为null
        $this->assertNull($room->getLocation());

        // 测试不同类型的位置
        $locations = [
            '1楼会议室',
            'Building A, Floor 3, Room 301',
            '北京市朝阳区xxx大厦',
            'Virtual Room - Cloud Platform',
        ];

        foreach ($locations as $location) {
            $room->setLocation($location);
            $this->assertEquals($location, $room->getLocation());
        }

        // 测试设置为null
        $room->setLocation(null);
        $this->assertNull($room->getLocation());
    }

    public function testRoomEquipment(): void
    {
        $room = new Room();

        // 测试初始值为null
        $this->assertNull($room->getEquipment());

        // 测试设备信息
        $equipmentList = [
            '投影仪',
            '投影仪、音响、白板',
            'Projector, Sound System, Whiteboard, Video Conference Equipment',
            '4K显示器 x2, 专业音响系统, 智能白板, 高清摄像头',
        ];

        foreach ($equipmentList as $equipment) {
            $room->setEquipment($equipment);
            $this->assertEquals($equipment, $room->getEquipment());
        }

        // 测试设置为null
        $room->setEquipment(null);
        $this->assertNull($room->getEquipment());
    }

    public function testRoomBookingRules(): void
    {
        $room = new Room();

        // 测试初始值为null
        $this->assertNull($room->getBookingRules());

        // 测试预订规则
        $rules = [
            '需提前24小时预约',
            '管理员审批后生效',
            'Requires admin approval, 48 hours advance booking',
            '工作日可预约，最长使用4小时，需要部门经理审批',
        ];

        foreach ($rules as $rule) {
            $room->setBookingRules($rule);
            $this->assertEquals($rule, $room->getBookingRules());
        }

        // 测试设置为null
        $room->setBookingRules(null);
        $this->assertNull($room->getBookingRules());
    }

    public function testRoomOrderWeight(): void
    {
        $room = new Room();

        $weights = [0, 1, 5, 10, -5, 100];

        foreach ($weights as $weight) {
            $room->setOrderWeight($weight);
            $this->assertEquals($weight, $room->getOrderWeight());
        }
    }

    public function testRoomRequiresApproval(): void
    {
        $room = new Room();

        // 测试默认值
        $this->assertFalse($room->isRequiresApproval());

        // 测试设置为true
        $room->setRequiresApproval(true);
        $this->assertTrue($room->isRequiresApproval());

        // 测试设置为false
        $room->setRequiresApproval(false);
        $this->assertFalse($room->isRequiresApproval());
    }

    public function testRoomExpireTime(): void
    {
        $room = new Room();

        // 测试初始值为null
        $this->assertNull($room->getExpirationTime());

        // 测试设置过期时间
        $expireTime = new \DateTimeImmutable('2025-12-31 23:59:59');
        $room->setExpirationTime($expireTime);
        $this->assertEquals($expireTime, $room->getExpirationTime());

        // 测试设置为null
        $room->setExpirationTime(null);
        $this->assertNull($room->getExpirationTime());
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'roomId' => ['roomId', 'test.room'],
            'name' => ['name', 'Test Room'],
            'description' => ['description', 'Test Description'],
            'roomType' => ['roomType', 'virtual'],
            'status' => ['status', 'available'],
            'capacity' => ['capacity', 10],
            'location' => ['location', 'Test Location'],
            'equipment' => ['equipment', 'Test Equipment'],
            'roomConfig' => ['roomConfig', ['max_participants' => 25, 'recording' => true]],
            'bookingRules' => ['bookingRules', 'Test Rules'],
            'orderWeight' => ['orderWeight', 10],
            'requiresApproval' => ['requiresApproval', false],
            'expirationTime' => ['expirationTime', new \DateTimeImmutable('2025-12-31 23:59:59')],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
            'updateTime' => ['updateTime', new \DateTimeImmutable('2024-01-01 09:30:00')],
        ];
    }
}

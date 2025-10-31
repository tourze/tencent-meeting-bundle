<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\MeetingRoom;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

/**
 * @internal
 */
#[CoversClass(MeetingRoom::class)]
final class MeetingRoomTest extends AbstractEntityTestCase
{
    protected function createEntity(): MeetingRoom
    {
        return new MeetingRoom();
    }

    public function testMeetingRoomCreation(): void
    {
        $meetingRoom = new MeetingRoom();
        $this->assertInstanceOf(MeetingRoom::class, $meetingRoom);
    }

    public function testMeetingRoomSettersAndGetters(): void
    {
        $meetingRoom = new MeetingRoom();

        // Test name
        $meetingRoom->setName('Test Room');
        $this->assertEquals('Test Room', $meetingRoom->getName());

        // Test roomId
        $meetingRoom->setRoomId('ROOM-001');
        $this->assertEquals('ROOM-001', $meetingRoom->getRoomId());

        // Test capacity
        $meetingRoom->setCapacity(50);
        $this->assertEquals(50, $meetingRoom->getCapacity());

        // Test location
        $meetingRoom->setLocation('Building A, Floor 3');
        $this->assertEquals('Building A, Floor 3', $meetingRoom->getLocation());

        // Test equipmentList
        $equipmentList = 'projector,whiteboard,video_conference';
        $meetingRoom->setEquipmentList($equipmentList);
        $this->assertEquals($equipmentList, $meetingRoom->getEquipmentList());

        // Test description
        $meetingRoom->setDescription('A test meeting room');
        $this->assertEquals('A test meeting room', $meetingRoom->getDescription());

        // Test status
        $meetingRoom->setStatus('maintenance');
        $this->assertEquals('maintenance', $meetingRoom->getStatus());

        // Test supportRecording
        $meetingRoom->setSupportRecording(true);
        $this->assertTrue($meetingRoom->isSupportRecording());

        // Test supportLive
        $meetingRoom->setSupportLive(true);
        $this->assertTrue($meetingRoom->isSupportLive());

        // Test supportScreenShare
        $meetingRoom->setSupportScreenShare(true);
        $this->assertTrue($meetingRoom->isSupportScreenShare());
    }

    public function testMeetingRoomRelations(): void
    {
        $meetingRoom = new MeetingRoom();

        // Create mock object for config relation
        $config = $this->createMock(TencentMeetingConfig::class);

        // Test config relation
        $meetingRoom->setConfig($config);
        $this->assertSame($config, $meetingRoom->getConfig());
    }

    public function testMeetingRoomTimeMethods(): void
    {
        $meetingRoom = new MeetingRoom();

        // Test createTime is set on creation
        // Initially, times should be null as they are set automatically by the system
        $this->assertNull($meetingRoom->getCreateTime());

        // Test updateTime
        $updateTime = new \DateTimeImmutable();
        $meetingRoom->setUpdateTime($updateTime);
        $this->assertEquals($updateTime, $meetingRoom->getUpdateTime());
    }

    public function testMeetingRoomToString(): void
    {
        $meetingRoom = new MeetingRoom();
        $meetingRoom->setName('Test Room');

        $string = (string) $meetingRoom;
        $this->assertStringContainsString('MeetingRoom', $string);
        $this->assertStringContainsString('Test Room', $string);
    }

    public function testMeetingRoomDefaultValues(): void
    {
        $meetingRoom = new MeetingRoom();

        // Test default status
        $this->assertEquals('available', $meetingRoom->getStatus());

        // Test default boolean values
        $this->assertFalse($meetingRoom->isSupportRecording());
        $this->assertFalse($meetingRoom->isSupportLive());
        $this->assertTrue($meetingRoom->isSupportScreenShare());

        // Test default capacity
        $this->assertEquals(1, $meetingRoom->getCapacity());
    }

    public function testMeetingRoomStatusChoices(): void
    {
        $meetingRoom = new MeetingRoom();

        // Test valid status choices
        $meetingRoom->setStatus('available');
        $this->assertEquals('available', $meetingRoom->getStatus());

        $meetingRoom->setStatus('occupied');
        $this->assertEquals('occupied', $meetingRoom->getStatus());

        $meetingRoom->setStatus('maintenance');
        $this->assertEquals('maintenance', $meetingRoom->getStatus());

        $meetingRoom->setStatus('disabled');
        $this->assertEquals('disabled', $meetingRoom->getStatus());
    }

    public function testMeetingRoomEquipmentHandling(): void
    {
        $meetingRoom = new MeetingRoom();

        // Test equipmentList can be null
        $meetingRoom->setEquipmentList(null);
        $this->assertNull($meetingRoom->getEquipmentList());

        // Test equipmentList can be string
        $equipmentList = 'projector,sound_system';
        $meetingRoom->setEquipmentList($equipmentList);
        $this->assertEquals($equipmentList, $meetingRoom->getEquipmentList());
    }

    public function testMeetingRoomStringableInterface(): void
    {
        $meetingRoom = new MeetingRoom();

        // Test that the class implements Stringable
        $this->assertInstanceOf(\Stringable::class, $meetingRoom);

        // Test toString with room name
        $meetingRoom->setName('Conference Room A');
        $string = (string) $meetingRoom;
        $this->assertStringContainsString('Conference Room A', $string);
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'roomId' => ['roomId', 'ROOM-001'],
            'name' => ['name', 'Test Room'],
            'capacity' => ['capacity', 50],
            'location' => ['location', 'Building A, Floor 3'],
            'equipmentList' => ['equipmentList', 'projector,whiteboard,video_conference'],
            'description' => ['description', 'A test meeting room'],
            'status' => ['status', 'available'],
            'supportRecording' => ['supportRecording', true],
            'supportLive' => ['supportLive', true],
            'supportScreenShare' => ['supportScreenShare', true],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
        ];
    }
}

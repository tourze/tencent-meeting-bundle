<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\MeetingBackground;
use Tourze\TencentMeetingBundle\Entity\MeetingDocument;
use Tourze\TencentMeetingBundle\Entity\MeetingGuest;
use Tourze\TencentMeetingBundle\Entity\MeetingLayout;
use Tourze\TencentMeetingBundle\Entity\MeetingRole;
use Tourze\TencentMeetingBundle\Entity\MeetingUser;
use Tourze\TencentMeetingBundle\Entity\MeetingVote;
use Tourze\TencentMeetingBundle\Entity\Recording;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Enum\MeetingStatus;

/**
 * @internal
 */
#[CoversClass(Meeting::class)]
final class MeetingTest extends AbstractEntityTestCase
{
    protected function createEntity(): Meeting
    {
        return new Meeting();
    }

    public function testMeetingCreation(): void
    {
        $meeting = new Meeting();

        $this->assertInstanceOf(Meeting::class, $meeting);
        $this->assertSame(0, $meeting->getId()); // New entities have ID = 0 before persistence
        $this->assertEquals(MeetingStatus::SCHEDULED, $meeting->getStatus());
        $this->assertEquals('Asia/Shanghai', $meeting->getTimezone());
        $this->assertFalse($meeting->isReminderSent());
        // Initially, times should be null as they are set automatically by the system
        $this->assertNull($meeting->getCreateTime());
        $this->assertNull($meeting->getUpdateTime());
        $this->assertNull($meeting->getEndTime());
        $this->assertNull($meeting->getMeetingUrl());
        $this->assertNull($meeting->getPassword());

        // 测试集合初始化
        $this->assertInstanceOf(Collection::class, $meeting->getAttendees());
        $this->assertInstanceOf(Collection::class, $meeting->getRecordings());
        $this->assertInstanceOf(Collection::class, $meeting->getGuests());
        $this->assertInstanceOf(Collection::class, $meeting->getVotes());
        $this->assertInstanceOf(Collection::class, $meeting->getDocuments());
        $this->assertInstanceOf(Collection::class, $meeting->getMeetingRoles());
        $this->assertInstanceOf(Collection::class, $meeting->getMeetingLayouts());
        $this->assertInstanceOf(Collection::class, $meeting->getMeetingBackgrounds());

        $this->assertCount(0, $meeting->getAttendees());
        $this->assertCount(0, $meeting->getRecordings());
        $this->assertCount(0, $meeting->getGuests());
        $this->assertCount(0, $meeting->getVotes());
        $this->assertCount(0, $meeting->getDocuments());
        $this->assertCount(0, $meeting->getMeetingRoles());
        $this->assertCount(0, $meeting->getMeetingLayouts());
        $this->assertCount(0, $meeting->getMeetingBackgrounds());
    }

    public function testMeetingSettersAndGetters(): void
    {
        $meeting = new Meeting();
        $config = new TencentMeetingConfig();

        $startTime = new \DateTimeImmutable('2024-06-01 10:00:00');
        $endTime = new \DateTimeImmutable('2024-06-01 11:00:00');
        $createTime = new \DateTimeImmutable('2024-05-01 09:00:00');
        $updateTime = new \DateTimeImmutable('2024-05-01 09:30:00');

        $meeting->setMeetingId('meeting_123');
        $meeting->setMeetingCode('code_456');
        $meeting->setSubject('测试会议');
        $meeting->setStartTime($startTime);
        $meeting->setEndTime($endTime);
        $meeting->setStatus(MeetingStatus::IN_PROGRESS);
        $meeting->setDuration(60);
        $meeting->setUserId('host_user_123');
        $meeting->setTimezone('UTC');
        $meeting->setMeetingUrl('https://meeting.example.com/join/123');
        $meeting->setPassword('secret123');
        $meeting->setCreateTime($createTime);
        $meeting->setUpdateTime($updateTime);
        $meeting->setReminderSent(true);
        $meeting->setConfig($config);

        $this->assertEquals('meeting_123', $meeting->getMeetingId());
        $this->assertEquals('code_456', $meeting->getMeetingCode());
        $this->assertEquals('测试会议', $meeting->getSubject());
        $this->assertEquals($startTime, $meeting->getStartTime());
        $this->assertEquals($endTime, $meeting->getEndTime());
        $this->assertEquals(MeetingStatus::IN_PROGRESS, $meeting->getStatus());
        $this->assertEquals(60, $meeting->getDuration());
        $this->assertEquals('host_user_123', $meeting->getUserId());
        $this->assertEquals('UTC', $meeting->getTimezone());
        $this->assertEquals('https://meeting.example.com/join/123', $meeting->getMeetingUrl());
        $this->assertEquals('secret123', $meeting->getPassword());
        $this->assertEquals($createTime, $meeting->getCreateTime());
        $this->assertEquals($updateTime, $meeting->getUpdateTime());
        $this->assertTrue($meeting->isReminderSent());
        $this->assertSame($config, $meeting->getConfig());
    }

    public function testMeetingToString(): void
    {
        $meeting = new Meeting();
        $meeting->setSubject('测试会议');

        // 使用反射设置ID，因为它通常由ORM设置
        $reflection = new \ReflectionClass($meeting);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($meeting, 123);

        $this->assertEquals('Meeting[id=123, subject=测试会议]', (string) $meeting);
    }

    public function testMeetingImplementsStringable(): void
    {
        $meeting = new Meeting();
        $meeting->setMeetingId('meeting_123');
        $meeting->setSubject('Test Meeting');

        $this->assertInstanceOf(\Stringable::class, $meeting);
        // 强制转换总是返回string，验证具体内容更有意义
        $this->assertEquals('Meeting[id=new, subject=Test Meeting]', (string) $meeting);
    }

    public function testMeetingCollectionMethods(): void
    {
        $meeting = new Meeting();

        // 测试添加和移除参会者
        $attendee = new MeetingUser();
        $meeting->addAttendee($attendee);
        $this->assertCount(1, $meeting->getAttendees());
        $this->assertTrue($meeting->getAttendees()->contains($attendee));
        $this->assertSame($meeting, $attendee->getMeeting());

        $meeting->removeAttendee($attendee);
        $this->assertCount(0, $meeting->getAttendees());
        $this->assertFalse($meeting->getAttendees()->contains($attendee));

        // 测试添加重复项不会增加集合大小
        $meeting->addAttendee($attendee);
        $meeting->addAttendee($attendee);
        $this->assertCount(1, $meeting->getAttendees());
    }

    public function testMeetingRecordingMethods(): void
    {
        $meeting = new Meeting();
        $recording = new Recording();

        $meeting->addRecording($recording);
        $this->assertCount(1, $meeting->getRecordings());
        $this->assertTrue($meeting->getRecordings()->contains($recording));
        $this->assertSame($meeting, $recording->getMeeting());

        $meeting->removeRecording($recording);
        $this->assertCount(0, $meeting->getRecordings());
    }

    public function testMeetingGuestMethods(): void
    {
        $meeting = new Meeting();
        $guest = new MeetingGuest();

        $meeting->addGuest($guest);
        $this->assertCount(1, $meeting->getGuests());
        $this->assertTrue($meeting->getGuests()->contains($guest));
        $this->assertSame($meeting, $guest->getMeeting());

        $meeting->removeGuest($guest);
        $this->assertCount(0, $meeting->getGuests());
    }

    public function testMeetingVoteMethods(): void
    {
        $meeting = new Meeting();
        $vote = new MeetingVote();

        $meeting->addVote($vote);
        $this->assertCount(1, $meeting->getVotes());
        $this->assertTrue($meeting->getVotes()->contains($vote));
        $this->assertSame($meeting, $vote->getMeeting());

        $meeting->removeVote($vote);
        $this->assertCount(0, $meeting->getVotes());
    }

    public function testMeetingDocumentMethods(): void
    {
        $meeting = new Meeting();
        $document = new MeetingDocument();

        $meeting->addDocument($document);
        $this->assertCount(1, $meeting->getDocuments());
        $this->assertTrue($meeting->getDocuments()->contains($document));
        $this->assertSame($meeting, $document->getMeeting());

        $meeting->removeDocument($document);
        $this->assertCount(0, $meeting->getDocuments());
    }

    public function testMeetingRoleMethods(): void
    {
        $meeting = new Meeting();
        $meetingRole = new MeetingRole();

        $meeting->addMeetingRole($meetingRole);
        $this->assertCount(1, $meeting->getMeetingRoles());
        $this->assertTrue($meeting->getMeetingRoles()->contains($meetingRole));
        $this->assertSame($meeting, $meetingRole->getMeeting());

        $meeting->removeMeetingRole($meetingRole);
        $this->assertCount(0, $meeting->getMeetingRoles());
    }

    public function testMeetingLayoutMethods(): void
    {
        $meeting = new Meeting();
        $meetingLayout = new MeetingLayout();

        $meeting->addMeetingLayout($meetingLayout);
        $this->assertCount(1, $meeting->getMeetingLayouts());
        $this->assertTrue($meeting->getMeetingLayouts()->contains($meetingLayout));
        $this->assertSame($meeting, $meetingLayout->getMeeting());

        $meeting->removeMeetingLayout($meetingLayout);
        $this->assertCount(0, $meeting->getMeetingLayouts());
    }

    public function testMeetingBackgroundMethods(): void
    {
        $meeting = new Meeting();
        $meetingBackground = new MeetingBackground();

        $meeting->addMeetingBackground($meetingBackground);
        $this->assertCount(1, $meeting->getMeetingBackgrounds());
        $this->assertTrue($meeting->getMeetingBackgrounds()->contains($meetingBackground));
        $this->assertSame($meeting, $meetingBackground->getMeeting());

        $meeting->removeMeetingBackground($meetingBackground);
        $this->assertCount(0, $meeting->getMeetingBackgrounds());
    }

    public function testMeetingStatusEnum(): void
    {
        $meeting = new Meeting();

        $statuses = [MeetingStatus::SCHEDULED, MeetingStatus::IN_PROGRESS, MeetingStatus::ENDED, MeetingStatus::CANCELLED];

        foreach ($statuses as $status) {
            $meeting->setStatus($status);
            $this->assertEquals($status, $meeting->getStatus());
        }
    }

    public function testMeetingTimezone(): void
    {
        $meeting = new Meeting();

        $timezones = ['UTC', 'America/New_York', 'Europe/London', 'Asia/Tokyo', 'Asia/Shanghai'];

        foreach ($timezones as $timezone) {
            $meeting->setTimezone($timezone);
            $this->assertEquals($timezone, $meeting->getTimezone());
        }
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'meetingId' => ['meetingId', 'meeting_123'],
            'meetingCode' => ['meetingCode', 'test_code_456'],
            'subject' => ['subject', 'Test Meeting'],
            'startTime' => ['startTime', new \DateTimeImmutable('2024-01-01 10:00:00')],
            'endTime' => ['endTime', new \DateTimeImmutable('2024-01-01 11:00:00')],
            'status' => ['status', MeetingStatus::SCHEDULED],
            'duration' => ['duration', 60],
            'userId' => ['userId', 'host_user_123'],
            'timezone' => ['timezone', 'Asia/Shanghai'],
            'meetingUrl' => ['meetingUrl', 'https://meeting.example.com/join/123'],
            'password' => ['password', 'secret123'],
            'reminderSent' => ['reminderSent', false],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
            'updateTime' => ['updateTime', new \DateTimeImmutable('2024-01-01 09:30:00')],
        ];
    }
}

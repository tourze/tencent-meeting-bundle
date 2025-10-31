<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\MeetingUser;
use Tourze\TencentMeetingBundle\Entity\Recording;
use Tourze\TencentMeetingBundle\Entity\User;
use Tourze\TencentMeetingBundle\Enum\MeetingStatus;

/**
 * @internal
 */
#[CoversClass(Meeting::class)]
final class EntityTest extends AbstractEntityTestCase
{
    protected function createEntity(): Meeting
    {
        return new Meeting();
    }

    public function testMeetingEntity(): void
    {
        $meeting = new Meeting();
        $this->assertInstanceOf(Meeting::class, $meeting);

        $meeting->setMeetingId('test_meeting_123');
        $meeting->setSubject('Test Meeting');
        $meeting->setUserId('host_user_id');

        $this->assertEquals('test_meeting_123', $meeting->getMeetingId());
        $this->assertEquals('Test Meeting', $meeting->getSubject());
        $this->assertEquals('host_user_id', $meeting->getUserId());
    }

    public function testMeetingAttendeesCollection(): void
    {
        $meeting = new Meeting();
        $this->assertCount(0, $meeting->getAttendees());

        $meetingUser = new MeetingUser();
        $user = new User();
        $user->setUserid('user_123');
        $meetingUser->setUser($user);

        $meeting->addAttendee($meetingUser);
        $this->assertCount(1, $meeting->getAttendees());
        $this->assertTrue($meeting->getAttendees()->contains($meetingUser));

        $meeting->removeAttendee($meetingUser);
        $this->assertCount(0, $meeting->getAttendees());
    }

    public function testMeetingRecordingsCollection(): void
    {
        $meeting = new Meeting();
        $this->assertCount(0, $meeting->getRecordings());

        $recording = new Recording();
        $recording->setRecordingId('rec_123');

        $meeting->addRecording($recording);
        $this->assertCount(1, $meeting->getRecordings());
        $this->assertTrue($meeting->getRecordings()->contains($recording));

        $meeting->removeRecording($recording);
        $this->assertCount(0, $meeting->getRecordings());
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'meetingId' => ['meetingId', 'test_meeting_123'],
            'meetingCode' => ['meetingCode', 'test_code_456'],
            'subject' => ['subject', 'Test Meeting Subject'],
            'startTime' => ['startTime', new \DateTimeImmutable('2024-01-01 10:00:00')],
            'endTime' => ['endTime', new \DateTimeImmutable('2024-01-01 11:00:00')],
            'status' => ['status', MeetingStatus::SCHEDULED],
            'duration' => ['duration', 60],
            'userId' => ['userId', 'host_user_123'],
            'timezone' => ['timezone', 'Asia/Shanghai'],
            'meetingUrl' => ['meetingUrl', 'https://meeting.example.com/join/123'],
            'password' => ['password', 'secret123'],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
            'updateTime' => ['updateTime', new \DateTimeImmutable('2024-01-01 09:30:00')],
        ];
    }
}

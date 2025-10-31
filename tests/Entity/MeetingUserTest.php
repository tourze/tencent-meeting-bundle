<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\MeetingUser;
use Tourze\TencentMeetingBundle\Entity\User;

/**
 * @internal
 */
#[CoversClass(MeetingUser::class)]
final class MeetingUserTest extends AbstractEntityTestCase
{
    protected function createEntity(): MeetingUser
    {
        return new MeetingUser();
    }

    public function testMeetingUserRelationship(): void
    {
        $meeting = new Meeting();
        $user = new User();
        $meetingUser = new MeetingUser();

        $meeting->setMeetingId('meeting_123');
        $user->setUserid('user_123');

        $meetingUser->setMeeting($meeting);
        $meetingUser->setUser($user);
        $meetingUser->setRole('host');

        $this->assertSame($meeting, $meetingUser->getMeeting());
        $this->assertSame($user, $meetingUser->getUser());
        $this->assertEquals('host', $meetingUser->getRole());
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'role' => ['role', 'host'],
            'attendeeStatus' => ['attendeeStatus', 'joined'],
            'joinTime' => ['joinTime', new \DateTimeImmutable('2024-01-01 10:00:00')],
            'leaveTime' => ['leaveTime', new \DateTimeImmutable('2024-01-01 11:00:00')],
            'attendDuration' => ['attendDuration', 3600],
            'deviceInfo' => ['deviceInfo', 'Windows 10 Chrome'],
            'networkType' => ['networkType', 'wifi'],
            'cameraOn' => ['cameraOn', true],
            'micOn' => ['micOn', false],
            'screenShared' => ['screenShared', false],
            'remark' => ['remark', 'Test meeting user remark'],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
        ];
    }
}

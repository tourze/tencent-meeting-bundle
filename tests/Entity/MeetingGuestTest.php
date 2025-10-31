<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Validator\Validation;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\MeetingGuest;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

/**
 * @internal
 */
#[CoversClass(MeetingGuest::class)]
final class MeetingGuestTest extends AbstractEntityTestCase
{
    protected function createEntity(): MeetingGuest
    {
        return new MeetingGuest();
    }

    public function testMeetingGuestCreation(): void
    {
        $meeting = new Meeting();
        $config = new TencentMeetingConfig();

        $guest = new MeetingGuest();
        $guest->setMeeting($meeting);
        $guest->setConfig($config);
        $guest->setGuestName('Test Guest');
        $guest->setEmail('test@example.com');
        $guest->setPhone('13800138000');
        $guest->setGuestType('external');
        $guest->setInviteStatus('pending');
        $guest->setAttendanceStatus('not_joined');
        $guest->setNeedReminder(true);
        $guest->setAttendDuration(0);

        $this->assertInstanceOf(MeetingGuest::class, $guest);
        $this->assertSame($meeting, $guest->getMeeting());
        $this->assertSame($config, $guest->getConfig());
        $this->assertEquals('Test Guest', $guest->getGuestName());
        $this->assertEquals('test@example.com', $guest->getEmail());
        $this->assertEquals('13800138000', $guest->getPhone());
        $this->assertEquals('external', $guest->getGuestType());
        $this->assertEquals('pending', $guest->getInviteStatus());
        $this->assertEquals('not_joined', $guest->getAttendanceStatus());
        $this->assertTrue($guest->isNeedReminder());
        $this->assertEquals(0, $guest->getAttendDuration());
    }

    public function testMeetingGuestToString(): void
    {
        $guest = new MeetingGuest();
        $guest->setGuestName('Test Guest');

        $reflection = new \ReflectionClass($guest);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($guest, 123);

        $this->assertEquals('MeetingGuest[id=123, name=Test Guest]', (string) $guest);
    }

    public function testMeetingGuestValidation(): void
    {
        $validator = Validation::createValidator();

        $guest = new MeetingGuest();

        // 测试有效数据
        $meeting = new Meeting();
        $config = new TencentMeetingConfig();
        $guest->setMeeting($meeting);
        $guest->setConfig($config);
        $guest->setGuestName('Valid Guest');

        $violations = $validator->validate($guest);
        $this->assertCount(0, $violations);
    }

    public function testGuestEmailValidation(): void
    {
        $guest = new MeetingGuest();

        // 测试设置和获取邮箱
        $this->assertNull($guest->getEmail());

        $guest->setEmail('test@example.com');
        $this->assertEquals('test@example.com', $guest->getEmail());

        $guest->setEmail(null);
        $this->assertNull($guest->getEmail());
    }

    public function testGuestPhoneValidation(): void
    {
        $guest = new MeetingGuest();

        // 测试设置和获取手机号
        $this->assertNull($guest->getPhone());

        $guest->setPhone('13800138000');
        $this->assertEquals('13800138000', $guest->getPhone());

        $guest->setPhone(null);
        $this->assertNull($guest->getPhone());
    }

    public function testTimeMethods(): void
    {
        $guest = new MeetingGuest();

        $invitationTime = new \DateTimeImmutable('2023-01-01 10:00:00');
        $guest->setInvitationTime($invitationTime);
        $this->assertEquals($invitationTime, $guest->getInvitationTime());

        $responseTime = new \DateTimeImmutable('2023-01-01 11:00:00');
        $guest->setResponseTime($responseTime);
        $this->assertEquals($responseTime, $guest->getResponseTime());

        $joinTime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $guest->setJoinTime($joinTime);
        $this->assertEquals($joinTime, $guest->getJoinTime());

        $leaveTime = new \DateTimeImmutable('2023-01-01 13:00:00');
        $guest->setLeaveTime($leaveTime);
        $this->assertEquals($leaveTime, $guest->getLeaveTime());
    }

    public function testOptionalFields(): void
    {
        $guest = new MeetingGuest();

        $this->assertNull($guest->getEmail());
        $this->assertNull($guest->getPhone());
        $this->assertNull($guest->getCompany());
        $this->assertNull($guest->getPosition());
        $this->assertNull($guest->getInvitationTime());
        $this->assertNull($guest->getResponseTime());
        $this->assertNull($guest->getJoinTime());
        $this->assertNull($guest->getLeaveTime());
        $this->assertNull($guest->getRemark());
        $this->assertNull($guest->getUpdateTime());

        // 设置可选字段
        $guest->setCompany('Test Company');
        $guest->setPosition('Test Position');
        $guest->setRemark('Test Remark');

        $this->assertEquals('Test Company', $guest->getCompany());
        $this->assertEquals('Test Position', $guest->getPosition());
        $this->assertEquals('Test Remark', $guest->getRemark());
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'guestName' => ['guestName', 'Test Guest'],
            'email' => ['email', 'test@example.com'],
            'phone' => ['phone', '13800138000'],
            'company' => ['company', 'Test Company'],
            'position' => ['position', 'Test Position'],
            'guestType' => ['guestType', 'external'],
            'inviteStatus' => ['inviteStatus', 'invited'],
            'invitationTime' => ['invitationTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
            'responseTime' => ['responseTime', new \DateTimeImmutable('2024-01-01 09:30:00')],
            'attendanceStatus' => ['attendanceStatus', 'expected'],
            'joinTime' => ['joinTime', new \DateTimeImmutable('2024-01-01 10:00:00')],
            'leaveTime' => ['leaveTime', new \DateTimeImmutable('2024-01-01 11:00:00')],
            'attendDuration' => ['attendDuration', 3600],
            'needReminder' => ['needReminder', true],
            'remark' => ['remark', 'Test guest remark'],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 08:00:00')],
            'updateTime' => ['updateTime', new \DateTimeImmutable('2024-01-01 08:30:00')],
        ];
    }
}

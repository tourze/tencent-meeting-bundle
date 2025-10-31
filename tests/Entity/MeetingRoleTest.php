<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\MeetingRole;
use Tourze\TencentMeetingBundle\Entity\Role;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

/**
 * @internal
 */
#[CoversClass(MeetingRole::class)]
final class MeetingRoleTest extends AbstractEntityTestCase
{
    protected function createEntity(): MeetingRole
    {
        return new MeetingRole();
    }

    public function testMeetingRoleCreation(): void
    {
        $meetingRole = new MeetingRole();
        $this->assertInstanceOf(MeetingRole::class, $meetingRole);
    }

    public function testMeetingRoleSettersAndGetters(): void
    {
        $meetingRole = new MeetingRole();

        // Test userId
        $meetingRole->setUserId('user_123');
        $this->assertEquals('user_123', $meetingRole->getUserId());

        // Test status
        $meetingRole->setStatus('revoked');
        $this->assertEquals('revoked', $meetingRole->getStatus());

        // Test assignedBy
        $meetingRole->setAssignedBy('admin_user');
        $this->assertEquals('admin_user', $meetingRole->getAssignedBy());

        // Test remark
        $meetingRole->setRemark('Test role assignment');
        $this->assertEquals('Test role assignment', $meetingRole->getRemark());
    }

    public function testMeetingRoleRelations(): void
    {
        $meetingRole = new MeetingRole();

        // Create mock objects for relations
        $meeting = $this->createMock(Meeting::class);
        $role = $this->createMock(Role::class);
        $config = $this->createMock(TencentMeetingConfig::class);

        // Test meeting relation
        $meetingRole->setMeeting($meeting);
        $this->assertSame($meeting, $meetingRole->getMeeting());

        // Test role relation
        $meetingRole->setRole($role);
        $this->assertSame($role, $meetingRole->getRole());

        // Test config relation
        $meetingRole->setConfig($config);
        $this->assertSame($config, $meetingRole->getConfig());
    }

    public function testMeetingRoleTimeMethods(): void
    {
        $meetingRole = new MeetingRole();

        // Test createTime is set on creation
        // Initially, times should be null as they are set automatically by the system
        $this->assertNull($meetingRole->getCreateTime());

        // Test updateTime
        $updateTime = new \DateTimeImmutable();
        $meetingRole->setUpdateTime($updateTime);
        $this->assertEquals($updateTime, $meetingRole->getUpdateTime());

        // Test assignmentTime
        $assignmentTime = new \DateTimeImmutable();
        $meetingRole->setAssignmentTime($assignmentTime);
        $this->assertEquals($assignmentTime, $meetingRole->getAssignmentTime());
    }

    public function testMeetingRoleToString(): void
    {
        $meetingRole = new MeetingRole();

        // Test toString when role is not set
        $string = (string) $meetingRole;
        $this->assertStringContainsString('MeetingRole', $string);
        $this->assertStringContainsString('unknown', $string);
        $this->assertStringContainsString('active', $string);
    }

    public function testMeetingRoleStatusChoices(): void
    {
        $meetingRole = new MeetingRole();

        // Test valid status choices
        $meetingRole->setStatus('active');
        $this->assertEquals('active', $meetingRole->getStatus());

        $meetingRole->setStatus('revoked');
        $this->assertEquals('revoked', $meetingRole->getStatus());
    }

    public function testMeetingRoleDefaultStatus(): void
    {
        $meetingRole = new MeetingRole();
        $this->assertEquals('active', $meetingRole->getStatus());
    }

    public function testMeetingRoleUserIdNullable(): void
    {
        $meetingRole = new MeetingRole();

        // Test userId can be null
        $meetingRole->setUserId(null);
        $this->assertNull($meetingRole->getUserId());

        // Test userId can be set to string
        $meetingRole->setUserId('test_user');
        $this->assertEquals('test_user', $meetingRole->getUserId());
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'userId' => ['userId', 'user_123'],
            'status' => ['status', 'active'],
            'assignedBy' => ['assignedBy', 'admin_user'],
            'remark' => ['remark', 'Test role assignment'],
            'assignmentTime' => ['assignmentTime', new \DateTimeImmutable('2024-01-01 10:00:00')],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
            'updateTime' => ['updateTime', new \DateTimeImmutable('2024-01-01 09:30:00')],
        ];
    }
}

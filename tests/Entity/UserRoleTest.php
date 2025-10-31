<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Role;
use Tourze\TencentMeetingBundle\Entity\User;
use Tourze\TencentMeetingBundle\Entity\UserRole;

/**
 * @internal
 */
#[CoversClass(UserRole::class)]
final class UserRoleTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new UserRole();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'status' => ['status', 'active'];
        yield 'assignedBy' => ['assignedBy', 'admin'];
        yield 'remark' => ['remark', 'Test remark'];
    }

    public function testUserRoleCreation(): void
    {
        $user = new User();
        $user->setUserid('user_001');
        $user->setUsername('Test User');
        $user->setEmail('test@example.com');

        $role = new Role();
        $role->setRoleId('admin_role');
        $role->setName('admin');
        $role->setDescription('Administrator');

        $userRole = new UserRole();
        $userRole->setUser($user);
        $userRole->setRole($role);
        $userRole->setStatus('active');
        $userRole->setAssignmentTime(new \DateTimeImmutable());

        $this->assertInstanceOf(UserRole::class, $userRole);
        $this->assertSame($user, $userRole->getUser());
        $this->assertSame($role, $userRole->getRole());
        $this->assertSame('active', $userRole->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $userRole->getAssignmentTime());
    }
}

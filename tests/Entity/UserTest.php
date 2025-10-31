<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Department;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Entity\User;
use Tourze\TencentMeetingBundle\Entity\UserRole;

/**
 * @internal
 */
#[CoversClass(User::class)]
final class UserTest extends AbstractEntityTestCase
{
    protected function createEntity(): User
    {
        return new User();
    }

    public function testUserCreation(): void
    {
        $user = new User();

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame(0, $user->getId()); // New entities have ID = 0 before persistence
        $this->assertNull($user->getUuid());
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getPhone());
        $this->assertEquals('enterprise', $user->getUserType());
        $this->assertEquals('active', $user->getStatus());
        $this->assertNull($user->getDepartment());
        // Initially, times should be null as they are set automatically by the system
        $this->assertNull($user->getCreateTime());
        $this->assertNull($user->getUpdateTime());

        // 测试集合初始化
        $this->assertInstanceOf(Collection::class, $user->getUserRoles());
        $this->assertCount(0, $user->getUserRoles());
    }

    public function testUserSettersAndGetters(): void
    {
        $user = new User();
        $department = new Department();
        $config = new TencentMeetingConfig();

        $createTime = new \DateTimeImmutable('2024-05-01 09:00:00');
        $updateTime = new \DateTimeImmutable('2024-05-01 09:30:00');

        $user->setUserid('user_123');
        $user->setUuid('uuid_456');
        $user->setUsername('张三');
        $user->setEmail('zhangsan@example.com');
        $user->setPhone('13800138000');
        $user->setUserType('personal');
        $user->setStatus('inactive');
        $user->setDepartment($department);
        $user->setCreateTime($createTime);
        $user->setUpdateTime($updateTime);
        $user->setConfig($config);

        $this->assertEquals('user_123', $user->getUserid());
        $this->assertEquals('uuid_456', $user->getUuid());
        $this->assertEquals('张三', $user->getUsername());
        $this->assertEquals('zhangsan@example.com', $user->getEmail());
        $this->assertEquals('13800138000', $user->getPhone());
        $this->assertEquals('personal', $user->getUserType());
        $this->assertEquals('inactive', $user->getStatus());
        $this->assertSame($department, $user->getDepartment());
        $this->assertEquals($createTime, $user->getCreateTime());
        $this->assertEquals($updateTime, $user->getUpdateTime());
        $this->assertSame($config, $user->getConfig());
    }

    public function testUserToString(): void
    {
        $user = new User();
        $user->setUserid('user_123');
        $user->setUsername('张三');

        $this->assertEquals('张三', (string) $user);

        // 测试没有用户名时的情况
        $user2 = new User();
        $user2->setUserid('user_456');

        // 使用反射设置ID
        $reflection = new \ReflectionClass($user2);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user2, 789);

        $this->assertEquals('User#789', (string) $user2);
    }

    public function testUserTypeChoices(): void
    {
        $user = new User();

        $validTypes = ['enterprise', 'personal'];

        foreach ($validTypes as $type) {
            $user->setUserType($type);
            $this->assertEquals($type, $user->getUserType());
        }
    }

    public function testUserStatusChoices(): void
    {
        $user = new User();

        $validStatuses = ['active', 'inactive', 'disabled'];

        foreach ($validStatuses as $status) {
            $user->setStatus($status);
            $this->assertEquals($status, $user->getStatus());
        }
    }

    public function testUserRoleMethods(): void
    {
        $user = new User();
        $userRole = new UserRole();

        $user->addUserRole($userRole);
        $this->assertCount(1, $user->getUserRoles());
        $this->assertTrue($user->getUserRoles()->contains($userRole));
        $this->assertSame($user, $userRole->getUser());

        $user->removeUserRole($userRole);
        $this->assertCount(0, $user->getUserRoles());
        $this->assertFalse($user->getUserRoles()->contains($userRole));

        // 测试添加重复项不会增加集合大小
        $user->addUserRole($userRole);
        $user->addUserRole($userRole);
        $this->assertCount(1, $user->getUserRoles());
    }

    public function testUserEmailValidation(): void
    {
        $user = new User();

        // 测试有效邮箱
        $validEmails = [
            'user@example.com',
            'test.email@domain.co.uk',
            'user123@test-domain.org',
            'chinese@测试.com',
        ];

        foreach ($validEmails as $email) {
            $user->setEmail($email);
            $this->assertEquals($email, $user->getEmail());
        }

        // 测试设置为null
        $user->setEmail(null);
        $this->assertNull($user->getEmail());
    }

    public function testUserPhoneValidation(): void
    {
        $user = new User();

        // 测试有效手机号
        $validPhones = [
            '13800138000',
            '86-13800138000',
            '+8613800138000',
            '021-12345678',
        ];

        foreach ($validPhones as $phone) {
            $user->setPhone($phone);
            $this->assertEquals($phone, $user->getPhone());
        }

        // 测试设置为null
        $user->setPhone(null);
        $this->assertNull($user->getPhone());
    }

    public function testUserDepartmentRelation(): void
    {
        $user = new User();
        $department = new Department();
        $department->setName('技术部');

        // 测试初始值为null
        $this->assertNull($user->getDepartment());

        // 测试设置部门
        $user->setDepartment($department);
        $this->assertSame($department, $user->getDepartment());

        // 测试设置为null
        $user->setDepartment(null);
        $this->assertNull($user->getDepartment());
    }

    public function testUserUuid(): void
    {
        $user = new User();

        // 测试初始值为null
        $this->assertNull($user->getUuid());

        // 测试设置UUID
        $uuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $user->setUuid($uuid);
        $this->assertEquals($uuid, $user->getUuid());

        // 测试设置为null
        $user->setUuid(null);
        $this->assertNull($user->getUuid());
    }

    public function testUserTimestamps(): void
    {
        $user = new User();

        // Initially, times should be null as they are set automatically by the system
        $this->assertNull($user->getCreateTime());
        $this->assertNull($user->getUpdateTime());

        // 手动设置时间
        $newCreateTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $newUpdateTime = new \DateTimeImmutable('2024-01-01 10:30:00');

        $user->setCreateTime($newCreateTime);
        $user->setUpdateTime($newUpdateTime);

        $this->assertEquals($newCreateTime, $user->getCreateTime());
        $this->assertEquals($newUpdateTime, $user->getUpdateTime());
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'userid' => ['userid', 'user_123'],
            'uuid' => ['uuid', 'uuid_456'],
            'username' => ['username', 'Test User'],
            'email' => ['email', 'test@example.com'],
            'phone' => ['phone', '13800138000'],
            'userType' => ['userType', 'enterprise'],
            'status' => ['status', 'active'],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
            'updateTime' => ['updateTime', new \DateTimeImmutable('2024-01-01 09:30:00')],
        ];
    }
}

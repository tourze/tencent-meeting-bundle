<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Role;

/**
 * @internal
 */
#[CoversClass(Role::class)]
final class RoleTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Role();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'roleId' => ['roleId', 'test.role'];
        yield 'name' => ['name', 'Test Role'];
        yield 'description' => ['description', 'Test Description'];
        yield 'roleType' => ['roleType', 'system'];
        yield 'status' => ['status', 'active'];
        yield 'orderWeight' => ['orderWeight', 10];
        yield 'parentRoleId' => ['parentRoleId', 'parent.role'];
    }

    public function testRoleCreation(): void
    {
        $role = new Role();
        $role->setRoleId('admin_role');
        $role->setName('admin');
        $role->setDescription('Administrator Role');
        $role->setRoleType('system');
        $role->setStatus('active');

        $this->assertInstanceOf(Role::class, $role);
        $this->assertSame('admin_role', $role->getRoleId());
        $this->assertSame('admin', $role->getName());
        $this->assertSame('Administrator Role', $role->getDescription());
        $this->assertSame('system', $role->getRoleType());
        $this->assertSame('active', $role->getStatus());
    }
}

<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Department;

/**
 * @internal
 */
#[CoversClass(Department::class)]
final class DepartmentTest extends AbstractEntityTestCase
{
    protected function createEntity(): Department
    {
        return new Department();
    }

    public function testDepartmentCreation(): void
    {
        $department = new Department();

        $this->assertInstanceOf(Department::class, $department);
        $this->assertSame(0, $department->getId()); // New entities have ID = 0 before persistence
        $this->assertEquals('', $department->getDepartmentId());
        $this->assertEquals('', $department->getName());
        $this->assertNull($department->getDescription());
        $this->assertNull($department->getParent());
        $this->assertNull($department->getPath());
        $this->assertEquals(0, $department->getLevel());
        $this->assertEquals(0, $department->getOrderWeight());
        $this->assertEquals('active', $department->getStatus());
        $this->assertNull($department->getUpdateTime());
    }

    public function testDepartmentSettersAndGetters(): void
    {
        $department = new Department();

        $department->setDepartmentId('dept_123');
        $department->setName('测试部门');
        $department->setDescription('这是一个测试部门');
        $department->setPath('/dept_123');
        $department->setLevel(2);
        $department->setOrderWeight(10);
        $department->setStatus('inactive');

        $this->assertEquals('dept_123', $department->getDepartmentId());
        $this->assertEquals('测试部门', $department->getName());
        $this->assertEquals('这是一个测试部门', $department->getDescription());
        $this->assertEquals('/dept_123', $department->getPath());
        $this->assertEquals(2, $department->getLevel());
        $this->assertEquals(10, $department->getOrderWeight());
        $this->assertEquals('inactive', $department->getStatus());
    }

    public function testDepartmentToString(): void
    {
        $department = new Department();
        $department->setName('测试部门');

        $this->assertEquals('测试部门', (string) $department);

        $department2 = new Department();
        $department2->setDepartmentId('dept_456');
        $department2->setName('');

        $this->assertEquals('dept_456', (string) $department2);

        $department3 = new Department();
        $this->assertEquals('', (string) $department3);
    }

    public function testDepartmentHierarchy(): void
    {
        $parent = new Department();
        $parent->setName('父部门');
        $parent->setDepartmentId('parent_123');

        $child = new Department();
        $child->setName('子部门');
        $child->setDepartmentId('child_456');

        // 测试父子关系
        $child->setParent($parent);
        $this->assertSame($parent, $child->getParent());

        // 测试层级计算
        $child->setLevel(1);
        $this->assertEquals(1, $child->getLevel());
    }

    public function testDepartmentCollectionMethods(): void
    {
        $department = new Department();

        $this->assertInstanceOf(Collection::class, $department->getChildren());
        $this->assertInstanceOf(Collection::class, $department->getUsers());
        $this->assertCount(0, $department->getChildren());
        $this->assertCount(0, $department->getUsers());
    }

    public function testDepartmentTimeMethods(): void
    {
        $department = new Department();

        // Initially, times should be null as they are set automatically by the system
        $this->assertNull($department->getCreateTime());
        $this->assertNull($department->getUpdateTime());

        $newTime = new \DateTimeImmutable('2024-01-01');
        $department->setCreateTime($newTime);
        $this->assertEquals($newTime, $department->getCreateTime());

        $department->setUpdateTime($newTime);
        $this->assertEquals($newTime, $department->getUpdateTime());
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'departmentId' => ['departmentId', 'dept_123'],
            'name' => ['name', 'Test Department'],
            'description' => ['description', 'Test department description'],
            'path' => ['path', '/dept_123'],
            'level' => ['level', 1],
            'orderWeight' => ['orderWeight', 10],
            'status' => ['status', 'active'],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Permission;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

/**
 * @internal
 */
#[CoversClass(Permission::class)]
final class PermissionTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Permission();
    }

    public function testPermissionCreation(): void
    {
        $permission = new Permission();

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertSame(0, $permission->getId()); // New entities have ID = 0 before persistence
        $this->assertNull($permission->getDescription());
        $this->assertEquals('system', $permission->getPermissionType());
        $this->assertEquals('active', $permission->getStatus());
        $this->assertNull($permission->getPermissionConfig());
        $this->assertEquals(0, $permission->getOrderWeight());
        $this->assertFalse($permission->isBuiltIn());
        // Initially, times should be null as they are set automatically by the system
        $this->assertNull($permission->getCreateTime());
        $this->assertNull($permission->getUpdateTime());
    }

    public function testPermissionSettersAndGetters(): void
    {
        $permission = new Permission();
        $config = new TencentMeetingConfig();

        $createTime = new \DateTimeImmutable('2024-05-01 09:00:00');
        $updateTime = new \DateTimeImmutable('2024-05-01 09:30:00');
        $permissionConfig = [
            'scope' => 'global',
            'actions' => ['read', 'write', 'delete'],
            'restrictions' => ['time_limit' => 3600],
            'dependencies' => ['user.active'],
        ];

        $permission->setPermissionId('meeting.create');
        $permission->setName('创建会议权限');
        $permission->setDescription('允许用户创建新的会议');
        $permission->setPermissionType('meeting');
        $permission->setPermissionCode('MEETING_CREATE');
        $permission->setStatus('inactive');
        $permission->setPermissionConfig($permissionConfig);
        $permission->setOrderWeight(10);
        $permission->setBuiltIn(true);
        $permission->setCreateTime($createTime);
        $permission->setUpdateTime($updateTime);
        $permission->setConfigEntity($config);

        $this->assertEquals('meeting.create', $permission->getPermissionId());
        $this->assertEquals('创建会议权限', $permission->getName());
        $this->assertEquals('允许用户创建新的会议', $permission->getDescription());
        $this->assertEquals('meeting', $permission->getPermissionType());
        $this->assertEquals('MEETING_CREATE', $permission->getPermissionCode());
        $this->assertEquals('inactive', $permission->getStatus());
        $this->assertEquals($permissionConfig, $permission->getPermissionConfig());
        $this->assertEquals(10, $permission->getOrderWeight());
        $this->assertTrue($permission->isBuiltIn());
        $this->assertEquals($createTime, $permission->getCreateTime());
        $this->assertEquals($updateTime, $permission->getUpdateTime());
        $this->assertSame($config, $permission->getConfigEntity());
    }

    public function testPermissionToString(): void
    {
        $permission = new Permission();
        $permission->setPermissionId('meeting.create');
        $permission->setName('Create Meeting Permission');

        $this->assertEquals('Create Meeting Permission', (string) $permission);

        $permission2 = new Permission();
        $permission2->setPermissionId('user.manage');
        $permission2->setName('管理用户权限');

        $this->assertEquals('管理用户权限', (string) $permission2);
    }

    public function testPermissionTypeChoices(): void
    {
        $permission = new Permission();

        $validTypes = ['system', 'user', 'meeting', 'recording', 'document', 'room'];

        foreach ($validTypes as $type) {
            $permission->setPermissionType($type);
            $this->assertEquals($type, $permission->getPermissionType());
        }
    }

    public function testPermissionStatusChoices(): void
    {
        $permission = new Permission();

        $validStatuses = ['active', 'inactive'];

        foreach ($validStatuses as $status) {
            $permission->setStatus($status);
            $this->assertEquals($status, $permission->getStatus());
        }
    }

    public function testPermissionConfigHandling(): void
    {
        $permission = new Permission();

        // 测试空配置
        $permission->setPermissionConfig(null);
        $this->assertNull($permission->getPermissionConfig());

        // 测试简单配置
        $simpleConfig = ['enabled' => true];
        $permission->setPermissionConfig($simpleConfig);
        $this->assertEquals($simpleConfig, $permission->getPermissionConfig());

        // 测试复杂配置
        $complexConfig = [
            'scope' => 'department',
            'actions' => [
                'create' => true,
                'read' => true,
                'update' => true,
                'delete' => false,
            ],
            'restrictions' => [
                'time_limits' => [
                    'start_hour' => 9,
                    'end_hour' => 18,
                ],
                'max_duration' => 7200,
                'require_approval' => true,
            ],
            'conditions' => [
                'user_level' => 'manager',
                'department' => ['IT', 'HR'],
                'location' => 'office',
            ],
            'notifications' => [
                'on_grant' => true,
                'on_revoke' => true,
                'email_template' => 'permission_change',
            ],
        ];

        $permission->setPermissionConfig($complexConfig);
        $this->assertEquals($complexConfig, $permission->getPermissionConfig());
    }

    public function testPermissionCode(): void
    {
        $permission = new Permission();

        // 测试不同类型的权限代码
        $permissionCodes = [
            'MEETING_CREATE',
            'MEETING_UPDATE',
            'USER_MANAGE',
            'SYSTEM_ADMIN',
            'RECORDING_DOWNLOAD',
            'DOCUMENT_UPLOAD',
            'ROOM_BOOK',
        ];

        foreach ($permissionCodes as $code) {
            $permission->setPermissionCode($code);
            $this->assertEquals($code, $permission->getPermissionCode());
        }
    }

    public function testPermissionOrderWeight(): void
    {
        $permission = new Permission();

        $weights = [0, 1, 5, 10, -5, 100];

        foreach ($weights as $weight) {
            $permission->setOrderWeight($weight);
            $this->assertEquals($weight, $permission->getOrderWeight());
        }
    }

    public function testPermissionBuiltIn(): void
    {
        $permission = new Permission();

        // 测试默认值
        $this->assertFalse($permission->isBuiltIn());

        // 测试设置为true
        $permission->setBuiltIn(true);
        $this->assertTrue($permission->isBuiltIn());

        // 测试设置为false
        $permission->setBuiltIn(false);
        $this->assertFalse($permission->isBuiltIn());
    }

    public function testPermissionDescription(): void
    {
        $permission = new Permission();

        // 测试初始值为null
        $this->assertNull($permission->getDescription());

        // 测试设置描述
        $descriptions = [
            '基本权限描述',
            'This is a basic permission for creating meetings',
            '允许用户执行特定操作的详细权限说明，包含使用场景和限制条件。',
            null,
        ];

        foreach ($descriptions as $description) {
            $permission->setDescription($description);
            $this->assertEquals($description, $permission->getDescription());
        }
    }

    public function testPermissionTimestamps(): void
    {
        $permission = new Permission();

        // Initially, times should be null as they are set automatically by the system
        $this->assertNull($permission->getCreateTime());
        $this->assertNull($permission->getUpdateTime());

        // 手动设置时间
        $newCreateTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $newUpdateTime = new \DateTimeImmutable('2024-01-01 10:30:00');

        $permission->setCreateTime($newCreateTime);
        $permission->setUpdateTime($newUpdateTime);

        $this->assertEquals($newCreateTime, $permission->getCreateTime());
        $this->assertEquals($newUpdateTime, $permission->getUpdateTime());
    }

    public function testPermissionRelationships(): void
    {
        $permission = new Permission();
        $config = new TencentMeetingConfig();

        // 测试设置和获取配置关联
        $permission->setConfigEntity($config);
        $this->assertSame($config, $permission->getConfigEntity());
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'permissionId' => ['permissionId', 'test.permission'],
            'name' => ['name', 'Test Permission'],
            'description' => ['description', 'Test Description'],
            'permissionType' => ['permissionType', 'system'],
            'permissionCode' => ['permissionCode', 'TEST_PERMISSION'],
            'status' => ['status', 'active'],
            'permissionConfig' => ['permissionConfig', ['scope' => 'global', 'actions' => ['read', 'write']]],
            'orderWeight' => ['orderWeight', 10],
            'builtIn' => ['builtIn', false],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
            'updateTime' => ['updateTime', new \DateTimeImmutable('2024-01-01 09:30:00')],
        ];
    }
}

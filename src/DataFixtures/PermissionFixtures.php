<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\Permission;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

class PermissionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var TencentMeetingConfig $config */
        $config = $this->getReference(TencentMeetingConfigFixtures::CONFIG_REFERENCE_1, TencentMeetingConfig::class);

        $permissionsData = [
            [
                'permissionId' => 'create_meeting',
                'name' => '创建会议',
                'description' => '允许用户创建会议',
                'permissionType' => 'meeting',
                'permissionCode' => 'MEETING_CREATE',
                'status' => 'active',
                'orderWeight' => 10,
                'isBuiltIn' => true,
                'permissionConfig' => [
                    'max_duration' => 480,
                    'max_participants' => 100,
                ],
            ],
            [
                'permissionId' => 'delete_meeting',
                'name' => '删除会议',
                'description' => '允许用户删除会议',
                'permissionType' => 'meeting',
                'permissionCode' => 'MEETING_DELETE',
                'status' => 'active',
                'orderWeight' => 20,
                'isBuiltIn' => true,
                'permissionConfig' => [
                    'require_confirmation' => true,
                ],
            ],
            [
                'permissionId' => 'manage_recording',
                'name' => '管理录制',
                'description' => '允许用户管理会议录制',
                'permissionType' => 'recording',
                'permissionCode' => 'RECORDING_MANAGE',
                'status' => 'active',
                'orderWeight' => 30,
                'isBuiltIn' => true,
                'permissionConfig' => [
                    'auto_delete_days' => 30,
                ],
            ],
            [
                'permissionId' => 'user_admin',
                'name' => '用户管理',
                'description' => '允许管理企业用户',
                'permissionType' => 'user',
                'permissionCode' => 'USER_ADMIN',
                'status' => 'active',
                'orderWeight' => 40,
                'isBuiltIn' => true,
                'permissionConfig' => null,
            ],
            [
                'permissionId' => 'system_config',
                'name' => '系统配置',
                'description' => '允许修改系统配置',
                'permissionType' => 'system',
                'permissionCode' => 'SYSTEM_CONFIG',
                'status' => 'active',
                'orderWeight' => 50,
                'isBuiltIn' => true,
                'permissionConfig' => [
                    'require_admin' => true,
                ],
            ],
        ];

        foreach ($permissionsData as $data) {
            $permission = new Permission();
            $permission->setPermissionId($data['permissionId']);
            $permission->setName($data['name']);
            $permission->setDescription($data['description']);
            $permission->setPermissionType($data['permissionType']);
            $permission->setPermissionCode($data['permissionCode']);
            $permission->setStatus($data['status']);
            $permission->setOrderWeight($data['orderWeight']);
            $permission->setBuiltIn($data['isBuiltIn']);
            $permission->setPermissionConfig($data['permissionConfig']);
            $permission->setConfigEntity($config);

            $manager->persist($permission);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TencentMeetingConfigFixtures::class,
        ];
    }
}

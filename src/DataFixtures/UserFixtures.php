<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\Department;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Entity\User;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 获取已创建的配置实体引用
        /** @var TencentMeetingConfig $config */
        $config = $this->getReference(TencentMeetingConfigFixtures::CONFIG_REFERENCE_1, TencentMeetingConfig::class);

        // 获取已创建的部门实体引用
        /** @var Department $department */
        $department = $this->getReference(DepartmentFixtures::DEPARTMENT_REFERENCE_1, Department::class);

        // 创建测试用的用户数据
        $userData = [
            [
                'userid' => 'zhang_san',
                'username' => '张三',
                'email' => 'zhangsan@test.local',
                'phone' => '13800138001',
                'userType' => 'enterprise',
            ],
            [
                'userid' => 'li_si',
                'username' => '李四',
                'email' => 'lisi@test.local',
                'phone' => '13800138002',
                'userType' => 'enterprise',
            ],
            [
                'userid' => 'wang_wu',
                'username' => '王五',
                'email' => 'wangwu@test.local',
                'phone' => '13800138003',
                'userType' => 'personal',
            ],
        ];

        foreach ($userData as $index => $data) {
            $user = new User();

            $user->setUserid($data['userid']);
            $user->setUuid('uuid_' . ($index + 1));
            $user->setUsername($data['username']);
            $user->setEmail($data['email']);
            $user->setPhone($data['phone']);
            $user->setUserType($data['userType']);
            $user->setStatus('active');
            $user->setDepartment($department);
            $user->setConfig($config);

            $manager->persist($user);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            DepartmentFixtures::class,
            TencentMeetingConfigFixtures::class,
        ];
    }
}

<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\Role;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Entity\User;
use Tourze\TencentMeetingBundle\Entity\UserRole;

class UserRoleFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var TencentMeetingConfig $config */
        $config = $this->getReference(TencentMeetingConfigFixtures::CONFIG_REFERENCE_1, TencentMeetingConfig::class);

        $user = new User();
        $user->setUserid('test_user_role_001');
        $user->setUuid('test_uuid_role_001');
        $user->setUsername('测试用户角色');
        $user->setEmail('userrole@test.local');
        $user->setPhone('13800138001');
        $user->setUserType('enterprise');
        $user->setStatus('active');
        $user->setConfig($config);
        $manager->persist($user);

        $role = new Role();
        $role->setRoleId('role_test_001');
        $role->setName('测试角色');
        $role->setDescription('用于测试的角色');
        $role->setRoleType('meeting');
        $role->setStatus('active');
        $role->setOrderWeight(1);
        $role->setPermissions(['read' => true, 'write' => false]);
        $role->setAttributes(['level' => 'standard']);
        $role->setConfig($config);
        $manager->persist($role);

        $userRole = new UserRole();
        $userRole->setUser($user);
        $userRole->setRole($role);
        $userRole->setStatus('active');
        $userRole->setAssignedBy('admin');
        $userRole->setRemark('测试用户角色分配');
        $userRole->setConfig($config);

        $manager->persist($userRole);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TencentMeetingConfigFixtures::class,
        ];
    }
}

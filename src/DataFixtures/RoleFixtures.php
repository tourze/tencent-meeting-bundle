<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\Role;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

class RoleFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var TencentMeetingConfig $config */
        $config = $this->getReference(TencentMeetingConfigFixtures::CONFIG_REFERENCE_1, TencentMeetingConfig::class);

        $roleData = [
            ['host', '主持人', '会议主持人角色'],
            ['cohost', '联席主持人', '会议联席主持人角色'],
            ['attendee', '参会者', '普通参会者角色'],
            ['observer', '观察员', '只读观察员角色'],
        ];

        foreach ($roleData as $index => [$roleType, $name, $description]) {
            $role = new Role();
            $role->setRoleId('role_' . $roleType);
            $role->setName($name);
            $role->setDescription($description);
            $role->setRoleType('meeting');
            $role->setStatus('active');
            $role->setOrderWeight($index + 1);
            $role->setPermissions([
                'can_mute' => $index < 2,
                'can_unmute' => $index < 2,
                'can_record' => 0 === $index,
                'can_share_screen' => $index < 3,
                'can_manage_participants' => $index < 2,
                'can_kick' => $index < 2,
            ]);
            $role->setAttributes([
                'is_default' => 2 === $index,
                'max_participants' => $index < 2 ? 1000 : 100,
                'priority' => 4 - $index,
            ]);
            $role->setConfig($config);

            $manager->persist($role);
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

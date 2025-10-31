<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\MeetingRole;
use Tourze\TencentMeetingBundle\Entity\Role;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Enum\MeetingStatus;

class MeetingRoleFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 使用已存在的配置实体
        /** @var TencentMeetingConfig $config */
        $config = $this->getReference(TencentMeetingConfigFixtures::CONFIG_REFERENCE_1, TencentMeetingConfig::class);

        // 创建测试用的会议实体
        $meeting = new Meeting();
        $meeting->setMeetingId('meeting_test_role_001');
        $meeting->setMeetingCode('MRL001');
        $meeting->setSubject('测试会议角色');
        $meeting->setStartTime(new \DateTimeImmutable('2024-01-01 10:00:00'));
        $meeting->setEndTime(new \DateTimeImmutable('2024-01-01 11:00:00'));
        $meeting->setStatus(MeetingStatus::SCHEDULED);
        $meeting->setDuration(60);
        $meeting->setConfig($config);
        $meeting->setUserId('test_user_001');
        $manager->persist($meeting);

        // 创建测试用的角色实体
        $roleTypes = [
            ['host', '主持人'],
            ['cohost', '联席主持人'],
            ['attendee', '参会者'],
        ];

        for ($i = 0; $i < 3; ++$i) {
            $role = new Role();
            $role->setRoleId('role_meeting_' . ($i + 1));
            $role->setName($roleTypes[$i][1]);
            $role->setDescription('会议' . $roleTypes[$i][1] . '角色');
            $role->setRoleType('meeting');
            $role->setStatus('active');
            $role->setOrderWeight($i + 1);
            $role->setPermissions([
                'can_mute' => $i < 2,
                'can_unmute' => $i < 2,
                'can_record' => 0 === $i,
                'can_share_screen' => $i < 2,
                'can_manage_participants' => $i < 2,
            ]);
            $role->setAttributes([
                'is_default' => 2 === $i,
                'max_participants' => 100,
                'can_kick' => $i < 2,
            ]);
            $role->setConfig($config);
            $manager->persist($role);

            // 创建会议角色关联
            $meetingRole = new MeetingRole();
            $meetingRole->setMeeting($meeting);
            $meetingRole->setRole($role);
            $meetingRole->setUserId('user_' . ($i + 1));
            $meetingRole->setAssignmentTime(new \DateTimeImmutable('2024-01-01 09:30:00'));
            $meetingRole->setStatus('active');
            $meetingRole->setAssignedBy('admin');
            $meetingRole->setRemark('会议角色分配测试数据 ' . ($i + 1));
            $meetingRole->setConfig($config);

            $manager->persist($meetingRole);
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

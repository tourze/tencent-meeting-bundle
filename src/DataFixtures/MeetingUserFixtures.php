<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\Department;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\MeetingUser;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Entity\User;
use Tourze\TencentMeetingBundle\Enum\MeetingStatus;

class MeetingUserFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var TencentMeetingConfig $config */
        $config = $this->getReference(TencentMeetingConfigFixtures::CONFIG_REFERENCE_1, TencentMeetingConfig::class);

        $department = new Department();
        $department->setDepartmentId('dept_meeting_user_001');
        $department->setName('会议用户部门');
        $department->setParent(null);
        $department->setPath('/会议用户部门');
        $department->setOrderWeight(1);
        $department->setStatus('active');
        $department->setConfig($config);
        $manager->persist($department);

        $users = [];
        for ($i = 1; $i <= 3; ++$i) {
            $user = new User();
            $user->setUserid('meeting_user_' . $i);
            $user->setUuid('meeting_uuid_' . $i);
            $user->setUsername('会议用户' . $i);
            $user->setEmail('meetinguser' . $i . '@test.local');
            $user->setPhone('1380013800' . $i);
            $user->setUserType('enterprise');
            $user->setStatus('active');
            $user->setDepartment($department);
            $user->setConfig($config);
            $manager->persist($user);
            $users[] = $user;
        }

        $meeting = new Meeting();
        $meeting->setMeetingId('meeting_001');
        $meeting->setMeetingCode('123456789');
        $meeting->setSubject('测试会议');
        $meeting->setUserId($users[0]->getUserid()); // 使用第一个用户作为主持人
        $meeting->setStartTime(new \DateTimeImmutable('+1 hour'));
        $meeting->setEndTime(new \DateTimeImmutable('+2 hours'));
        $meeting->setStatus(MeetingStatus::SCHEDULED);
        $meeting->setDuration(60);
        $meeting->setPassword('123456');
        $meeting->setConfig($config);
        $manager->persist($meeting);

        $roles = ['host', 'cohost', 'attendee'];
        $statuses = ['invited', 'joined', 'left'];

        foreach ($users as $index => $user) {
            $meetingUser = new MeetingUser();
            $meetingUser->setMeeting($meeting);
            $meetingUser->setUser($user);
            $meetingUser->setRole($roles[$index % count($roles)]);
            $meetingUser->setAttendeeStatus($statuses[$index % count($statuses)]);

            if (1 === $index) {
                $meetingUser->setJoinTime(new \DateTimeImmutable('-30 minutes'));
                $meetingUser->setLeaveTime(new \DateTimeImmutable('-5 minutes'));
                $meetingUser->setAttendDuration(1500);
            }

            $meetingUser->setDeviceInfo('Windows Desktop Client');
            $meetingUser->setNetworkType('WiFi');
            $meetingUser->setCameraOn(0 === $index);
            $meetingUser->setMicOn(2 !== $index);
            $meetingUser->setScreenShared(false);
            $meetingUser->setRemark('测试参会用户' . ($index + 1));

            $manager->persist($meetingUser);
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

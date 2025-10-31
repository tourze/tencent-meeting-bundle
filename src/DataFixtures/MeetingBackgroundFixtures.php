<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\Background;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\MeetingBackground;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Enum\MeetingStatus;

class MeetingBackgroundFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 使用已存在的配置实体
        /** @var TencentMeetingConfig $config */
        $config = $this->getReference(TencentMeetingConfigFixtures::CONFIG_REFERENCE_1, TencentMeetingConfig::class);

        // 创建测试用的会议实体
        $meeting = new Meeting();
        $meeting->setMeetingId('meeting_test_bg_001');
        $meeting->setMeetingCode('MBG001');
        $meeting->setSubject('测试会议背景');
        $meeting->setStartTime(new \DateTimeImmutable('2024-01-01 10:00:00'));
        $meeting->setEndTime(new \DateTimeImmutable('2024-01-01 11:00:00'));
        $meeting->setStatus(MeetingStatus::SCHEDULED);
        $meeting->setDuration(60);
        $meeting->setConfig($config);
        $meeting->setUserId('test_user_001');
        $manager->persist($meeting);

        // 创建测试用的背景实体
        $background = new Background();
        $background->setBackgroundId('bg_test_001');
        $background->setName('测试背景');
        $background->setDescription('这是一个测试背景');
        $background->setImageUrl('https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=1920&h=1080&fit=crop');
        $background->setBackgroundType('image');
        $background->setStatus('active');
        $background->setDefault(true);
        $background->setFileSize(1024000);
        $background->setImageFormat('jpg');
        $background->setImageWidth(1920);
        $background->setImageHeight(1080);
        $background->setConfigEntity($config);
        $manager->persist($background);

        // 创建测试用的会议背景关联数据
        for ($i = 1; $i <= 3; ++$i) {
            $meetingBackground = new MeetingBackground();

            $meetingBackground->setMeeting($meeting);
            $meetingBackground->setBackground($background);
            $meetingBackground->setApplicationTime(new \DateTimeImmutable('2024-01-01 09:30:00'));
            $meetingBackground->setStatus('active');
            $meetingBackground->setAppliedBy('admin');
            $meetingBackground->setCustomConfig([
                'opacity' => 0.8,
                'blur' => 0,
                'brightness' => 1.0,
            ]);
            $meetingBackground->setRemark('测试会议背景关联 ' . $i);
            $meetingBackground->setConfig($config);

            $manager->persist($meetingBackground);
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

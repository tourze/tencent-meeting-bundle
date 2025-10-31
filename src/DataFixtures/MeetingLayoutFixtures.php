<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\Layout;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\MeetingLayout;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Enum\MeetingStatus;

class MeetingLayoutFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 使用已存在的配置实体
        /** @var TencentMeetingConfig $config */
        $config = $this->getReference(TencentMeetingConfigFixtures::CONFIG_REFERENCE_1, TencentMeetingConfig::class);

        // 创建测试用的会议实体
        $meeting = new Meeting();
        $meeting->setMeetingId('meeting_test_layout_001');
        $meeting->setMeetingCode('MLT001');
        $meeting->setSubject('测试会议布局');
        $meeting->setStartTime(new \DateTimeImmutable('2024-01-01 10:00:00'));
        $meeting->setEndTime(new \DateTimeImmutable('2024-01-01 11:00:00'));
        $meeting->setStatus(MeetingStatus::SCHEDULED);
        $meeting->setDuration(60);
        $meeting->setConfig($config);
        $meeting->setUserId('test_user_001');
        $manager->persist($meeting);

        // 创建测试用的布局实体
        $layoutTypes = ['gallery', 'speaker', 'grid'];

        for ($i = 0; $i < 3; ++$i) {
            $layout = new Layout();
            $layout->setLayoutId('layout_meeting_' . ($i + 1));
            $layout->setName('会议布局 ' . ($i + 1));
            $layout->setDescription('用于会议测试的布局 ' . ($i + 1));
            $layout->setLayoutType($layoutTypes[$i]);
            $layout->setStatus('active');
            $layout->setDefault(0 === $i);
            $layout->setMaxParticipants(25 + ($i * 10));
            $layout->setLayoutConfig([
                'theme' => 'meeting',
                'show_participant_names' => true,
                'auto_switch_speaker' => true,
                'grid_size' => $i + 1,
            ]);
            $layout->setThumbnailUrl('https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=300&fit=crop');
            $layout->setOrderWeight($i + 1);
            $layout->setBuiltIn(true);
            $layout->setApplicableScope('meeting');
            $layout->setConfigEntity($config);
            $manager->persist($layout);

            // 创建会议布局关联
            $meetingLayout = new MeetingLayout();
            $meetingLayout->setMeeting($meeting);
            $meetingLayout->setLayout($layout);
            $meetingLayout->setApplicationTime(new \DateTimeImmutable('2024-01-01 09:45:00'));
            $meetingLayout->setStatus('active');
            $meetingLayout->setAppliedBy('system');
            $meetingLayout->setCustomConfig([
                'override_settings' => false,
                'preserve_participant_order' => true,
                'custom_theme' => 'default',
            ]);
            $meetingLayout->setRemark('会议布局关联测试数据 ' . ($i + 1));
            $meetingLayout->setConfig($config);

            $manager->persist($meetingLayout);
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

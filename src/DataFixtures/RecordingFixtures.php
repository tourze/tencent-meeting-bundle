<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\Recording;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Enum\MeetingStatus;

class RecordingFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var TencentMeetingConfig $config */
        $config = $this->getReference(TencentMeetingConfigFixtures::CONFIG_REFERENCE_1, TencentMeetingConfig::class);

        $meeting = new Meeting();
        $meeting->setMeetingId('meeting_test_recording_001');
        $meeting->setMeetingCode('REC001');
        $meeting->setSubject('测试录制会议');
        $meeting->setStartTime(new \DateTimeImmutable('2024-01-01 10:00:00'));
        $meeting->setEndTime(new \DateTimeImmutable('2024-01-01 11:00:00'));
        $meeting->setStatus(MeetingStatus::ENDED);
        $meeting->setDuration(60);
        $meeting->setUserId('test_user_001');
        $meeting->setConfig($config);
        $manager->persist($meeting);

        for ($i = 1; $i <= 3; ++$i) {
            $recording = new Recording();
            $recording->setRecordingId('recording_' . $i);
            $recording->setRecordingName('会议录制 ' . $i);
            // 确保 fileUrl 字段有值，这是 NOT NULL 约束要求的
            $recording->setFileUrl('https://test-storage.recordings.com/recording_' . $i . '.mp4');
            $recording->setRecordingUrl('https://test-storage.recordings.com/recording_' . $i . '.mp4');
            $recording->setFileSize(1024000 * $i * 50);
            $recording->setDuration(3600 * $i);
            $recording->setRecordingType('cloud');
            $recording->setStatus('available');
            $recording->setRecordingFormat('mp4');
            $recording->setResolution('1920x1080');
            $recording->setMeeting($meeting);
            $recording->setStartTime(new \DateTimeImmutable('2024-01-01 10:00:00'));
            $recording->setEndTime(new \DateTimeImmutable('2024-01-01 11:00:00'));
            $recording->setDownloadUrl('https://test-storage.downloads.com/recording_' . $i . '.mp4');
            $recording->setPlayUrl('https://test-player.videos.com/recording_' . $i);
            $recording->setExpirationTime(new \DateTimeImmutable('2024-12-31 23:59:59'));
            $recording->setPassword('rec' . $i);
            $recording->setConfig($config);

            $manager->persist($recording);
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

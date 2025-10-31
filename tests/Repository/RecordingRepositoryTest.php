<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\Recording;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Enum\MeetingStatus;
use Tourze\TencentMeetingBundle\Repository\RecordingRepository;

/**
 * @internal
 */
#[CoversClass(RecordingRepository::class)]
#[RunTestsInSeparateProcesses]
final class RecordingRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // Repository 测试基类不需要特殊的初始化
    }

    protected function createNewEntity(): object
    {
        // 创建必须的会议配置和会议实体
        $config = new TencentMeetingConfig();
        $config->setAppId('test-app-id-' . uniqid());
        $config->setSecretId('test-secret-id');
        $config->setSecretKey('test-secret-key');
        $config->setAuthType('JWT');
        $config->setEnabled(true);

        $meeting = new Meeting();
        $meeting->setMeetingId('test-meeting-' . uniqid());
        $meeting->setMeetingCode('meeting-code-' . uniqid());
        $meeting->setSubject('Test Meeting Subject');
        $meeting->setStartTime(new \DateTimeImmutable('+1 day'));
        $meeting->setEndTime(new \DateTimeImmutable('+1 day +1 hour'));
        $meeting->setStatus(MeetingStatus::SCHEDULED);
        $meeting->setDuration(60);
        $meeting->setUserId('test-user-' . uniqid());
        $meeting->setTimezone('Asia/Shanghai');
        $meeting->setConfig($config);

        $recording = new Recording();
        $recording->setRecordingId('test-recording-' . uniqid());
        $recording->setMeeting($meeting);
        $recording->setRecordingType('cloud');
        $recording->setFileUrl('https://example.com/recording.mp4');
        $recording->setFileName('test-recording.mp4');
        $recording->setFileSize(1048576);
        $recording->setDuration(3600);
        $recording->setFileFormat('mp4');
        $recording->setStatus('available');
        $recording->setStartTime(new \DateTimeImmutable());
        $recording->setEndTime(new \DateTimeImmutable('+1 hour'));
        $recording->setShareStatus('private');

        return $recording;
    }

    /**
     * @return ServiceEntityRepository<Recording>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(RecordingRepository::class);
    }
}

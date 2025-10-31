<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\Recording;

/**
 * @internal
 */
#[CoversClass(Recording::class)]
final class RecordingTest extends AbstractEntityTestCase
{
    protected function createEntity(): Recording
    {
        return new Recording();
    }

    public function testRecordingEntity(): void
    {
        $meeting = new Meeting();
        $recording = new Recording();

        $meeting->setMeetingId('meeting_123');
        $recording->setRecordingId('rec_123');
        $recording->setFileUrl('https://example.com/recording.mp4');
        $recording->setMeeting($meeting);

        $this->assertEquals('rec_123', $recording->getRecordingId());
        $this->assertEquals('https://example.com/recording.mp4', $recording->getFileUrl());
        $this->assertSame($meeting, $recording->getMeeting());
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'recordingId' => ['recordingId', 'rec_123'],
            'recordingType' => ['recordingType', 'cloud'],
            'fileUrl' => ['fileUrl', 'https://example.com/recording.mp4'],
            'fileName' => ['fileName', 'meeting_recording.mp4'],
            'fileSize' => ['fileSize', 1024000],
            'duration' => ['duration', 3600],
            'fileFormat' => ['fileFormat', 'mp4'],
            'status' => ['status', 'available'],
            'startTime' => ['startTime', new \DateTimeImmutable('2024-01-01 10:00:00')],
            'endTime' => ['endTime', new \DateTimeImmutable('2024-01-01 11:00:00')],
            'expirationTime' => ['expirationTime', new \DateTimeImmutable('2024-12-31 23:59:59')],
            'shareStatus' => ['shareStatus', 'private'],
            'viewCount' => ['viewCount', 5],
            'downloadCount' => ['downloadCount', 2],
            'remark' => ['remark', 'Test recording remark'],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
        ];
    }
}

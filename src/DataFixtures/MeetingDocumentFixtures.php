<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\MeetingDocument;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Enum\MeetingStatus;

class MeetingDocumentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 使用已存在的配置实体
        /** @var TencentMeetingConfig $config */
        $config = $this->getReference(TencentMeetingConfigFixtures::CONFIG_REFERENCE_1, TencentMeetingConfig::class);

        // 创建测试用的会议实体
        $meeting = new Meeting();
        $meeting->setMeetingId('meeting_test_doc_001');
        $meeting->setMeetingCode('MDC001');
        $meeting->setSubject('测试会议文档');
        $meeting->setStartTime(new \DateTimeImmutable('2024-01-01 10:00:00'));
        $meeting->setEndTime(new \DateTimeImmutable('2024-01-01 11:00:00'));
        $meeting->setStatus(MeetingStatus::SCHEDULED);
        $meeting->setDuration(60);
        $meeting->setConfig($config);
        $meeting->setUserId('test_user_001');
        $manager->persist($meeting);

        // 创建测试用的会议文档数据
        $documentTypes = ['pdf', 'doc', 'ppt', 'xls', 'txt', 'image'];
        $documentNames = [
            '项目计划书.pdf',
            '会议议程.docx',
            '财务报表.xlsx',
            '产品演示.pptx',
            '会议纪要.txt',
            '设计图.jpg',
        ];

        for ($i = 0; $i < 3; ++$i) {
            $meetingDocument = new MeetingDocument();

            $meetingDocument->setMeeting($meeting);
            $meetingDocument->setDocumentName($documentNames[$i]);
            $meetingDocument->setDocumentUrl('https://test-cdn.documents.com/' . $documentNames[$i]);
            $meetingDocument->setDocumentType($documentTypes[$i]);
            $meetingDocument->setFileSize(1024000 * ($i + 1)); // 1MB, 2MB, 3MB
            $meetingDocument->setMimeType($this->getMimeType($documentTypes[$i]));
            $meetingDocument->setStatus('available');
            $meetingDocument->setFilePath('/uploads/documents/' . $documentNames[$i]);
            $meetingDocument->setStoragePath('s3://documents/' . $documentNames[$i]);
            $meetingDocument->setThumbnailUrl('https://test-cdn.thumbnails.com/' . $documentNames[$i] . '.jpg');
            $meetingDocument->setUploaderUserId('user_' . ($i + 1));
            $meetingDocument->setPermissions([
                'read' => 'all',
                'download' => 'host,cohost',
                'edit' => 'host',
            ]);
            $meetingDocument->setDownloadCount($i * 5);
            $meetingDocument->setViewCount($i * 10);
            $meetingDocument->setExpirationTime(new \DateTimeImmutable('2024-12-31 23:59:59'));
            $meetingDocument->setDescription('这是第 ' . ($i + 1) . ' 个测试文档');
            $meetingDocument->setRemark('会议文档测试数据 ' . ($i + 1));
            $meetingDocument->setConfig($config);

            $manager->persist($meetingDocument);
        }

        $manager->flush();
    }

    private function getMimeType(string $documentType): string
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'ppt' => 'application/vnd.ms-powerpoint',
            'xls' => 'application/vnd.ms-excel',
            'txt' => 'text/plain',
            'image' => 'image/jpeg',
        ];

        return $mimeTypes[$documentType] ?? 'application/octet-stream';
    }

    public function getDependencies(): array
    {
        return [
            TencentMeetingConfigFixtures::class,
        ];
    }
}

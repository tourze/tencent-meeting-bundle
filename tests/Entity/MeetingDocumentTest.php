<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Validator\Validation;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\MeetingDocument;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

/**
 * @internal
 */
#[CoversClass(MeetingDocument::class)]
final class MeetingDocumentTest extends AbstractEntityTestCase
{
    protected function createEntity(): MeetingDocument
    {
        return new MeetingDocument();
    }

    public function testMeetingDocumentCreation(): void
    {
        $meeting = new Meeting();
        $config = new TencentMeetingConfig();

        $document = new MeetingDocument();
        $document->setMeeting($meeting);
        $document->setConfig($config);
        $document->setDocumentName('Test Document');
        $document->setDocumentUrl('https://example.com/document.pdf');
        $document->setDocumentType('agenda');
        $document->setFileSize(1024);
        $document->setMimeType('application/pdf');

        $this->assertInstanceOf(MeetingDocument::class, $document);
        $this->assertSame($meeting, $document->getMeeting());
        $this->assertSame($config, $document->getConfig());
        $this->assertEquals('Test Document', $document->getDocumentName());
        $this->assertEquals('https://example.com/document.pdf', $document->getDocumentUrl());
        $this->assertEquals('agenda', $document->getDocumentType());
        $this->assertEquals(1024, $document->getFileSize());
        $this->assertEquals('application/pdf', $document->getMimeType());
    }

    public function testMeetingDocumentToString(): void
    {
        $document = new MeetingDocument();
        $document->setDocumentName('Test Document');

        $reflection = new \ReflectionClass($document);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($document, 123);

        $this->assertEquals('MeetingDocument[id=123, name=Test Document]', (string) $document);
    }

    public function testMeetingDocumentSettersAndGetters(): void
    {
        $document = new MeetingDocument();

        // 测试基本属性
        $document->setDocumentName('Test Document');
        $this->assertEquals('Test Document', $document->getDocumentName());

        $document->setDocumentUrl('https://example.com/document.pdf');
        $this->assertEquals('https://example.com/document.pdf', $document->getDocumentUrl());

        $document->setDocumentType('agenda');
        $this->assertEquals('agenda', $document->getDocumentType());

        $document->setFileSize(1024);
        $this->assertEquals(1024, $document->getFileSize());

        $document->setMimeType('application/pdf');
        $this->assertEquals('application/pdf', $document->getMimeType());

        $document->setStoragePath('/path/to/document.pdf');
        $this->assertEquals('/path/to/document.pdf', $document->getStoragePath());

        $expireTime = new \DateTimeImmutable('2023-12-31 23:59:59');
        $document->setExpirationTime($expireTime);
        $this->assertEquals($expireTime, $document->getExpirationTime());

        $document->setRemark('Test remark');
        $this->assertEquals('Test remark', $document->getRemark());

        // 测试关系属性
        $meeting = new Meeting();
        $document->setMeeting($meeting);
        $this->assertSame($meeting, $document->getMeeting());

        $config = new TencentMeetingConfig();
        $document->setConfig($config);
        $this->assertSame($config, $document->getConfig());
    }

    public function testMeetingDocumentValidation(): void
    {
        $validator = Validation::createValidator();

        // 测试有效数据
        $meeting = new Meeting();
        $config = new TencentMeetingConfig();
        $document = new MeetingDocument();
        $document->setMeeting($meeting);
        $document->setConfig($config);
        $document->setDocumentName('Valid Document');
        $document->setDocumentUrl('https://example.com/document.pdf');

        $violations = $validator->validate($document);
        $this->assertCount(0, $violations);
    }

    public function testDocumentUrlValidation(): void
    {
        $document = new MeetingDocument();

        // 测试设置和获取 URL
        $this->assertEquals('', $document->getDocumentUrl());

        $document->setDocumentUrl('https://example.com/document.pdf');
        $this->assertEquals('https://example.com/document.pdf', $document->getDocumentUrl());
    }

    public function testExpireTimeValidation(): void
    {
        $document = new MeetingDocument();

        // 测试设置和获取过期时间
        $this->assertNull($document->getExpirationTime());

        $expireTime = new \DateTimeImmutable('2025-12-31 23:59:59');
        $document->setExpirationTime($expireTime);
        $this->assertEquals($expireTime, $document->getExpirationTime());

        $document->setExpirationTime(null);
        $this->assertNull($document->getExpirationTime());
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'documentName' => ['documentName', 'Test Document'],
            'documentUrl' => ['documentUrl', 'https://example.com/document.pdf'],
            'documentType' => ['documentType', 'pdf'],
            'fileSize' => ['fileSize', 1024],
            'mimeType' => ['mimeType', 'application/pdf'],
            'status' => ['status', 'available'],
            'filePath' => ['filePath', '/path/to/document.pdf'],
            'storagePath' => ['storagePath', '/storage/path/document.pdf'],
            'thumbnailUrl' => ['thumbnailUrl', 'https://example.com/thumbnail.jpg'],
            'uploaderUserId' => ['uploaderUserId', 'user_123'],
            'permissions' => ['permissions', ['read' => true, 'write' => false]],
            'downloadCount' => ['downloadCount', 5],
            'viewCount' => ['viewCount', 10],
            'expirationTime' => ['expirationTime', new \DateTimeImmutable('2025-12-31 23:59:59')],
            'description' => ['description', 'Test document description'],
            'remark' => ['remark', 'Test document remark'],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
            'updateTime' => ['updateTime', new \DateTimeImmutable('2024-01-01 09:30:00')],
        ];
    }
}

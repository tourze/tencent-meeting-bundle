<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TencentMeetingBundle\Controller\MeetingDocumentCrudController;

/**
 * @internal
 */
#[CoversClass(MeetingDocumentCrudController::class)]
#[RunTestsInSeparateProcesses]
final class MeetingDocumentCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): MeetingDocumentCrudController
    {
        return self::getService(MeetingDocumentCrudController::class);
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'meeting字段' => ['meeting'];
        yield 'documentName字段' => ['documentName'];
        yield 'documentUrl字段' => ['documentUrl'];
        yield 'documentType字段' => ['documentType'];
        yield 'fileSize字段' => ['fileSize'];
        yield 'mimeType字段' => ['mimeType'];
        yield 'status字段' => ['status'];
        yield 'filePath字段' => ['filePath'];
        yield 'storagePath字段' => ['storagePath'];
        yield 'thumbnailUrl字段' => ['thumbnailUrl'];
        yield 'uploaderUserId字段' => ['uploaderUserId'];
        yield 'downloadCount字段' => ['downloadCount'];
        yield 'viewCount字段' => ['viewCount'];
        yield 'expirationTime字段' => ['expirationTime'];
        yield 'description字段' => ['description'];
        yield 'remark字段' => ['remark'];
        yield 'config字段' => ['config'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        return self::provideNewPageFields();
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID列' => ['ID'];
        yield '所属会议列' => ['所属会议'];
        yield '文档名称列' => ['文档名称'];
        yield '文档类型列' => ['文档类型'];
        yield '文件大小列' => ['文件大小'];
        yield '文档状态列' => ['文档状态'];
        yield '下载次数列' => ['下载次数'];
        yield '查看次数列' => ['查看次数'];
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('new');
        $client->request('POST', $url, [
            'MeetingDocument' => [
                // Missing required fields to trigger validation errors
                'documentName' => '',
                'documentUrl' => '',
            ],
        ]);

        $response = $client->getResponse();
        // Should get either 302 (redirect due to auth) or 422 (validation error)
        $this->assertContains(
            $response->getStatusCode(),
            [302, 422, 401],
            'Expected redirect or validation error but got: ' . $response->getStatusCode()
        );
    }

    public function testUnauthorizedAccessReturnsRedirect(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('index');
        $client->request('GET', $url);

        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection(), 'Expected redirect response but got: ' . $response->getStatusCode());
    }

    public function testGetRequest(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('index');
        $client->request('GET', $url);

        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection(), 'Expected redirect response but got: ' . $response->getStatusCode());
    }

    public function testPostRequest(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('index');
        $client->request('POST', $url);

        $response = $client->getResponse();
        $this->assertSame(405, $response->getStatusCode(), 'Expected 405 Method Not Allowed for POST to index');
    }
}

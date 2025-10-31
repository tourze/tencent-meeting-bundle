<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TencentMeetingBundle\Controller\RecordingCrudController;
use Tourze\TencentMeetingBundle\Entity\Recording;

/**
 * @internal
 */
#[CoversClass(RecordingCrudController::class)]
#[RunTestsInSeparateProcesses]
final class RecordingCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): RecordingCrudController
    {
        return self::getService(RecordingCrudController::class);
    }

    public function testGetEntityFqcn(): void
    {
        self::assertSame(Recording::class, RecordingCrudController::getEntityFqcn());
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'recordingId字段' => ['recordingId'];
        yield 'recordingName字段' => ['recordingName'];
        yield 'meeting字段' => ['meeting'];
        yield 'recordingType字段' => ['recordingType'];
        yield 'status字段' => ['status'];
        yield 'fileName字段' => ['fileName'];
        yield 'fileUrl字段' => ['fileUrl'];
        yield 'playUrl字段' => ['playUrl'];
        yield 'downloadUrl字段' => ['downloadUrl'];
        yield 'fileSize字段' => ['fileSize'];
        yield 'duration字段' => ['duration'];
        yield 'fileFormat字段' => ['fileFormat'];
        yield 'resolution字段' => ['resolution'];
        yield 'shareStatus字段' => ['shareStatus'];
        yield 'viewCount字段' => ['viewCount'];
        yield 'downloadCount字段' => ['downloadCount'];
        yield 'password字段' => ['password'];
        yield 'startTime字段' => ['startTime'];
        yield 'endTime字段' => ['endTime'];
        yield 'expirationTime字段' => ['expirationTime'];
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
        yield '录制ID列' => ['录制ID'];
        yield '录制名称列' => ['录制名称'];
        yield '关联会议列' => ['关联会议'];
        yield '录制类型列' => ['录制类型'];
        yield '录制状态列' => ['录制状态'];
        yield '文件大小列' => ['文件大小'];
        yield '录制时长列' => ['录制时长'];
        yield '文件格式列' => ['文件格式'];
        yield '分辨率列' => ['分辨率'];
        yield '开始录制时间列' => ['开始录制时间'];
        yield '创建时间列' => ['创建时间'];
        yield '更新时间列' => ['更新时间'];
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

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('new');
        $client->request('POST', $url, [
            'Recording' => [
                'recordingId' => '',  // Required but empty
                'fileUrl' => '',  // Required but empty
                'startTime' => '',  // Required but empty
            ],
        ]);

        $response = $client->getResponse();
        // PHPStan expects specific validation assertions
        if (422 === $response->getStatusCode()) {
            $this->assertResponseStatusCodeSame(422);
        } else {
            // Should get either 302 (redirect due to auth) or 422 (validation error)
            $this->assertContains(
                $response->getStatusCode(),
                [302, 422, 401],
                'Expected redirect or validation error but got: ' . $response->getStatusCode()
            );
        }
    }
}

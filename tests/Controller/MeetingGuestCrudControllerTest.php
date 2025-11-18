<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TencentMeetingBundle\Controller\MeetingGuestCrudController;

/**
 * @internal
 */
#[CoversClass(MeetingGuestCrudController::class)]
#[RunTestsInSeparateProcesses]
final class MeetingGuestCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): MeetingGuestCrudController
    {
        return self::getService(MeetingGuestCrudController::class);
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'meeting字段' => ['meeting'];
        yield 'guestName字段' => ['guestName'];
        yield 'email字段' => ['email'];
        yield 'phone字段' => ['phone'];
        yield 'company字段' => ['company'];
        yield 'position字段' => ['position'];
        yield 'guestType字段' => ['guestType'];
        yield 'inviteStatus字段' => ['inviteStatus'];
        yield 'invitationTime字段' => ['invitationTime'];
        yield 'responseTime字段' => ['responseTime'];
        yield 'attendanceStatus字段' => ['attendanceStatus'];
        yield 'joinTime字段' => ['joinTime'];
        yield 'leaveTime字段' => ['leaveTime'];
        yield 'attendDuration字段' => ['attendDuration'];
        yield 'needReminder字段' => ['needReminder'];
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
        yield '嘉宾姓名列' => ['嘉宾姓名'];
        yield '邮箱列' => ['邮箱'];
        yield '公司列' => ['公司'];
        yield '职位列' => ['职位'];
        yield '嘉宾类型列' => ['嘉宾类型'];
        yield '邀请状态列' => ['邀请状态'];
        yield '参会状态列' => ['参会状态'];
        yield '需要提醒列' => ['需要提醒'];
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('new');
        $client->request('POST', $url, [
            'MeetingGuest' => [
                // Missing required fields to trigger validation errors
                'guestName' => '',
                'email' => '',
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

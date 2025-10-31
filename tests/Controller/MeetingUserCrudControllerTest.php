<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TencentMeetingBundle\Controller\MeetingUserCrudController;
use Tourze\TencentMeetingBundle\Entity\MeetingUser;

/**
 * @internal
 */
#[CoversClass(MeetingUserCrudController::class)]
#[RunTestsInSeparateProcesses]
final class MeetingUserCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): MeetingUserCrudController
    {
        return self::getService(MeetingUserCrudController::class);
    }

    public function testGetEntityFqcn(): void
    {
        self::assertSame(MeetingUser::class, MeetingUserCrudController::getEntityFqcn());
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'meeting字段' => ['meeting'];
        yield 'user字段' => ['user'];
        yield 'role字段' => ['role'];
        yield 'attendeeStatus字段' => ['attendeeStatus'];
        yield 'joinTime字段' => ['joinTime'];
        yield 'leaveTime字段' => ['leaveTime'];
        yield 'attendDuration字段' => ['attendDuration'];
        yield 'deviceInfo字段' => ['deviceInfo'];
        yield 'networkType字段' => ['networkType'];
        yield 'cameraOn字段' => ['cameraOn'];
        yield 'micOn字段' => ['micOn'];
        yield 'screenShared字段' => ['screenShared'];
        yield 'remark字段' => ['remark'];
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
        yield '用户列' => ['用户'];
        yield '用户角色列' => ['用户角色'];
        yield '参会状态列' => ['参会状态'];
        yield '入会时间列' => ['入会时间'];
        yield '参会时长列' => ['参会时长'];
        yield '摄像头状态列' => ['摄像头状态'];
        yield '麦克风状态列' => ['麦克风状态'];
        yield '屏幕共享状态列' => ['屏幕共享状态'];
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

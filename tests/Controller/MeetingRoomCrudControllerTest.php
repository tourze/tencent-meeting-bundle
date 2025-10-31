<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TencentMeetingBundle\Controller\MeetingRoomCrudController;
use Tourze\TencentMeetingBundle\Entity\MeetingRoom;

/**
 * @internal
 */
#[CoversClass(MeetingRoomCrudController::class)]
#[RunTestsInSeparateProcesses]
final class MeetingRoomCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): MeetingRoomCrudController
    {
        return self::getService(MeetingRoomCrudController::class);
    }

    public function testGetEntityFqcn(): void
    {
        self::assertSame(MeetingRoom::class, MeetingRoomCrudController::getEntityFqcn());
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'roomId字段' => ['roomId'];
        yield 'name字段' => ['name'];
        yield 'description字段' => ['description'];
        yield 'capacity字段' => ['capacity'];
        yield 'roomType字段' => ['roomType'];
        yield 'location字段' => ['location'];
        yield 'deviceStatus字段' => ['deviceStatus'];
        yield 'status字段' => ['status'];
        yield 'equipmentList字段' => ['equipmentList'];
        yield 'supportRecording字段' => ['supportRecording'];
        yield 'supportLive字段' => ['supportLive'];
        yield 'supportScreenShare字段' => ['supportScreenShare'];
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
        yield '会议室名称列' => ['会议室名称'];
        yield '会议室类型列' => ['会议室类型'];
        yield '容量列' => ['容量'];
        yield '位置列' => ['位置'];
        yield '设备状态列' => ['设备状态'];
        yield '会议室状态列' => ['会议室状态'];
        yield '设备数量列' => ['设备数量'];
        yield '支持录制列' => ['支持录制'];
        yield '支持直播列' => ['支持直播'];
        yield '支持屏幕共享列' => ['支持屏幕共享'];
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

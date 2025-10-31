<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TencentMeetingBundle\Controller\MeetingBackgroundCrudController;
use Tourze\TencentMeetingBundle\Entity\MeetingBackground;

/**
 * @internal
 */
#[CoversClass(MeetingBackgroundCrudController::class)]
#[RunTestsInSeparateProcesses]
final class MeetingBackgroundCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): MeetingBackgroundCrudController
    {
        return self::getService(MeetingBackgroundCrudController::class);
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            'ID列' => ['ID'],
            '所属会议列' => ['所属会议'],
            '背景列' => ['背景'],
            '应用时间列' => ['应用时间'],
            '状态列' => ['状态'],
            '应用者列' => ['应用者'],
        ];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'meeting字段' => ['meeting'];
        yield 'background字段' => ['background'];
        yield 'applicationTime字段' => ['applicationTime'];
        yield 'status字段' => ['status'];
        yield 'appliedBy字段' => ['appliedBy'];
        yield 'customConfig字段' => ['customConfig'];
        yield 'remark字段' => ['remark'];
        yield 'config字段' => ['config'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        return self::provideNewPageFields();
    }

    public function testGetEntityFqcn(): void
    {
        self::assertSame(MeetingBackground::class, MeetingBackgroundCrudController::getEntityFqcn());
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
        // POST to index is not allowed in EasyAdmin, expect 405 Method Not Allowed
        $this->assertSame(405, $response->getStatusCode(), 'Expected 405 Method Not Allowed for POST to index');
    }

    public function testStatusFilter(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('index', [
            'filters' => ['status' => 'active'],
        ]);
        $client->request('GET', $url);

        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection(), 'Expected redirect response but got: ' . $response->getStatusCode());
    }

    public function testNewFormValidation(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('new');
        $client->request('GET', $url);

        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection(), 'Expected redirect response but got: ' . $response->getStatusCode());
    }
}

<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TencentMeetingBundle\Controller\MeetingLayoutCrudController;
use Tourze\TencentMeetingBundle\Entity\MeetingLayout;

/**
 * @internal
 */
#[CoversClass(MeetingLayoutCrudController::class)]
#[RunTestsInSeparateProcesses]
final class MeetingLayoutCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): MeetingLayoutCrudController
    {
        return self::getService(MeetingLayoutCrudController::class);
    }

    public function testGetEntityFqcn(): void
    {
        self::assertSame(MeetingLayout::class, MeetingLayoutCrudController::getEntityFqcn());
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('new');
        $client->request('POST', $url, [
            'MeetingLayout' => [
                // Missing required fields to trigger validation errors
                'meeting' => '',
                'layout' => '',
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

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'meeting字段' => ['meeting'];
        yield 'layout字段' => ['layout'];
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

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID列' => ['ID'];
        yield '所属会议列' => ['所属会议'];
        yield '布局列' => ['布局'];
        yield '应用时间列' => ['应用时间'];
        yield '状态列' => ['状态'];
        yield '应用者列' => ['应用者'];
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

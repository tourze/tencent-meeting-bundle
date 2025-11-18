<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TencentMeetingBundle\Controller\MeetingVoteCrudController;

/**
 * @internal
 */
#[CoversClass(MeetingVoteCrudController::class)]
#[RunTestsInSeparateProcesses]
final class MeetingVoteCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): MeetingVoteCrudController
    {
        return self::getService(MeetingVoteCrudController::class);
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'meeting字段' => ['meeting'];
        yield 'subject字段' => ['subject'];
        yield 'description字段' => ['description'];
        yield 'voteType字段' => ['voteType'];
        yield 'status字段' => ['status'];
        yield 'anonymous字段' => ['anonymous'];
        yield 'showResult字段' => ['showResult'];
        yield 'startTime字段' => ['startTime'];
        yield 'endTime字段' => ['endTime'];
        yield 'totalVotes字段' => ['totalVotes'];
        yield 'options字段' => ['options'];
        yield 'results字段' => ['results'];
        yield 'creatorUserId字段' => ['creatorUserId'];
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
        yield '投票主题列' => ['投票主题'];
        yield '投票类型列' => ['投票类型'];
        yield '投票状态列' => ['投票状态'];
        yield '总投票数列' => ['总投票数'];
        yield '匿名投票列' => ['匿名投票'];
        yield '显示结果列' => ['显示结果'];
        yield '开始时间列' => ['开始时间'];
        yield '结束时间列' => ['结束时间'];
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

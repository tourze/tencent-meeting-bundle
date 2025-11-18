<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TencentMeetingBundle\Controller\MeetingCrudController;
use Tourze\TencentMeetingBundle\Entity\Meeting;

/**
 * @internal
 */
#[CoversClass(MeetingCrudController::class)]
#[RunTestsInSeparateProcesses]
final class MeetingCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): MeetingCrudController
    {
        return self::getService(MeetingCrudController::class);
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID列' => ['ID'];
        yield '会议ID列' => ['会议ID'];
        yield '会议主题列' => ['会议主题'];
        yield '会议号列' => ['会议号'];
        yield '开始时间列' => ['开始时间'];
        yield '会议状态列' => ['会议状态'];
        yield '参会人数列' => ['参会人数'];
        yield '文档数量列' => ['文档数量'];
        yield '已发送提醒列' => ['已发送提醒'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'meetingId字段' => ['meetingId'];
        yield 'meetingCode字段' => ['meetingCode'];
        yield 'subject字段' => ['subject'];
        yield 'startTime字段' => ['startTime'];
        yield 'endTime字段' => ['endTime'];
        yield 'status字段' => ['status'];
        yield 'duration字段' => ['duration'];
        yield 'timezone字段' => ['timezone'];
        yield 'password字段' => ['password'];
        yield 'config字段' => ['config'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        return self::provideNewPageFields();
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

    public function testMeetingIdFilter(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('index', [
            'filters' => ['meetingId' => 'meeting_001'],
        ]);
        $client->request('GET', $url);

        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection(), 'Expected redirect response but got: ' . $response->getStatusCode());
    }

    public function testSubjectFilter(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('index', [
            'filters' => ['subject' => '技术讨论会'],
        ]);
        $client->request('GET', $url);

        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection(), 'Expected redirect response but got: ' . $response->getStatusCode());
    }

    public function testStatusFilter(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('index', [
            'filters' => ['status' => 'MEETING_STATE_WAITING'],
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

    public function testValidationErrors(): void
    {
        $entity = new Meeting();

        // 测试必填字段为空时的验证错误
        /** @var ValidatorInterface $validator */
        $validator = self::getContainer()->get('validator');
        $violations = $validator->validate($entity);

        $this->assertGreaterThan(0, $violations->count(), '应该有验证错误');

        // 验证特定字段的错误
        $propertyPaths = [];
        foreach ($violations as $violation) {
            $propertyPaths[] = $violation->getPropertyPath();
        }

        // 验证必填字段
        $this->assertContains('meetingId', $propertyPaths, 'meetingId应该有验证错误');
        $this->assertContains('subject', $propertyPaths, 'subject应该有验证错误');
        $this->assertContains('startTime', $propertyPaths, 'startTime应该有验证错误');
        $this->assertContains('userId', $propertyPaths, 'userId应该有验证错误');
    }
}

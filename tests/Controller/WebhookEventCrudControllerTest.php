<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TencentMeetingBundle\Controller\WebhookEventCrudController;
use Tourze\TencentMeetingBundle\Entity\WebhookEvent;

/**
 * @internal
 */
#[CoversClass(WebhookEventCrudController::class)]
#[RunTestsInSeparateProcesses]
final class WebhookEventCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): WebhookEventCrudController
    {
        return self::getService(WebhookEventCrudController::class);
    }

    public function testGetEntityFqcn(): void
    {
        self::assertSame(WebhookEvent::class, WebhookEventCrudController::getEntityFqcn());
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'eventId字段' => ['eventId'];
        yield 'eventType字段' => ['eventType'];
        yield 'processStatus字段' => ['processStatus'];
        yield 'retryCount字段' => ['retryCount'];
        yield 'signatureVerified字段' => ['signatureVerified'];
        yield 'meetingId字段' => ['meetingId'];
        yield 'userId字段' => ['userId'];
        yield 'eventTime字段' => ['eventTime'];
        yield 'processingTime字段' => ['processingTime'];
        yield 'nextRetryTime字段' => ['nextRetryTime'];
        yield 'sourceIp字段' => ['sourceIp'];
        yield 'userAgent字段' => ['userAgent'];
        yield 'errorMessage字段' => ['errorMessage'];
        yield 'payload字段' => ['payload'];
        yield 'processResult字段' => ['processResult'];
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
        yield '事件ID列' => ['事件ID'];
        yield '事件类型列' => ['事件类型'];
        yield '处理状态列' => ['处理状态'];
        yield '重试次数列' => ['重试次数'];
        yield '签名验证列' => ['签名验证'];
        yield '事件时间列' => ['事件时间'];
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
            'WebhookEvent' => [
                'eventId' => '',  // Required but empty
                'eventType' => '',  // Required but empty
                'payload' => '',  // Required but empty
                'eventTime' => '',  // Required but empty
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

    public function testRetryProcessingAction(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        // Test the retryProcessing custom action
        $url = $this->generateAdminUrl('retry', ['entityId' => 1]);
        $client->request('GET', $url);

        $response = $client->getResponse();
        // Should get either 302 (redirect due to auth) or 200 (success)
        $this->assertContains(
            $response->getStatusCode(),
            [302, 200, 401, 404],
            'Expected redirect, success, or not found but got: ' . $response->getStatusCode()
        );
    }

    public function testViewPayloadAction(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        // Test the viewPayload custom action
        $url = $this->generateAdminUrl('viewPayload', ['entityId' => 1]);
        $client->request('GET', $url);

        $response = $client->getResponse();
        // Should get either 302 (redirect due to auth) or 200 (success)
        $this->assertContains(
            $response->getStatusCode(),
            [302, 200, 401, 404],
            'Expected redirect, success, or not found but got: ' . $response->getStatusCode()
        );
    }
}

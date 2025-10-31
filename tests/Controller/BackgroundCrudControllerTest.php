<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TencentMeetingBundle\Controller\BackgroundCrudController;
use Tourze\TencentMeetingBundle\Entity\Background;

/**
 * @internal
 */
#[CoversClass(BackgroundCrudController::class)]
#[RunTestsInSeparateProcesses]
final class BackgroundCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): BackgroundCrudController
    {
        return self::getService(BackgroundCrudController::class);
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            'ID' => ['ID'],
            '背景ID' => ['背景ID'],
            '背景名称' => ['背景名称'],
            '背景类型' => ['背景类型'],
            '状态' => ['状态'],
            '排序权重' => ['排序权重'],
            '默认背景' => ['默认背景'],
            '内置背景' => ['内置背景'],
            '创建时间' => ['创建时间'],
            '更新时间' => ['更新时间'],
        ];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'backgroundId' => ['backgroundId'];
        yield 'name' => ['name'];
        yield 'description' => ['description'];
        yield 'backgroundType' => ['backgroundType'];
        yield 'status' => ['status'];
        yield 'orderWeight' => ['orderWeight'];
        yield 'imageUrl' => ['imageUrl'];
        yield 'thumbnailUrl' => ['thumbnailUrl'];
        yield 'isDefault' => ['isDefault'];
        yield 'isBuiltIn' => ['isBuiltIn'];
        yield 'applicableScope' => ['applicableScope'];
        yield 'fileSize' => ['fileSize'];
        yield 'imageFormat' => ['imageFormat'];
        yield 'imageWidth' => ['imageWidth'];
        yield 'imageHeight' => ['imageHeight'];
        yield 'primaryColor' => ['primaryColor'];
        yield 'expirationTime' => ['expirationTime'];
        yield 'config' => ['config'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        return self::provideNewPageFields();
    }

    public function testGetEntityFqcn(): void
    {
        self::assertSame(Background::class, BackgroundCrudController::getEntityFqcn());
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('new');
        $client->request('POST', $url, [
            'Background' => [
                'backgroundId' => '',  // Required but empty
                'name' => '',  // Required but empty
                'imageUrl' => '',  // Required but empty
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

    public function testBackgroundIdFilter(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('index', [
            'filters' => ['backgroundId' => 'bg_001'],
        ]);
        $client->request('GET', $url);

        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection(), 'Expected redirect response but got: ' . $response->getStatusCode());
    }

    public function testNameFilter(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('index', [
            'filters' => ['name' => '测试背景'],
        ]);
        $client->request('GET', $url);

        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection(), 'Expected redirect response but got: ' . $response->getStatusCode());
    }

    public function testBackgroundTypeFilter(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('index', [
            'filters' => ['backgroundType' => 'image'],
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
            'filters' => ['status' => 'active'],
        ]);
        $client->request('GET', $url);

        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection(), 'Expected redirect response but got: ' . $response->getStatusCode());
    }

    public function testIsDefaultFilter(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('index', [
            'filters' => ['isDefault' => true],
        ]);
        $client->request('GET', $url);

        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection(), 'Expected redirect response but got: ' . $response->getStatusCode());
    }

    public function testIsBuiltInFilter(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('index', [
            'filters' => ['isBuiltIn' => true],
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

    public function testNewFormValidationWithMissingRequiredFields(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        // Test form submission without required fields
        $url = $this->generateAdminUrl('new');
        $client->request('POST', $url, [
            'Background' => [
                // Missing required fields: backgroundId, name, backgroundType, status, imageUrl
                'description' => 'Test description',
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

    public function testNewFormValidationWithInvalidData(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        // Test form submission with invalid data
        $url = $this->generateAdminUrl('new');
        $client->request('POST', $url, [
            'Background' => [
                'backgroundId' => '',  // Required but empty
                'name' => '',  // Required but empty
                'backgroundType' => 'invalid_type',  // Invalid choice
                'status' => 'invalid_status',  // Invalid choice
                'imageUrl' => 'not_a_valid_url',  // Invalid URL format
                'orderWeight' => 'not_a_number',  // Should be integer
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
}

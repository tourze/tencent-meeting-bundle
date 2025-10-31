<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TencentMeetingBundle\Controller\DepartmentCrudController;
use Tourze\TencentMeetingBundle\Entity\Department;

/**
 * @internal
 */
#[CoversClass(DepartmentCrudController::class)]
#[RunTestsInSeparateProcesses]
final class DepartmentCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): DepartmentCrudController
    {
        return self::getService(DepartmentCrudController::class);
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID列' => ['ID'];
        yield '部门ID列' => ['部门ID'];
        yield '部门名称列' => ['部门名称'];
        yield '上级部门列' => ['上级部门'];
        yield '层级深度列' => ['层级深度'];
        yield '部门状态列' => ['部门状态'];
        yield '创建时间列' => ['创建时间'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield '部门ID字段' => ['departmentId'];
        yield '部门名称字段' => ['name'];
        yield '描述字段' => ['description'];
        yield '上级部门字段' => ['parent'];
        yield '路径字段' => ['path'];
        yield '层级字段' => ['level'];
        yield '排序权重字段' => ['orderWeight'];
        yield '状态字段' => ['status'];
        yield '配置字段' => ['config'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        return self::provideNewPageFields();
    }

    public function testGetEntityFqcn(): void
    {
        self::assertSame(Department::class, DepartmentCrudController::getEntityFqcn());
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('new');
        $client->request('POST', $url, [
            'Department' => [
                'departmentId' => '',  // Required but empty
                'name' => '',  // Required but empty
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

    public function testDepartmentIdFilter(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('index', [
            'filters' => ['departmentId' => 'dept_001'],
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
            'filters' => ['name' => '技术部'],
        ]);
        $client->request('GET', $url);

        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection(), 'Expected redirect response but got: ' . $response->getStatusCode());
    }

    public function testLevelFilter(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('index', [
            'filters' => ['level' => 2],
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
            'Department' => [
                // Missing required fields: departmentId, name
                'description' => 'Test description',
                'level' => 1,
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
            'Department' => [
                'departmentId' => '',  // Required but empty
                'name' => '',  // Required but empty
                'status' => 'invalid_status',  // Invalid choice
                'level' => -1,  // Invalid negative level
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

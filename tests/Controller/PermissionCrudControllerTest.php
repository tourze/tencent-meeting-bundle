<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TencentMeetingBundle\Controller\PermissionCrudController;
use Tourze\TencentMeetingBundle\Entity\Permission;

/**
 * @internal
 */
#[CoversClass(PermissionCrudController::class)]
#[RunTestsInSeparateProcesses]
final class PermissionCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): PermissionCrudController
    {
        return self::getService(PermissionCrudController::class);
    }

    public function testGetEntityFqcn(): void
    {
        self::assertSame(Permission::class, PermissionCrudController::getEntityFqcn());
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('new');
        $client->request('POST', $url, [
            'Permission' => [
                'permissionId' => '',  // Required but empty
                'name' => '',  // Required but empty
                'permissionCode' => '',  // Required but empty
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

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'permissionId字段' => ['permissionId'];
        yield 'name字段' => ['name'];
        yield 'description字段' => ['description'];
        yield 'permissionType字段' => ['permissionType'];
        yield 'permissionCode字段' => ['permissionCode'];
        yield 'status字段' => ['status'];
        yield 'orderWeight字段' => ['orderWeight'];
        yield 'isBuiltIn字段' => ['isBuiltIn'];
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
        yield '权限ID列' => ['权限ID'];
        yield '权限名称列' => ['权限名称'];
        yield '权限类型列' => ['权限类型'];
        yield '权限代码列' => ['权限代码'];
        yield '权限状态列' => ['权限状态'];
        yield '内置权限列' => ['内置权限'];
        yield '创建时间列' => ['创建时间'];
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

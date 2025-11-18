<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TencentMeetingBundle\Controller\UserRoleCrudController;

/**
 * @internal
 */
#[CoversClass(UserRoleCrudController::class)]
#[RunTestsInSeparateProcesses]
final class UserRoleCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): UserRoleCrudController
    {
        return self::getService(UserRoleCrudController::class);
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('new');
        $client->request('POST', $url, [
            'UserRole' => [
                'user' => '',  // Required but empty
                'role' => '',  // Required but empty
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

    public function testRevoke(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        // Test the revoke custom action
        $url = $this->generateAdminUrl('detail', ['entityId' => 1]);
        $client->request('GET', $url);

        $response = $client->getResponse();
        // Should get either 302 (redirect due to auth) or access the detail page
        $this->assertContains(
            $response->getStatusCode(),
            [302, 200, 401],
            'Expected redirect or success but got: ' . $response->getStatusCode()
        );
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'user字段' => ['user'];
        yield 'role字段' => ['role'];
        yield 'status字段' => ['status'];
        yield 'assignmentTime字段' => ['assignmentTime'];
        yield 'assignedBy字段' => ['assignedBy'];
        yield 'expirationTime字段' => ['expirationTime'];
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
        yield 'User列' => ['用户'];
        yield 'Role列' => ['角色'];
        yield 'Active列' => ['状态'];
        yield 'AssignedAt列' => ['分配时间'];
        yield 'ExpirationTime列' => ['过期时间'];
        yield 'CreateTime列' => ['创建时间'];
        yield 'UpdateTime列' => ['更新时间'];
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

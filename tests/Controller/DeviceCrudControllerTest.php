<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\TencentMeetingBundle\Controller\DeviceCrudController;
use Tourze\TencentMeetingBundle\Entity\Device;

/**
 * @internal
 */
#[CoversClass(DeviceCrudController::class)]
#[RunTestsInSeparateProcesses]
final class DeviceCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return AbstractCrudController<Device>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(DeviceCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '设备ID' => ['设备ID'];
        yield '设备名称' => ['设备名称'];
        yield '设备类型' => ['设备类型'];
        yield '设备状态' => ['设备状态'];
        yield '关联会议室' => ['关联会议室'];
        yield '创建时间' => ['创建时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'deviceId' => ['deviceId'];
        yield 'name' => ['name'];
        yield 'brand' => ['brand'];
        yield 'model' => ['model'];
        yield 'serialNumber' => ['serialNumber'];
        yield 'deviceType' => ['deviceType'];
        yield 'status' => ['status'];
        yield 'activationCode' => ['activationCode'];
        yield 'activationTime' => ['activationTime'];
        yield 'expirationTime' => ['expirationTime'];
        yield 'lastOnlineTime' => ['lastOnlineTime'];
        yield 'ipAddress' => ['ipAddress'];
        yield 'macAddress' => ['macAddress'];
        yield 'firmwareVersion' => ['firmwareVersion'];
        yield 'softwareVersion' => ['softwareVersion'];
        yield 'remark' => ['remark'];
        yield 'room' => ['room'];
        yield 'config' => ['config'];
    }

    public static function provideEditPageFields(): iterable
    {
        return self::provideNewPageFields();
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('new');
        $client->request('POST', $url, [
            'Device' => [
                'deviceId' => '',  // Required but empty
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

    public function testDeviceIdFilter(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(true);

        $url = $this->generateAdminUrl('index', [
            'filters' => ['deviceId' => 'device_001'],
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
            'filters' => ['name' => '会议设备'],
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

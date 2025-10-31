<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TencentMeetingBundle\Service\ClientFactoryInterface;

/**
 * @internal
 */
#[CoversClass(ClientFactoryInterface::class)]
final class ClientFactoryInterfaceTest extends TestCase
{
    public function testInterfaceCanBeInstantiated(): void
    {
        $mock = $this->createMock(ClientFactoryInterface::class);
        $this->assertInstanceOf(ClientFactoryInterface::class, $mock);
    }

    public function testInterfaceHasRequiredMethods(): void
    {
        $reflection = new \ReflectionClass(ClientFactoryInterface::class);
        $this->assertTrue($reflection->isInterface());

        // 检查接口是否包含所有必需的方法
        $requiredMethods = [
            'createMeetingClient',
            'createUserClient',
            'createRoomClient',
            'createRecordingClient',
            'createWebhookClient',
            'createAuthService',
            'createSyncService',
        ];

        $interfaceMethods = get_class_methods(ClientFactoryInterface::class);

        foreach ($requiredMethods as $method) {
            $this->assertContains($method, $interfaceMethods, "Method {$method} not found in interface");
        }
    }

    public function testInterfaceMethodSignatures(): void
    {
        $reflection = new \ReflectionClass(ClientFactoryInterface::class);

        // 测试createMeetingClient方法签名
        $method = $reflection->getMethod('createMeetingClient');
        $this->assertEquals('createMeetingClient', $method->getName());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('object', $method->getReturnType());

        // 测试createUserClient方法签名
        $method = $reflection->getMethod('createUserClient');
        $this->assertEquals('createUserClient', $method->getName());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('object', $method->getReturnType());

        // 测试createAuthService方法签名
        $method = $reflection->getMethod('createAuthService');
        $this->assertEquals('createAuthService', $method->getName());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('object', $method->getReturnType());
    }

    public function testInterfaceExtendsNothing(): void
    {
        $reflection = new \ReflectionClass(ClientFactoryInterface::class);
        $interfaces = $reflection->getInterfaceNames();

        // ClientFactoryInterface不应该继承其他接口
        $this->assertEmpty($interfaces, 'ClientFactoryInterface should not extend other interfaces');
    }

    public function testMockImplementsInterface(): void
    {
        $mock = $this->createMock(ClientFactoryInterface::class);
        $mockClient = $this->createMock(\stdClass::class);
        $mock->method('createMeetingClient')->willReturn($mockClient);
        $mock->method('createUserClient')->willReturn($mockClient);

        $this->assertInstanceOf(ClientFactoryInterface::class, $mock);
        $this->assertSame($mockClient, $mock->createMeetingClient());
        $this->assertSame($mockClient, $mock->createUserClient());
    }

    public function testInterfaceMethodsArePublic(): void
    {
        $reflection = new \ReflectionClass(ClientFactoryInterface::class);

        $methods = $reflection->getMethods();

        foreach ($methods as $method) {
            $this->assertTrue($method->isPublic(), "Method {$method->getName()} should be public");
        }
    }

    public function testInterfaceHasNoConstructor(): void
    {
        $reflection = new \ReflectionClass(ClientFactoryInterface::class);

        $this->assertFalse($reflection->hasMethod('__construct'), 'Interface should not have constructor');
    }
}

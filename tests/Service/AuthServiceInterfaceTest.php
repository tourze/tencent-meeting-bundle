<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TencentMeetingBundle\Service\AuthServiceInterface;

/**
 * @internal
 */
#[CoversClass(AuthServiceInterface::class)]
final class AuthServiceInterfaceTest extends TestCase
{
    public function testInterfaceCanBeInstantiated(): void
    {
        $mock = $this->createMock(AuthServiceInterface::class);
        $this->assertInstanceOf(AuthServiceInterface::class, $mock);
    }

    public function testInterfaceHasRequiredMethods(): void
    {
        $reflection = new \ReflectionClass(AuthServiceInterface::class);
        $this->assertTrue($reflection->isInterface());

        // 检查接口是否包含所有必需的方法
        $requiredMethods = [
            'authenticate',
            'refreshToken',
            'validateToken',
            'getUserInfo',
            'getPermissions',
            'hasPermission',
            'checkAccess',
            'logout',
        ];

        $interfaceMethods = get_class_methods(AuthServiceInterface::class);

        foreach ($requiredMethods as $method) {
            $this->assertContains($method, $interfaceMethods, "Method {$method} not found in interface");
        }
    }

    public function testInterfaceMethodSignatures(): void
    {
        $reflection = new \ReflectionClass(AuthServiceInterface::class);

        // 测试authenticate方法签名
        $method = $reflection->getMethod('authenticate');
        $this->assertEquals('authenticate', $method->getName());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('bool', $method->getReturnType());

        // 测试refreshToken方法签名
        $method = $reflection->getMethod('refreshToken');
        $this->assertEquals('refreshToken', $method->getName());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('bool', $method->getReturnType());

        // 测试validateToken方法签名
        $method = $reflection->getMethod('validateToken');
        $this->assertEquals('validateToken', $method->getName());
        $this->assertEquals(1, $method->getNumberOfParameters());
        $this->assertEquals('bool', $method->getReturnType());

        // 测试hasPermission方法签名
        $method = $reflection->getMethod('hasPermission');
        $this->assertEquals('hasPermission', $method->getName());
        $this->assertEquals(1, $method->getNumberOfParameters());
        $this->assertEquals('bool', $method->getReturnType());
    }

    public function testInterfaceExtendsNothing(): void
    {
        $reflection = new \ReflectionClass(AuthServiceInterface::class);
        $interfaces = $reflection->getInterfaceNames();

        // AuthServiceInterface不应该继承其他接口
        $this->assertEmpty($interfaces, 'AuthServiceInterface should not extend other interfaces');
    }

    public function testMockImplementsInterface(): void
    {
        $mock = $this->createMock(AuthServiceInterface::class);
        $mock->method('authenticate')->willReturn(true);
        $mock->method('validateToken')->willReturn(true);
        $mock->method('hasPermission')->willReturn(true);

        $this->assertInstanceOf(AuthServiceInterface::class, $mock);
        $this->assertTrue($mock->authenticate());
        $this->assertTrue($mock->validateToken('token'));
        $this->assertTrue($mock->hasPermission('permission'));
    }

    public function testInterfaceMethodsArePublic(): void
    {
        $reflection = new \ReflectionClass(AuthServiceInterface::class);

        $methods = $reflection->getMethods();

        foreach ($methods as $method) {
            $this->assertTrue($method->isPublic(), "Method {$method->getName()} should be public");
        }
    }

    public function testInterfaceHasNoConstructor(): void
    {
        $reflection = new \ReflectionClass(AuthServiceInterface::class);

        $this->assertFalse($reflection->hasMethod('__construct'), 'Interface should not have constructor');
    }

    public function testInterfaceMethodParameters(): void
    {
        $reflection = new \ReflectionClass(AuthServiceInterface::class);

        // 测试validateToken方法参数
        $method = $reflection->getMethod('validateToken');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters, 'validateToken method should have 1 parameter');
        $this->assertEquals('token', $parameters[0]->getName(), 'validateToken method parameter should be named token');
        $this->assertEquals('string', $parameters[0]->getType(), 'validateToken method parameter should be string');

        // 测试hasPermission方法参数
        $method = $reflection->getMethod('hasPermission');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters, 'hasPermission method should have 1 parameter');
        $this->assertEquals('permission', $parameters[0]->getName(), 'hasPermission method parameter should be named permission');
        $this->assertEquals('string', $parameters[0]->getType(), 'hasPermission method parameter should be string');

        // 测试checkAccess方法参数
        $method = $reflection->getMethod('checkAccess');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters, 'checkAccess method should have 1 parameter');
        $this->assertEquals('resource', $parameters[0]->getName(), 'checkAccess method parameter should be named resource');
        $this->assertEquals('string', $parameters[0]->getType(), 'checkAccess method parameter should be string');
    }
}

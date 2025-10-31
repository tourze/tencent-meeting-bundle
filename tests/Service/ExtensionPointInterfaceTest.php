<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TencentMeetingBundle\Service\ExtensionPointInterface;

/**
 * @internal
 */
#[CoversClass(ExtensionPointInterface::class)]
final class ExtensionPointInterfaceTest extends TestCase
{
    public function testInterfaceCanBeInstantiated(): void
    {
        $mock = $this->createMock(ExtensionPointInterface::class);
        $this->assertInstanceOf(ExtensionPointInterface::class, $mock);
    }

    public function testInterfaceHasRequiredMethods(): void
    {
        $reflection = new \ReflectionClass(ExtensionPointInterface::class);
        $this->assertTrue($reflection->isInterface());

        // 检查接口是否包含所有必需的方法
        $requiredMethods = [
            'execute',
            'supports',
            'getPriority',
            'getName',
            'getDescription',
            'getExtensionPointType',
            'beforeExecute',
            'afterExecute',
            'onError',
        ];

        $interfaceMethods = get_class_methods(ExtensionPointInterface::class);

        foreach ($requiredMethods as $method) {
            $this->assertContains($method, $interfaceMethods, "Method {$method} not found in interface");
        }
    }

    public function testInterfaceMethodSignatures(): void
    {
        $reflection = new \ReflectionClass(ExtensionPointInterface::class);

        // 测试execute方法签名
        $method = $reflection->getMethod('execute');
        $this->assertEquals('execute', $method->getName());
        $this->assertEquals(1, $method->getNumberOfParameters());
        $this->assertEquals('mixed', $method->getReturnType());

        // 测试supports方法签名
        $method = $reflection->getMethod('supports');
        $this->assertEquals('supports', $method->getName());
        $this->assertEquals(1, $method->getNumberOfParameters());
        $this->assertEquals('bool', $method->getReturnType());

        // 测试getPriority方法签名
        $method = $reflection->getMethod('getPriority');
        $this->assertEquals('getPriority', $method->getName());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('int', $method->getReturnType());

        // 测试getName方法签名
        $method = $reflection->getMethod('getName');
        $this->assertEquals('getName', $method->getName());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('string', $method->getReturnType());

        // 测试getDescription方法签名
        $method = $reflection->getMethod('getDescription');
        $this->assertEquals('getDescription', $method->getName());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('string', $method->getReturnType());
    }

    public function testInterfaceExtendsNothing(): void
    {
        $reflection = new \ReflectionClass(ExtensionPointInterface::class);
        $interfaces = $reflection->getInterfaceNames();

        // ExtensionPointInterface不应该继承其他接口
        $this->assertEmpty($interfaces, 'ExtensionPointInterface should not extend other interfaces');
    }

    public function testMockImplementsInterface(): void
    {
        $mock = $this->createMock(ExtensionPointInterface::class);
        $mockContext = $this->createMock(\stdClass::class);
        $mock->method('execute')->willReturn('result');
        $mock->method('supports')->willReturn(true);
        $mock->method('getPriority')->willReturn(100);
        $mock->method('getName')->willReturn('test-extension');

        $this->assertInstanceOf(ExtensionPointInterface::class, $mock);
        $this->assertEquals('result', $mock->execute($mockContext));
        $this->assertTrue($mock->supports($mockContext));
        $this->assertEquals(100, $mock->getPriority());
        $this->assertEquals('test-extension', $mock->getName());
    }

    public function testInterfaceMethodsArePublic(): void
    {
        $reflection = new \ReflectionClass(ExtensionPointInterface::class);

        $methods = $reflection->getMethods();

        foreach ($methods as $method) {
            $this->assertTrue($method->isPublic(), "Method {$method->getName()} should be public");
        }
    }

    public function testInterfaceHasNoConstructor(): void
    {
        $reflection = new \ReflectionClass(ExtensionPointInterface::class);

        $this->assertFalse($reflection->hasMethod('__construct'), 'Interface should not have constructor');
    }

    public function testInterfaceMethodParameters(): void
    {
        $reflection = new \ReflectionClass(ExtensionPointInterface::class);

        // 测试execute方法参数
        $method = $reflection->getMethod('execute');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters, 'execute method should have 1 parameter');
        $this->assertEquals('context', $parameters[0]->getName(), 'execute method parameter should be named context');

        // 测试supports方法参数
        $method = $reflection->getMethod('supports');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters, 'supports method should have 1 parameter');
        $this->assertEquals('context', $parameters[0]->getName(), 'supports method parameter should be named context');

        // 测试getExtensionPointType方法签名
        $method = $reflection->getMethod('getExtensionPointType');
        $this->assertEquals('getExtensionPointType', $method->getName());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('string', $method->getReturnType());

        // 测试beforeExecute方法签名
        $method = $reflection->getMethod('beforeExecute');
        $this->assertEquals('beforeExecute', $method->getName());
        $this->assertEquals(1, $method->getNumberOfParameters());
        $this->assertEquals('void', $method->getReturnType());

        // 测试afterExecute方法签名
        $method = $reflection->getMethod('afterExecute');
        $this->assertEquals('afterExecute', $method->getName());
        $this->assertEquals(2, $method->getNumberOfParameters());
        $this->assertEquals('void', $method->getReturnType());

        // 测试onError方法签名
        $method = $reflection->getMethod('onError');
        $this->assertEquals('onError', $method->getName());
        $this->assertEquals(2, $method->getNumberOfParameters());
        $this->assertEquals('void', $method->getReturnType());
    }
}

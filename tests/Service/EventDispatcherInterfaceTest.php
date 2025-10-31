<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TencentMeetingBundle\Service\EventDispatcherInterface;

/**
 * @internal
 */
#[CoversClass(EventDispatcherInterface::class)]
final class EventDispatcherInterfaceTest extends TestCase
{
    public function testInterfaceCanBeInstantiated(): void
    {
        $mock = $this->createMock(EventDispatcherInterface::class);
        $this->assertInstanceOf(EventDispatcherInterface::class, $mock);
    }

    public function testInterfaceHasRequiredMethods(): void
    {
        $reflection = new \ReflectionClass(EventDispatcherInterface::class);
        $this->assertTrue($reflection->isInterface());

        // 检查接口是否包含所有必需的方法
        $requiredMethods = [
            'dispatch',
            'addListener',
            'removeListener',
            'getListeners',
            'hasListeners',
            'validateEvent',
            'validateSignature',
            'processEvent',
        ];

        $interfaceMethods = get_class_methods(EventDispatcherInterface::class);

        foreach ($requiredMethods as $method) {
            $this->assertContains($method, $interfaceMethods, "Method {$method} not found in interface");
        }
    }

    public function testInterfaceMethodSignatures(): void
    {
        $reflection = new \ReflectionClass(EventDispatcherInterface::class);

        // 测试dispatch方法签名
        $method = $reflection->getMethod('dispatch');
        $this->assertEquals('dispatch', $method->getName());
        $this->assertEquals(1, $method->getNumberOfParameters());
        $this->assertEquals('bool', $method->getReturnType());

        // 测试addListener方法签名
        $method = $reflection->getMethod('addListener');
        $this->assertEquals('addListener', $method->getName());
        $this->assertEquals(2, $method->getNumberOfParameters());
        $this->assertEquals('void', $method->getReturnType());

        // 测试validateEvent方法签名
        $method = $reflection->getMethod('validateEvent');
        $this->assertEquals('validateEvent', $method->getName());
        $this->assertEquals(1, $method->getNumberOfParameters());
        $this->assertEquals('bool', $method->getReturnType());
    }

    public function testInterfaceExtendsNothing(): void
    {
        $reflection = new \ReflectionClass(EventDispatcherInterface::class);
        $interfaces = $reflection->getInterfaceNames();

        // EventDispatcherInterface不应该继承其他接口
        $this->assertEmpty($interfaces, 'EventDispatcherInterface should not extend other interfaces');
    }

    public function testMockImplementsInterface(): void
    {
        $mock = $this->createMock(EventDispatcherInterface::class);
        $mockEvent = $this->createMock(\stdClass::class);
        $mock->method('dispatch')->willReturn(true);
        $mock->method('validateEvent')->willReturn(true);
        $mock->method('hasListeners')->willReturn(true);

        $this->assertInstanceOf(EventDispatcherInterface::class, $mock);
        $this->assertTrue($mock->dispatch($mockEvent));
        $this->assertTrue($mock->validateEvent($mockEvent));
        $this->assertTrue($mock->hasListeners('test.event'));
    }

    public function testInterfaceMethodsArePublic(): void
    {
        $reflection = new \ReflectionClass(EventDispatcherInterface::class);

        $methods = $reflection->getMethods();

        foreach ($methods as $method) {
            $this->assertTrue($method->isPublic(), "Method {$method->getName()} should be public");
        }
    }

    public function testInterfaceHasNoConstructor(): void
    {
        $reflection = new \ReflectionClass(EventDispatcherInterface::class);

        $this->assertFalse($reflection->hasMethod('__construct'), 'Interface should not have constructor');
    }

    public function testInterfaceMethodParameters(): void
    {
        $reflection = new \ReflectionClass(EventDispatcherInterface::class);

        // 测试dispatch方法参数
        $method = $reflection->getMethod('dispatch');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters, 'dispatch method should have 1 parameter');
        $this->assertEquals('event', $parameters[0]->getName(), 'dispatch method parameter should be named event');
        $this->assertEquals('object', $parameters[0]->getType(), 'dispatch method parameter should be object');

        // 测试addListener方法参数
        $method = $reflection->getMethod('addListener');
        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters, 'addListener method should have 2 parameters');
        $this->assertEquals('eventName', $parameters[0]->getName(), 'addListener method first parameter should be named eventName');
        $this->assertEquals('listener', $parameters[1]->getName(), 'addListener method second parameter should be named listener');
    }
}

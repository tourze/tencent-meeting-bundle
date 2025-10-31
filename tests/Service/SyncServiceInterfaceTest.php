<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TencentMeetingBundle\Service\SyncServiceInterface;

/**
 * @internal
 */
#[CoversClass(SyncServiceInterface::class)]
final class SyncServiceInterfaceTest extends TestCase
{
    public function testInterfaceCanBeInstantiated(): void
    {
        $mock = $this->createMock(SyncServiceInterface::class);
        $this->assertInstanceOf(SyncServiceInterface::class, $mock);
    }

    public function testInterfaceHasRequiredMethods(): void
    {
        $reflection = new \ReflectionClass(SyncServiceInterface::class);
        $this->assertTrue($reflection->isInterface());

        // 检查接口是否包含所有必需的方法
        $requiredMethods = [
            'syncMeetings',
            'syncUsers',
            'syncRooms',
            'syncRecordings',
            'syncWebhookEvents',
            'syncAll',
            'getSyncStatus',
            'getSyncStats',
        ];

        $interfaceMethods = get_class_methods(SyncServiceInterface::class);

        foreach ($requiredMethods as $method) {
            $this->assertContains($method, $interfaceMethods, "Method {$method} not found in interface");
        }
    }

    public function testInterfaceMethodSignatures(): void
    {
        $reflection = new \ReflectionClass(SyncServiceInterface::class);

        // 测试syncMeetings方法签名
        $method = $reflection->getMethod('syncMeetings');
        $this->assertEquals('syncMeetings', $method->getName());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('array', $method->getReturnType());

        // 测试syncUsers方法签名
        $method = $reflection->getMethod('syncUsers');
        $this->assertEquals('syncUsers', $method->getName());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('array', $method->getReturnType());

        // 测试getSyncStatus方法签名
        $method = $reflection->getMethod('getSyncStatus');
        $this->assertEquals('getSyncStatus', $method->getName());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('array', $method->getReturnType());

        // 测试getSyncStats方法签名
        $method = $reflection->getMethod('getSyncStats');
        $this->assertEquals('getSyncStats', $method->getName());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('array', $method->getReturnType());
    }

    public function testInterfaceExtendsNothing(): void
    {
        $reflection = new \ReflectionClass(SyncServiceInterface::class);
        $interfaces = $reflection->getInterfaceNames();

        // SyncServiceInterface不应该继承其他接口
        $this->assertEmpty($interfaces, 'SyncServiceInterface should not extend other interfaces');
    }

    public function testMockImplementsInterface(): void
    {
        $mock = $this->createMock(SyncServiceInterface::class);
        $mock->method('getSyncStatus')->willReturn(['status' => 'idle']);
        $mock->method('getSyncStats')->willReturn(['success' => true]);

        $this->assertInstanceOf(SyncServiceInterface::class, $mock);
        $this->assertEquals(['status' => 'idle'], $mock->getSyncStatus());
        $this->assertEquals(['success' => true], $mock->getSyncStats());
    }

    public function testInterfaceMethodsArePublic(): void
    {
        $reflection = new \ReflectionClass(SyncServiceInterface::class);

        $methods = $reflection->getMethods();

        foreach ($methods as $method) {
            $this->assertTrue($method->isPublic(), "Method {$method->getName()} should be public");
        }
    }

    public function testInterfaceHasNoConstructor(): void
    {
        $reflection = new \ReflectionClass(SyncServiceInterface::class);

        $this->assertFalse($reflection->hasMethod('__construct'), 'Interface should not have constructor');
    }

    public function testInterfaceReturnTypeAnnotations(): void
    {
        $reflection = new \ReflectionClass(SyncServiceInterface::class);

        // 检查方法的返回类型注释
        $method = $reflection->getMethod('syncMeetings');
        $docComment = $method->getDocComment();
        $this->assertNotFalse($docComment, 'syncMeetings method should have doc comment');
        $this->assertStringContainsString('@return array', $docComment, 'syncMeetings method should have return type annotation');

        $method = $reflection->getMethod('getSyncStatus');
        $docComment = $method->getDocComment();
        $this->assertNotFalse($docComment, 'getSyncStatus method should have doc comment');
        $this->assertStringContainsString('@return array', $docComment, 'getSyncStatus method should have return type annotation');
    }
}

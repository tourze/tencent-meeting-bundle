<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TencentMeetingBundle\Service\ConfigServiceInterface;

/**
 * @internal
 */
#[CoversClass(ConfigServiceInterface::class)]
final class ConfigServiceInterfaceTest extends TestCase
{
    public function testInterfaceCanBeInstantiated(): void
    {
        $mock = $this->createMock(ConfigServiceInterface::class);
        $this->assertInstanceOf(ConfigServiceInterface::class, $mock);
    }

    public function testInterfaceHasRequiredMethods(): void
    {
        $reflection = new \ReflectionClass(ConfigServiceInterface::class);
        $this->assertTrue($reflection->isInterface());

        // 检查接口是否包含所有必需的方法
        $requiredMethods = [
            'getApiUrl',
            'getTimeout',
            'getRetryTimes',
            'getLogLevel',
            'isDebugEnabled',
            'getCacheTtl',
            'getWebhookSecret',
            'getCacheDriver',
            'getRedisHost',
            'getRedisPort',
            'getRedisPassword',
            'getAuthToken',
            'getProxyHost',
            'getProxyPort',
            'getVerifySsl',
            'getAllConfig',
        ];

        $interfaceMethods = get_class_methods(ConfigServiceInterface::class);

        foreach ($requiredMethods as $method) {
            $this->assertContains($method, $interfaceMethods, "Method {$method} not found in interface");
        }
    }

    public function testInterfaceMethodSignatures(): void
    {
        $reflection = new \ReflectionClass(ConfigServiceInterface::class);

        // 测试getApiUrl方法签名
        $method = $reflection->getMethod('getApiUrl');
        $this->assertEquals('getApiUrl', $method->getName());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('string', $method->getReturnType());

        // 测试getTimeout方法签名
        $method = $reflection->getMethod('getTimeout');
        $this->assertEquals('getTimeout', $method->getName());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('int', $method->getReturnType());

        // 测试getAllConfig方法签名
        $method = $reflection->getMethod('getAllConfig');
        $this->assertEquals('getAllConfig', $method->getName());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('array', $method->getReturnType());
    }

    public function testInterfaceExtendsNothing(): void
    {
        $reflection = new \ReflectionClass(ConfigServiceInterface::class);
        $interfaces = $reflection->getInterfaceNames();

        // ConfigServiceInterface不应该继承其他接口
        $this->assertEmpty($interfaces, 'ConfigServiceInterface should not extend other interfaces');
    }

    public function testMockImplementsInterface(): void
    {
        $mock = $this->createMock(ConfigServiceInterface::class);

        // 配置Mock的期望行为
        $mock->method('getApiUrl')->willReturn('https://api.example.com');
        $mock->method('getTimeout')->willReturn(30);
        $mock->method('getAllConfig')->willReturn(['api_url' => 'https://api.example.com', 'timeout' => 30]);

        $this->assertInstanceOf(ConfigServiceInterface::class, $mock);
        $this->assertEquals('https://api.example.com', $mock->getApiUrl());
        $this->assertEquals(30, $mock->getTimeout());
        $mock->getAllConfig(); // 调用方法验证其可用性，返回类型已由方法签名保证
    }
}

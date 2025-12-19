<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TencentMeetingBundle\Service\HttpClientService;

/**
 * HttpClientService 方法签名和基本功能测试
 *
 * @internal
 */
#[CoversClass(HttpClientService::class)]
#[RunTestsInSeparateProcesses]
final class HttpClientServiceMethodTest extends AbstractIntegrationTestCase
{
    private HttpClientService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(HttpClientService::class);
    }

    public function testDeleteMethodExists(): void
    {
        // 检查方法签名
        $reflection = new \ReflectionMethod($this->service, 'delete');
        $this->assertEquals('delete', $reflection->getName());
        $this->assertTrue($reflection->isPublic());

        $parameters = $reflection->getParameters();
        $this->assertCount(3, $parameters);
        $this->assertEquals('url', $parameters[0]->getName());
        $this->assertEquals('headers', $parameters[1]->getName());
        $this->assertEquals('timeout', $parameters[2]->getName());
    }

    public function testPatchMethodExists(): void
    {
        $reflection = new \ReflectionMethod($this->service, 'patch');
        $this->assertEquals('patch', $reflection->getName());
        $this->assertTrue($reflection->isPublic());

        $parameters = $reflection->getParameters();
        $this->assertCount(4, $parameters);
        $this->assertEquals('url', $parameters[0]->getName());
        $this->assertEquals('data', $parameters[1]->getName());
        $this->assertEquals('headers', $parameters[2]->getName());
        $this->assertEquals('timeout', $parameters[3]->getName());
    }

    public function testPostMethodExists(): void
    {
        $reflection = new \ReflectionMethod($this->service, 'post');
        $this->assertEquals('post', $reflection->getName());
        $this->assertTrue($reflection->isPublic());

        $parameters = $reflection->getParameters();
        $this->assertCount(4, $parameters);
        $this->assertEquals('url', $parameters[0]->getName());
        $this->assertEquals('data', $parameters[1]->getName());
        $this->assertEquals('headers', $parameters[2]->getName());
        $this->assertEquals('timeout', $parameters[3]->getName());
    }

    public function testPutMethodExists(): void
    {
        $reflection = new \ReflectionMethod($this->service, 'put');
        $this->assertEquals('put', $reflection->getName());
        $this->assertTrue($reflection->isPublic());

        $parameters = $reflection->getParameters();
        $this->assertCount(4, $parameters);
        $this->assertEquals('url', $parameters[0]->getName());
        $this->assertEquals('data', $parameters[1]->getName());
        $this->assertEquals('headers', $parameters[2]->getName());
        $this->assertEquals('timeout', $parameters[3]->getName());
    }

    public function testRequestMethodExists(): void
    {
        $reflection = new \ReflectionMethod($this->service, 'request');
        $this->assertEquals('request', $reflection->getName());
        $this->assertTrue($reflection->isPublic());

        $parameters = $reflection->getParameters();
        $this->assertCount(5, $parameters);
        $this->assertEquals('method', $parameters[0]->getName());
        $this->assertEquals('url', $parameters[1]->getName());
        $this->assertEquals('data', $parameters[2]->getName());
        $this->assertEquals('headers', $parameters[3]->getName());
        $this->assertEquals('timeout', $parameters[4]->getName());
    }

    public function testRequestAsyncMethodExists(): void
    {
        $reflection = new \ReflectionMethod($this->service, 'requestAsync');
        $this->assertEquals('requestAsync', $reflection->getName());
        $this->assertTrue($reflection->isPublic());

        $parameters = $reflection->getParameters();
        $this->assertCount(5, $parameters);
        $this->assertEquals('method', $parameters[0]->getName());
        $this->assertEquals('url', $parameters[1]->getName());
        $this->assertEquals('data', $parameters[2]->getName());
        $this->assertEquals('headers', $parameters[3]->getName());
        $this->assertEquals('timeout', $parameters[4]->getName());
    }

    public function testRequestMultipleMethodExists(): void
    {
        $reflection = new \ReflectionMethod($this->service, 'requestMultiple');
        $this->assertEquals('requestMultiple', $reflection->getName());
        $this->assertTrue($reflection->isPublic());

        $parameters = $reflection->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('requests', $parameters[0]->getName());
    }

    public function testStreamMethodExists(): void
    {
        $reflection = new \ReflectionMethod($this->service, 'stream');
        $this->assertEquals('stream', $reflection->getName());
        $this->assertTrue($reflection->isPublic());

        $parameters = $reflection->getParameters();
        $this->assertCount(5, $parameters);
        $this->assertEquals('method', $parameters[0]->getName());
        $this->assertEquals('url', $parameters[1]->getName());
        $this->assertEquals('data', $parameters[2]->getName());
        $this->assertEquals('headers', $parameters[3]->getName());
        $this->assertEquals('timeout', $parameters[4]->getName());
    }

    public function testCheckAvailabilityMethodExists(): void
    {
        $reflection = new \ReflectionMethod($this->service, 'checkAvailability');
        $this->assertEquals('checkAvailability', $reflection->getName());
        $this->assertTrue($reflection->isPublic());

        $parameters = $reflection->getParameters();
        $this->assertCount(0, $parameters);
    }

    public function testMethodReturnTypes(): void
    {
        $deleteReflection = new \ReflectionMethod($this->service, 'delete');
        $returnType = $deleteReflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('array', $returnType->getName());

        $postReflection = new \ReflectionMethod($this->service, 'post');
        $returnType = $postReflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('array', $returnType->getName());

        $putReflection = new \ReflectionMethod($this->service, 'put');
        $returnType = $putReflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('array', $returnType->getName());

        $patchReflection = new \ReflectionMethod($this->service, 'patch');
        $returnType = $patchReflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('array', $returnType->getName());

        $requestReflection = new \ReflectionMethod($this->service, 'request');
        $returnType = $requestReflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('array', $returnType->getName());

        $checkAvailabilityReflection = new \ReflectionMethod($this->service, 'checkAvailability');
        $returnType = $checkAvailabilityReflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('bool', $returnType->getName());
    }

    public function testParameterDefaults(): void
    {
        // 测试 delete 方法的默认参数
        $deleteReflection = new \ReflectionMethod($this->service, 'delete');
        $parameters = $deleteReflection->getParameters();
        $this->assertTrue($parameters[1]->isDefaultValueAvailable()); // headers 有默认值
        $this->assertEquals([], $parameters[1]->getDefaultValue());
        $this->assertTrue($parameters[2]->isDefaultValueAvailable()); // timeout 有默认值
        $this->assertNull($parameters[2]->getDefaultValue());

        // 测试 post 方法的默认参数
        $postReflection = new \ReflectionMethod($this->service, 'post');
        $parameters = $postReflection->getParameters();
        $this->assertTrue($parameters[1]->isDefaultValueAvailable()); // data 有默认值
        $this->assertEquals([], $parameters[1]->getDefaultValue());
        $this->assertTrue($parameters[2]->isDefaultValueAvailable()); // headers 有默认值
        $this->assertEquals([], $parameters[2]->getDefaultValue());
        $this->assertTrue($parameters[3]->isDefaultValueAvailable()); // timeout 有默认值
        $this->assertNull($parameters[3]->getDefaultValue());
    }

    public function testRequestMultipleWithEmptyArray(): void
    {
        $result = $this->service->requestMultiple([]);

        $this->assertEmpty($result);
    }

    public function testServiceConstruction(): void
    {
        $this->assertInstanceOf(HttpClientService::class, $this->service);
    }

    public function testGetClientInfoMethod(): void
    {
        $info = $this->service->getClientInfo();
        $this->assertArrayHasKey('version', $info);
        $this->assertArrayHasKey('base_uri', $info);
        $this->assertArrayHasKey('timeout', $info);
        $this->assertArrayHasKey('retry_times', $info);
    }

    public function testGetStatsMethod(): void
    {
        $stats = $this->service->getStats();
        $this->assertArrayHasKey('total_requests', $stats);
        $this->assertArrayHasKey('successful_requests', $stats);
        $this->assertArrayHasKey('failed_requests', $stats);
        $this->assertArrayHasKey('average_response_time', $stats);
        $this->assertArrayHasKey('cache_hits', $stats);
    }

    public function testResetMethod(): void
    {
        $this->expectNotToPerformAssertions();

        // reset 方法应该不抛出异常
        $this->service->reset();
    }
}

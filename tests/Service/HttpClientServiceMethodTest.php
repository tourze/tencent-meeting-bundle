<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\TencentMeetingBundle\Service\ConfigServiceInterface;
use Tourze\TencentMeetingBundle\Service\HttpClientService;

/**
 * HttpClientService 方法签名和基本功能测试
 *
 * @internal
 */
#[CoversClass(HttpClientService::class)]
final class HttpClientServiceMethodTest extends TestCase
{
    private ConfigServiceInterface $configService;

    private LoggerInterface $loggerService;

    protected function setUp(): void
    {
        // 使用匿名类替代 Mock
        $this->configService = new class implements ConfigServiceInterface {
            public function getApiUrl(): string
            {
                return 'https://api.meeting.qq.com';
            }

            public function getTimeout(): int
            {
                return 30;
            }

            public function getRetryTimes(): int
            {
                return 0;
            }

            public function getAuthToken(): ?string
            {
                return null;
            }

            public function getLogLevel(): string
            {
                return 'info';
            }

            public function isDebugEnabled(): bool
            {
                return false;
            }

            public function getCacheTtl(): int
            {
                return 3600;
            }

            public function getWebhookSecret(): ?string
            {
                return null;
            }

            public function getCacheDriver(): string
            {
                return 'array';
            }

            public function getRedisHost(): ?string
            {
                return null;
            }

            public function getRedisPort(): ?int
            {
                return null;
            }

            public function getRedisPassword(): ?string
            {
                return null;
            }

            public function getSecretKey(): ?string
            {
                return null;
            }

            public function getProxyHost(): ?string
            {
                return null;
            }

            public function getProxyPort(): ?int
            {
                return null;
            }

            public function getVerifySsl(): bool
            {
                return true;
            }

            public function getAllConfig(): array
            {
                return [];
            }
        };

        $this->loggerService = $this->createMock(LoggerInterface::class);
    }

    public function testDeleteMethodExists(): void
    {
        $service = new HttpClientService($this->configService, $this->loggerService);

        // 检查方法签名
        $reflection = new \ReflectionMethod($service, 'delete');
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
        $service = new HttpClientService($this->configService, $this->loggerService);

        $reflection = new \ReflectionMethod($service, 'patch');
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
        $service = new HttpClientService($this->configService, $this->loggerService);

        $reflection = new \ReflectionMethod($service, 'post');
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
        $service = new HttpClientService($this->configService, $this->loggerService);

        $reflection = new \ReflectionMethod($service, 'put');
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
        $service = new HttpClientService($this->configService, $this->loggerService);

        $reflection = new \ReflectionMethod($service, 'request');
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
        $service = new HttpClientService($this->configService, $this->loggerService);

        $reflection = new \ReflectionMethod($service, 'requestAsync');
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
        $service = new HttpClientService($this->configService, $this->loggerService);

        $reflection = new \ReflectionMethod($service, 'requestMultiple');
        $this->assertEquals('requestMultiple', $reflection->getName());
        $this->assertTrue($reflection->isPublic());

        $parameters = $reflection->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('requests', $parameters[0]->getName());
    }

    public function testStreamMethodExists(): void
    {
        $service = new HttpClientService($this->configService, $this->loggerService);

        $reflection = new \ReflectionMethod($service, 'stream');
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
        $service = new HttpClientService($this->configService, $this->loggerService);

        $reflection = new \ReflectionMethod($service, 'checkAvailability');
        $this->assertEquals('checkAvailability', $reflection->getName());
        $this->assertTrue($reflection->isPublic());

        $parameters = $reflection->getParameters();
        $this->assertCount(0, $parameters);
    }

    public function testMethodReturnTypes(): void
    {
        $service = new HttpClientService($this->configService, $this->loggerService);

        $deleteReflection = new \ReflectionMethod($service, 'delete');
        $returnType = $deleteReflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('array', $returnType->getName());

        $postReflection = new \ReflectionMethod($service, 'post');
        $returnType = $postReflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('array', $returnType->getName());

        $putReflection = new \ReflectionMethod($service, 'put');
        $returnType = $putReflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('array', $returnType->getName());

        $patchReflection = new \ReflectionMethod($service, 'patch');
        $returnType = $patchReflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('array', $returnType->getName());

        $requestReflection = new \ReflectionMethod($service, 'request');
        $returnType = $requestReflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('array', $returnType->getName());

        $checkAvailabilityReflection = new \ReflectionMethod($service, 'checkAvailability');
        $returnType = $checkAvailabilityReflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('bool', $returnType->getName());
    }

    public function testParameterDefaults(): void
    {
        $service = new HttpClientService($this->configService, $this->loggerService);

        // 测试 delete 方法的默认参数
        $deleteReflection = new \ReflectionMethod($service, 'delete');
        $parameters = $deleteReflection->getParameters();
        $this->assertTrue($parameters[1]->isDefaultValueAvailable()); // headers 有默认值
        $this->assertEquals([], $parameters[1]->getDefaultValue());
        $this->assertTrue($parameters[2]->isDefaultValueAvailable()); // timeout 有默认值
        $this->assertNull($parameters[2]->getDefaultValue());

        // 测试 post 方法的默认参数
        $postReflection = new \ReflectionMethod($service, 'post');
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
        $service = new HttpClientService($this->configService, $this->loggerService);

        $result = $service->requestMultiple([]);

        $this->assertEmpty($result);
    }

    public function testServiceConstruction(): void
    {
        $service = new HttpClientService($this->configService, $this->loggerService);

        $this->assertInstanceOf(HttpClientService::class, $service);
    }

    public function testGetClientInfoMethod(): void
    {
        $service = new HttpClientService($this->configService, $this->loggerService);

        $info = $service->getClientInfo();
        $this->assertArrayHasKey('version', $info);
        $this->assertArrayHasKey('base_uri', $info);
        $this->assertArrayHasKey('timeout', $info);
        $this->assertArrayHasKey('retry_times', $info);
    }

    public function testGetStatsMethod(): void
    {
        $service = new HttpClientService($this->configService, $this->loggerService);

        $stats = $service->getStats();
        $this->assertArrayHasKey('total_requests', $stats);
        $this->assertArrayHasKey('successful_requests', $stats);
        $this->assertArrayHasKey('failed_requests', $stats);
        $this->assertArrayHasKey('average_response_time', $stats);
        $this->assertArrayHasKey('cache_hits', $stats);
    }

    public function testResetMethod(): void
    {
        $this->expectNotToPerformAssertions();

        $service = new HttpClientService($this->configService, $this->loggerService);

        // reset 方法应该不抛出异常
        $service->reset();
    }
}

<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\TencentMeetingBundle\Exception\ApiException;
use Tourze\TencentMeetingBundle\Exception\NetworkException;
use Tourze\TencentMeetingBundle\Service\BaseClient;
use Tourze\TencentMeetingBundle\Service\ConfigService;
use Tourze\TencentMeetingBundle\Service\HttpClientService;

/**
 * @internal
 */
#[CoversClass(BaseClient::class)]
final class BaseClientTest extends TestCase
{
    private TestableBaseClient $baseClient;

    private ConfigService&MockObject $configService;

    private HttpClientService&MockObject $httpClientService;

    private LoggerInterface&MockObject $loggerService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configService = $this->createMock(ConfigService::class);
        $this->httpClientService = $this->createMock(HttpClientService::class);
        $this->loggerService = $this->createMock(LoggerInterface::class);

        // 配置默认的ConfigService方法返回值
        $this->configService->method('getApiUrl')->willReturn('https://api.meeting.qq.com');
        $this->configService->method('getTimeout')->willReturn(30);
        $this->configService->method('getRetryTimes')->willReturn(3);

        $this->baseClient = new TestableBaseClient(
            $this->configService,
            $this->httpClientService,
            $this->loggerService
        );
    }

    public function testBaseClientCanBeInstantiated(): void
    {
        $this->assertInstanceOf(BaseClient::class, $this->baseClient);
        $this->assertInstanceOf(TestableBaseClient::class, $this->baseClient);
    }

    public function testBaseClientIsAbstract(): void
    {
        $reflection = new \ReflectionClass(BaseClient::class);
        $this->assertTrue($reflection->isAbstract());
        $this->assertFalse($reflection->isInstantiable());
    }

    public function testTestableBaseClientCanBeInstantiated(): void
    {
        $reflection = new \ReflectionClass(TestableBaseClient::class);
        $this->assertFalse($reflection->isAbstract());
        $this->assertTrue($reflection->isInstantiable());
    }

    public function testBaseClientHasRequiredConstructor(): void
    {
        $reflection = new \ReflectionClass(BaseClient::class);
        $constructor = $reflection->getConstructor();
        // Verify constructor exists and has 3 parameters
        $this->assertInstanceOf(\ReflectionMethod::class, $constructor);
        $this->assertCount(3, $constructor->getParameters());
    }

    public function testBaseClientConstructorParameters(): void
    {
        $reflection = new \ReflectionClass(BaseClient::class);
        $constructor = $reflection->getConstructor();
        // Verify constructor exists
        $this->assertInstanceOf(\ReflectionMethod::class, $constructor);
        $parameters = $constructor->getParameters();

        $this->assertEquals('configService', $parameters[0]->getName());
        $this->assertEquals('httpClientService', $parameters[1]->getName());
        $this->assertEquals('loggerService', $parameters[2]->getName());
    }

    public function testBaseClientHasProtectedProperties(): void
    {
        $reflection = new \ReflectionClass(BaseClient::class);

        $expectedProperties = ['configService', 'httpClientService', 'loggerService'];

        foreach ($expectedProperties as $property) {
            $this->assertTrue($reflection->hasProperty($property), "Property {$property} should exist");
            $prop = $reflection->getProperty($property);
            $this->assertTrue($prop->isPrivate() || $prop->isProtected(), "Property {$property} should be private or protected");
        }
    }

    public function testGetRequestWithValidPath(): void
    {
        $expectedResponse = ['success' => true, 'data' => 'test'];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.meeting.qq.com/test', [], self::anything(), 30)
            ->willReturn($expectedResponse)
        ;

        $result = $this->baseClient->get('/test');

        $this->assertSame($expectedResponse, $result);
    }

    public function testPostRequestWithValidData(): void
    {
        $data = ['name' => 'Test', 'type' => 1];
        $expectedResponse = ['success' => true, 'id' => 123];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.meeting.qq.com/create', $data, self::anything(), 30)
            ->willReturn($expectedResponse)
        ;

        $result = $this->baseClient->post('/create', $data);

        $this->assertSame($expectedResponse, $result);
    }

    public function testPutRequestWithValidData(): void
    {
        $data = ['name' => 'Updated Test'];
        $expectedResponse = ['success' => true, 'updated' => true];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('PUT', 'https://api.meeting.qq.com/update/123', $data, self::anything(), 30)
            ->willReturn($expectedResponse)
        ;

        $result = $this->baseClient->put('/update/123', $data);

        $this->assertSame($expectedResponse, $result);
    }

    public function testDeleteRequestWithValidPath(): void
    {
        $expectedResponse = ['success' => true, 'deleted' => true];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('DELETE', 'https://api.meeting.qq.com/delete/123', [], self::anything(), 30)
            ->willReturn($expectedResponse)
        ;

        $result = $this->baseClient->delete('/delete/123');

        $this->assertSame($expectedResponse, $result);
    }

    public function testPatchRequestWithValidData(): void
    {
        $data = ['status' => 'active'];
        $expectedResponse = ['success' => true, 'patched' => true];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('PATCH', 'https://api.meeting.qq.com/patch/123', $data, self::anything(), 30)
            ->willReturn($expectedResponse)
        ;

        $result = $this->baseClient->patch('/patch/123', $data);

        $this->assertSame($expectedResponse, $result);
    }

    public function testSetAuthenticationWithValidData(): void
    {
        $authentication = ['type' => 'Bearer', 'token' => 'test-token'];
        $expectedResponse = ['success' => true];

        // 设置认证信息
        $this->baseClient->setAuthentication($authentication);

        // 验证认证信息在请求中生效
        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.meeting.qq.com/test',
                [],
                self::callback(function (array $headers): bool {
                    return isset($headers['Authorization']) && 'Bearer test-token' === $headers['Authorization'];
                }),
                30
            )
            ->willReturn($expectedResponse)
        ;

        $result = $this->baseClient->get('/test');
        $this->assertSame($expectedResponse, $result);
    }

    public function testSetAuthTokenWithValidToken(): void
    {
        $token = 'test-auth-token-123';
        $expectedResponse = ['success' => true];

        // 设置认证Token
        $this->baseClient->setAuthToken($token);

        // 验证Token在请求中生效
        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.meeting.qq.com/test',
                [],
                self::callback(function (array $headers): bool {
                    return isset($headers['Authorization']) && 'Bearer test-auth-token-123' === $headers['Authorization'];
                }),
                30
            )
            ->willReturn($expectedResponse)
        ;

        $result = $this->baseClient->get('/test');
        $this->assertSame($expectedResponse, $result);
    }

    public function testSetOAuth2TokenWithValidToken(): void
    {
        $accessToken = 'oauth2-access-token-123';
        $expectedResponse = ['success' => true];

        // 设置OAuth2 Token
        $this->baseClient->setOAuth2Token($accessToken);

        // 验证OAuth2 Token在请求中生效
        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.meeting.qq.com/test',
                [],
                self::callback(function (array $headers): bool {
                    return isset($headers['Authorization']) && 'Bearer oauth2-access-token-123' === $headers['Authorization'];
                }),
                30
            )
            ->willReturn($expectedResponse)
        ;

        $result = $this->baseClient->get('/test');
        $this->assertSame($expectedResponse, $result);
    }

    public function testClearAuthentication(): void
    {
        $expectedResponse = ['success' => true];

        // 先设置认证信息
        $this->baseClient->setAuthToken('test-token');

        // 清除认证信息
        $this->baseClient->clearAuthentication();

        // 验证认证信息已被清除（请求中不包含Authorization头）
        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.meeting.qq.com/test',
                [],
                self::callback(function (array $headers): bool {
                    return !isset($headers['Authorization']);
                }),
                30
            )
            ->willReturn($expectedResponse)
        ;

        $result = $this->baseClient->get('/test');
        $this->assertSame($expectedResponse, $result);
    }

    public function testSetHeadersWithValidHeaders(): void
    {
        $headers = ['Custom-Header' => 'custom-value', 'X-Test' => 'test-value'];
        $expectedResponse = ['success' => true];

        // 设置自定义头
        $this->baseClient->setHeaders($headers);

        // 验证自定义头在请求中生效
        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.meeting.qq.com/test',
                [],
                self::callback(function (array $requestHeaders): bool {
                    return isset($requestHeaders['Custom-Header']) && 'custom-value' === $requestHeaders['Custom-Header']
                        && isset($requestHeaders['X-Test']) && 'test-value' === $requestHeaders['X-Test'];
                }),
                30
            )
            ->willReturn($expectedResponse)
        ;

        $result = $this->baseClient->get('/test');
        $this->assertSame($expectedResponse, $result);
    }

    public function testAddHeaderWithValidData(): void
    {
        $expectedResponse = ['success' => true];

        // 添加自定义头
        $this->baseClient->addHeader('X-Custom', 'custom-value');

        // 验证自定义头在请求中生效
        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.meeting.qq.com/test',
                [],
                self::callback(function (array $headers): bool {
                    return isset($headers['X-Custom']) && 'custom-value' === $headers['X-Custom'];
                }),
                30
            )
            ->willReturn($expectedResponse)
        ;

        $result = $this->baseClient->get('/test');
        $this->assertSame($expectedResponse, $result);
    }

    public function testRemoveHeaderWithValidName(): void
    {
        $expectedResponse = ['success' => true];

        // 先添加一个头
        $this->baseClient->addHeader('X-Remove-Me', 'value');

        // 然后移除它
        $this->baseClient->removeHeader('X-Remove-Me');

        // 验证头已被移除（请求中不包含该头）
        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.meeting.qq.com/test',
                [],
                self::callback(function (array $headers): bool {
                    return !isset($headers['X-Remove-Me']);
                }),
                30
            )
            ->willReturn($expectedResponse)
        ;

        $result = $this->baseClient->get('/test');
        $this->assertSame($expectedResponse, $result);
    }

    public function testSetTimeoutWithValidValue(): void
    {
        $expectedResponse = ['success' => true];

        // 设置超时时间
        $this->baseClient->setTimeout(60);

        // 验证超时时间在请求中生效
        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.meeting.qq.com/test',
                [],
                self::anything(),
                60  // 验证超时时间参数
            )
            ->willReturn($expectedResponse)
        ;

        $result = $this->baseClient->get('/test');
        $this->assertSame($expectedResponse, $result);
    }

    public function testSetRetryTimesWithValidValue(): void
    {
        // 创建独立的mock对象避免冲突
        /** @var ConfigService&MockObject $configService */
        $configService = $this->createMock(ConfigService::class);
        $configService->method('getApiUrl')->willReturn('https://api.meeting.qq.com');
        $configService->method('getTimeout')->willReturn(30);
        $configService->method('getRetryTimes')->willReturn(0);
        /** @var HttpClientService&MockObject $httpClientService */
        $httpClientService = $this->createMock(HttpClientService::class);
        /** @var LoggerInterface&MockObject $loggerService */
        $loggerService = $this->createMock(LoggerInterface::class);

        $baseClient = new TestableBaseClient(
            $configService,
            $httpClientService,
            $loggerService
        );

        // 设置重试次数为2（减少次数避免递归调用问题）
        $baseClient->setRetryTimes(2);

        // 模拟第一次请求失败，第二次请求成功
        $httpClientService
            ->expects($this->exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new \RuntimeException('connection timeout')),
                ['success' => true, 'data' => 'retry_success']
            )
        ;

        // 执行请求，应该在第二次成功
        $result = $baseClient->get('/test');

        // 验证请求成功
        $this->assertSame(['success' => true, 'data' => 'retry_success'], $result);

        // 验证重试统计数据
        $stats = $baseClient->getStats();
        $this->assertSame(1, $stats['retries']);
        $this->assertSame(1, $stats['successful_requests']);
    }

    public function testSetBaseUrlWithValidUrl(): void
    {
        $expectedResponse = ['success' => true];

        // 设置新的基础URL
        $this->baseClient->setBaseUrl('https://new.api.url');

        // 验证新基础URL在请求中生效
        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://new.api.url/test', // 验证URL参数
                [],
                self::anything(),
                30
            )
            ->willReturn($expectedResponse)
        ;

        $result = $this->baseClient->get('/test');
        $this->assertSame($expectedResponse, $result);
    }

    public function testSetBaseUrlWithTrailingSlash(): void
    {
        $expectedResponse = ['success' => true];

        // 设置带尾部斜杠的基础URL
        $this->baseClient->setBaseUrl('https://api.test.com/');

        // 验证尾部斜杠被正确处理（应该被移除）
        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.test.com/test', // 验证尾部斜杠被移除
                [],
                self::anything(),
                30
            )
            ->willReturn($expectedResponse)
        ;

        $result = $this->baseClient->get('/test');
        $this->assertSame($expectedResponse, $result);
    }

    public function testGetStatsWithInitialValues(): void
    {
        $stats = $this->baseClient->getStats();
        $this->assertArrayHasKey('total_requests', $stats);
        $this->assertArrayHasKey('successful_requests', $stats);
        $this->assertArrayHasKey('failed_requests', $stats);
        $this->assertArrayHasKey('total_response_time', $stats);
        $this->assertArrayHasKey('retries', $stats);

        // 初始值应该都是0
        $this->assertSame(0, $stats['total_requests']);
        $this->assertSame(0, $stats['successful_requests']);
        $this->assertSame(0, $stats['failed_requests']);
        $this->assertSame(0, $stats['total_response_time']);
        $this->assertSame(0, $stats['retries']);
    }

    public function testResetStatsMethod(): void
    {
        // 先执行一些操作来修改统计数据
        $this->baseClient->incrementStat('total_requests', 5);
        $this->baseClient->incrementStat('successful_requests', 3);

        // 重置统计数据
        $this->baseClient->resetStats();

        $stats = $this->baseClient->getStats();
        $this->assertSame(0, $stats['total_requests']);
        $this->assertSame(0, $stats['successful_requests']);
    }

    public function testResetClientMethod(): void
    {
        // 修改一些设置
        $this->baseClient->setTimeout(120);
        $this->baseClient->setAuthToken('test-token');
        $this->baseClient->incrementStat('total_requests', 10);

        // 重置客户端
        $this->baseClient->reset();

        // 统计数据应该被重置
        $stats = $this->baseClient->getStats();
        $this->assertSame(0, $stats['total_requests']);
    }

    public function testIncrementStatWithValidKey(): void
    {
        $this->baseClient->incrementStat('total_requests', 3);

        $stats = $this->baseClient->getStats();
        $this->assertSame(3, $stats['total_requests']);
    }

    public function testIncrementStatWithInvalidKey(): void
    {
        // 记录增加前的统计数据
        $statsBefore = $this->baseClient->getStats();

        // 使用无效的键增加统计
        $this->baseClient->incrementStat('invalid_key', 1);

        // 验证统计数据未发生变化（因为键无效）
        $statsAfter = $this->baseClient->getStats();
        $this->assertSame($statsBefore, $statsAfter);
    }

    public function testGetTotalRequestsMethod(): void
    {
        $this->baseClient->incrementStat('total_requests', 5);

        $totalRequests = $this->baseClient->getTotalRequests();
        $this->assertSame(5, $totalRequests);
    }

    public function testGetSuccessRateWithZeroRequests(): void
    {
        $successRate = $this->baseClient->getSuccessRate();
        $this->assertSame(0.0, $successRate);
    }

    public function testGetSuccessRateWithValidRequests(): void
    {
        $this->baseClient->incrementStat('total_requests', 10);
        $this->baseClient->incrementStat('successful_requests', 8);

        $successRate = $this->baseClient->getSuccessRate();
        $this->assertSame(80.0, $successRate);
    }

    public function testGetAverageResponseTimeWithZeroRequests(): void
    {
        $avgResponseTime = $this->baseClient->getAverageResponseTime();
        $this->assertSame(0.0, $avgResponseTime);
    }

    public function testGetAverageResponseTimeWithValidRequests(): void
    {
        $this->baseClient->incrementStat('total_requests', 4);
        $this->baseClient->incrementStat('total_response_time', 2000);

        $avgResponseTime = $this->baseClient->getAverageResponseTime();
        $this->assertSame(500.0, $avgResponseTime);
    }

    public function testRequestStatsUpdateOnSuccessfulRequest(): void
    {
        $expectedResponse = ['success' => true, 'data' => 'test'];

        // 创建独立的mock对象避免冲突
        /** @var ConfigService&MockObject $configService */
        $configService = $this->createMock(ConfigService::class);
        $configService->method('getApiUrl')->willReturn('https://api.meeting.qq.com');
        $configService->method('getTimeout')->willReturn(30);
        $configService->method('getRetryTimes')->willReturn(0); // 禁用重试避免多次调用
        /** @var HttpClientService&MockObject $httpClientService */
        $httpClientService = $this->createMock(HttpClientService::class);
        $httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        /** @var LoggerInterface&MockObject $loggerService */
        $loggerService = $this->createMock(LoggerInterface::class);

        $baseClient = new TestableBaseClient(
            $configService,
            $httpClientService,
            $loggerService
        );

        $baseClient->get('/test');

        $stats = $baseClient->getStats();
        $this->assertSame(1, $stats['total_requests']);
        $this->assertSame(1, $stats['successful_requests']);
        $this->assertSame(0, $stats['failed_requests']);
    }

    public function testRequestStatsUpdateOnFailedRequest(): void
    {
        $expectedResponse = ['success' => false, 'error' => 'test error'];

        // 创建独立的mock对象避免冲突
        /** @var ConfigService&MockObject $configService */
        $configService = $this->createMock(ConfigService::class);
        $configService->method('getApiUrl')->willReturn('https://api.meeting.qq.com');
        $configService->method('getTimeout')->willReturn(30);
        $configService->method('getRetryTimes')->willReturn(0); // 禁用重试避免多次调用
        /** @var HttpClientService&MockObject $httpClientService */
        $httpClientService = $this->createMock(HttpClientService::class);
        $httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        /** @var LoggerInterface&MockObject $loggerService */
        $loggerService = $this->createMock(LoggerInterface::class);

        $baseClient = new TestableBaseClient(
            $configService,
            $httpClientService,
            $loggerService
        );

        $baseClient->get('/test');

        $stats = $baseClient->getStats();
        $this->assertSame(1, $stats['total_requests']);
        $this->assertSame(0, $stats['successful_requests']);
        $this->assertSame(1, $stats['failed_requests']);
    }

    public function testRequestStatsUpdateOnException(): void
    {
        // 创建独立的mock对象避免冲突
        /** @var ConfigService&MockObject $configService */
        $configService = $this->createMock(ConfigService::class);
        $configService->method('getApiUrl')->willReturn('https://api.meeting.qq.com');
        $configService->method('getTimeout')->willReturn(30);
        $configService->method('getRetryTimes')->willReturn(0); // 禁用重试避免多次调用
        /** @var HttpClientService&MockObject $httpClientService */
        $httpClientService = $this->createMock(HttpClientService::class);
        $httpClientService
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new \RuntimeException('Test exception'))
        ;

        /** @var LoggerInterface&MockObject $loggerService */
        $loggerService = $this->createMock(LoggerInterface::class);

        $baseClient = new TestableBaseClient(
            $configService,
            $httpClientService,
            $loggerService
        );

        $this->expectException(ApiException::class);
        $baseClient->get('/test');

        $stats = $baseClient->getStats();
        $this->assertSame(1, $stats['total_requests']);
        $this->assertSame(0, $stats['successful_requests']);
        $this->assertSame(1, $stats['failed_requests']);
    }

    public function testLoggingIsCalledDuringRequest(): void
    {
        $expectedResponse = ['success' => true];

        // 创建独立的mock对象避免冲突
        /** @var ConfigService&MockObject $configService */
        $configService = $this->createMock(ConfigService::class);
        $configService->method('getApiUrl')->willReturn('https://api.meeting.qq.com');
        $configService->method('getTimeout')->willReturn(30);
        $configService->method('getRetryTimes')->willReturn(0); // 禁用重试避免多次调用
        /** @var HttpClientService&MockObject $httpClientService */
        $httpClientService = $this->createMock(HttpClientService::class);
        /** @var LoggerInterface&MockObject $loggerService */
        $loggerService = $this->createMock(LoggerInterface::class);

        $loggerService
            ->expects($this->exactly(2))
            ->method('info')
        ;

        $httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $baseClient = new TestableBaseClient(
            $configService,
            $httpClientService,
            $loggerService
        );

        $baseClient->get('/test');
    }

    public function testErrorHandlingForApiException(): void
    {
        // 创建独立的mock对象避免冲突
        /** @var ConfigService&MockObject $configService */
        $configService = $this->createMock(ConfigService::class);
        $configService->method('getApiUrl')->willReturn('https://api.meeting.qq.com');
        $configService->method('getTimeout')->willReturn(30);
        $configService->method('getRetryTimes')->willReturn(0); // 禁用重试避免多次调用
        /** @var HttpClientService&MockObject $httpClientService */
        $httpClientService = $this->createMock(HttpClientService::class);
        $httpClientService
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new \InvalidArgumentException('Invalid parameter'))
        ;

        /** @var LoggerInterface&MockObject $loggerService */
        $loggerService = $this->createMock(LoggerInterface::class);

        $baseClient = new TestableBaseClient(
            $configService,
            $httpClientService,
            $loggerService
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('无效的请求参数: Invalid parameter');
        $this->expectExceptionCode(400);

        $baseClient->get('/test');
    }

    public function testErrorHandlingForNetworkTimeout(): void
    {
        // 创建独立的mock对象避免冲突
        /** @var ConfigService&MockObject $configService */
        $configService = $this->createMock(ConfigService::class);
        $configService->method('getApiUrl')->willReturn('https://api.meeting.qq.com');
        $configService->method('getTimeout')->willReturn(30);
        $configService->method('getRetryTimes')->willReturn(0); // 禁用重试避免多次调用
        /** @var HttpClientService&MockObject $httpClientService */
        $httpClientService = $this->createMock(HttpClientService::class);
        $httpClientService
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new \RuntimeException('Connection timeout'))
        ;

        /** @var LoggerInterface&MockObject $loggerService */
        $loggerService = $this->createMock(LoggerInterface::class);

        $baseClient = new TestableBaseClient(
            $configService,
            $httpClientService,
            $loggerService
        );

        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('请求超时: Connection timeout');

        $baseClient->get('/test');
    }

    public function testErrorHandlingForConnectionError(): void
    {
        // 创建独立的mock对象避免冲突
        /** @var ConfigService&MockObject $configService */
        $configService = $this->createMock(ConfigService::class);
        $configService->method('getApiUrl')->willReturn('https://api.meeting.qq.com');
        $configService->method('getTimeout')->willReturn(30);
        $configService->method('getRetryTimes')->willReturn(0); // 禁用重试避免多次调用
        /** @var HttpClientService&MockObject $httpClientService */
        $httpClientService = $this->createMock(HttpClientService::class);
        $httpClientService
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new \RuntimeException('connection failed'))
        ;

        /** @var LoggerInterface&MockObject $loggerService */
        $loggerService = $this->createMock(LoggerInterface::class);

        $baseClient = new TestableBaseClient(
            $configService,
            $httpClientService,
            $loggerService
        );

        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('网络连接失败: connection failed');

        $baseClient->get('/test');
    }
}

<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TencentMeetingBundle\Service\ConfigService;

/**
 * @internal
 */
#[CoversClass(ConfigService::class)]
final class ConfigServiceTest extends TestCase
{
    private ConfigService $configService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configService = new ConfigService();
    }

    protected function tearDown(): void
    {
        // 清理环境变量
        $keys = [
            'TENCENT_MEETING_API_URL',
            'TENCENT_MEETING_TIMEOUT',
            'TENCENT_MEETING_RETRY_TIMES',
            'TENCENT_MEETING_LOG_LEVEL',
            'TENCENT_MEETING_DEBUG_ENABLED',
            'TENCENT_MEETING_CACHE_TTL',
            'TENCENT_MEETING_WEBHOOK_SECRET',
            'TENCENT_MEETING_CACHE_DRIVER',
            'TENCENT_MEETING_REDIS_HOST',
            'TENCENT_MEETING_REDIS_PORT',
            'TENCENT_MEETING_REDIS_PASSWORD',
            'TENCENT_MEETING_AUTH_TOKEN',
            'TENCENT_MEETING_SECRET_KEY',
            'TENCENT_MEETING_PROXY_HOST',
            'TENCENT_MEETING_PROXY_PORT',
            'TENCENT_MEETING_VERIFY_SSL',
        ];

        foreach ($keys as $key) {
            unset($_ENV[$key]);
        }

        parent::tearDown();
    }

    public function testConfigServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(ConfigService::class, $this->configService);
    }

    public function testImplementsConfigServiceInterface(): void
    {
        $this->assertInstanceOf('Tourze\TencentMeetingBundle\Service\ConfigServiceInterface', $this->configService);
    }

    public function testGetApiUrlWithDefaultValue(): void
    {
        $apiUrl = $this->configService->getApiUrl();
        $this->assertSame('https://api.meeting.qq.com', $apiUrl);
    }

    public function testGetApiUrlWithEnvironmentValue(): void
    {
        $_ENV['TENCENT_MEETING_API_URL'] = 'https://custom.api.meeting.qq.com';
        $apiUrl = $this->configService->getApiUrl();
        $this->assertSame('https://custom.api.meeting.qq.com', $apiUrl);
    }

    public function testGetTimeoutWithDefaultValue(): void
    {
        $timeout = $this->configService->getTimeout();
        $this->assertSame(30, $timeout);
    }

    public function testGetTimeoutWithEnvironmentValue(): void
    {
        $_ENV['TENCENT_MEETING_TIMEOUT'] = '60';
        $timeout = $this->configService->getTimeout();
        $this->assertSame(60, $timeout);
    }

    public function testGetRetryTimesWithDefaultValue(): void
    {
        $retryTimes = $this->configService->getRetryTimes();
        $this->assertSame(3, $retryTimes);
    }

    public function testGetRetryTimesWithEnvironmentValue(): void
    {
        $_ENV['TENCENT_MEETING_RETRY_TIMES'] = '5';
        $retryTimes = $this->configService->getRetryTimes();
        $this->assertSame(5, $retryTimes);
    }

    public function testGetLogLevelWithDefaultValue(): void
    {
        $logLevel = $this->configService->getLogLevel();
        $this->assertSame('info', $logLevel);
    }

    public function testGetLogLevelWithEnvironmentValue(): void
    {
        $_ENV['TENCENT_MEETING_LOG_LEVEL'] = 'debug';
        $logLevel = $this->configService->getLogLevel();
        $this->assertSame('debug', $logLevel);
    }

    public function testIsDebugEnabledWithDefaultValue(): void
    {
        $debugEnabled = $this->configService->isDebugEnabled();
        $this->assertFalse($debugEnabled);
    }

    public function testIsDebugEnabledWithTrueValue(): void
    {
        $_ENV['TENCENT_MEETING_DEBUG_ENABLED'] = 'true';
        $debugEnabled = $this->configService->isDebugEnabled();
        $this->assertTrue($debugEnabled);
    }

    public function testIsDebugEnabledWithFalseValue(): void
    {
        $_ENV['TENCENT_MEETING_DEBUG_ENABLED'] = 'false';
        $debugEnabled = $this->configService->isDebugEnabled();
        $this->assertFalse($debugEnabled);
    }

    public function testGetCacheTtlWithDefaultValue(): void
    {
        $cacheTtl = $this->configService->getCacheTtl();
        $this->assertSame(3600, $cacheTtl);
    }

    public function testGetCacheTtlWithEnvironmentValue(): void
    {
        $_ENV['TENCENT_MEETING_CACHE_TTL'] = '7200';
        $cacheTtl = $this->configService->getCacheTtl();
        $this->assertSame(7200, $cacheTtl);
    }

    public function testGetWebhookSecretWithDefaultValue(): void
    {
        $webhookSecret = $this->configService->getWebhookSecret();
        $this->assertNull($webhookSecret);
    }

    public function testGetWebhookSecretWithEnvironmentValue(): void
    {
        $_ENV['TENCENT_MEETING_WEBHOOK_SECRET'] = 'secret123';
        $webhookSecret = $this->configService->getWebhookSecret();
        $this->assertSame('secret123', $webhookSecret);
    }

    public function testGetCacheDriverWithDefaultValue(): void
    {
        $cacheDriver = $this->configService->getCacheDriver();
        $this->assertSame('file', $cacheDriver);
    }

    public function testGetCacheDriverWithEnvironmentValue(): void
    {
        $_ENV['TENCENT_MEETING_CACHE_DRIVER'] = 'redis';
        $cacheDriver = $this->configService->getCacheDriver();
        $this->assertSame('redis', $cacheDriver);
    }

    public function testGetRedisHostWithDefaultValue(): void
    {
        $redisHost = $this->configService->getRedisHost();
        $this->assertNull($redisHost);
    }

    public function testGetRedisHostWithEnvironmentValue(): void
    {
        $_ENV['TENCENT_MEETING_REDIS_HOST'] = 'localhost';
        $redisHost = $this->configService->getRedisHost();
        $this->assertSame('localhost', $redisHost);
    }

    public function testGetRedisPortWithDefaultValue(): void
    {
        $redisPort = $this->configService->getRedisPort();
        $this->assertNull($redisPort);
    }

    public function testGetRedisPortWithEnvironmentValue(): void
    {
        $_ENV['TENCENT_MEETING_REDIS_PORT'] = '6379';
        $redisPort = $this->configService->getRedisPort();
        $this->assertSame(6379, $redisPort);
    }

    public function testGetRedisPasswordWithDefaultValue(): void
    {
        $redisPassword = $this->configService->getRedisPassword();
        $this->assertNull($redisPassword);
    }

    public function testGetRedisPasswordWithEnvironmentValue(): void
    {
        $_ENV['TENCENT_MEETING_REDIS_PASSWORD'] = 'password123';
        $redisPassword = $this->configService->getRedisPassword();
        $this->assertSame('password123', $redisPassword);
    }

    public function testGetAuthTokenWithDefaultValue(): void
    {
        $authToken = $this->configService->getAuthToken();
        $this->assertNull($authToken);
    }

    public function testGetAuthTokenWithEnvironmentValue(): void
    {
        $_ENV['TENCENT_MEETING_AUTH_TOKEN'] = 'token123';
        $authToken = $this->configService->getAuthToken();
        $this->assertSame('token123', $authToken);
    }

    public function testGetSecretKeyWithDefaultValue(): void
    {
        $secretKey = $this->configService->getSecretKey();
        $this->assertNull($secretKey);
    }

    public function testGetSecretKeyWithEnvironmentValue(): void
    {
        $_ENV['TENCENT_MEETING_SECRET_KEY'] = 'secretkey123';
        $secretKey = $this->configService->getSecretKey();
        $this->assertSame('secretkey123', $secretKey);
    }

    public function testGetProxyHostWithDefaultValue(): void
    {
        $proxyHost = $this->configService->getProxyHost();
        $this->assertNull($proxyHost);
    }

    public function testGetProxyHostWithEnvironmentValue(): void
    {
        $_ENV['TENCENT_MEETING_PROXY_HOST'] = 'proxy.example.com';
        $proxyHost = $this->configService->getProxyHost();
        $this->assertSame('proxy.example.com', $proxyHost);
    }

    public function testGetProxyPortWithDefaultValue(): void
    {
        $proxyPort = $this->configService->getProxyPort();
        $this->assertNull($proxyPort);
    }

    public function testGetProxyPortWithEnvironmentValue(): void
    {
        $_ENV['TENCENT_MEETING_PROXY_PORT'] = '8080';
        $proxyPort = $this->configService->getProxyPort();
        $this->assertSame(8080, $proxyPort);
    }

    public function testGetVerifySslWithDefaultValue(): void
    {
        $verifySsl = $this->configService->getVerifySsl();
        $this->assertTrue($verifySsl);
    }

    public function testGetVerifySslWithFalseValue(): void
    {
        $_ENV['TENCENT_MEETING_VERIFY_SSL'] = 'false';
        $verifySsl = $this->configService->getVerifySsl();
        $this->assertFalse($verifySsl);
    }

    public function testGetAllConfig(): void
    {
        $_ENV['TENCENT_MEETING_API_URL'] = 'https://test.api.com';
        $_ENV['TENCENT_MEETING_TIMEOUT'] = '45';
        $_ENV['TENCENT_MEETING_DEBUG_ENABLED'] = 'true';

        $config = $this->configService->getAllConfig();
        $this->assertSame('https://test.api.com', $config['api_url']);
        $this->assertSame(45, $config['timeout']);
        $this->assertTrue($config['debug_enabled']);
        $this->assertArrayHasKey('retry_times', $config);
        $this->assertArrayHasKey('log_level', $config);
        $this->assertArrayHasKey('cache_ttl', $config);
    }

    public function testEmptyEnvironmentVariableUsesDefault(): void
    {
        $_ENV['TENCENT_MEETING_API_URL'] = '';
        $apiUrl = $this->configService->getApiUrl();
        $this->assertSame('https://api.meeting.qq.com', $apiUrl);
    }
}

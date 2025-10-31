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
final class EnvironmentConfigTest extends TestCase
{
    public function testEnvironmentVariableReading(): void
    {
        // 这个测试会失败，因为ConfigService还不存在
        $configService = new ConfigService();
        $apiUrl = $configService->getApiUrl();

        $this->assertNotEmpty($apiUrl);
    }

    public function testDefaultValues(): void
    {
        $configService = new ConfigService();

        // 测试默认值
        $timeout = $configService->getTimeout();
        $this->assertEquals(30, $timeout);

        $retryTimes = $configService->getRetryTimes();
        $this->assertEquals(3, $retryTimes);
    }

    public function testCustomEnvironmentVariables(): void
    {
        // 设置临时环境变量
        $_ENV['TENCENT_MEETING_API_URL'] = 'https://custom.api.meeting.qq.com';
        $_ENV['TENCENT_MEETING_TIMEOUT'] = '60';

        $configService = new ConfigService();

        $this->assertEquals('https://custom.api.meeting.qq.com', $configService->getApiUrl());
        $this->assertEquals(60, $configService->getTimeout());

        // 清理环境变量
        unset($_ENV['TENCENT_MEETING_API_URL'], $_ENV['TENCENT_MEETING_TIMEOUT']);
    }

    public function testTypeConversion(): void
    {
        $_ENV['TENCENT_MEETING_TIMEOUT'] = '120';
        $_ENV['TENCENT_MEETING_RETRY_TIMES'] = '5';

        $configService = new ConfigService();

        // Verify type conversion from string to int (return types guarantee int)
        $this->assertEquals(120, $configService->getTimeout());
        $this->assertEquals(5, $configService->getRetryTimes());

        unset($_ENV['TENCENT_MEETING_TIMEOUT'], $_ENV['TENCENT_MEETING_RETRY_TIMES']);
    }
}

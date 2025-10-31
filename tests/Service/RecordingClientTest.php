<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\TencentMeetingBundle\Exception\ApiException;
use Tourze\TencentMeetingBundle\Service\ConfigService;
use Tourze\TencentMeetingBundle\Service\HttpClientService;
use Tourze\TencentMeetingBundle\Service\RecordingClient;

/**
 * @internal
 */
#[CoversClass(RecordingClient::class)]
final class RecordingClientTest extends TestCase
{
    private RecordingClient $recordingClient;

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

        $this->recordingClient = new RecordingClient(
            $this->configService,
            $this->httpClientService,
            $this->loggerService
        );
    }

    public function testRecordingClientCanBeInstantiated(): void
    {
        $this->assertInstanceOf(RecordingClient::class, $this->recordingClient);
    }

    public function testGetRecordingWithValidId(): void
    {
        $recordingId = 'rec123456';
        $expectedResponse = [
            'success' => true,
            'recording_id' => 'rec123456',
            'meeting_id' => 'meeting123',
            'status' => 'completed',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.meeting.qq.com/v1/recordings/rec123456')
            ->willReturn($expectedResponse)
        ;

        $result = $this->recordingClient->getRecording($recordingId);

        $this->assertTrue($result['success']);
        $this->assertSame('rec123456', $result['recording_id']);
    }

    public function testGetMeetingRecordingsWithFilters(): void
    {
        $meetingId = 'meeting123';
        $filters = [
            'page' => 1,
            'page_size' => 10,
            'status' => 'completed',
        ];

        $expectedResponse = [
            'success' => true,
            'recordings' => [],
            'pagination' => [],
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->recordingClient->getMeetingRecordings($meetingId, $filters);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('recordings', $result);
    }

    public function testStartRecordingWithValidMeetingId(): void
    {
        $meetingId = 'meeting123';
        $expectedResponse = [
            'success' => true,
            'recording_id' => 'rec123456',
            'status' => 'recording',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.meeting.qq.com/v1/meetings/meeting123/recordings/start', [])
            ->willReturn($expectedResponse)
        ;

        $result = $this->recordingClient->startRecording($meetingId);

        $this->assertTrue($result['success']);
        $this->assertSame('rec123456', $result['recording_id']);
    }

    public function testStopRecordingWithValidMeetingId(): void
    {
        $meetingId = 'meeting123';
        $expectedResponse = [
            'success' => true,
            'recording_id' => 'rec123456',
            'status' => 'stopped',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.meeting.qq.com/v1/meetings/meeting123/recordings/stop', [])
            ->willReturn($expectedResponse)
        ;

        $result = $this->recordingClient->stopRecording($meetingId);

        $this->assertTrue($result['success']);
        $this->assertSame('stopped', $result['status']);
    }

    public function testDeleteRecordingWithValidId(): void
    {
        $recordingId = 'rec123456';
        $expectedResponse = [
            'success' => true,
            'recording_id' => 'rec123456',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('DELETE', 'https://api.meeting.qq.com/v1/recordings/rec123456')
            ->willReturn($expectedResponse)
        ;

        $result = $this->recordingClient->deleteRecording($recordingId);

        $this->assertTrue($result['success']);
    }

    public function testUpdateRecordingSettingsWithValidData(): void
    {
        $meetingId = 'meeting123';
        $settings = [
            'auto_start' => true,
            'cloud_storage' => true,
            'watermark' => false,
        ];

        $expectedResponse = [
            'success' => true,
            'meeting_id' => 'meeting123',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->recordingClient->updateRecordingSettings($meetingId, $settings);

        $this->assertTrue($result['success']);
    }

    public function testUpdateRecordingSettingsWithInvalidSetting(): void
    {
        $meetingId = 'meeting123';
        $settings = [
            'invalid_setting' => true,
        ];

        $this->loggerService
            ->expects($this->once())
            ->method('error')
        ;

        $result = $this->recordingClient->updateRecordingSettings($meetingId, $settings);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertNotEmpty($result['error']);
        $this->assertIsString($result['error']);
        $this->assertStringContainsString('无效的录制设置: invalid_setting', $result['error']);
        $this->assertSame('updateRecordingSettings', $result['operation']);
    }

    public function testUpdateRecordingSettingsWithNonBooleanValue(): void
    {
        $meetingId = 'meeting123';
        $settings = [
            'auto_start' => 'not_boolean',
        ];

        $this->loggerService
            ->expects($this->once())
            ->method('error')
        ;

        $result = $this->recordingClient->updateRecordingSettings($meetingId, $settings);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertNotEmpty($result['error']);
        $this->assertIsString($result['error']);
        $this->assertStringContainsString('录制设置 auto_start 必须是布尔值', $result['error']);
        $this->assertSame('updateRecordingSettings', $result['operation']);
    }

    public function testShareRecordingWithValidData(): void
    {
        $recordingId = 'rec123456';
        $shareData = [
            'share_type' => 'public',
            'expire_time' => 1704067200,
        ];

        $expectedResponse = [
            'success' => true,
            'recording_id' => 'rec123456',
            'share_url' => 'https://example.com/share/rec123456',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->recordingClient->shareRecording($recordingId, $shareData);

        $this->assertTrue($result['success']);
        $this->assertSame('https://example.com/share/rec123456', $result['share_url']);
    }

    public function testShareRecordingWithMissingRequiredFields(): void
    {
        $recordingId = 'rec123456';
        $shareData = [
            'share_type' => 'public',
            // missing expire_time
        ];

        $result = $this->recordingClient->shareRecording($recordingId, $shareData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('分享数据缺少必需字段: expire_time', $result['error']);
    }

    public function testShareRecordingWithInvalidShareType(): void
    {
        $recordingId = 'rec123456';
        $shareData = [
            'share_type' => 'invalid_type',
            'expire_time' => 1704067200,
        ];

        $result = $this->recordingClient->shareRecording($recordingId, $shareData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('无效的分享类型', $result['error']);
    }

    public function testShareRecordingWithInvalidExpireTime(): void
    {
        $recordingId = 'rec123456';
        $shareData = [
            'share_type' => 'public',
            'expire_time' => 'invalid_time',
        ];

        $result = $this->recordingClient->shareRecording($recordingId, $shareData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('过期时间必须是时间戳格式', $result['error']);
    }

    public function testDownloadRecordingWithValidId(): void
    {
        $recordingId = 'rec123456';
        $expectedResponse = [
            'success' => true,
            'recording_id' => 'rec123456',
            'download_url' => 'https://example.com/download/rec123456',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.meeting.qq.com/v1/recordings/rec123456/download')
            ->willReturn($expectedResponse)
        ;

        $result = $this->recordingClient->downloadRecording($recordingId);

        $this->assertTrue($result['success']);
        $this->assertSame('https://example.com/download/rec123456', $result['download_url']);
    }

    public function testSearchRecordingsWithSearchParams(): void
    {
        $searchParams = [
            'keyword' => 'test meeting',
            'start_time' => 1704067200,
            'end_time' => 1704070800,
            'page' => 1,
            'page_size' => 10,
        ];

        $expectedResponse = [
            'success' => true,
            'recordings' => [],
            'pagination' => [],
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->recordingClient->searchRecordings($searchParams);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('recordings', $result);
    }

    public function testHandleApiExceptionInGetRecording(): void
    {
        $recordingId = 'rec123456';

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new ApiException('Recording not found', 404))
        ;

        $result = $this->recordingClient->getRecording($recordingId);

        $this->assertFalse($result['success']);
        $this->assertSame('API请求失败: Recording not found', $result['error']);
        $this->assertSame(500, $result['code']);
        $this->assertSame('getRecording', $result['operation']);
    }

    public function testFormatRecordingResponseWithInvalidResponse(): void
    {
        $recordingId = 'rec123456';

        $invalidResponse = [
            'success' => false,
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($invalidResponse)
        ;

        $result = $this->recordingClient->getRecording($recordingId);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('录制操作失败', $result['error']);
    }

    public function testGetRecordingTranscriptionWithValidId(): void
    {
        $recordingId = 'rec123456';
        $expectedResponse = [
            'success' => true,
            'recording_id' => 'rec123456',
            'transcription' => 'Meeting transcription content',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.meeting.qq.com/v1/recordings/rec123456/transcription')
            ->willReturn($expectedResponse)
        ;

        $result = $this->recordingClient->getRecordingTranscription($recordingId);

        $this->assertTrue($result['success']);
        $this->assertSame('Meeting transcription content', $result['transcription']);
    }

    public function testGetRecordingAnalyticsWithValidId(): void
    {
        $recordingId = 'rec123456';
        $expectedResponse = [
            'success' => true,
            'recording_id' => 'rec123456',
            'analytics' => [
                'views' => 100,
                'downloads' => 10,
            ],
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.meeting.qq.com/v1/recordings/rec123456/analytics')
            ->willReturn($expectedResponse)
        ;

        $result = $this->recordingClient->getRecordingAnalytics($recordingId);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('analytics', $result);
        $this->assertSame(100, ((array) $result['analytics'])['views']);
    }

    public function testPauseRecordingWithValidMeetingId(): void
    {
        $meetingId = 'meeting123';
        $expectedResponse = [
            'success' => true,
            'meeting_id' => 'meeting123',
            'status' => 'paused',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.meeting.qq.com/v1/meetings/meeting123/recordings/pause', [])
            ->willReturn($expectedResponse)
        ;

        $result = $this->recordingClient->pauseRecording($meetingId);

        $this->assertTrue($result['success']);
        $this->assertSame('meeting123', $result['meeting_id']);
        $this->assertSame('paused', $result['status']);
    }

    public function testPauseRecordingWithApiException(): void
    {
        $meetingId = 'meeting123';

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new ApiException('Recording not active', 400))
        ;

        $result = $this->recordingClient->pauseRecording($meetingId);

        $this->assertFalse($result['success']);
        $this->assertSame('API请求失败: Recording not active', $result['error']);
        $this->assertSame(500, $result['code']);
        $this->assertSame('pauseRecording', $result['operation']);
    }

    public function testResumeRecordingWithValidMeetingId(): void
    {
        $meetingId = 'meeting123';
        $expectedResponse = [
            'success' => true,
            'meeting_id' => 'meeting123',
            'status' => 'recording',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.meeting.qq.com/v1/meetings/meeting123/recordings/resume', [])
            ->willReturn($expectedResponse)
        ;

        $result = $this->recordingClient->resumeRecording($meetingId);

        $this->assertTrue($result['success']);
        $this->assertSame('meeting123', $result['meeting_id']);
        $this->assertSame('recording', $result['status']);
    }

    public function testResumeRecordingWithApiException(): void
    {
        $meetingId = 'meeting123';

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new ApiException('Recording not paused', 400))
        ;

        $result = $this->recordingClient->resumeRecording($meetingId);

        $this->assertFalse($result['success']);
        $this->assertSame('API请求失败: Recording not paused', $result['error']);
        $this->assertSame(500, $result['code']);
        $this->assertSame('resumeRecording', $result['operation']);
    }
}

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
use Tourze\TencentMeetingBundle\Service\MeetingClient;

/**
 * @internal
 */
#[CoversClass(MeetingClient::class)]
final class MeetingClientTest extends TestCase
{
    private MeetingClient $meetingClient;

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

        $this->meetingClient = new MeetingClient(
            $this->configService,
            $this->httpClientService,
            $this->loggerService
        );
    }

    public function testMeetingClientCanBeInstantiated(): void
    {
        $this->assertInstanceOf(MeetingClient::class, $this->meetingClient);
    }

    public function testCreateMeetingWithValidData(): void
    {
        $meetingData = [
            'subject' => 'Test Meeting',
            'start_time' => 1704067200, // 2024-01-01 00:00:00
            'end_time' => 1704070800,   // 2024-01-01 01:00:00
            'type' => 1,
        ];

        $expectedResponse = [
            'success' => true,
            'meeting_id' => '123456',
            'subject' => 'Test Meeting',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->meetingClient->createMeeting($meetingData);

        $this->assertTrue($result['success']);
        $this->assertSame('123456', $result['meeting_id']);
    }

    public function testCreateMeetingWithMissingRequiredFields(): void
    {
        $meetingData = [
            'subject' => 'Test Meeting',
            // missing start_time, end_time, type
        ];

        $this->loggerService
            ->expects($this->atLeastOnce())
            ->method('error')
        ;

        $result = $this->meetingClient->createMeeting($meetingData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertNotEmpty($result['error']);
        $this->assertIsString($result['error']);
        $this->assertStringContainsString('会议数据缺少必需字段', $result['error']);
        $this->assertSame('createMeeting', $result['operation']);
    }

    public function testCreateMeetingWithInvalidTimeFormat(): void
    {
        $meetingData = [
            'subject' => 'Test Meeting',
            'start_time' => 'invalid-time',
            'end_time' => 1704070800,
            'type' => 1,
        ];

        $this->loggerService
            ->expects($this->atLeastOnce())
            ->method('error')
        ;

        $result = $this->meetingClient->createMeeting($meetingData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertNotEmpty($result['error']);
        $this->assertIsString($result['error']);
        $this->assertStringContainsString('会议开始时间必须是时间戳格式', $result['error']);
        $this->assertSame('createMeeting', $result['operation']);
    }

    public function testCreateMeetingWithInvalidType(): void
    {
        $meetingData = [
            'subject' => 'Test Meeting',
            'start_time' => 1704067200,
            'end_time' => 1704070800,
            'type' => 999, // invalid type
        ];

        $this->loggerService
            ->expects($this->atLeastOnce())
            ->method('error')
        ;

        $result = $this->meetingClient->createMeeting($meetingData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertNotEmpty($result['error']);
        $this->assertIsString($result['error']);
        $this->assertStringContainsString('无效的会议类型', $result['error']);
        $this->assertSame('createMeeting', $result['operation']);
    }

    public function testGetMeetingWithValidId(): void
    {
        $meetingId = '123456';
        $expectedResponse = [
            'success' => true,
            'meeting_id' => '123456',
            'subject' => 'Test Meeting',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.meeting.qq.com/v1/meetings/123456')
            ->willReturn($expectedResponse)
        ;

        $result = $this->meetingClient->getMeeting($meetingId);

        $this->assertTrue($result['success']);
        $this->assertSame('123456', $result['meeting_id']);
    }

    public function testUpdateMeetingWithValidData(): void
    {
        $meetingId = '123456';
        $updateData = [
            'subject' => 'Updated Meeting',
        ];

        $expectedResponse = [
            'success' => true,
            'meeting_id' => '123456',
            'subject' => 'Updated Meeting',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->meetingClient->updateMeeting($meetingId, $updateData);

        $this->assertTrue($result['success']);
        $this->assertSame('Updated Meeting', $result['subject']);
    }

    public function testDeleteMeetingWithValidId(): void
    {
        $meetingId = '123456';
        $expectedResponse = [
            'success' => true,
            'meeting_id' => '123456',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('DELETE', 'https://api.meeting.qq.com/v1/meetings/123456')
            ->willReturn($expectedResponse)
        ;

        $result = $this->meetingClient->deleteMeeting($meetingId);

        $this->assertTrue($result['success']);
    }

    public function testListMeetingsWithFilters(): void
    {
        $filters = [
            'page' => 1,
            'page_size' => 10,
            'type' => 1,
        ];

        $expectedResponse = [
            'success' => true,
            'meeting_id' => null,
            'meetings' => [],
            'pagination' => [],
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->meetingClient->listMeetings($filters);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('meetings', $result);
    }

    public function testAddMeetingParticipantWithValidData(): void
    {
        $meetingId = '123456';
        $participantData = [
            'user_id' => 'user123',
            'role' => 'attendee',
        ];

        $expectedResponse = [
            'success' => true,
            'meeting_id' => '123456',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->meetingClient->addMeetingParticipant($meetingId, $participantData);

        $this->assertTrue($result['success']);
    }

    public function testAddMeetingParticipantWithInvalidRole(): void
    {
        $meetingId = '123456';
        $participantData = [
            'user_id' => 'user123',
            'role' => 'invalid_role',
        ];

        $this->loggerService
            ->expects($this->atLeastOnce())
            ->method('error')
        ;

        $result = $this->meetingClient->addMeetingParticipant($meetingId, $participantData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertNotEmpty($result['error']);
        $this->assertIsString($result['error']);
        $this->assertStringContainsString('无效的参与者角色', $result['error']);
        $this->assertSame('addMeetingParticipant', $result['operation']);
    }

    public function testUpdateMeetingSettingsWithValidData(): void
    {
        $meetingId = '123456';
        $settings = [
            'mute_enable' => true,
            'waiting_room_enable' => false,
        ];

        $expectedResponse = [
            'success' => true,
            'meeting_id' => '123456',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->meetingClient->updateMeetingSettings($meetingId, $settings);

        $this->assertTrue($result['success']);
    }

    public function testUpdateMeetingSettingsWithInvalidSetting(): void
    {
        $meetingId = '123456';
        $settings = [
            'invalid_setting' => true,
        ];

        $this->loggerService
            ->expects($this->atLeastOnce())
            ->method('error')
        ;

        $result = $this->meetingClient->updateMeetingSettings($meetingId, $settings);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertNotEmpty($result['error']);
        $this->assertIsString($result['error']);
        $this->assertStringContainsString('无效的会议设置: invalid_setting', $result['error']);
        $this->assertSame('updateMeetingSettings', $result['operation']);
    }

    public function testUpdateMeetingSettingsWithNonBooleanValue(): void
    {
        $meetingId = '123456';
        $settings = [
            'mute_enable' => 'not_boolean',
        ];

        $this->loggerService
            ->expects($this->atLeastOnce())
            ->method('error')
        ;

        $result = $this->meetingClient->updateMeetingSettings($meetingId, $settings);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertNotEmpty($result['error']);
        $this->assertIsString($result['error']);
        $this->assertStringContainsString('会议设置 mute_enable 必须是布尔值', $result['error']);
        $this->assertSame('updateMeetingSettings', $result['operation']);
    }

    public function testHandleApiExceptionInGetMeeting(): void
    {
        $meetingId = '123456';

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new ApiException('API Error', 400))
        ;

        $this->loggerService
            ->expects($this->atLeastOnce())
            ->method('error')
        ;

        $result = $this->meetingClient->getMeeting($meetingId);

        $this->assertFalse($result['success']);
        $this->assertSame('API请求失败: API Error', $result['error']);
        $this->assertSame(500, $result['code']);
        $this->assertSame('getMeeting', $result['operation']);
    }

    public function testFormatMeetingResponseWithInvalidResponse(): void
    {
        $meetingData = [
            'subject' => 'Test Meeting',
            'start_time' => 1704067200,
            'end_time' => 1704070800,
            'type' => 1,
        ];

        $invalidResponse = [
            'success' => false,
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($invalidResponse)
        ;

        $result = $this->meetingClient->createMeeting($meetingData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('会议操作失败', $result['error']);
    }

    public function testCancelMeetingWithValidId(): void
    {
        $meetingId = '123456';
        $expectedResponse = [
            'success' => true,
            'meeting_id' => '123456',
            'status' => 'cancelled',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.meeting.qq.com/v1/meetings/123456/cancel', [])
            ->willReturn($expectedResponse)
        ;

        $result = $this->meetingClient->cancelMeeting($meetingId);

        $this->assertTrue($result['success']);
        $this->assertSame('123456', $result['meeting_id']);
        $this->assertSame('cancelled', $result['status']);
    }

    public function testCancelMeetingWithApiException(): void
    {
        $meetingId = '123456';

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new ApiException('Meeting not found', 404))
        ;

        $this->loggerService
            ->expects($this->atLeastOnce())
            ->method('error')
        ;

        $result = $this->meetingClient->cancelMeeting($meetingId);

        $this->assertFalse($result['success']);
        $this->assertSame('API请求失败: Meeting not found', $result['error']);
        $this->assertSame(500, $result['code']);
        $this->assertSame('cancelMeeting', $result['operation']);
    }

    public function testEndMeetingWithValidId(): void
    {
        $meetingId = '123456';
        $expectedResponse = [
            'success' => true,
            'meeting_id' => '123456',
            'status' => 'ended',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.meeting.qq.com/v1/meetings/123456/end', [])
            ->willReturn($expectedResponse)
        ;

        $result = $this->meetingClient->endMeeting($meetingId);

        $this->assertTrue($result['success']);
        $this->assertSame('123456', $result['meeting_id']);
        $this->assertSame('ended', $result['status']);
    }

    public function testEndMeetingWithApiException(): void
    {
        $meetingId = '123456';

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new ApiException('Meeting cannot be ended', 400))
        ;

        $this->loggerService
            ->expects($this->atLeastOnce())
            ->method('error')
        ;

        $result = $this->meetingClient->endMeeting($meetingId);

        $this->assertFalse($result['success']);
        $this->assertSame('API请求失败: Meeting cannot be ended', $result['error']);
        $this->assertSame(500, $result['code']);
        $this->assertSame('endMeeting', $result['operation']);
    }

    public function testRemoveMeetingParticipantWithValidData(): void
    {
        $meetingId = '123456';
        $userId = 'user123';
        $expectedResponse = [
            'success' => true,
            'meeting_id' => '123456',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('DELETE', 'https://api.meeting.qq.com/v1/meetings/123456/participants/user123')
            ->willReturn($expectedResponse)
        ;

        $result = $this->meetingClient->removeMeetingParticipant($meetingId, $userId);

        $this->assertTrue($result['success']);
        $this->assertSame('123456', $result['meeting_id']);
    }

    public function testRemoveMeetingParticipantWithApiException(): void
    {
        $meetingId = '123456';
        $userId = 'user123';

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new ApiException('Participant not found', 404))
        ;

        $this->loggerService
            ->expects($this->atLeastOnce())
            ->method('error')
        ;

        $result = $this->meetingClient->removeMeetingParticipant($meetingId, $userId);

        $this->assertFalse($result['success']);
        $this->assertSame('API请求失败: Participant not found', $result['error']);
        $this->assertSame(500, $result['code']);
        $this->assertSame('removeMeetingParticipant', $result['operation']);
    }
}

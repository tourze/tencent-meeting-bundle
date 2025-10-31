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
use Tourze\TencentMeetingBundle\Service\RoomClient;

/**
 * @internal
 */
#[CoversClass(RoomClient::class)]
final class RoomClientTest extends TestCase
{
    private RoomClient $roomClient;

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

        $this->roomClient = new RoomClient(
            $this->configService,
            $this->httpClientService,
            $this->loggerService
        );
    }

    public function testRoomClientCanBeInstantiated(): void
    {
        $this->assertInstanceOf(RoomClient::class, $this->roomClient);
    }

    public function testGetRoomWithValidId(): void
    {
        $roomId = 'room123';
        $expectedResponse = [
            'success' => true,
            'room_id' => 'room123',
            'name' => 'Conference Room A',
            'capacity' => 10,
            'location' => 'Building 1, Floor 2',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.meeting.qq.com/v1/rooms/room123')
            ->willReturn($expectedResponse)
        ;

        $result = $this->roomClient->getRoom($roomId);

        $this->assertTrue($result['success']);
        $this->assertSame('room123', $result['room_id']);
        $this->assertSame('Conference Room A', $result['name']);
    }

    public function testCreateRoomWithValidData(): void
    {
        $roomData = [
            'name' => 'Conference Room B',
            'capacity' => 20,
            'location' => 'Building 2, Floor 1',
            'equipment' => ['projector', 'microphone'],
        ];

        $expectedResponse = [
            'success' => true,
            'room_id' => 'room456',
            'name' => 'Conference Room B',
            'capacity' => 20,
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->roomClient->createRoom($roomData);

        $this->assertTrue($result['success']);
        $this->assertSame('room456', $result['room_id']);
    }

    public function testCreateRoomWithMissingRequiredFields(): void
    {
        $roomData = [
            'name' => 'Conference Room B',
            // missing capacity and location
        ];

        $result = $this->roomClient->createRoom($roomData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('会议室数据缺少必需字段: capacity', $result['error']);
    }

    public function testCreateRoomWithInvalidCapacity(): void
    {
        $roomData = [
            'name' => 'Conference Room B',
            'capacity' => -5, // invalid capacity
            'location' => 'Building 2, Floor 1',
        ];

        $result = $this->roomClient->createRoom($roomData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('会议室容量必须是正数', $result['error']);
    }

    public function testCreateRoomWithInvalidEquipment(): void
    {
        $roomData = [
            'name' => 'Conference Room B',
            'capacity' => 20,
            'location' => 'Building 2, Floor 1',
            'equipment' => 'not_an_array', // invalid equipment format
        ];

        $result = $this->roomClient->createRoom($roomData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('会议室设备必须是数组', $result['error']);
    }

    public function testUpdateRoomWithValidData(): void
    {
        $roomId = 'room123';
        $updateData = [
            'name' => 'Updated Conference Room',
            'capacity' => 15,
        ];

        $expectedResponse = [
            'success' => true,
            'room_id' => 'room123',
            'name' => 'Updated Conference Room',
            'capacity' => 15,
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->roomClient->updateRoom($roomId, $updateData);

        $this->assertTrue($result['success']);
        $this->assertSame('Updated Conference Room', $result['name']);
    }

    public function testDeleteRoomWithValidId(): void
    {
        $roomId = 'room123';
        $expectedResponse = [
            'success' => true,
            'room_id' => 'room123',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('DELETE', 'https://api.meeting.qq.com/v1/rooms/room123')
            ->willReturn($expectedResponse)
        ;

        $result = $this->roomClient->deleteRoom($roomId);

        $this->assertTrue($result['success']);
    }

    public function testListRoomsWithFilters(): void
    {
        $filters = [
            'page' => 1,
            'page_size' => 10,
            'capacity_min' => 5,
            'capacity_max' => 20,
            'location' => 'Building 1',
        ];

        $expectedResponse = [
            'success' => true,
            'rooms' => [],
            'pagination' => [],
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->roomClient->listRooms($filters);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('rooms', $result);
    }

    public function testUpdateRoomSettingsWithValidData(): void
    {
        $roomId = 'room123';
        $settings = [
            'auto_book' => true,
            'approval_required' => false,
            'max_booking_duration' => 480,
        ];

        $expectedResponse = [
            'success' => true,
            'room_id' => 'room123',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->roomClient->updateRoomSettings($roomId, $settings);

        $this->assertTrue($result['success']);
    }

    public function testUpdateRoomSettingsWithInvalidSetting(): void
    {
        $roomId = 'room123';
        $settings = [
            'invalid_setting' => true,
        ];

        $result = $this->roomClient->updateRoomSettings($roomId, $settings);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('无效的会议室设置: invalid_setting', $result['error']);
    }

    public function testCheckRoomAvailabilityWithValidTimeRange(): void
    {
        $roomId = 'room123';
        $timeRange = [
            'start_time' => 1704067200, // 2024-01-01 00:00:00
            'end_time' => 1704070800,   // 2024-01-01 01:00:00
        ];

        $expectedResponse = [
            'success' => true,
            'room_id' => 'room123',
            'available' => true,
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->roomClient->checkRoomAvailability($roomId, $timeRange);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['available']);
    }

    public function testCheckRoomAvailabilityWithMissingTimeFields(): void
    {
        $roomId = 'room123';
        $timeRange = [
            'start_time' => 1704067200,
            // missing end_time
        ];

        $result = $this->roomClient->checkRoomAvailability($roomId, $timeRange);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('时间范围缺少必需字段: end_time', $result['error']);
    }

    public function testCheckRoomAvailabilityWithInvalidTimeFormat(): void
    {
        $roomId = 'room123';
        $timeRange = [
            'start_time' => 'invalid_time',
            'end_time' => 1704070800,
        ];

        $result = $this->roomClient->checkRoomAvailability($roomId, $timeRange);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('时间必须是时间戳格式', $result['error']);
    }

    public function testCheckRoomAvailabilityWithInvalidTimeOrder(): void
    {
        $roomId = 'room123';
        $timeRange = [
            'start_time' => 1704070800,
            'end_time' => 1704067200, // end time before start time
        ];

        $result = $this->roomClient->checkRoomAvailability($roomId, $timeRange);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('开始时间必须早于结束时间', $result['error']);
    }

    public function testReserveRoomWithValidData(): void
    {
        $roomId = 'room123';
        $bookingData = [
            'start_time' => 1704067200,
            'end_time' => 1704070800,
            'booked_by' => 'user123',
            'purpose' => 'Team meeting',
        ];

        $expectedResponse = [
            'success' => true,
            'room_id' => 'room123',
            'booking_id' => 'booking456',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->roomClient->reserveRoom($roomId, $bookingData);

        $this->assertTrue($result['success']);
        $this->assertSame('booking456', $result['booking_id'] ?? null);
    }

    public function testReserveRoomWithMissingRequiredFields(): void
    {
        $roomId = 'room123';
        $bookingData = [
            'start_time' => 1704067200,
            'end_time' => 1704070800,
            // missing booked_by
        ];

        $result = $this->roomClient->reserveRoom($roomId, $bookingData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('预订数据缺少必需字段: booked_by', $result['error']);
    }

    public function testReleaseRoomWithValidData(): void
    {
        $roomId = 'room123';
        $bookingId = 'booking456';

        $expectedResponse = [
            'success' => true,
            'room_id' => 'room123',
            'booking_id' => 'booking456',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.meeting.qq.com/v1/rooms/room123/release', ['booking_id' => 'booking456'])
            ->willReturn($expectedResponse)
        ;

        $result = $this->roomClient->releaseRoom($roomId, $bookingId);

        $this->assertTrue($result['success']);
    }

    public function testGetRoomMeetingsWithFilters(): void
    {
        $roomId = 'room123';
        $filters = [
            'page' => 1,
            'page_size' => 10,
            'status' => 'scheduled',
        ];

        $expectedResponse = [
            'success' => true,
            'meetings' => [],
            'pagination' => [],
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->roomClient->getRoomMeetings($roomId, $filters);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('meetings', $result);
    }

    public function testGetRoomCapacityWithValidId(): void
    {
        $roomId = 'room123';
        $expectedResponse = [
            'success' => true,
            'room_id' => 'room123',
            'capacity' => 20,
            'current_occupancy' => 5,
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.meeting.qq.com/v1/rooms/room123/capacity')
            ->willReturn($expectedResponse)
        ;

        $result = $this->roomClient->getRoomCapacity($roomId);

        $this->assertTrue($result['success']);
        $this->assertSame(20, $result['capacity']);
        $this->assertSame(5, $result['current_occupancy'] ?? null);
    }

    public function testHandleApiExceptionInGetRoom(): void
    {
        $roomId = 'room123';

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new ApiException('Room not found', 404))
        ;

        $result = $this->roomClient->getRoom($roomId);

        $this->assertFalse($result['success']);
        $this->assertSame('API请求失败: Room not found', $result['error']);
        $this->assertSame(500, $result['code']);
        $this->assertSame('getRoom', $result['operation']);
    }

    public function testFormatRoomResponseWithInvalidResponse(): void
    {
        $roomId = 'room123';

        $invalidResponse = [
            'success' => false,
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($invalidResponse)
        ;

        $result = $this->roomClient->getRoom($roomId);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('会议室操作失败', $result['error']);
    }
}

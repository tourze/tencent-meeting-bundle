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
use Tourze\TencentMeetingBundle\Service\UserClient;

/**
 * @internal
 */
#[CoversClass(UserClient::class)]
final class UserClientTest extends TestCase
{
    private UserClient $userClient;

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

        $this->userClient = new UserClient(
            $this->configService,
            $this->httpClientService,
            $this->loggerService
        );
    }

    public function testUserClientCanBeInstantiated(): void
    {
        $this->assertInstanceOf(UserClient::class, $this->userClient);
    }

    public function testGetUserWithValidId(): void
    {
        $userId = 'user123';
        $expectedResponse = [
            'success' => true,
            'user_id' => 'user123',
            'username' => 'john_doe',
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'role' => 'user',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.meeting.qq.com/v1/users/user123')
            ->willReturn($expectedResponse)
        ;

        $result = $this->userClient->getUser($userId);

        $this->assertTrue($result['success']);
        $this->assertSame('user123', $result['user_id']);
        $this->assertSame('john_doe', $result['username']);
    }

    public function testCreateUserWithValidData(): void
    {
        $userData = [
            'username' => 'jane_doe',
            'email' => 'jane@example.com',
            'phone' => '13812345678',
            'name' => 'Jane Doe',
            'role' => 'user',
        ];

        $expectedResponse = [
            'success' => true,
            'user_id' => 'user456',
            'username' => 'jane_doe',
            'email' => 'jane@example.com',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->userClient->createUser($userData);

        $this->assertTrue($result['success']);
        $this->assertSame('user456', $result['user_id']);
    }

    public function testCreateUserWithMissingRequiredFields(): void
    {
        $userData = [
            'username' => 'jane_doe',
            'email' => 'jane@example.com',
            // missing phone
        ];

        $result = $this->userClient->createUser($userData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('用户数据缺少必需字段: phone', $result['error']);
    }

    public function testCreateUserWithInvalidEmail(): void
    {
        $userData = [
            'username' => 'jane_doe',
            'email' => 'invalid-email',
            'phone' => '13812345678',
        ];

        $result = $this->userClient->createUser($userData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('无效的邮箱格式', $result['error']);
    }

    public function testCreateUserWithInvalidPhone(): void
    {
        $userData = [
            'username' => 'jane_doe',
            'email' => 'jane@example.com',
            'phone' => '123456', // invalid phone format
        ];

        // HTTP client should never be called because validation should fail first
        $this->httpClientService
            ->expects($this->never())
            ->method('request')
        ;

        $result = $this->userClient->createUser($userData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('无效的手机号格式', $result['error']);
    }

    public function testCreateUserWithInvalidRole(): void
    {
        $userData = [
            'username' => 'jane_doe',
            'email' => 'jane@example.com',
            'phone' => '13812345678',
            'role' => 'invalid_role',
        ];

        $result = $this->userClient->createUser($userData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('无效的用户角色', $result['error']);
    }

    public function testUpdateUserWithValidData(): void
    {
        $userId = 'user123';
        $updateData = [
            'name' => 'John Smith',
            'email' => 'john.smith@example.com',
        ];

        $expectedResponse = [
            'success' => true,
            'user_id' => 'user123',
            'name' => 'John Smith',
            'email' => 'john.smith@example.com',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->userClient->updateUser($userId, $updateData);

        $this->assertTrue($result['success']);
        $this->assertSame('John Smith', $result['name']);
    }

    public function testDeleteUserWithValidId(): void
    {
        $userId = 'user123';
        $expectedResponse = [
            'success' => true,
            'user_id' => 'user123',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('DELETE', 'https://api.meeting.qq.com/v1/users/user123')
            ->willReturn($expectedResponse)
        ;

        $result = $this->userClient->deleteUser($userId);

        $this->assertTrue($result['success']);
    }

    public function testListUsersWithFilters(): void
    {
        $filters = [
            'page' => 1,
            'page_size' => 10,
            'department_id' => 'dept123',
            'role' => 'user',
        ];

        $expectedResponse = [
            'success' => true,
            'users' => [],
            'pagination' => [],
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->userClient->listUsers($filters);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('users', $result);
    }

    public function testGetUserDepartmentsWithValidId(): void
    {
        $userId = 'user123';
        $expectedResponse = [
            'success' => true,
            'user_id' => 'user123',
            'departments' => [
                ['id' => 'dept1', 'name' => 'Engineering'],
                ['id' => 'dept2', 'name' => 'Product'],
            ],
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.meeting.qq.com/v1/users/user123/departments')
            ->willReturn($expectedResponse)
        ;

        $result = $this->userClient->getUserDepartments($userId);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('departments', $result);
        $this->assertCount(2, (array) $result['departments']);
    }

    public function testUpdateUserSettingsWithValidData(): void
    {
        $userId = 'user123';
        $settings = [
            'email_notification' => true,
            'sms_notification' => false,
            'auto_join_mic' => true,
        ];

        $expectedResponse = [
            'success' => true,
            'user_id' => 'user123',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->userClient->updateUserSettings($userId, $settings);

        $this->assertTrue($result['success']);
    }

    public function testUpdateUserSettingsWithInvalidSetting(): void
    {
        $userId = 'user123';
        $settings = [
            'invalid_setting' => true,
        ];

        $result = $this->userClient->updateUserSettings($userId, $settings);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('无效的用户设置: invalid_setting', $result['error']);
    }

    public function testUpdateUserSettingsWithNonBooleanValue(): void
    {
        $userId = 'user123';
        $settings = [
            'email_notification' => 'not_boolean',
        ];

        $result = $this->userClient->updateUserSettings($userId, $settings);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('用户设置 email_notification 必须是布尔值', $result['error']);
    }

    public function testActivateUserWithValidId(): void
    {
        $userId = 'user123';
        $expectedResponse = [
            'success' => true,
            'user_id' => 'user123',
            'status' => 'active',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.meeting.qq.com/v1/users/user123/activate', [])
            ->willReturn($expectedResponse)
        ;

        $result = $this->userClient->activateUser($userId);

        $this->assertTrue($result['success']);
        $this->assertSame('active', $result['status']);
    }

    public function testDeactivateUserWithValidId(): void
    {
        $userId = 'user123';
        $expectedResponse = [
            'success' => true,
            'user_id' => 'user123',
            'status' => 'inactive',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.meeting.qq.com/v1/users/user123/deactivate', [])
            ->willReturn($expectedResponse)
        ;

        $result = $this->userClient->deactivateUser($userId);

        $this->assertTrue($result['success']);
        $this->assertSame('inactive', $result['status']);
    }

    public function testResetUserPasswordWithValidId(): void
    {
        $userId = 'user123';
        $expectedResponse = [
            'success' => true,
            'user_id' => 'user123',
            'message' => 'Password reset successfully',
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.meeting.qq.com/v1/users/user123/reset-password', [])
            ->willReturn($expectedResponse)
        ;

        $result = $this->userClient->resetUserPassword($userId);

        $this->assertTrue($result['success']);
        $this->assertSame('Password reset successfully', $result['message'] ?? null);
    }

    public function testBatchCreateUsersWithValidData(): void
    {
        $usersData = [
            [
                'username' => 'user1',
                'email' => 'user1@example.com',
                'phone' => '13812345678',
            ],
            [
                'username' => 'user2',
                'email' => 'user2@example.com',
                'phone' => '13812345679',
            ],
        ];

        $expectedResponse = [
            'success' => true,
            'created_users' => ['user1', 'user2'],
            'failed_users' => [],
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.meeting.qq.com/v1/users/batch', ['users' => $usersData])
            ->willReturn($expectedResponse)
        ;

        $result = $this->userClient->batchCreateUsers($usersData);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('created_users', $result);
        $this->assertCount(2, (array) $result['created_users']);
    }

    public function testBatchCreateUsersWithInvalidUserData(): void
    {
        $usersData = [
            [
                'username' => 'user1',
                'email' => 'user1@example.com',
                // missing phone
            ],
        ];

        $result = $this->userClient->batchCreateUsers($usersData);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('用户数据缺少必需字段: phone', $result['error']);
    }

    public function testSearchUsersWithSearchParams(): void
    {
        $searchParams = [
            'keyword' => 'john',
            'search_fields' => ['username', 'email', 'name'],
            'page' => 1,
            'page_size' => 10,
        ];

        $expectedResponse = [
            'success' => true,
            'users' => [],
            'pagination' => [],
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($expectedResponse)
        ;

        $result = $this->userClient->searchUsers($searchParams);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('users', $result);
    }

    public function testGetUserMeetingsWithFilters(): void
    {
        $userId = 'user123';
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

        $result = $this->userClient->getUserMeetings($userId, $filters);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('meetings', $result);
    }

    public function testGetUserRecordingsWithFilters(): void
    {
        $userId = 'user123';
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

        $result = $this->userClient->getUserRecordings($userId, $filters);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('recordings', $result);
    }

    public function testHandleApiExceptionInGetUser(): void
    {
        $userId = 'user123';

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new ApiException('User not found', 404))
        ;

        $result = $this->userClient->getUser($userId);

        $this->assertFalse($result['success']);
        $this->assertSame('API请求失败: User not found', $result['error']);
        $this->assertSame(500, $result['code']);
        $this->assertSame('getUser', $result['operation']);
    }

    public function testFormatUserResponseWithInvalidResponse(): void
    {
        $userId = 'user123';

        $invalidResponse = [
            'success' => false,
        ];

        $this->httpClientService
            ->expects($this->once())
            ->method('request')
            ->willReturn($invalidResponse)
        ;

        $result = $this->userClient->getUser($userId);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('用户操作失败', $result['error']);
    }
}

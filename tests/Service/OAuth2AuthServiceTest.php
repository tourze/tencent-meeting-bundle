<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TencentMeetingBundle\Exception\AuthenticationException;
use Tourze\TencentMeetingBundle\Service\OAuth2AuthService;

/**
 * @internal
 */
#[CoversClass(OAuth2AuthService::class)]
#[RunTestsInSeparateProcesses]
final class OAuth2AuthServiceTest extends AbstractIntegrationTestCase
{
    private OAuth2AuthService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(OAuth2AuthService::class);
    }

    public function testServiceCanBeInstantiatedFromContainer(): void
    {
        $this->assertInstanceOf(OAuth2AuthService::class, $this->service);
    }

    public function testGetAuthorizationUrl(): void
    {
        $authUrl = $this->service->getAuthorizationUrl();

        $this->assertStringStartsWith('https://api.meeting.qq.com/oauth2/authorize', $authUrl);
        $this->assertStringContainsString('response_type=code', $authUrl);
        $this->assertStringContainsString('client_id=', $authUrl);
        $this->assertStringContainsString('redirect_uri=', $authUrl);
        $this->assertStringContainsString('scope=', $authUrl);
        $this->assertStringContainsString('state=', $authUrl);
    }

    public function testHandleAuthorizationCallbackSuccess(): void
    {
        $code = 'test_authorization_code';
        $state = 'valid_state';

        $result = $this->service->handleAuthorizationCallback($code, $state);

        $this->assertTrue($result);
        $accessToken = $this->service->getAccessToken();
        $refreshToken = $this->service->getRefreshToken();
        // Verify tokens are set (return types guarantee string|null)
        $this->assertIsString($accessToken);
        $this->assertIsString($refreshToken);
        $this->assertStringStartsWith('mock_access_token_', $accessToken);
        $this->assertStringStartsWith('mock_refresh_token_', $refreshToken);
    }

    public function testValidateTokenWithValidToken(): void
    {
        $validToken = 'valid_token_123';

        $result = $this->service->validateToken($validToken);

        $this->assertTrue($result);
    }

    public function testValidateTokenWithEmptyToken(): void
    {
        $result = $this->service->validateToken('');

        $this->assertFalse($result);
    }

    public function testRefreshTokenSuccess(): void
    {
        // 先设置一个refresh token
        $this->service->handleAuthorizationCallback('test_code', 'test_state');

        $result = $this->service->refreshToken();

        $this->assertTrue($result);
        $accessToken = $this->service->getAccessToken();
        $this->assertIsString($accessToken);
        $this->assertStringStartsWith('mock_new_access_token_', $accessToken);
    }

    public function testRefreshTokenWithoutRefreshToken(): void
    {
        // 集成测试无法验证日志调用，只验证返回值
        $result = $this->service->refreshToken();

        $this->assertFalse($result);
    }

    public function testGetUserInfoAfterAuth(): void
    {
        // 先进行认证
        $this->service->handleAuthorizationCallback('test_code', 'test_state');

        $userInfo = $this->service->getUserInfo();
        $this->assertArrayHasKey('sub', $userInfo);
        $this->assertArrayHasKey('username', $userInfo);
        $this->assertArrayHasKey('email', $userInfo);
        $this->assertArrayHasKey('permissions', $userInfo);
        $this->assertEquals('oauth2_user', $userInfo['username']);
        $this->assertEquals('oauth2_user@example.com', $userInfo['email']);
    }

    public function testGetUserInfoWithoutAuth(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('用户未认证');

        $this->service->getUserInfo();
    }

    public function testGetPermissionsAfterAuth(): void
    {
        $this->service->handleAuthorizationCallback('test_code', 'test_state');

        $permissions = $this->service->getPermissions();
        $this->assertContains('meeting:read', $permissions);
        $this->assertContains('meeting:write', $permissions);
    }

    public function testHasPermissionAfterAuth(): void
    {
        $this->service->handleAuthorizationCallback('test_code', 'test_state');

        $this->assertTrue($this->service->hasPermission('meeting:read'));
        $this->assertTrue($this->service->hasPermission('meeting:write'));
        $this->assertFalse($this->service->hasPermission('admin:all'));
    }

    public function testCheckAccessAfterAuth(): void
    {
        $this->service->handleAuthorizationCallback('test_code', 'test_state');

        $this->assertTrue($this->service->checkAccess('meeting:read'));
        $this->assertFalse($this->service->checkAccess('admin:delete'));
    }

    public function testLogout(): void
    {
        // 先进行认证
        $this->service->handleAuthorizationCallback('test_code', 'test_state');
        $this->assertIsString($this->service->getAccessToken());

        // 退出登录
        $result = $this->service->logout();

        $this->assertTrue($result);
        $this->assertNull($this->service->getAccessToken());
        $this->assertNull($this->service->getRefreshToken());
    }

    public function testIsTokenExpiredWithoutToken(): void
    {
        $result = $this->service->isTokenExpired();

        $this->assertFalse($result);
    }

    public function testIsTokenExpiringSoon(): void
    {
        $this->service->handleAuthorizationCallback('test_code', 'test_state');

        // Token 刚创建，不应该即将过期
        $result = $this->service->isTokenExpiringSoon();

        $this->assertFalse($result);
    }

    public function testScopesManagement(): void
    {
        $originalScopes = $this->service->getScopes();
        $this->assertContains('meeting:read', $originalScopes);

        $newScopes = ['user:read', 'calendar:write'];
        $this->service->setScopes($newScopes);

        $this->assertEquals($newScopes, $this->service->getScopes());
    }

    public function testGetTokenExpiration(): void
    {
        $this->assertNull($this->service->getTokenExpiration());

        $callbackResult = $this->service->handleAuthorizationCallback('test_code', 'test_state');
        $this->assertTrue($callbackResult, '授权回调应该成功');

        $expiration = $this->service->getTokenExpiration();

        // Verify expiration is set and is a valid future timestamp
        // exchangeCodeForToken 返回 expires_in=3600，所以应该设置为 time()+3600
        $this->assertNotNull($expiration, 'Token expiration 应该被设置为有效的时间戳');
        $this->assertGreaterThan(time(), $expiration, 'Token 过期时间应该在未来');
        $this->assertLessThan(time() + 7200, $expiration, 'Token 过期时间应该在合理范围内');
    }

    public function testAuthenticateWithoutToken(): void
    {
        $result = $this->service->authenticate();

        $this->assertFalse($result);
    }

    public function testAuthenticateWithValidToken(): void
    {
        // 先获取token
        $this->service->handleAuthorizationCallback('test_code', 'test_state');

        $result = $this->service->authenticate();

        $this->assertTrue($result);
    }
}

<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use Firebase\JWT\JWT;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\TencentMeetingBundle\Exception\AuthenticationException;
use Tourze\TencentMeetingBundle\Service\AuthServiceInterface;
use Tourze\TencentMeetingBundle\Service\ConfigServiceInterface;
use Tourze\TencentMeetingBundle\Service\JwtAuthService;

/**
 * @internal
 */
#[CoversClass(JwtAuthService::class)]
final class JwtAuthServiceTest extends TestCase
{
    private JwtAuthService $jwtAuthService;

    private MockObject&LoggerInterface $loggerService;

    private MockObject&ConfigServiceInterface $configService;

    protected function setUp(): void
    {
        $this->loggerService = $this->createMock(LoggerInterface::class);
        $this->configService = $this->createMock(ConfigServiceInterface::class);

        // 配置默认的mock行为
        $this->configService->method('getSecretKey')->willReturn('test-secret-key-123');

        $this->jwtAuthService = new JwtAuthService(
            $this->loggerService,
            $this->configService
        );
    }

    public function testImplementsAuthServiceInterface(): void
    {
        $this->assertInstanceOf(AuthServiceInterface::class, $this->jwtAuthService);
    }

    public function testGenerateTokenReturnsValidJwt(): void
    {
        $token = $this->jwtAuthService->generateToken();

        $this->assertNotEmpty($token);

        // JWT应该有3个部分，由.分隔
        $parts = explode('.', $token);
        $this->assertCount(3, $parts, 'JWT should have 3 parts separated by dots');

        // 每个部分都应该是base64编码的
        foreach ($parts as $part) {
            $this->assertNotEmpty($part, 'JWT part should not be empty');
        }
    }

    public function testValidateTokenWithValidToken(): void
    {
        $token = $this->jwtAuthService->generateToken();

        $result = $this->jwtAuthService->validateToken($token);

        $this->assertTrue($result);
    }

    public function testValidateTokenWithInvalidToken(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Token验证失败');

        $this->jwtAuthService->validateToken('invalid.token.here');
    }

    public function testValidateTokenWithExpiredToken(): void
    {
        // 创建一个已经过期的token（通过时间回退模拟）
        $expiredPayload = [
            'iss' => 'tencent-meeting-bundle',
            'aud' => 'tencent-meeting-api',
            'iat' => time() - 7200, // 2小时前
            'exp' => time() - 3600, // 1小时前过期
            'sub' => 'user_test',
        ];

        $expiredToken = JWT::encode($expiredPayload, 'test-secret-key-123', 'HS256');

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Token已过期');

        $this->jwtAuthService->validateToken($expiredToken);
    }

    public function testAuthenticateGeneratesNewToken(): void
    {
        $result = $this->jwtAuthService->authenticate();

        $this->assertTrue($result);
        $this->assertNotNull($this->jwtAuthService->getCurrentToken());
    }

    public function testAuthenticateWithExistingValidToken(): void
    {
        // 先生成一个token
        $this->jwtAuthService->authenticate();
        $firstToken = $this->jwtAuthService->getCurrentToken();

        // 再次认证应该重用现有的有效token
        $result = $this->jwtAuthService->authenticate();

        $this->assertTrue($result);
        $this->assertEquals($firstToken, $this->jwtAuthService->getCurrentToken());
    }

    public function testRefreshTokenWithValidCurrentToken(): void
    {
        // 先认证获得token
        $this->jwtAuthService->authenticate();
        $originalToken = $this->jwtAuthService->getCurrentToken();

        // 刷新token
        $result = $this->jwtAuthService->refreshToken();
        $newToken = $this->jwtAuthService->getCurrentToken();

        $this->assertTrue($result);
        $this->assertNotNull($newToken);
        $this->assertNotEquals($originalToken, $newToken);
    }

    public function testRefreshTokenWithoutCurrentToken(): void
    {
        // 没有当前token时尝试刷新
        $this->loggerService->expects($this->once())
            ->method('error')
            ->with(
                'TencentMeeting JWT Token刷新失败',
                self::callback(function ($context) {
                    return is_array($context) && isset($context['exception']) && $context['exception'] instanceof \Throwable;
                })
            )
        ;

        $result = $this->jwtAuthService->refreshToken();

        $this->assertFalse($result);
    }

    public function testGetUserInfoAfterValidation(): void
    {
        $token = $this->jwtAuthService->generateToken();
        $this->jwtAuthService->validateToken($token);

        $userInfo = $this->jwtAuthService->getUserInfo();
        $this->assertArrayHasKey('iss', $userInfo);
        $this->assertArrayHasKey('aud', $userInfo);
        $this->assertArrayHasKey('sub', $userInfo);
        $this->assertEquals('tencent-meeting-bundle', $userInfo['iss']);
        $this->assertEquals('tencent-meeting-api', $userInfo['aud']);
    }

    public function testGetUserInfoWithoutAuthentication(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('用户未认证');

        $this->jwtAuthService->getUserInfo();
    }

    public function testGetPermissionsAfterValidation(): void
    {
        $token = $this->jwtAuthService->generateToken();
        $this->jwtAuthService->validateToken($token);

        $permissions = $this->jwtAuthService->getPermissions();
        $this->assertContains('meeting:create', $permissions);
        $this->assertContains('meeting:read', $permissions);
        $this->assertContains('meeting:update', $permissions);
        $this->assertContains('meeting:delete', $permissions);
    }

    public function testGetPermissionsWithoutAuthentication(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('用户未认证');

        $this->jwtAuthService->getPermissions();
    }

    public function testHasPermissionWithValidPermission(): void
    {
        $token = $this->jwtAuthService->generateToken();
        $this->jwtAuthService->validateToken($token);

        $result = $this->jwtAuthService->hasPermission('meeting:create');

        $this->assertTrue($result);
    }

    public function testHasPermissionWithInvalidPermission(): void
    {
        $token = $this->jwtAuthService->generateToken();
        $this->jwtAuthService->validateToken($token);

        $result = $this->jwtAuthService->hasPermission('invalid:permission');

        $this->assertFalse($result);
    }

    public function testHasPermissionWithoutAuthentication(): void
    {
        $this->loggerService->expects($this->once())
            ->method('error')
            ->with(
                'TencentMeeting 权限检查失败',
                self::callback(function ($context) {
                    return is_array($context) && isset($context['exception']) && $context['exception'] instanceof \Throwable;
                })
            )
        ;

        $result = $this->jwtAuthService->hasPermission('meeting:create');

        $this->assertFalse($result);
    }

    public function testCheckAccessWithValidResource(): void
    {
        $token = $this->jwtAuthService->generateToken();
        $this->jwtAuthService->validateToken($token);

        $result = $this->jwtAuthService->checkAccess('meeting:create');

        $this->assertTrue($result);
    }

    public function testCheckAccessWithoutAuthentication(): void
    {
        $result = $this->jwtAuthService->checkAccess('meeting:create');

        $this->assertFalse($result);
    }

    public function testLogout(): void
    {
        // 先认证获得token
        $this->jwtAuthService->authenticate();
        $this->assertNotNull($this->jwtAuthService->getCurrentToken());

        // 退出登录
        $result = $this->jwtAuthService->logout();

        $this->assertTrue($result);
        $this->assertNull($this->jwtAuthService->getCurrentToken());
    }

    public function testSetCurrentToken(): void
    {
        $token = $this->jwtAuthService->generateToken();

        $this->loggerService->expects($this->once())
            ->method('info')
            ->with('TencentMeeting JWT Token设置成功')
        ;

        $this->jwtAuthService->setCurrentToken($token);

        $this->assertEquals($token, $this->jwtAuthService->getCurrentToken());
    }

    public function testSetCurrentTokenWithInvalidToken(): void
    {
        $this->expectException(AuthenticationException::class);

        $this->jwtAuthService->setCurrentToken('invalid.token.here');
    }

    public function testGetTokenExpirationAfterValidation(): void
    {
        $token = $this->jwtAuthService->generateToken();
        $this->jwtAuthService->validateToken($token);

        $expiration = $this->jwtAuthService->getTokenExpiration();

        $this->assertIsInt($expiration);
        $this->assertGreaterThan(time(), $expiration);
    }

    public function testGetTokenExpirationWithoutValidation(): void
    {
        $expiration = $this->jwtAuthService->getTokenExpiration();

        $this->assertNull($expiration);
    }

    public function testIsTokenExpiringSoon(): void
    {
        $token = $this->jwtAuthService->generateToken();
        $this->jwtAuthService->validateToken($token);

        // 新生成的token不应该即将过期（默认1小时有效期）
        $result = $this->jwtAuthService->isTokenExpiringSoon();

        $this->assertFalse($result);
    }

    public function testIsTokenExpiringSoonWithLargeThreshold(): void
    {
        $token = $this->jwtAuthService->generateToken();
        $this->jwtAuthService->validateToken($token);

        // 设置一个大的阈值（2小时），应该返回true
        $result = $this->jwtAuthService->isTokenExpiringSoon(7200);

        $this->assertTrue($result);
    }

    public function testAutoRefreshTokenIfNeeded(): void
    {
        $token = $this->jwtAuthService->generateToken();
        $this->jwtAuthService->validateToken($token);

        // 正常情况下不需要刷新
        $result = $this->jwtAuthService->autoRefreshTokenIfNeeded();

        $this->assertTrue($result);
    }

    public function testRevokeCurrentToken(): void
    {
        // 先获得一个token
        $this->jwtAuthService->authenticate();
        $this->assertNotNull($this->jwtAuthService->getCurrentToken());

        $this->loggerService->expects($this->once())
            ->method('info')
            ->with('TencentMeeting JWT Token已撤销')
        ;

        // 撤销当前token
        $result = $this->jwtAuthService->revokeToken();

        $this->assertTrue($result);
        $this->assertNull($this->jwtAuthService->getCurrentToken());
    }

    public function testRevokeSpecificToken(): void
    {
        $token = $this->jwtAuthService->generateToken();

        $this->loggerService->expects($this->once())
            ->method('info')
            ->with('TencentMeeting JWT Token已撤销')
        ;

        $result = $this->jwtAuthService->revokeToken($token);

        $this->assertTrue($result);
    }

    public function testRevokeTokenWithoutToken(): void
    {
        $this->loggerService->expects($this->once())
            ->method('error')
            ->with(
                'TencentMeeting JWT Token撤销失败',
                self::callback(function ($context) {
                    return is_array($context) && isset($context['exception']) && $context['exception'] instanceof \Throwable;
                })
            )
        ;

        $result = $this->jwtAuthService->revokeToken();

        $this->assertFalse($result);
    }

    public function testAuthenticateWithConfigServiceFailure(): void
    {
        // 模拟配置服务异常
        $this->configService->method('getSecretKey')->willThrowException(new \RuntimeException('Config error'));

        $this->loggerService->expects($this->once())
            ->method('error')
            ->with(
                'TencentMeeting JWT认证失败',
                self::callback(function ($context) {
                    return is_array($context) && isset($context['exception']) && $context['exception'] instanceof \Throwable;
                })
            )
        ;

        $result = $this->jwtAuthService->authenticate();

        $this->assertFalse($result);
    }
}

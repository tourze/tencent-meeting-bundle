<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Service;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Log\LoggerInterface;
use Tourze\TencentMeetingBundle\Exception\AuthenticationException;

/**
 * JWT认证服务
 *
 * 实现JWT认证机制，支持企业自建应用的认证方式
 */
class JwtAuthService implements AuthServiceInterface
{
    private string $secretKey;

    private string $issuer;

    private string $audience;

    private int $expiresIn;

    /** @var array<string, mixed>|null */
    private ?array $currentUser = null;

    private ?string $currentToken = null;

    public function __construct(
        private LoggerInterface $loggerService,
        private ?ConfigServiceInterface $configService = null,
    ) {
        $this->secretKey = $configService?->getSecretKey() ?? 'default_secret_key';
        $this->issuer = 'tencent-meeting-bundle';
        $this->audience = 'tencent-meeting-api';
        $this->expiresIn = 3600; // 1 hour
    }

    /**
     * 执行认证
     */
    public function authenticate(): bool
    {
        try {
            // 检查是否已有有效的Token
            if (null !== $this->currentToken && '' !== $this->currentToken && $this->validateToken($this->currentToken)) {
                return true;
            }

            // 从配置服务获取最新配置
            if (null !== $this->configService) {
                $this->secretKey = (string) $this->configService->getSecretKey();
            }

            // 生成新的Token
            $this->currentToken = $this->generateToken();

            return true;
        } catch (\Throwable $e) {
            $this->loggerService->error('TencentMeeting JWT认证失败', ['exception' => $e]);

            return false;
        }
    }

    /**
     * 刷新Token
     */
    public function refreshToken(): bool
    {
        try {
            if (null === $this->currentToken || '' === $this->currentToken) {
                throw new AuthenticationException('没有可刷新的Token');
            }

            // 验证当前Token是否有效
            if (!$this->validateToken($this->currentToken)) {
                throw new AuthenticationException('当前Token已失效');
            }

            // 生成新的Token
            $this->currentToken = $this->generateToken();

            return true;
        } catch (\Throwable $e) {
            $this->loggerService->error('TencentMeeting JWT Token刷新失败', ['exception' => $e]);

            return false;
        }
    }

    /**
     * 验证Token
     */
    public function validateToken(string $token): bool
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));

            // 验证Token声明
            if (!isset($decoded->iss) || $decoded->iss !== $this->issuer) {
                throw new AuthenticationException('无效的签发者');
            }

            if (!isset($decoded->aud) || $decoded->aud !== $this->audience) {
                throw new AuthenticationException('无效的接收者');
            }

            if (!isset($decoded->exp) || $decoded->exp < time()) {
                throw new AuthenticationException('Token已过期');
            }

            // 缓存用户信息
            /** @var array<string, mixed> */
            $decodedArray = (array) $decoded;
            $this->currentUser = $decodedArray;

            return true;
        } catch (ExpiredException $e) {
            throw new AuthenticationException('Token已过期', 0, $e);
        } catch (BeforeValidException $e) {
            throw new AuthenticationException('Token尚未生效', 0, $e);
        } catch (\Throwable $e) {
            throw new AuthenticationException('Token验证失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 获取用户信息
     * @return array<string, mixed>
     */
    public function getUserInfo(): array
    {
        if (null === $this->currentUser) {
            throw new AuthenticationException('用户未认证');
        }

        return $this->currentUser;
    }

    /**
     * 获取权限列表
     * @return array<string>
     */
    public function getPermissions(): array
    {
        if (null === $this->currentUser) {
            throw new AuthenticationException('用户未认证');
        }

        $permissions = $this->currentUser['permissions'] ?? [];

        return is_array($permissions) ? array_values(array_filter($permissions, 'is_string')) : [];
    }

    /**
     * 检查是否有特定权限
     */
    public function hasPermission(string $permission): bool
    {
        try {
            $permissions = $this->getPermissions();

            return in_array($permission, $permissions, true);
        } catch (\Throwable $e) {
            $this->loggerService->error('TencentMeeting 权限检查失败', ['exception' => $e]);

            return false;
        }
    }

    /**
     * 检查资源访问权限
     */
    public function checkAccess(string $resource): bool
    {
        try {
            if (null === $this->currentUser) {
                return false;
            }

            // 简单的权限检查逻辑
            $userId = $this->currentUser['sub'] ?? null;
            if (null === $userId || '' === $userId) {
                return false;
            }

            // 检查资源权限
            return $this->hasPermission($resource);
        } catch (\Throwable $e) {
            $this->loggerService->error('TencentMeeting 资源访问检查失败', ['exception' => $e]);

            return false;
        }
    }

    /**
     * 退出登录
     */
    public function logout(): bool
    {
        // 清除当前Token和用户信息
        $this->currentToken = null;
        $this->currentUser = null;

        return true;
    }

    /**
     * 生成JWT Token
     */
    public function generateToken(): string
    {
        $now = time();
        $payload = [
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'iat' => $now,
            'exp' => $now + $this->expiresIn,
            'sub' => 'user_' . uniqid(),
            'permissions' => ['meeting:create', 'meeting:read', 'meeting:update', 'meeting:delete'],
            'user_info' => [
                'user_id' => 'user_' . uniqid(),
                'username' => 'test_user',
                'email' => 'test@example.com',
            ],
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    /**
     * 获取当前Token
     */
    public function getCurrentToken(): ?string
    {
        return $this->currentToken;
    }

    /**
     * 设置当前Token
     */
    public function setCurrentToken(string $token): void
    {
        $this->currentToken = $token;

        // 验证并解析Token
        if ($this->validateToken($token)) {
            $this->loggerService->info('TencentMeeting JWT Token设置成功');
        }
    }

    /**
     * 获取Token过期时间
     */
    public function getTokenExpiration(): ?int
    {
        if (null === $this->currentUser) {
            return null;
        }

        $exp = $this->currentUser['exp'] ?? null;

        return is_int($exp) ? $exp : null;
    }

    /**
     * 检查Token是否即将过期
     */
    public function isTokenExpiringSoon(int $threshold = 300): bool
    {
        $expiration = $this->getTokenExpiration();
        if (null === $expiration) {
            return false;
        }

        return ($expiration - time()) < $threshold;
    }

    /**
     * 自动刷新Token（如果即将过期）
     */
    public function autoRefreshTokenIfNeeded(): bool
    {
        if ($this->isTokenExpiringSoon()) {
            return $this->refreshToken();
        }

        return true;
    }

    /**
     * 撤销Token
     */
    public function revokeToken(?string $token = null): bool
    {
        try {
            if (null === $token) {
                $token = $this->currentToken;
            }

            if (null === $token || '' === $token) {
                throw new AuthenticationException('没有可撤销的Token');
            }

            // 在实际应用中，这里应该将Token添加到黑名单
            // 这里简单地清除当前Token
            if ($token === $this->currentToken) {
                $this->currentToken = null;
                $this->currentUser = null;
            }

            $this->loggerService->info('TencentMeeting JWT Token已撤销');

            return true;
        } catch (\Throwable $e) {
            $this->loggerService->error('TencentMeeting JWT Token撤销失败', ['exception' => $e]);

            return false;
        }
    }
}

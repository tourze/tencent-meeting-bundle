<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Service;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Tourze\TencentMeetingBundle\Exception\AuthenticationException;
use Tourze\TencentMeetingBundle\Exception\SignatureException;

/**
 * OAuth2.0认证服务
 *
 * 实现OAuth2.0认证机制，支持第三方应用的认证方式
 */
#[WithMonologChannel(channel: 'tencent_meeting')]
final class OAuth2AuthService implements AuthServiceInterface
{
    private string $clientId;

    private string $redirectUri;

    private string $authorizationUrl;

    /** @var array<string> */
    private array $scopes;

    private ?string $accessToken = null;

    private ?string $refreshToken = null;

    private ?int $expiresIn = null;

    /** @var array<string, mixed>|null */
    private ?array $currentUser = null;

    public function __construct(
        private CacheService $cacheService,
        private LoggerInterface $loggerService,
    ) {
        $this->clientId = 'default_client_id';
        $this->redirectUri = 'https://your-app.com/callback';
        $this->authorizationUrl = 'https://api.meeting.qq.com/oauth2/authorize';
        $this->scopes = ['meeting:read', 'meeting:write', 'user:read'];
    }

    /**
     * 执行认证
     */
    public function authenticate(): bool
    {
        try {
            // 检查是否已有有效的Access Token
            if (null !== $this->accessToken && $this->validateToken($this->accessToken)) {
                return true;
            }

            // 检查是否可以使用Refresh Token
            if (null !== $this->refreshToken) {
                return $this->refreshToken();
            }

            // 需要重新进行OAuth2授权流程
            return false;
        } catch (\Throwable $e) {
            $this->loggerService->error('TencentMeeting OAuth2认证失败', ['exception' => $e]);

            return false;
        }
    }

    /**
     * 刷新Token
     */
    public function refreshToken(): bool
    {
        try {
            if (null === $this->refreshToken) {
                throw new AuthenticationException('没有可刷新的Refresh Token');
            }

            $newTokens = $this->refreshAccessToken($this->refreshToken);

            $this->accessToken = is_string($newTokens['access_token']) ? $newTokens['access_token'] : null;
            $this->refreshToken = isset($newTokens['refresh_token']) && is_string($newTokens['refresh_token']) ? $newTokens['refresh_token'] : $this->refreshToken;
            $this->expiresIn = isset($newTokens['expires_in']) && is_int($newTokens['expires_in']) ? $newTokens['expires_in'] : null;

            // 缓存新的Token
            $this->cacheTokens();

            return true;
        } catch (\Throwable $e) {
            $this->loggerService->error('TencentMeeting OAuth2 Token刷新失败', ['exception' => $e]);

            return false;
        }
    }

    /**
     * 验证Token
     */
    public function validateToken(string $token): bool
    {
        // 检查Token是否为空
        if ('' === $token) {
            return false;
        }

        // 检查Token是否过期
        if (null !== $this->expiresIn && time() >= $this->expiresIn) {
            return false;
        }

        // 可以添加更多的Token验证逻辑
        // 例如：检查Token格式、验证签名等

        return true;
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
            $this->loggerService->error('TencentMeeting OAuth2权限检查失败', ['exception' => $e]);

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
            if (null === $userId) {
                return false;
            }

            // 检查资源权限
            return $this->hasPermission($resource);
        } catch (\Throwable $e) {
            $this->loggerService->error('TencentMeeting OAuth2资源访问检查失败', ['exception' => $e]);

            return false;
        }
    }

    /**
     * 退出登录
     */
    public function logout(): bool
    {
        try {
            // 注意：在实际实现中，应该撤销Token，但当前示例代码直接返回true

            // 清除当前Token和用户信息
            $this->accessToken = null;
            $this->refreshToken = null;
            $this->expiresIn = null;
            $this->currentUser = null;

            // 清除缓存
            $this->clearCachedTokens();

            return true;
        } catch (\Throwable $e) {
            $this->loggerService->error('TencentMeeting OAuth2退出登录失败', ['exception' => $e]);

            return false;
        }
    }

    /**
     * 获取Access Token
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * 获取Refresh Token
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * 获取授权URL
     */
    public function getAuthorizationUrl(): string
    {
        $params = [
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => implode(' ', $this->scopes),
            'state' => bin2hex(random_bytes(16)),
        ];

        return $this->authorizationUrl . '?' . http_build_query($params);
    }

    /**
     * 处理授权回调
     */
    public function handleAuthorizationCallback(string $code, string $state): bool
    {
        try {
            // 验证state参数（防止CSRF攻击）
            if (!$this->validateState($state)) {
                throw new AuthenticationException('无效的state参数');
            }

            // 使用授权码获取Access Token
            $tokens = $this->exchangeCodeForToken($code);

            $this->accessToken = is_string($tokens['access_token']) ? $tokens['access_token'] : null;
            $this->refreshToken = isset($tokens['refresh_token']) && is_string($tokens['refresh_token']) ? $tokens['refresh_token'] : null;
            $this->expiresIn = isset($tokens['expires_in']) && is_numeric($tokens['expires_in']) ? time() + (int) $tokens['expires_in'] : null;

            // 获取用户信息
            if (null !== $this->accessToken && '' !== trim($this->accessToken)) {
                $this->currentUser = $this->getUserInfoFromToken($this->accessToken);
            }

            // 缓存Token
            $this->cacheTokens();

            return true;
        } catch (\Throwable $e) {
            $this->loggerService->error('TencentMeeting OAuth2授权回调处理失败', ['exception' => $e]);

            return false;
        }
    }

    /**
     * 使用授权码交换Access Token
     * @return array<string, string|int>
     */
    private function exchangeCodeForToken(string $code): array
    {
        // 这里应该实现实际的OAuth2 Token交换逻辑
        // 由于这是示例代码，我们返回模拟数据

        return [
            'access_token' => 'mock_access_token_' . bin2hex(random_bytes(16)),
            'refresh_token' => 'mock_refresh_token_' . bin2hex(random_bytes(16)),
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * 刷新Access Token
     */
    /**
     * @return array<string, string|int>
     */
    private function refreshAccessToken(string $refreshToken): array
    {
        // 这里应该实现实际的OAuth2 Token刷新逻辑
        // 由于这是示例代码，我们返回模拟数据

        return [
            'access_token' => 'mock_new_access_token_' . bin2hex(random_bytes(16)),
            'refresh_token' => 'mock_new_refresh_token_' . bin2hex(random_bytes(16)),
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * 验证state参数
     */
    private function validateState(string $state): bool
    {
        // 这里应该实现实际的state验证逻辑
        // 由于这是示例代码，我们直接返回true

        return true;
    }

    /**
     * 从Token获取用户信息
     */
    /**
     * @return array<string, mixed>
     */
    private function getUserInfoFromToken(string $token): array
    {
        // 这里应该实现实际的用户信息获取逻辑
        // 由于这是示例代码，我们返回模拟数据

        return [
            'sub' => 'user_' . uniqid(),
            'username' => 'oauth2_user',
            'email' => 'oauth2_user@example.com',
            'permissions' => ['meeting:read', 'meeting:write'],
            'scopes' => $this->scopes,
        ];
    }

    /**
     * 缓存Token
     */
    private function cacheTokens(): void
    {
        if (null !== $this->accessToken && null !== $this->expiresIn) {
            $this->cacheService->cacheConfig('oauth2_access_token', $this->accessToken, $this->expiresIn - time());
        }

        if (null !== $this->refreshToken) {
            $this->cacheService->cacheConfig('oauth2_refresh_token', $this->refreshToken, 86400); // 24小时
        }
    }

    /**
     * 清除缓存的Token
     */
    private function clearCachedTokens(): void
    {
        $this->cacheService->invalidateConfigCache();
    }

    /**
     * 检查Token是否过期
     */
    public function isTokenExpired(): bool
    {
        return null !== $this->expiresIn && time() >= $this->expiresIn;
    }

    /**
     * 获取权限范围
     * @return array<string>
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * 设置权限范围
     * @param array<string> $scopes
     */
    public function setScopes(array $scopes): void
    {
        $this->scopes = $scopes;
    }

    /**
     * 获取Token过期时间
     */
    public function getTokenExpiration(): ?int
    {
        return $this->expiresIn;
    }

    /**
     * 检查Token是否即将过期
     */
    public function isTokenExpiringSoon(int $threshold = 300): bool
    {
        return null !== $this->expiresIn && ($this->expiresIn - time()) < $threshold;
    }
}

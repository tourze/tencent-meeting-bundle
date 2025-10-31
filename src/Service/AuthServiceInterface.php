<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Service;

/**
 * 认证服务接口
 *
 * 定义JWT和OAuth2.0认证的统一接口，为Bundle提供认证和授权能力
 */
interface AuthServiceInterface
{
    /**
     * 执行认证
     *
     * @return bool 认证是否成功
     */
    public function authenticate(): bool;

    /**
     * 刷新Token
     *
     * @return bool 刷新是否成功
     */
    public function refreshToken(): bool;

    /**
     * 验证Token
     *
     * @param string $token JWT Token
     * @return bool 验证是否通过
     */
    public function validateToken(string $token): bool;

    /**
     * 获取用户信息
     *
     * @return array<string, mixed> 用户信息
     */
    public function getUserInfo(): array;

    /**
     * 获取权限列表
     *
     * @return array<string> 权限列表
     */
    public function getPermissions(): array;

    /**
     * 检查是否有特定权限
     *
     * @param string $permission 权限名称
     * @return bool 是否有权限
     */
    public function hasPermission(string $permission): bool;

    /**
     * 检查资源访问权限
     *
     * @param string $resource 资源名称
     * @return bool 是否有访问权限
     */
    public function checkAccess(string $resource): bool;

    /**
     * 退出登录
     *
     * @return bool 退出是否成功
     */
    public function logout(): bool;
}

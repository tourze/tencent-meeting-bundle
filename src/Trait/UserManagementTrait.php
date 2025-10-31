<?php

namespace Tourze\TencentMeetingBundle\Trait;

/**
 * 用户管理操作trait
 *
 * 提供用户管理相关的操作，如设置管理、状态管理等
 */
trait UserManagementTrait
{
    /**
     * 获取用户设置
     *
     * @param string $userId 用户ID
     * @return array<string, mixed> 用户设置
     */
    public function getUserSettings(string $userId): array
    {
        try {
            $response = $this->get('/v1/users/' . urlencode($userId) . '/settings');

            return $this->formatUserResponse($response);
        } catch (\Throwable $e) {
            return $this->handleUserError($e, 'getUserSettings', ['user_id' => $userId]);
        }
    }

    /**
     * 更新用户设置
     *
     * @param string $userId 用户ID
     * @param array<string, mixed> $settings 用户设置
     * @return array<string, mixed> 更新结果
     */
    public function updateUserSettings(string $userId, array $settings): array
    {
        try {
            // 验证设置数据
            $this->validateUserSettings($settings);

            $response = $this->put('/v1/users/' . urlencode($userId) . '/settings', $settings);

            return $this->formatUserResponse($response);
        } catch (\Throwable $e) {
            return $this->handleUserError($e, 'updateUserSettings', [
                'user_id' => $userId,
                'settings' => $settings,
            ]);
        }
    }

    /**
     * 激活用户
     *
     * @param string $userId 用户ID
     * @return array<string, mixed> 激活结果
     */
    public function activateUser(string $userId): array
    {
        try {
            $response = $this->post('/v1/users/' . urlencode($userId) . '/activate', []);

            return $this->formatUserResponse($response);
        } catch (\Throwable $e) {
            return $this->handleUserError($e, 'activateUser', ['user_id' => $userId]);
        }
    }

    /**
     * 停用用户
     *
     * @param string $userId 用户ID
     * @return array<string, mixed> 停用结果
     */
    public function deactivateUser(string $userId): array
    {
        try {
            $response = $this->post('/v1/users/' . urlencode($userId) . '/deactivate', []);

            return $this->formatUserResponse($response);
        } catch (\Throwable $e) {
            return $this->handleUserError($e, 'deactivateUser', ['user_id' => $userId]);
        }
    }

    /**
     * 重置用户密码
     *
     * @param string $userId 用户ID
     * @return array<string, mixed> 重置结果
     */
    public function resetUserPassword(string $userId): array
    {
        try {
            $response = $this->post('/v1/users/' . urlencode($userId) . '/reset-password', []);

            return $this->formatUserResponse($response);
        } catch (\Throwable $e) {
            return $this->handleUserError($e, 'resetUserPassword', ['user_id' => $userId]);
        }
    }
}

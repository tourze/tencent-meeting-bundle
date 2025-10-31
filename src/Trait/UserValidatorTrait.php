<?php

namespace Tourze\TencentMeetingBundle\Trait;

use Tourze\TencentMeetingBundle\Exception\ApiException;

/**
 * 用户验证trait
 *
 * 提供用户数据验证的通用方法，降低验证逻辑的复杂度
 */
trait UserValidatorTrait
{
    /**
     * 验证用户数据
     *
     * @param array<string, mixed> $userData 用户数据
     * @param bool $isUpdate 是否为更新操作
     */
    private function validateUserData(array $userData, bool $isUpdate = false): void
    {
        $this->validateUserRequiredFields($userData, $isUpdate);

        if (isset($userData['email'])) {
            $this->validateUserEmail($userData['email']);
        }

        if (isset($userData['phone'])) {
            $this->validateUserPhone($userData['phone']);
        }

        if (isset($userData['role'])) {
            $this->validateUserRole($userData['role']);
        }
    }

    /**
     * 验证用户设置
     *
     * @param array<string, mixed> $settings 用户设置
     */
    private function validateUserSettings(array $settings): void
    {
        $validSettings = $this->getValidUserSettings();

        foreach (array_keys($settings) as $setting) {
            if (!in_array($setting, $validSettings, true)) {
                throw new ApiException("无效的用户设置: {$setting}");
            }

            if (!is_bool($settings[$setting])) {
                throw new ApiException("用户设置 {$setting} 必须是布尔值");
            }
        }
    }

    /**
     * 验证用户必需字段
     *
     * @param array<string, mixed> $userData 用户数据
     * @param bool $isUpdate 是否为更新操作
     */
    private function validateUserRequiredFields(array $userData, bool $isUpdate): void
    {
        if ($isUpdate) {
            return;
        }

        $requiredFields = ['username', 'email', 'phone'];

        foreach ($requiredFields as $field) {
            if (!isset($userData[$field]) || '' === $userData[$field]) {
                throw new ApiException("用户数据缺少必需字段: {$field}");
            }
        }
    }

    /**
     * 验证邮箱格式
     *
     * @param mixed $email 邮箱值
     */
    private function validateUserEmail(mixed $email): void
    {
        if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ApiException('无效的邮箱格式');
        }
    }

    /**
     * 验证手机号格式
     *
     * @param mixed $phone 手机号值
     */
    private function validateUserPhone(mixed $phone): void
    {
        if (!is_string($phone) && !is_numeric($phone)) {
            throw new ApiException('手机号必须是字符串或数字');
        }

        if (1 !== preg_match('/^1[3-9]\d{9}$/', (string) $phone)) {
            throw new ApiException('无效的手机号格式');
        }
    }

    /**
     * 验证用户角色
     *
     * @param mixed $role 角色值
     */
    private function validateUserRole(mixed $role): void
    {
        $validRoles = ['admin', 'user', 'guest'];

        if (!in_array($role, $validRoles, true)) {
            throw new ApiException('无效的用户角色');
        }
    }

    /**
     * 获取有效的用户设置列表
     *
     * @return array<string> 有效设置列表
     */
    private function getValidUserSettings(): array
    {
        return [
            'email_notification', 'sms_notification', 'meeting_reminder',
            'auto_join_mic', 'auto_join_camera', 'waiting_room',
        ];
    }
}

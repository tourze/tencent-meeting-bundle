<?php

namespace Tourze\TencentMeetingBundle\Trait;

/**
 * 用户关联数据trait
 *
 * 提供用户关联数据的查询操作，如部门、权限、会议、录制等
 */
trait UserRelatedDataTrait
{
    /**
     * 获取用户部门
     *
     * @param string $userId 用户ID
     * @return array<string, mixed> 部门信息
     */
    public function getUserDepartments(string $userId): array
    {
        try {
            $response = $this->get('/v1/users/' . urlencode($userId) . '/departments');

            return $this->formatUserResponse($response);
        } catch (\Throwable $e) {
            return $this->handleUserError($e, 'getUserDepartments', ['user_id' => $userId]);
        }
    }

    /**
     * 获取用户权限
     *
     * @param string $userId 用户ID
     * @return array<string, mixed> 权限信息
     */
    public function getUserPermissions(string $userId): array
    {
        try {
            $response = $this->get('/v1/users/' . urlencode($userId) . '/permissions');

            return $this->formatUserResponse($response);
        } catch (\Throwable $e) {
            return $this->handleUserError($e, 'getUserPermissions', ['user_id' => $userId]);
        }
    }

    /**
     * 获取用户会议
     *
     * @param string $userId 用户ID
     * @param array<string, mixed> $filters 过滤条件
     * @return array<string, mixed> 会议列表
     */
    public function getUserMeetings(string $userId, array $filters = []): array
    {
        try {
            // 构建查询参数
            $queryParams = $this->buildUserMeetingParams($filters);

            $response = $this->get('/v1/users/' . urlencode($userId) . '/meetings?' . http_build_query($queryParams));

            return $this->formatUserResponse($response);
        } catch (\Throwable $e) {
            return $this->handleUserError($e, 'getUserMeetings', [
                'user_id' => $userId,
                'filters' => $filters,
            ]);
        }
    }

    /**
     * 获取用户录制
     *
     * @param string $userId 用户ID
     * @param array<string, mixed> $filters 过滤条件
     * @return array<string, mixed> 录制列表
     */
    public function getUserRecordings(string $userId, array $filters = []): array
    {
        try {
            // 构建查询参数
            $queryParams = $this->buildUserRecordingParams($filters);

            $response = $this->get('/v1/users/' . urlencode($userId) . '/recordings?' . http_build_query($queryParams));

            return $this->formatUserResponse($response);
        } catch (\Throwable $e) {
            return $this->handleUserError($e, 'getUserRecordings', [
                'user_id' => $userId,
                'filters' => $filters,
            ]);
        }
    }
}

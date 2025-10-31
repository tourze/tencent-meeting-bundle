<?php

namespace Tourze\TencentMeetingBundle\Trait;

use Tourze\TencentMeetingBundle\Exception\ApiException;
use Tourze\TencentMeetingBundle\Exception\AuthenticationException;

/**
 * 用户辅助方法trait
 *
 * 提供用户相关的辅助方法，如参数构建、响应格式化、错误处理等
 */
trait UserHelperTrait
{
    /**
     * 构建用户参数
     *
     * @param array<string, mixed> $userData 用户数据
     * @return array<string, mixed> 请求参数
     */
    private function buildUserParams(array $userData): array
    {
        $params = [];

        // 基本用户信息
        $mapping = [
            'username' => 'username',
            'email' => 'email',
            'phone' => 'phone',
            'name' => 'name',
            'role' => 'role',
            'department_id' => 'department_id',
            'position' => 'position',
            'avatar' => 'avatar',
        ];

        foreach ($mapping as $dataKey => $paramKey) {
            if (isset($userData[$dataKey])) {
                $params[$paramKey] = $userData[$dataKey];
            }
        }

        // 用户设置
        if (isset($userData['settings'])) {
            $params['settings'] = $userData['settings'];
        }

        return $params;
    }

    /**
     * 构建用户列表参数
     *
     * @param array<string, mixed> $filters 过滤条件
     * @return array<string, mixed> 查询参数
     */
    private function buildUserListParams(array $filters): array
    {
        $params = [];

        // 分页参数
        if (isset($filters['page']) && (is_int($filters['page']) || is_numeric($filters['page']))) {
            $params['page'] = (int) $filters['page'];
        }

        if (isset($filters['page_size']) && (is_int($filters['page_size']) || is_numeric($filters['page_size']))) {
            $params['page_size'] = (int) $filters['page_size'];
        }

        // 过滤条件
        if (isset($filters['department_id'])) {
            $params['department_id'] = $filters['department_id'];
        }

        if (isset($filters['role'])) {
            $params['role'] = $filters['role'];
        }

        if (isset($filters['status'])) {
            $params['status'] = $filters['status'];
        }

        return $params;
    }

    /**
     * 构建用户会议参数
     *
     * @param array<string, mixed> $filters 过滤条件
     * @return array<string, mixed> 查询参数
     */
    private function buildUserMeetingParams(array $filters): array
    {
        $params = [];

        // 分页参数
        if (isset($filters['page']) && (is_int($filters['page']) || is_numeric($filters['page']))) {
            $params['page'] = (int) $filters['page'];
        }

        if (isset($filters['page_size']) && (is_int($filters['page_size']) || is_numeric($filters['page_size']))) {
            $params['page_size'] = (int) $filters['page_size'];
        }

        // 过滤条件
        if (isset($filters['status'])) {
            $params['status'] = $filters['status'];
        }

        if (isset($filters['start_time']) && (is_int($filters['start_time']) || is_numeric($filters['start_time']))) {
            $params['start_time'] = (int) $filters['start_time'];
        }

        if (isset($filters['end_time']) && (is_int($filters['end_time']) || is_numeric($filters['end_time']))) {
            $params['end_time'] = (int) $filters['end_time'];
        }

        return $params;
    }

    /**
     * 构建用户录制参数
     *
     * @param array<string, mixed> $filters 过滤条件
     * @return array<string, mixed> 查询参数
     */
    private function buildUserRecordingParams(array $filters): array
    {
        $params = [];

        // 分页参数
        if (isset($filters['page']) && (is_int($filters['page']) || is_numeric($filters['page']))) {
            $params['page'] = (int) $filters['page'];
        }

        if (isset($filters['page_size']) && (is_int($filters['page_size']) || is_numeric($filters['page_size']))) {
            $params['page_size'] = (int) $filters['page_size'];
        }

        // 过滤条件
        if (isset($filters['status'])) {
            $params['status'] = $filters['status'];
        }

        if (isset($filters['start_time']) && (is_int($filters['start_time']) || is_numeric($filters['start_time']))) {
            $params['start_time'] = (int) $filters['start_time'];
        }

        if (isset($filters['end_time']) && (is_int($filters['end_time']) || is_numeric($filters['end_time']))) {
            $params['end_time'] = (int) $filters['end_time'];
        }

        return $params;
    }

    /**
     * 构建用户搜索参数
     *
     * @param array<string, mixed> $searchParams 搜索参数
     * @return array<string, mixed> 查询参数
     */
    private function buildUserSearchParams(array $searchParams): array
    {
        $params = [];

        // 搜索关键词
        if (isset($searchParams['keyword'])) {
            $params['keyword'] = $searchParams['keyword'];
        }

        // 搜索范围
        if (isset($searchParams['search_fields'])) {
            $params['search_fields'] = $searchParams['search_fields'];
        }

        // 分页参数
        if (isset($searchParams['page']) && (is_int($searchParams['page']) || is_numeric($searchParams['page']))) {
            $params['page'] = (int) $searchParams['page'];
        }

        if (isset($searchParams['page_size']) && (is_int($searchParams['page_size']) || is_numeric($searchParams['page_size']))) {
            $params['page_size'] = (int) $searchParams['page_size'];
        }

        return $params;
    }

    /**
     * 格式化用户响应
     *
     * @param array<string, mixed> $response 原始响应
     * @return array<string, mixed> 格式化后的响应
     */
    private function formatUserResponse(array $response): array
    {
        if (!isset($response['success']) || !is_bool($response['success']) || !$response['success']) {
            throw new ApiException('用户操作失败');
        }

        $formatted = [
            'success' => true,
            'user_id' => $response['user_id'] ?? null,
            'username' => $response['username'] ?? null,
            'email' => $response['email'] ?? null,
            'phone' => $response['phone'] ?? null,
            'name' => $response['name'] ?? null,
            'role' => $response['role'] ?? null,
            'status' => $response['status'] ?? null,
            'created_at' => $response['created_at'] ?? null,
            'updated_at' => $response['updated_at'] ?? null,
        ];

        // 添加额外信息
        if (isset($response['departments'])) {
            $formatted['departments'] = $response['departments'];
        }

        if (isset($response['permissions'])) {
            $formatted['permissions'] = $response['permissions'];
        }

        if (isset($response['settings'])) {
            $formatted['settings'] = $response['settings'];
        }

        if (isset($response['message'])) {
            $formatted['message'] = $response['message'];
        }

        // 如果是批量创建响应，添加批量创建信息
        if (isset($response['created_users'])) {
            $formatted['created_users'] = $response['created_users'];
        }

        if (isset($response['failed_users'])) {
            $formatted['failed_users'] = $response['failed_users'];
        }

        // 如果是列表响应，添加分页信息
        if (isset($response['users'])) {
            $formatted['users'] = $response['users'];
            $formatted['pagination'] = $response['pagination'] ?? [];
        }

        if (isset($response['meetings'])) {
            $formatted['meetings'] = $response['meetings'];
            $formatted['pagination'] = $response['pagination'] ?? [];
        }

        if (isset($response['recordings'])) {
            $formatted['recordings'] = $response['recordings'];
            $formatted['pagination'] = $response['pagination'] ?? [];
        }

        return $formatted;
    }

    /**
     * 处理用户错误
     *
     * @param \Throwable $exception 异常
     * @param string $operation 操作类型
     * @param array<string, mixed> $context 上下文信息
     * @return array<string, mixed> 错误响应
     */
    private function handleUserError(\Throwable $exception, string $operation, array $context): array
    {
        $this->loggerService->error('TencentMeeting 用户操作失败: ' . $operation, [
            'exception' => $exception,
            'context' => $context,
        ]);

        // 根据异常类型处理不同的错误情况
        if ($exception instanceof ApiException) {
            return [
                'success' => false,
                'error' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'operation' => $operation,
                'context' => $context,
            ];
        }

        if ($exception instanceof AuthenticationException) {
            return [
                'success' => false,
                'error' => '认证失败',
                'code' => 401,
                'operation' => $operation,
                'context' => $context,
            ];
        }

        // 默认错误处理
        return [
            'success' => false,
            'error' => '用户操作失败: ' . $exception->getMessage(),
            'code' => 500,
            'operation' => $operation,
            'context' => $context,
        ];
    }
}

<?php

namespace Tourze\TencentMeetingBundle\Trait;

/**
 * 用户CRUD操作trait
 *
 * 提供用户基础的增删改查操作
 */
trait UserCrudOperationsTrait
{
    /**
     * 获取用户信息
     *
     * @param string $userId 用户ID
     * @return array<string, mixed> 用户信息
     */
    public function getUser(string $userId): array
    {
        try {
            $response = $this->get('/v1/users/' . urlencode($userId));

            return $this->formatUserResponse($response);
        } catch (\Throwable $e) {
            return $this->handleUserError($e, 'getUser', ['user_id' => $userId]);
        }
    }

    /**
     * 创建用户
     *
     * @param array<string, mixed> $userData 用户数据
     * @return array<string, mixed> 创建结果
     */
    public function createUser(array $userData): array
    {
        try {
            // 验证用户数据
            $this->validateUserData($userData);

            // 构建请求参数
            $params = $this->buildUserParams($userData);

            // 发送请求
            $response = $this->post('/v1/users', $params);

            return $this->formatUserResponse($response);
        } catch (\Throwable $e) {
            return $this->handleUserError($e, 'createUser', $userData);
        }
    }

    /**
     * 更新用户
     *
     * @param string $userId 用户ID
     * @param array<string, mixed> $updateData 更新数据
     * @return array<string, mixed> 更新结果
     */
    public function updateUser(string $userId, array $updateData): array
    {
        try {
            // 验证更新数据
            $this->validateUserData($updateData, true);

            // 构建请求参数
            $params = $this->buildUserParams($updateData);

            // 发送请求
            $response = $this->put('/v1/users/' . urlencode($userId), $params);

            return $this->formatUserResponse($response);
        } catch (\Throwable $e) {
            return $this->handleUserError($e, 'updateUser', [
                'user_id' => $userId,
                'update_data' => $updateData,
            ]);
        }
    }

    /**
     * 删除用户
     *
     * @param string $userId 用户ID
     * @return array<string, mixed> 删除结果
     */
    public function deleteUser(string $userId): array
    {
        try {
            $response = $this->delete('/v1/users/' . urlencode($userId));

            return $this->formatUserResponse($response);
        } catch (\Throwable $e) {
            return $this->handleUserError($e, 'deleteUser', ['user_id' => $userId]);
        }
    }

    /**
     * 获取用户列表
     *
     * @param array<string, mixed> $filters 过滤条件
     * @return array<string, mixed> 用户列表
     */
    public function listUsers(array $filters = []): array
    {
        try {
            // 构建查询参数
            $queryParams = $this->buildUserListParams($filters);

            // 发送请求
            $response = $this->get('/v1/users?' . http_build_query($queryParams));

            return $this->formatUserResponse($response);
        } catch (\Throwable $e) {
            return $this->handleUserError($e, 'listUsers', $filters);
        }
    }

    /**
     * 搜索用户
     *
     * @param array<string, mixed> $searchParams 搜索参数
     * @return array<string, mixed> 搜索结果
     */
    public function searchUsers(array $searchParams): array
    {
        try {
            $queryParams = $this->buildUserSearchParams($searchParams);

            $response = $this->get('/v1/users/search?' . http_build_query($queryParams));

            return $this->formatUserResponse($response);
        } catch (\Throwable $e) {
            return $this->handleUserError($e, 'searchUsers', $searchParams);
        }
    }

    /**
     * 批量创建用户
     *
     * @param array<int, array<string, mixed>> $usersData 用户数据数组
     * @return array<string, mixed> 批量创建结果
     */
    public function batchCreateUsers(array $usersData): array
    {
        try {
            // 验证所有用户数据
            foreach ($usersData as $userData) {
                $this->validateUserData($userData);
            }

            $response = $this->post('/v1/users/batch', ['users' => $usersData]);

            return $this->formatUserResponse($response);
        } catch (\Throwable $e) {
            return $this->handleUserError($e, 'batchCreateUsers', ['users_data' => $usersData]);
        }
    }
}

<?php

namespace Tourze\TencentMeetingBundle\Service;

use Tourze\TencentMeetingBundle\Exception\ApiException;
use Tourze\TencentMeetingBundle\Exception\AuthenticationException;

/**
 * 会议室客户端
 *
 * 提供会议室管理的完整API封装，包括创建、查询、更新、删除等操作
 */
class RoomClient extends BaseClient
{
    /**
     * 获取会议室信息
     *
     * @param string $roomId 会议室ID
     * @return array<string, mixed> 会议室信息
     */
    public function getRoom(string $roomId): array
    {
        try {
            $response = $this->get('/v1/rooms/' . urlencode($roomId));

            return $this->formatRoomResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRoomError($e, 'getRoom', ['room_id' => $roomId]);
        }
    }

    /**
     * 创建会议室
     *
     * @param array<string, mixed> $roomData 会议室数据
     * @return array<string, mixed> 创建结果
     */
    public function createRoom(array $roomData): array
    {
        try {
            // 验证会议室数据
            $this->validateRoomData($roomData);

            // 构建请求参数
            $params = $this->buildRoomParams($roomData);

            // 发送请求
            $response = $this->post('/v1/rooms', $params);

            return $this->formatRoomResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRoomError($e, 'createRoom', $roomData);
        }
    }

    /**
     * 更新会议室
     *
     * @param string $roomId 会议室ID
     * @param array<string, mixed> $updateData 更新数据
     * @return array<string, mixed> 更新结果
     */
    public function updateRoom(string $roomId, array $updateData): array
    {
        try {
            // 验证更新数据
            $this->validateRoomData($updateData, true);

            // 构建请求参数
            $params = $this->buildRoomParams($updateData);

            // 发送请求
            $response = $this->put('/v1/rooms/' . urlencode($roomId), $params);

            return $this->formatRoomResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRoomError($e, 'updateRoom', [
                'room_id' => $roomId,
                'update_data' => $updateData,
            ]);
        }
    }

    /**
     * 删除会议室
     *
     * @param string $roomId 会议室ID
     * @return array<string, mixed> 删除结果
     */
    public function deleteRoom(string $roomId): array
    {
        try {
            $response = $this->delete('/v1/rooms/' . urlencode($roomId));

            return $this->formatRoomResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRoomError($e, 'deleteRoom', ['room_id' => $roomId]);
        }
    }

    /**
     * 获取会议室列表
     *
     * @param array<string, mixed> $filters 过滤条件
     * @return array<string, mixed> 会议室列表
     */
    public function listRooms(array $filters = []): array
    {
        try {
            // 构建查询参数
            $queryParams = $this->buildRoomListParams($filters);

            // 发送请求
            $response = $this->get('/v1/rooms?' . http_build_query($queryParams));

            return $this->formatRoomResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRoomError($e, 'listRooms', $filters);
        }
    }

    /**
     * 获取会议室状态
     *
     * @param string $roomId 会议室ID
     * @return array<string, mixed> 会议室状态
     */
    public function getRoomStatus(string $roomId): array
    {
        try {
            $response = $this->get('/v1/rooms/' . urlencode($roomId) . '/status');

            return $this->formatRoomResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRoomError($e, 'getRoomStatus', ['room_id' => $roomId]);
        }
    }

    /**
     * 获取会议室设置
     *
     * @param string $roomId 会议室ID
     * @return array<string, mixed> 会议室设置
     */
    public function getRoomSettings(string $roomId): array
    {
        try {
            $response = $this->get('/v1/rooms/' . urlencode($roomId) . '/settings');

            return $this->formatRoomResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRoomError($e, 'getRoomSettings', ['room_id' => $roomId]);
        }
    }

    /**
     * 更新会议室设置
     *
     * @param string $roomId 会议室ID
     * @param array<string, mixed> $settings 会议室设置
     * @return array<string, mixed> 更新结果
     */
    public function updateRoomSettings(string $roomId, array $settings): array
    {
        try {
            // 验证设置数据
            $this->validateRoomSettings($settings);

            $response = $this->put('/v1/rooms/' . urlencode($roomId) . '/settings', $settings);

            return $this->formatRoomResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRoomError($e, 'updateRoomSettings', [
                'room_id' => $roomId,
                'settings' => $settings,
            ]);
        }
    }

    /**
     * 获取会议室会议
     *
     * @param string $roomId 会议室ID
     * @param array<string, mixed> $filters 过滤条件
     * @return array<string, mixed> 会议列表
     */
    public function getRoomMeetings(string $roomId, array $filters = []): array
    {
        try {
            // 构建查询参数
            $queryParams = $this->buildRoomMeetingParams($filters);

            $response = $this->get('/v1/rooms/' . urlencode($roomId) . '/meetings?' . http_build_query($queryParams));

            return $this->formatRoomResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRoomError($e, 'getRoomMeetings', [
                'room_id' => $roomId,
                'filters' => $filters,
            ]);
        }
    }

    /**
     * 获取会议室容量
     *
     * @param string $roomId 会议室ID
     * @return array<string, mixed> 容量信息
     */
    public function getRoomCapacity(string $roomId): array
    {
        try {
            $response = $this->get('/v1/rooms/' . urlencode($roomId) . '/capacity');

            return $this->formatRoomResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRoomError($e, 'getRoomCapacity', ['room_id' => $roomId]);
        }
    }

    /**
     * 检查会议室可用性
     *
     * @param string $roomId 会议室ID
     * @param array<string, mixed> $timeRange 时间范围
     * @return array<string, mixed> 可用性信息
     */
    public function checkRoomAvailability(string $roomId, array $timeRange): array
    {
        try {
            // 验证时间范围
            $this->validateTimeRange($timeRange);

            $response = $this->post('/v1/rooms/' . urlencode($roomId) . '/availability', $timeRange);

            return $this->formatRoomResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRoomError($e, 'checkRoomAvailability', [
                'room_id' => $roomId,
                'time_range' => $timeRange,
            ]);
        }
    }

    /**
     * 预订会议室
     *
     * @param string $roomId 会议室ID
     * @param array<string, mixed> $bookingData 预订数据
     * @return array<string, mixed> 预订结果
     */
    public function reserveRoom(string $roomId, array $bookingData): array
    {
        try {
            // 验证预订数据
            $this->validateBookingData($bookingData);

            $response = $this->post('/v1/rooms/' . urlencode($roomId) . '/reserve', $bookingData);

            return $this->formatRoomResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRoomError($e, 'reserveRoom', [
                'room_id' => $roomId,
                'booking_data' => $bookingData,
            ]);
        }
    }

    /**
     * 释放会议室预订
     *
     * @param string $roomId 会议室ID
     * @param string $bookingId 预订ID
     * @return array<string, mixed> 释放结果
     */
    public function releaseRoom(string $roomId, string $bookingId): array
    {
        try {
            $response = $this->post('/v1/rooms/' . urlencode($roomId) . '/release', [
                'booking_id' => $bookingId,
            ]);

            return $this->formatRoomResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRoomError($e, 'releaseRoom', [
                'room_id' => $roomId,
                'booking_id' => $bookingId,
            ]);
        }
    }

    /**
     * 验证会议室数据
     *
     * @param array<string, mixed> $roomData 会议室数据
     * @param bool $isUpdate 是否为更新操作
     */
    private function validateRoomData(array $roomData, bool $isUpdate = false): void
    {
        $this->validateRoomRequiredFields($roomData, $isUpdate);
        $this->validateRoomCapacity($roomData);
        $this->validateRoomEquipment($roomData);
    }

    /**
     * 验证会议室必需字段
     *
     * @param array<string, mixed> $roomData 会议室数据
     * @param bool $isUpdate 是否为更新操作
     */
    private function validateRoomRequiredFields(array $roomData, bool $isUpdate): void
    {
        if ($isUpdate) {
            return;
        }

        $requiredFields = ['name', 'capacity', 'location'];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $roomData) || $roomData[$field] === null || $roomData[$field] === '') {
                throw new ApiException("会议室数据缺少必需字段: {$field}");
            }
        }
    }

    /**
     * 验证会议室容量
     *
     * @param array<string, mixed> $roomData 会议室数据
     */
    private function validateRoomCapacity(array $roomData): void
    {
        if (!isset($roomData['capacity'])) {
            return;
        }

        if (!is_numeric($roomData['capacity']) || $roomData['capacity'] <= 0) {
            throw new ApiException('会议室容量必须是正数');
        }
    }

    /**
     * 验证会议室设备
     *
     * @param array<string, mixed> $roomData 会议室数据
     */
    private function validateRoomEquipment(array $roomData): void
    {
        if (!isset($roomData['equipment'])) {
            return;
        }

        if (!is_array($roomData['equipment'])) {
            throw new ApiException('会议室设备必须是数组');
        }
    }

    /**
     * 验证会议室设置
     *
     * @param array<string, mixed> $settings 会议室设置
     */
    private function validateRoomSettings(array $settings): void
    {
        $validSettings = [
            'auto_book', 'approval_required', 'max_booking_duration',
            'advance_booking_days', 'cancel_policy',
        ];

        foreach (array_keys($settings) as $setting) {
            if (!in_array($setting, $validSettings, true)) {
                throw new ApiException("无效的会议室设置: {$setting}");
            }
        }
    }

    /**
     * 验证时间范围
     *
     * @param array<string, mixed> $timeRange 时间范围
     */
    private function validateTimeRange(array $timeRange): void
    {
        $requiredFields = ['start_time', 'end_time'];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $timeRange) || $timeRange[$field] === null || $timeRange[$field] === '') {
                throw new ApiException("时间范围缺少必需字段: {$field}");
            }
        }

        // 验证时间格式
        if (!is_numeric($timeRange['start_time']) || !is_numeric($timeRange['end_time'])) {
            throw new ApiException('时间必须是时间戳格式');
        }

        // 验证时间顺序
        if ($timeRange['start_time'] >= $timeRange['end_time']) {
            throw new ApiException('开始时间必须早于结束时间');
        }
    }

    /**
     * 验证预订数据
     *
     * @param array<string, mixed> $bookingData 预订数据
     */
    private function validateBookingData(array $bookingData): void
    {
        $requiredFields = ['start_time', 'end_time', 'booked_by'];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $bookingData) || $bookingData[$field] === null || $bookingData[$field] === '') {
                throw new ApiException("预订数据缺少必需字段: {$field}");
            }
        }

        // 验证时间范围
        $this->validateTimeRange($bookingData);
    }

    /**
     * 构建会议室参数
     *
     * @param array<string, mixed> $roomData 会议室数据
     * @return array<string, mixed> 请求参数
     */
    private function buildRoomParams(array $roomData): array
    {
        return $this->mapRoomDataToParams($roomData);
    }

    /**
     * 映射会议室数据到参数
     *
     * @param array<string, mixed> $roomData 会议室数据
     * @return array<string, mixed> 请求参数
     */
    private function mapRoomDataToParams(array $roomData): array
    {
        $mapping = [
            'name' => 'name',
            'capacity' => 'capacity',
            'location' => 'location',
            'description' => 'description',
            'floor' => 'floor',
            'building' => 'building',
            'equipment' => 'equipment',
        ];

        $params = [];
        foreach ($mapping as $dataKey => $paramKey) {
            if (isset($roomData[$dataKey])) {
                $params[$paramKey] = $roomData[$dataKey];
            }
        }

        return $params;
    }

    /**
     * 构建会议室列表参数
     *
     * @param array<string, mixed> $filters 过滤条件
     * @return array<string, mixed> 查询参数
     */
    private function buildRoomListParams(array $filters): array
    {
        return $this->mapFiltersToParams($filters, [
            'page' => 'int',
            'page_size' => 'int',
            'capacity_min' => 'int',
            'capacity_max' => 'int',
            'location' => 'string',
            'status' => 'string',
        ]);
    }

    /**
     * 映射过滤条件到参数
     *
     * @param array<string, mixed> $filters 过滤条件
     * @param array<string, string> $mapping 映射规则
     * @return array<string, mixed> 查询参数
     */
    private function mapFiltersToParams(array $filters, array $mapping): array
    {
        $params = [];

        foreach ($mapping as $key => $type) {
            if (!isset($filters[$key])) {
                continue;
            }

            $params[$key] = match ($type) {
                'int' => is_numeric($filters[$key]) ? (int) $filters[$key] : 0,
                'string' => $filters[$key],
                default => $filters[$key],
            };
        }

        return $params;
    }

    /**
     * 构建会议室会议参数
     *
     * @param array<string, mixed> $filters 过滤条件
     * @return array<string, mixed> 查询参数
     */
    private function buildRoomMeetingParams(array $filters): array
    {
        return $this->mapFiltersToParams($filters, [
            'page' => 'int',
            'page_size' => 'int',
            'status' => 'string',
            'start_time' => 'int',
            'end_time' => 'int',
        ]);
    }

    /**
     * 格式化会议室响应
     *
     * @param array<string, mixed> $response 原始响应
     * @return array<string, mixed> 格式化后的响应
     */
    private function formatRoomResponse(array $response): array
    {
        $this->validateRoomResponseSuccess($response);

        return $this->buildFormattedRoomResponse($response);
    }

    /**
     * 验证会议室响应成功
     *
     * @param array<string, mixed> $response API 响应
     */
    private function validateRoomResponseSuccess(array $response): void
    {
        if (!isset($response['success']) || !is_bool($response['success']) || !$response['success']) {
            throw new ApiException('会议室操作失败');
        }
    }

    /**
     * 构建格式化会议室响应
     *
     * @param array<string, mixed> $response 原始响应
     * @return array<string, mixed> 格式化后的响应
     */
    private function buildFormattedRoomResponse(array $response): array
    {
        $formatted = $this->buildBasicRoomFields($response);
        $formatted = $this->addOptionalRoomFields($formatted, $response);

        return $this->addRoomListFields($formatted, $response);
    }

    /**
     * 构建基础会议室字段
     *
     * @param array<string, mixed> $response 原始响应
     * @return array<string, mixed> 基础字段
     */
    private function buildBasicRoomFields(array $response): array
    {
        return [
            'success' => true,
            'room_id' => $response['room_id'] ?? null,
            'name' => $response['name'] ?? null,
            'capacity' => $response['capacity'] ?? null,
            'location' => $response['location'] ?? null,
            'status' => $response['status'] ?? null,
            'created_at' => $response['created_at'] ?? null,
            'updated_at' => $response['updated_at'] ?? null,
        ];
    }

    /**
     * 添加可选会议室字段
     *
     * @param array<string, mixed> $formatted 已格式化数据
     * @param array<string, mixed> $response 原始响应
     * @return array<string, mixed> 包含可选字段的数据
     */
    private function addOptionalRoomFields(array $formatted, array $response): array
    {
        $optionalFields = [
            'equipment', 'settings', 'meetings', 'available', 'booking_id',
            'current_capacity', 'max_capacity', 'current_occupancy',
        ];

        foreach ($optionalFields as $field) {
            if (isset($response[$field])) {
                $formatted[$field] = $response[$field];
            }
        }

        return $formatted;
    }

    /**
     * 添加会议室列表字段
     *
     * @param array<string, mixed> $formatted 已格式化数据
     * @param array<string, mixed> $response 原始响应
     * @return array<string, mixed> 包含列表字段的数据
     */
    private function addRoomListFields(array $formatted, array $response): array
    {
        if (isset($response['rooms'])) {
            $formatted['rooms'] = $response['rooms'];
            $formatted['pagination'] = $response['pagination'] ?? [];
        }

        return $formatted;
    }

    /**
     * 处理会议室错误
     *
     * @param \Throwable $exception 异常
     * @param string $operation 操作类型
     * @param array<string, mixed> $context 上下文信息
     * @return array<string, mixed> 错误响应
     */
    private function handleRoomError(\Throwable $exception, string $operation, array $context): array
    {
        $this->loggerService->error('TencentMeeting 会议室操作失败: ' . $operation, [
            'exception' => $exception,
            'context' => $context,
        ]);

        return $this->createRoomErrorResponse($exception, $operation, $context);
    }

    /**
     * 创建会议室错误响应
     *
     * @param \Throwable $exception 异常
     * @param string $operation 操作类型
     * @param array<string, mixed> $context 上下文信息
     * @return array<string, mixed> 错误响应
     */
    private function createRoomErrorResponse(\Throwable $exception, string $operation, array $context): array
    {
        if ($exception instanceof ApiException) {
            return $this->buildRoomApiErrorResponse($exception, $operation, $context);
        }

        if ($exception instanceof AuthenticationException) {
            return $this->buildRoomAuthErrorResponse($operation, $context);
        }

        return $this->buildRoomDefaultErrorResponse($exception, $operation, $context);
    }

    /**
     * 构建会议室API错误响应
     *
     * @param ApiException $exception API异常
     * @param string $operation 操作类型
     * @param array<string, mixed> $context 上下文信息
     * @return array<string, mixed> API错误响应
     */
    private function buildRoomApiErrorResponse(ApiException $exception, string $operation, array $context): array
    {
        return [
            'success' => false,
            'error' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'operation' => $operation,
            'context' => $context,
        ];
    }

    /**
     * 构建会议室认证错误响应
     *
     * @param string $operation 操作类型
     * @param array<string, mixed> $context 上下文信息
     * @return array<string, mixed> 认证错误响应
     */
    private function buildRoomAuthErrorResponse(string $operation, array $context): array
    {
        return [
            'success' => false,
            'error' => '认证失败',
            'code' => 401,
            'operation' => $operation,
            'context' => $context,
        ];
    }

    /**
     * 构建会议室默认错误响应
     *
     * @param \Throwable $exception 异常
     * @param string $operation 操作类型
     * @param array<string, mixed> $context 上下文信息
     * @return array<string, mixed> 默认错误响应
     */
    private function buildRoomDefaultErrorResponse(\Throwable $exception, string $operation, array $context): array
    {
        return [
            'success' => false,
            'error' => '会议室操作失败: ' . $exception->getMessage(),
            'code' => 500,
            'operation' => $operation,
            'context' => $context,
        ];
    }
}

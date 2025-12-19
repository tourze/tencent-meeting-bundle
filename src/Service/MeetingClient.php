<?php

namespace Tourze\TencentMeetingBundle\Service;

use Monolog\Attribute\WithMonologChannel;
use Tourze\TencentMeetingBundle\Exception\ApiException;
use Tourze\TencentMeetingBundle\Exception\AuthenticationException;

/**
 * 会议客户端
 *
 * 提供会议管理的完整API封装，包括创建、查询、更新、删除等操作
 */
#[WithMonologChannel(channel: 'tencent_meeting')]
final class MeetingClient extends BaseClient
{
    /**
     * 创建会议
     *
     * @param array<string, mixed> $meetingData 会议数据
     * @return array<string, mixed> 创建结果
     */
    public function createMeeting(array $meetingData): array
    {
        try {
            // 验证会议数据
            $this->validateMeetingData($meetingData);

            // 构建请求参数
            $params = $this->buildMeetingParams($meetingData);

            // 发送请求
            $response = $this->post('/v1/meetings', $params);

            // 格式化响应
            return $this->formatMeetingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleMeetingError($e, 'createMeeting', $meetingData);
        }
    }

    /**
     * 获取会议信息
     *
     * @param string $meetingId 会议ID
     * @return array<string, mixed> 会议信息
     */
    public function getMeeting(string $meetingId): array
    {
        try {
            $response = $this->get('/v1/meetings/' . urlencode($meetingId));

            return $this->formatMeetingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleMeetingError($e, 'getMeeting', ['meeting_id' => $meetingId]);
        }
    }

    /**
     * 更新会议
     *
     * @param string $meetingId 会议ID
     * @param array<string, mixed> $updateData 更新数据
     * @return array<string, mixed> 更新结果
     */
    public function updateMeeting(string $meetingId, array $updateData): array
    {
        try {
            // 验证更新数据
            $this->validateMeetingData($updateData, true);

            // 构建请求参数
            $params = $this->buildMeetingParams($updateData);

            // 发送请求
            $response = $this->put('/v1/meetings/' . urlencode($meetingId), $params);

            return $this->formatMeetingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleMeetingError($e, 'updateMeeting', [
                'meeting_id' => $meetingId,
                'update_data' => $updateData,
            ]);
        }
    }

    /**
     * 删除会议
     *
     * @param string $meetingId 会议ID
     * @return array<string, mixed> 删除结果
     */
    public function deleteMeeting(string $meetingId): array
    {
        try {
            $response = $this->delete('/v1/meetings/' . urlencode($meetingId));

            return $this->formatMeetingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleMeetingError($e, 'deleteMeeting', ['meeting_id' => $meetingId]);
        }
    }

    /**
     * 获取会议列表
     *
     * @param array<string, mixed> $filters 过滤条件
     * @return array<string, mixed> 会议列表
     */
    public function listMeetings(array $filters = []): array
    {
        try {
            // 构建查询参数
            $queryParams = $this->buildMeetingListParams($filters);

            // 发送请求
            $response = $this->get('/v1/meetings?' . http_build_query($queryParams));

            return $this->formatMeetingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleMeetingError($e, 'listMeetings', $filters);
        }
    }

    /**
     * 取消会议
     *
     * @param string $meetingId 会议ID
     * @return array<string, mixed> 取消结果
     */
    public function cancelMeeting(string $meetingId): array
    {
        try {
            $response = $this->post('/v1/meetings/' . urlencode($meetingId) . '/cancel', []);

            return $this->formatMeetingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleMeetingError($e, 'cancelMeeting', ['meeting_id' => $meetingId]);
        }
    }

    /**
     * 结束会议
     *
     * @param string $meetingId 会议ID
     * @return array<string, mixed> 结束结果
     */
    public function endMeeting(string $meetingId): array
    {
        try {
            $response = $this->post('/v1/meetings/' . urlencode($meetingId) . '/end', []);

            return $this->formatMeetingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleMeetingError($e, 'endMeeting', ['meeting_id' => $meetingId]);
        }
    }

    /**
     * 获取会议参与者
     *
     * @param string $meetingId 会议ID
     * @return array<string, mixed> 参与者列表
     */
    public function getMeetingParticipants(string $meetingId): array
    {
        try {
            $response = $this->get('/v1/meetings/' . urlencode($meetingId) . '/participants');

            return $this->formatMeetingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleMeetingError($e, 'getMeetingParticipants', ['meeting_id' => $meetingId]);
        }
    }

    /**
     * 添加会议参与者
     *
     * @param string $meetingId 会议ID
     * @param array<string, mixed> $participantData 参与者数据
     * @return array<string, mixed> 添加结果
     */
    public function addMeetingParticipant(string $meetingId, array $participantData): array
    {
        try {
            // 验证参与者数据
            $this->validateParticipantData($participantData);

            $response = $this->post('/v1/meetings/' . urlencode($meetingId) . '/participants', $participantData);

            return $this->formatMeetingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleMeetingError($e, 'addMeetingParticipant', [
                'meeting_id' => $meetingId,
                'participant_data' => $participantData,
            ]);
        }
    }

    /**
     * 移除会议参与者
     *
     * @param string $meetingId 会议ID
     * @param string $userId 用户ID
     * @return array<string, mixed> 移除结果
     */
    public function removeMeetingParticipant(string $meetingId, string $userId): array
    {
        try {
            $response = $this->delete('/v1/meetings/' . urlencode($meetingId) . '/participants/' . urlencode($userId));

            return $this->formatMeetingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleMeetingError($e, 'removeMeetingParticipant', [
                'meeting_id' => $meetingId,
                'user_id' => $userId,
            ]);
        }
    }

    /**
     * 获取会议录制
     *
     * @param string $meetingId 会议ID
     * @return array<string, mixed> 录制列表
     */
    public function getMeetingRecordings(string $meetingId): array
    {
        try {
            $response = $this->get('/v1/meetings/' . urlencode($meetingId) . '/recordings');

            return $this->formatMeetingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleMeetingError($e, 'getMeetingRecordings', ['meeting_id' => $meetingId]);
        }
    }

    /**
     * 获取会议状态
     *
     * @param string $meetingId 会议ID
     * @return array<string, mixed> 会议状态
     */
    public function getMeetingStatus(string $meetingId): array
    {
        try {
            $response = $this->get('/v1/meetings/' . urlencode($meetingId) . '/status');

            return $this->formatMeetingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleMeetingError($e, 'getMeetingStatus', ['meeting_id' => $meetingId]);
        }
    }

    /**
     * 获取会议设置
     *
     * @param string $meetingId 会议ID
     * @return array<string, mixed> 会议设置
     */
    public function getMeetingSettings(string $meetingId): array
    {
        try {
            $response = $this->get('/v1/meetings/' . urlencode($meetingId) . '/settings');

            return $this->formatMeetingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleMeetingError($e, 'getMeetingSettings', ['meeting_id' => $meetingId]);
        }
    }

    /**
     * 更新会议设置
     *
     * @param string $meetingId 会议ID
     * @param array<string, mixed> $settings 会议设置
     * @return array<string, mixed> 更新结果
     */
    public function updateMeetingSettings(string $meetingId, array $settings): array
    {
        try {
            // 验证设置数据
            $this->validateMeetingSettings($settings);

            $response = $this->put('/v1/meetings/' . urlencode($meetingId) . '/settings', $settings);

            return $this->formatMeetingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleMeetingError($e, 'updateMeetingSettings', [
                'meeting_id' => $meetingId,
                'settings' => $settings,
            ]);
        }
    }

    /**
     * 验证会议数据
     *
     * @param array<string, mixed> $meetingData 会议数据
     * @param bool $isUpdate 是否为更新操作
     */
    private function validateMeetingData(array $meetingData, bool $isUpdate = false): void
    {
        $this->validateRequiredFields($meetingData, $isUpdate);
        $this->validateTimeFields($meetingData);
        $this->validateMeetingType($meetingData);
    }

    /**
     * 验证必需字段
     *
     * @param array<string, mixed> $meetingData
     * @param bool $isUpdate
     */
    private function validateRequiredFields(array $meetingData, bool $isUpdate): void
    {
        if ($isUpdate) {
            return;
        }

        $requiredFields = ['subject', 'start_time', 'end_time', 'type'];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $meetingData) || $meetingData[$field] === null || $meetingData[$field] === '') {
                throw new ApiException("会议数据缺少必需字段: {$field}");
            }
        }
    }

    /**
     * 验证时间字段
     *
     * @param array<string, mixed> $meetingData
     */
    private function validateTimeFields(array $meetingData): void
    {
        if (isset($meetingData['start_time']) && !is_numeric($meetingData['start_time'])) {
            throw new ApiException('会议开始时间必须是时间戳格式');
        }

        if (isset($meetingData['end_time']) && !is_numeric($meetingData['end_time'])) {
            throw new ApiException('会议结束时间必须是时间戳格式');
        }
    }

    /**
     * 验证会议类型
     *
     * @param array<string, mixed> $meetingData
     */
    private function validateMeetingType(array $meetingData): void
    {
        if (!isset($meetingData['type'])) {
            return;
        }

        $validTypes = [0, 1, 2]; // 0: 即时会议, 1: 预约会议, 2: 周期会议
        if (!in_array($meetingData['type'], $validTypes, true)) {
            throw new ApiException('无效的会议类型');
        }
    }

    /**
     * 验证参与者数据
     *
     * @param array<string, mixed> $participantData 参与者数据
     */
    private function validateParticipantData(array $participantData): void
    {
        $requiredFields = ['user_id', 'role'];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $participantData) || $participantData[$field] === null || $participantData[$field] === '') {
                throw new ApiException("参与者数据缺少必需字段: {$field}");
            }
        }

        // 验证角色
        $validRoles = ['host', 'cohost', 'attendee'];
        if (!in_array($participantData['role'], $validRoles, true)) {
            throw new ApiException('无效的参与者角色');
        }
    }

    /**
     * 验证会议设置
     *
     * @param array<string, mixed> $settings 会议设置
     */
    private function validateMeetingSettings(array $settings): void
    {
        $validSettings = [
            'mute_enable', 'waiting_room_enable', 'live_enable',
            'auto_record_enable', 'watermark_enable', 'screen_share_enable',
        ];

        foreach (array_keys($settings) as $setting) {
            if (!in_array($setting, $validSettings, true)) {
                throw new ApiException("无效的会议设置: {$setting}");
            }

            if (!is_bool($settings[$setting])) {
                throw new ApiException("会议设置 {$setting} 必须是布尔值");
            }
        }
    }

    /**
     * 构建会议参数
     *
     * @param array<string, mixed> $meetingData 会议数据
     * @return array<string, mixed> 请求参数
     */
    private function buildMeetingParams(array $meetingData): array
    {
        $params = [];

        // 基本会议信息
        $mapping = [
            'subject' => 'subject',
            'start_time' => 'start_time',
            'end_time' => 'end_time',
            'type' => 'type',
            'password' => 'password',
            'description' => 'description',
            'location' => 'location',
            'timezone' => 'timezone',
        ];

        foreach ($mapping as $dataKey => $paramKey) {
            if (isset($meetingData[$dataKey])) {
                $params[$paramKey] = $meetingData[$dataKey];
            }
        }

        // 会议设置
        if (isset($meetingData['settings'])) {
            $params['settings'] = $meetingData['settings'];
        }

        // 参与者信息
        if (isset($meetingData['participants'])) {
            $params['participants'] = $meetingData['participants'];
        }

        return $params;
    }

    /**
     * 构建会议列表参数
     *
     * @param array<string, mixed> $filters 过滤条件
     * @return array<string, mixed> 查询参数
     */
    private function buildMeetingListParams(array $filters): array
    {
        $params = [];

        $params = $this->addIntFilterIfPresent($filters, $params, 'page');
        $params = $this->addIntFilterIfPresent($filters, $params, 'page_size');
        $params = $this->addIntFilterIfPresent($filters, $params, 'type');
        $params = $this->addIntFilterIfPresent($filters, $params, 'start_time');
        $params = $this->addIntFilterIfPresent($filters, $params, 'end_time');

        if (isset($filters['status'])) {
            $params['status'] = $filters['status'];
        }

        return $params;
    }

    /**
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function addIntFilterIfPresent(array $filters, array $params, string $key): array
    {
        if (isset($filters[$key]) && is_numeric($filters[$key])) {
            $params[$key] = (int) $filters[$key];
        }

        return $params;
    }

    /**
     * 格式化会议响应
     *
     * @param array<string, mixed> $response 原始响应
     * @return array<string, mixed> 格式化后的响应
     */
    private function formatMeetingResponse(array $response): array
    {
        $this->validateResponseSuccess($response);

        return $this->buildFormattedResponse($response);
    }

    /**
     * 验证响应成功
     *
     * @param array<string, mixed> $response
     */
    private function validateResponseSuccess(array $response): void
    {
        if (!isset($response['success']) || !is_bool($response['success']) || !$response['success']) {
            throw new ApiException('会议操作失败');
        }
    }

    /**
     * 构建格式化响应
     *
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     */
    private function buildFormattedResponse(array $response): array
    {
        $formatted = $this->buildBasicMeetingFields($response);

        return $this->addOptionalMeetingFields($formatted, $response);
    }

    /**
     * 构建基础会议字段
     *
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     */
    private function buildBasicMeetingFields(array $response): array
    {
        return [
            'success' => true,
            'meeting_id' => $response['meeting_id'] ?? null,
            'subject' => $response['subject'] ?? null,
            'start_time' => $response['start_time'] ?? null,
            'end_time' => $response['end_time'] ?? null,
            'status' => $response['status'] ?? null,
            'type' => $response['type'] ?? null,
            'meeting_code' => $response['meeting_code'] ?? null,
            'join_url' => $response['join_url'] ?? null,
        ];
    }

    /**
     * 添加可选会议字段
     *
     * @param array<string, mixed> $formatted
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     */
    private function addOptionalMeetingFields(array $formatted, array $response): array
    {
        $optionalFields = ['participants', 'settings', 'recordings', 'meetings', 'pagination'];

        foreach ($optionalFields as $field) {
            if (isset($response[$field])) {
                $formatted[$field] = $response[$field];
            }
        }

        return $formatted;
    }

    /**
     * 处理会议错误
     *
     * @param \Throwable $exception 异常
     * @param string $operation 操作类型
     * @param array<string, mixed> $context 上下文信息
     * @return array<string, mixed> 错误响应
     */
    private function handleMeetingError(\Throwable $exception, string $operation, array $context): array
    {
        $this->loggerService->error('TencentMeeting 会议操作失败: ' . $operation, [
            'exception' => $exception,
            'context' => $context,
        ]);

        return $this->createErrorResponse($exception, $operation, $context);
    }

    /**
     * 创建错误响应
     *
     * @param \Throwable $exception
     * @param string $operation
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function createErrorResponse(\Throwable $exception, string $operation, array $context): array
    {
        if ($exception instanceof ApiException) {
            return $this->buildApiErrorResponse($exception, $operation, $context);
        }

        if ($exception instanceof AuthenticationException) {
            return $this->buildAuthErrorResponse($operation, $context);
        }

        return $this->buildDefaultErrorResponse($exception, $operation, $context);
    }

    /**
     * 构建API错误响应
     *
     * @param ApiException $exception
     * @param string $operation
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function buildApiErrorResponse(ApiException $exception, string $operation, array $context): array
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
     * 构建认证错误响应
     *
     * @param string $operation
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function buildAuthErrorResponse(string $operation, array $context): array
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
     * 构建默认错误响应
     *
     * @param \Throwable $exception
     * @param string $operation
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function buildDefaultErrorResponse(\Throwable $exception, string $operation, array $context): array
    {
        return [
            'success' => false,
            'error' => '会议操作失败: ' . $exception->getMessage(),
            'code' => 500,
            'operation' => $operation,
            'context' => $context,
        ];
    }
}

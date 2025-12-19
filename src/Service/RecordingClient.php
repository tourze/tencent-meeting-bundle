<?php

namespace Tourze\TencentMeetingBundle\Service;

use Monolog\Attribute\WithMonologChannel;
use Tourze\TencentMeetingBundle\Exception\ApiException;
use Tourze\TencentMeetingBundle\Exception\AuthenticationException;

/**
 * 录制客户端
 *
 * 提供录制管理的完整API封装，包括查询、控制、下载等操作
 */
#[WithMonologChannel(channel: 'tencent_meeting')]
final class RecordingClient extends BaseClient
{
    /**
     * 获取录制信息
     *
     * @param string $recordingId 录制ID
     * @return array<string, mixed> 录制信息
     */
    public function getRecording(string $recordingId): array
    {
        try {
            $response = $this->get('/v1/recordings/' . urlencode($recordingId));

            return $this->formatRecordingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRecordingError($e, 'getRecording', ['recording_id' => $recordingId]);
        }
    }

    /**
     * 获取会议录制
     *
     * @param string $meetingId 会议ID
     * @param array<string, mixed> $filters 过滤条件
     * @return array<string, mixed> 录制列表
     */
    public function getMeetingRecordings(string $meetingId, array $filters = []): array
    {
        try {
            $queryParams = $this->buildRecordingListParams($filters);

            $response = $this->get('/v1/meetings/' . urlencode($meetingId) . '/recordings?' . http_build_query($queryParams));

            return $this->formatRecordingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRecordingError($e, 'getMeetingRecordings', [
                'meeting_id' => $meetingId,
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
            $queryParams = $this->buildRecordingListParams($filters);

            $response = $this->get('/v1/users/' . urlencode($userId) . '/recordings?' . http_build_query($queryParams));

            return $this->formatRecordingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRecordingError($e, 'getUserRecordings', [
                'user_id' => $userId,
                'filters' => $filters,
            ]);
        }
    }

    /**
     * 开始录制
     *
     * @param string $meetingId 会议ID
     * @return array<string, mixed> 录制结果
     */
    public function startRecording(string $meetingId): array
    {
        try {
            $response = $this->post('/v1/meetings/' . urlencode($meetingId) . '/recordings/start', []);

            return $this->formatRecordingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRecordingError($e, 'startRecording', ['meeting_id' => $meetingId]);
        }
    }

    /**
     * 停止录制
     *
     * @param string $meetingId 会议ID
     * @return array<string, mixed> 停止结果
     */
    public function stopRecording(string $meetingId): array
    {
        try {
            $response = $this->post('/v1/meetings/' . urlencode($meetingId) . '/recordings/stop', []);

            return $this->formatRecordingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRecordingError($e, 'stopRecording', ['meeting_id' => $meetingId]);
        }
    }

    /**
     * 暂停录制
     *
     * @param string $meetingId 会议ID
     * @return array<string, mixed> 暂停结果
     */
    public function pauseRecording(string $meetingId): array
    {
        try {
            $response = $this->post('/v1/meetings/' . urlencode($meetingId) . '/recordings/pause', []);

            return $this->formatRecordingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRecordingError($e, 'pauseRecording', ['meeting_id' => $meetingId]);
        }
    }

    /**
     * 恢复录制
     *
     * @param string $meetingId 会议ID
     * @return array<string, mixed> 恢复结果
     */
    public function resumeRecording(string $meetingId): array
    {
        try {
            $response = $this->post('/v1/meetings/' . urlencode($meetingId) . '/recordings/resume', []);

            return $this->formatRecordingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRecordingError($e, 'resumeRecording', ['meeting_id' => $meetingId]);
        }
    }

    /**
     * 删除录制
     *
     * @param string $recordingId 录制ID
     * @return array<string, mixed> 删除结果
     */
    public function deleteRecording(string $recordingId): array
    {
        try {
            $response = $this->delete('/v1/recordings/' . urlencode($recordingId));

            return $this->formatRecordingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRecordingError($e, 'deleteRecording', ['recording_id' => $recordingId]);
        }
    }

    /**
     * 获取录制状态
     *
     * @param string $recordingId 录制ID
     * @return array<string, mixed> 录制状态
     */
    public function getRecordingStatus(string $recordingId): array
    {
        try {
            $response = $this->get('/v1/recordings/' . urlencode($recordingId) . '/status');

            return $this->formatRecordingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRecordingError($e, 'getRecordingStatus', ['recording_id' => $recordingId]);
        }
    }

    /**
     * 获取录制设置
     *
     * @param string $meetingId 会议ID
     * @return array<string, mixed> 录制设置
     */
    public function getRecordingSettings(string $meetingId): array
    {
        try {
            $response = $this->get('/v1/meetings/' . urlencode($meetingId) . '/recordings/settings');

            return $this->formatRecordingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRecordingError($e, 'getRecordingSettings', ['meeting_id' => $meetingId]);
        }
    }

    /**
     * 更新录制设置
     *
     * @param string $meetingId 会议ID
     * @param array<string, mixed> $settings 录制设置
     * @return array<string, mixed> 更新结果
     */
    public function updateRecordingSettings(string $meetingId, array $settings): array
    {
        try {
            // 验证设置数据
            $this->validateRecordingSettings($settings);

            $response = $this->put('/v1/meetings/' . urlencode($meetingId) . '/recordings/settings', $settings);

            return $this->formatRecordingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRecordingError($e, 'updateRecordingSettings', [
                'meeting_id' => $meetingId,
                'settings' => $settings,
            ]);
        }
    }

    /**
     * 下载录制
     *
     * @param string $recordingId 录制ID
     * @return array<string, mixed> 下载信息
     */
    public function downloadRecording(string $recordingId): array
    {
        try {
            $response = $this->get('/v1/recordings/' . urlencode($recordingId) . '/download');

            return $this->formatRecordingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRecordingError($e, 'downloadRecording', ['recording_id' => $recordingId]);
        }
    }

    /**
     * 分享录制
     *
     * @param string $recordingId 录制ID
     * @param array<string, mixed> $shareData 分享数据
     * @return array<string, mixed> 分享结果
     */
    public function shareRecording(string $recordingId, array $shareData): array
    {
        try {
            // 验证分享数据
            $this->validateShareData($shareData);

            $response = $this->post('/v1/recordings/' . urlencode($recordingId) . '/share', $shareData);

            return $this->formatRecordingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRecordingError($e, 'shareRecording', [
                'recording_id' => $recordingId,
                'share_data' => $shareData,
            ]);
        }
    }

    /**
     * 获取录制转录
     *
     * @param string $recordingId 录制ID
     * @return array<string, mixed> 转录结果
     */
    public function getRecordingTranscription(string $recordingId): array
    {
        try {
            $response = $this->get('/v1/recordings/' . urlencode($recordingId) . '/transcription');

            return $this->formatRecordingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRecordingError($e, 'getRecordingTranscription', ['recording_id' => $recordingId]);
        }
    }

    /**
     * 获取录制分析
     *
     * @param string $recordingId 录制ID
     * @return array<string, mixed> 分析结果
     */
    public function getRecordingAnalytics(string $recordingId): array
    {
        try {
            $response = $this->get('/v1/recordings/' . urlencode($recordingId) . '/analytics');

            return $this->formatRecordingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRecordingError($e, 'getRecordingAnalytics', ['recording_id' => $recordingId]);
        }
    }

    /**
     * 搜索录制
     *
     * @param array<string, mixed> $searchParams 搜索参数
     * @return array<string, mixed> 搜索结果
     */
    public function searchRecordings(array $searchParams): array
    {
        try {
            $queryParams = $this->buildRecordingSearchParams($searchParams);

            $response = $this->get('/v1/recordings/search?' . http_build_query($queryParams));

            return $this->formatRecordingResponse($response);
        } catch (\Throwable $e) {
            return $this->handleRecordingError($e, 'searchRecordings', $searchParams);
        }
    }

    /**
     * 验证录制设置
     *
     * @param array<string, mixed> $settings 录制设置
     */
    private function validateRecordingSettings(array $settings): void
    {
        $validSettings = [
            'auto_start', 'auto_stop', 'cloud_storage', 'local_storage',
            'watermark', 'transcription', 'analytics',
        ];

        foreach (array_keys($settings) as $setting) {
            if (!in_array($setting, $validSettings, true)) {
                throw new ApiException("无效的录制设置: {$setting}");
            }

            if (!is_bool($settings[$setting])) {
                throw new ApiException("录制设置 {$setting} 必须是布尔值");
            }
        }
    }

    /**
     * 验证分享数据
     *
     * @param array<string, mixed> $shareData 分享数据
     */
    private function validateShareData(array $shareData): void
    {
        $requiredFields = ['share_type', 'expire_time'];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $shareData) || $shareData[$field] === null || $shareData[$field] === '') {
                throw new ApiException("分享数据缺少必需字段: {$field}");
            }
        }

        // 验证分享类型
        $validShareTypes = ['public', 'private', 'password'];
        if (!in_array($shareData['share_type'], $validShareTypes, true)) {
            throw new ApiException('无效的分享类型');
        }

        // 验证过期时间
        if (!is_numeric($shareData['expire_time'])) {
            throw new ApiException('过期时间必须是时间戳格式');
        }
    }

    /**
     * 构建录制列表参数
     *
     * @param array<string, mixed> $filters 过滤条件
     * @return array<string, mixed> 查询参数
     */
    private function buildRecordingListParams(array $filters): array
    {
        $params = [];

        $params = $this->addIntFilterIfPresent($filters, $params, 'page');
        $params = $this->addIntFilterIfPresent($filters, $params, 'page_size');
        $params = $this->addIntFilterIfPresent($filters, $params, 'start_time');
        $params = $this->addIntFilterIfPresent($filters, $params, 'end_time');

        $stringFilters = ['status', 'sort_by', 'sort_order'];
        foreach ($stringFilters as $key) {
            if (isset($filters[$key])) {
                $params[$key] = $filters[$key];
            }
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
     * 构建录制搜索参数
     *
     * @param array<string, mixed> $searchParams 搜索参数
     * @return array<string, mixed> 查询参数
     */
    private function buildRecordingSearchParams(array $searchParams): array
    {
        $params = [];

        $stringFields = ['keyword', 'search_fields'];
        foreach ($stringFields as $key) {
            if (isset($searchParams[$key])) {
                $params[$key] = $searchParams[$key];
            }
        }

        $params = $this->addIntFilterIfPresent($searchParams, $params, 'start_time');
        $params = $this->addIntFilterIfPresent($searchParams, $params, 'end_time');
        $params = $this->addIntFilterIfPresent($searchParams, $params, 'page');

        return $this->addIntFilterIfPresent($searchParams, $params, 'page_size');
    }

    /**
     * 格式化录制响应
     *
     * @param array<string, mixed> $response 原始响应
     * @return array<string, mixed> 格式化后的响应
     */
    private function formatRecordingResponse(array $response): array
    {
        if (!isset($response['success']) || true !== $response['success']) {
            throw new ApiException('录制操作失败');
        }

        $formatted = [
            'success' => true,
            'recording_id' => $response['recording_id'] ?? null,
            'meeting_id' => $response['meeting_id'] ?? null,
            'user_id' => $response['user_id'] ?? null,
            'status' => $response['status'] ?? null,
            'duration' => $response['duration'] ?? null,
            'size' => $response['size'] ?? null,
            'format' => $response['format'] ?? null,
            'resolution' => $response['resolution'] ?? null,
            'created_at' => $response['created_at'] ?? null,
            'updated_at' => $response['updated_at'] ?? null,
        ];

        // 添加额外信息
        if (isset($response['download_url'])) {
            $formatted['download_url'] = $response['download_url'];
        }

        if (isset($response['share_url'])) {
            $formatted['share_url'] = $response['share_url'];
        }

        if (isset($response['transcription'])) {
            $formatted['transcription'] = $response['transcription'];
        }

        if (isset($response['analytics'])) {
            $formatted['analytics'] = $response['analytics'];
        }

        // 如果是列表响应，添加分页信息
        if (isset($response['recordings'])) {
            $formatted['recordings'] = $response['recordings'];
            $formatted['pagination'] = $response['pagination'] ?? [];
        }

        return $formatted;
    }

    /**
     * 处理录制错误
     *
     * @param \Throwable $exception 异常
     * @param string $operation 操作类型
     * @param array<string, mixed> $context 上下文信息
     * @return array<string, mixed> 错误响应
     */
    private function handleRecordingError(\Throwable $exception, string $operation, array $context): array
    {
        $this->loggerService->error('TencentMeeting 录制操作失败: ' . $operation, [
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
            'error' => '录制操作失败: ' . $exception->getMessage(),
            'code' => 500,
            'operation' => $operation,
            'context' => $context,
        ];
    }
}

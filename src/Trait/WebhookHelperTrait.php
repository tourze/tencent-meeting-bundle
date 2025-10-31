<?php

namespace Tourze\TencentMeetingBundle\Trait;

use Tourze\TencentMeetingBundle\Exception\ApiException;
use Tourze\TencentMeetingBundle\Exception\AuthenticationException;

/**
 * Webhook辅助方法trait
 *
 * 提供Webhook相关的辅助方法，如参数构建、响应格式化、错误处理等
 */
trait WebhookHelperTrait
{
    /**
     * 构建Webhook参数
     *
     * @param array<string, mixed> $webhookData Webhook数据
     * @return array<string, mixed> 请求参数
     */
    private function buildWebhookParams(array $webhookData): array
    {
        $params = [];

        // 基本Webhook信息
        $mapping = [
            'url' => 'url',
            'events' => 'events',
            'secret' => 'secret',
            'description' => 'description',
            'enabled' => 'enabled',
        ];

        foreach ($mapping as $dataKey => $paramKey) {
            if (isset($webhookData[$dataKey])) {
                $params[$paramKey] = $webhookData[$dataKey];
            }
        }

        return $params;
    }

    /**
     * 构建Webhook列表参数
     *
     * @param array<string, mixed> $filters 过滤条件
     * @return array<string, mixed> 查询参数
     */
    private function buildWebhookListParams(array $filters): array
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
        if (isset($filters['enabled'])) {
            $params['enabled'] = (bool) $filters['enabled'];
        }

        if (isset($filters['event_type'])) {
            $params['event_type'] = $filters['event_type'];
        }

        return $params;
    }

    /**
     * 构建Webhook日志参数
     *
     * @param array<string, mixed> $filters 过滤条件
     * @return array<string, mixed> 查询参数
     */
    private function buildWebhookLogParams(array $filters): array
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

        if (isset($filters['event_type'])) {
            $params['event_type'] = $filters['event_type'];
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
     * 格式化Webhook响应
     *
     * @param array<string, mixed> $response 原始响应
     * @return array<string, mixed> 格式化后的响应
     */
    private function formatWebhookResponse(array $response): array
    {
        if (!isset($response['success']) || !is_bool($response['success']) || !$response['success']) {
            throw new ApiException('Webhook操作失败');
        }

        $formatted = [
            'success' => true,
            'webhook_id' => $response['webhook_id'] ?? null,
            'url' => $response['url'] ?? null,
            'events' => $response['events'] ?? [],
            'enabled' => $response['enabled'] ?? false,
            'created_at' => $response['created_at'] ?? null,
            'updated_at' => $response['updated_at'] ?? null,
        ];

        // 添加额外信息
        if (isset($response['secret'])) {
            $formatted['secret'] = $response['secret'];
        }

        if (isset($response['description'])) {
            $formatted['description'] = $response['description'];
        }

        if (isset($response['status'])) {
            $formatted['status'] = $response['status'];
        }

        if (isset($response['test_result'])) {
            $formatted['test_result'] = $response['test_result'];
        }

        if (isset($response['response_time'])) {
            $formatted['response_time'] = $response['response_time'];
        }

        if (isset($response['retry_result'])) {
            $formatted['retry_result'] = $response['retry_result'];
        }

        // 如果是列表响应，添加分页信息
        if (isset($response['webhooks'])) {
            $formatted['webhooks'] = $response['webhooks'];
            $formatted['pagination'] = $response['pagination'] ?? [];
        }

        // 如果是日志响应，添加日志信息
        if (isset($response['logs'])) {
            $formatted['logs'] = $response['logs'];
            $formatted['pagination'] = $response['pagination'] ?? [];
        }

        return $formatted;
    }

    /**
     * 处理Webhook错误
     *
     * @param \Throwable $exception 异常
     * @param string $operation 操作类型
     * @param array<string, mixed> $context 上下文信息
     * @return array<string, mixed> 错误响应
     */
    private function handleWebhookError(\Throwable $exception, string $operation, array $context): array
    {
        $this->loggerService->error('TencentMeeting Webhook操作失败: ' . $operation, [
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
            'error' => 'Webhook操作失败: ' . $exception->getMessage(),
            'code' => 500,
            'operation' => $operation,
            'context' => $context,
        ];
    }
}

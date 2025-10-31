<?php

namespace Tourze\TencentMeetingBundle\Trait;

/**
 * Webhook CRUD操作trait
 *
 * 提供Webhook基础的增删改查操作
 */
trait WebhookCrudOperationsTrait
{
    /**
     * 获取Webhook信息
     *
     * @param string $webhookId Webhook ID
     * @return array<string, mixed> Webhook信息
     */
    public function getWebhook(string $webhookId): array
    {
        try {
            $response = $this->get('/v1/webhooks/' . urlencode($webhookId));

            return $this->formatWebhookResponse($response);
        } catch (\Throwable $e) {
            return $this->handleWebhookError($e, 'getWebhook', ['webhook_id' => $webhookId]);
        }
    }

    /**
     * 创建Webhook
     *
     * @param array<string, mixed> $webhookData Webhook数据
     * @return array<string, mixed> 创建结果
     */
    public function createWebhook(array $webhookData): array
    {
        try {
            // 验证Webhook数据
            $this->validateWebhookData($webhookData);

            // 构建请求参数
            $params = $this->buildWebhookParams($webhookData);

            // 发送请求
            $response = $this->post('/v1/webhooks', $params);

            return $this->formatWebhookResponse($response);
        } catch (\Throwable $e) {
            return $this->handleWebhookError($e, 'createWebhook', $webhookData);
        }
    }

    /**
     * 更新Webhook
     *
     * @param string $webhookId Webhook ID
     * @param array<string, mixed> $updateData 更新数据
     * @return array<string, mixed> 更新结果
     */
    public function updateWebhook(string $webhookId, array $updateData): array
    {
        try {
            // 验证更新数据
            $this->validateWebhookData($updateData, true);

            // 构建请求参数
            $params = $this->buildWebhookParams($updateData);

            // 发送请求
            $response = $this->put('/v1/webhooks/' . urlencode($webhookId), $params);

            return $this->formatWebhookResponse($response);
        } catch (\Throwable $e) {
            return $this->handleWebhookError($e, 'updateWebhook', [
                'webhook_id' => $webhookId,
                'update_data' => $updateData,
            ]);
        }
    }

    /**
     * 删除Webhook
     *
     * @param string $webhookId Webhook ID
     * @return array<string, mixed> 删除结果
     */
    public function deleteWebhook(string $webhookId): array
    {
        try {
            $response = $this->delete('/v1/webhooks/' . urlencode($webhookId));

            return $this->formatWebhookResponse($response);
        } catch (\Throwable $e) {
            return $this->handleWebhookError($e, 'deleteWebhook', ['webhook_id' => $webhookId]);
        }
    }

    /**
     * 获取Webhook列表
     *
     * @param array<string, mixed> $filters 过滤条件
     * @return array<string, mixed> Webhook列表
     */
    public function listWebhooks(array $filters = []): array
    {
        try {
            // 构建查询参数
            $queryParams = $this->buildWebhookListParams($filters);

            // 发送请求
            $response = $this->get('/v1/webhooks?' . http_build_query($queryParams));

            return $this->formatWebhookResponse($response);
        } catch (\Throwable $e) {
            return $this->handleWebhookError($e, 'listWebhooks', $filters);
        }
    }
}

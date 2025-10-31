<?php

namespace Tourze\TencentMeetingBundle\Trait;

/**
 * Webhook查询操作trait
 *
 * 提供Webhook查询相关的操作，如获取事件、状态、日志等
 */
trait WebhookQueryTrait
{
    /**
     * 获取Webhook事件
     *
     * @param string $webhookId Webhook ID
     * @return array<string, mixed> 事件列表
     */
    public function getWebhookEvents(string $webhookId): array
    {
        try {
            $response = $this->get('/v1/webhooks/' . urlencode($webhookId) . '/events');

            return $this->formatWebhookResponse($response);
        } catch (\Throwable $e) {
            return $this->handleWebhookError($e, 'getWebhookEvents', ['webhook_id' => $webhookId]);
        }
    }

    /**
     * 获取Webhook状态
     *
     * @param string $webhookId Webhook ID
     * @return array<string, mixed> Webhook状态
     */
    public function getWebhookStatus(string $webhookId): array
    {
        try {
            $response = $this->get('/v1/webhooks/' . urlencode($webhookId) . '/status');

            return $this->formatWebhookResponse($response);
        } catch (\Throwable $e) {
            return $this->handleWebhookError($e, 'getWebhookStatus', ['webhook_id' => $webhookId]);
        }
    }

    /**
     * 获取Webhook日志
     *
     * @param string $webhookId Webhook ID
     * @param array<string, mixed> $filters 过滤条件
     * @return array<string, mixed> 日志列表
     */
    public function getWebhookLogs(string $webhookId, array $filters = []): array
    {
        try {
            // 构建查询参数
            $queryParams = $this->buildWebhookLogParams($filters);

            $response = $this->get('/v1/webhooks/' . urlencode($webhookId) . '/logs?' . http_build_query($queryParams));

            return $this->formatWebhookResponse($response);
        } catch (\Throwable $e) {
            return $this->handleWebhookError($e, 'getWebhookLogs', [
                'webhook_id' => $webhookId,
                'filters' => $filters,
            ]);
        }
    }
}

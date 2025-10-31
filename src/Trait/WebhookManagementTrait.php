<?php

namespace Tourze\TencentMeetingBundle\Trait;

/**
 * Webhook管理操作trait
 *
 * 提供Webhook管理相关的操作，如测试、启用、禁用、重试等
 */
trait WebhookManagementTrait
{
    /**
     * 测试Webhook
     *
     * @param string $webhookId Webhook ID
     * @return array<string, mixed> 测试结果
     */
    public function testWebhook(string $webhookId): array
    {
        try {
            $response = $this->post('/v1/webhooks/' . urlencode($webhookId) . '/test', []);

            return $this->formatWebhookResponse($response);
        } catch (\Throwable $e) {
            return $this->handleWebhookError($e, 'testWebhook', ['webhook_id' => $webhookId]);
        }
    }

    /**
     * 启用Webhook
     *
     * @param string $webhookId Webhook ID
     * @return array<string, mixed> 启用结果
     */
    public function enableWebhook(string $webhookId): array
    {
        try {
            $response = $this->post('/v1/webhooks/' . urlencode($webhookId) . '/enable', []);

            return $this->formatWebhookResponse($response);
        } catch (\Throwable $e) {
            return $this->handleWebhookError($e, 'enableWebhook', ['webhook_id' => $webhookId]);
        }
    }

    /**
     * 禁用Webhook
     *
     * @param string $webhookId Webhook ID
     * @return array<string, mixed> 禁用结果
     */
    public function disableWebhook(string $webhookId): array
    {
        try {
            $response = $this->post('/v1/webhooks/' . urlencode($webhookId) . '/disable', []);

            return $this->formatWebhookResponse($response);
        } catch (\Throwable $e) {
            return $this->handleWebhookError($e, 'disableWebhook', ['webhook_id' => $webhookId]);
        }
    }

    /**
     * 重试Webhook
     *
     * @param string $webhookId Webhook ID
     * @param string $eventId 事件ID
     * @return array<string, mixed> 重试结果
     */
    public function retryWebhook(string $webhookId, string $eventId): array
    {
        try {
            $response = $this->post('/v1/webhooks/' . urlencode($webhookId) . '/retry', [
                'event_id' => $eventId,
            ]);

            return $this->formatWebhookResponse($response);
        } catch (\Throwable $e) {
            return $this->handleWebhookError($e, 'retryWebhook', [
                'webhook_id' => $webhookId,
                'event_id' => $eventId,
            ]);
        }
    }
}

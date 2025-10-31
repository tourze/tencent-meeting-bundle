<?php

namespace Tourze\TencentMeetingBundle\Trait;

use Tourze\TencentMeetingBundle\Exception\ApiException;

/**
 * Webhook验证trait
 *
 * 提供Webhook数据验证的通用方法，降低验证逻辑的复杂度
 */
trait WebhookValidatorTrait
{
    /**
     * 验证Webhook数据
     *
     * @param array<string, mixed> $webhookData Webhook数据
     * @param bool $isUpdate 是否为更新操作
     */
    private function validateWebhookData(array $webhookData, bool $isUpdate = false): void
    {
        $this->validateRequiredFields($webhookData, $isUpdate);

        if (isset($webhookData['url'])) {
            $this->validateWebhookUrl($webhookData['url']);
        }

        if (isset($webhookData['events'])) {
            $this->validateWebhookEvents($webhookData['events']);
        }

        if (isset($webhookData['secret'])) {
            $this->validateWebhookSecret($webhookData['secret']);
        }
    }

    /**
     * 验证必需字段
     *
     * @param array<string, mixed> $webhookData Webhook数据
     * @param bool $isUpdate 是否为更新操作
     */
    private function validateRequiredFields(array $webhookData, bool $isUpdate): void
    {
        if ($isUpdate) {
            return;
        }

        $requiredFields = ['url', 'events'];

        foreach ($requiredFields as $field) {
            if (!isset($webhookData[$field]) || '' === $webhookData[$field]) {
                throw new ApiException("Webhook数据缺少必需字段: {$field}");
            }
        }
    }

    /**
     * 验证URL格式
     *
     * @param mixed $url URL值
     */
    private function validateWebhookUrl(mixed $url): void
    {
        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw new ApiException('无效的URL格式');
        }
    }

    /**
     * 验证事件列表
     *
     * @param mixed $events 事件数据
     */
    private function validateWebhookEvents(mixed $events): void
    {
        if (!is_array($events)) {
            throw new ApiException('事件列表必须是数组');
        }

        $validEvents = $this->getValidWebhookEvents();

        foreach ($events as $event) {
            if (!is_string($event) || !in_array($event, $validEvents, true)) {
                $eventStr = is_string($event) ? $event : gettype($event);
                throw new ApiException("无效的事件类型: {$eventStr}");
            }
        }
    }

    /**
     * 验证密钥
     *
     * @param mixed $secret 密钥值
     */
    private function validateWebhookSecret(mixed $secret): void
    {
        if (!is_string($secret) || strlen($secret) < 8) {
            throw new ApiException('Webhook密钥必须是至少8个字符的字符串');
        }
    }

    /**
     * 获取有效的Webhook事件列表
     *
     * @return array<string> 有效事件列表
     */
    private function getValidWebhookEvents(): array
    {
        return [
            'meeting.created', 'meeting.updated', 'meeting.deleted',
            'meeting.started', 'meeting.ended', 'meeting.cancelled',
            'user.joined', 'user.left', 'recording.started',
            'recording.ended', 'recording.ready',
        ];
    }
}

<?php

namespace Tourze\TencentMeetingBundle\Factory;

use Tourze\TencentMeetingBundle\Service\MeetingClient;
use Tourze\TencentMeetingBundle\Service\RecordingClient;
use Tourze\TencentMeetingBundle\Service\RoomClient;
use Tourze\TencentMeetingBundle\Service\SyncService;
use Tourze\TencentMeetingBundle\Service\UserClient;
use Tourze\TencentMeetingBundle\Service\WebhookClient;

/**
 * 客户端工厂接口
 *
 * 定义客户端工厂的标准接口
 */
interface ClientFactoryInterface
{
    /**
     * 创建会议客户端
     */
    public function createMeetingClient(): MeetingClient;

    /**
     * 创建用户客户端
     */
    public function createUserClient(): UserClient;

    /**
     * 创建会议室客户端
     */
    public function createRoomClient(): RoomClient;

    /**
     * 创建录制客户端
     */
    public function createRecordingClient(): RecordingClient;

    /**
     * 创建Webhook客户端
     */
    public function createWebhookClient(): WebhookClient;

    /**
     * 创建同步服务
     */
    public function createSyncService(): SyncService;

    /**
     * 获取会议客户端
     */
    public function getMeetingClient(): MeetingClient;

    /**
     * 获取用户客户端
     */
    public function getUserClient(): UserClient;

    /**
     * 获取会议室客户端
     */
    public function getRoomClient(): RoomClient;

    /**
     * 获取录制客户端
     */
    public function getRecordingClient(): RecordingClient;

    /**
     * 获取Webhook客户端
     */
    public function getWebhookClient(): WebhookClient;

    /**
     * 获取同步服务
     */
    public function getSyncService(): SyncService;

    /**
     * 配置工厂
     *
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration): void;

    /**
     * 重置工厂
     */
    public function reset(): void;
}

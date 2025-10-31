<?php

namespace Tourze\TencentMeetingBundle\Service;

/**
 * 客户端工厂接口
 *
 * 定义各种API客户端的统一创建接口，为Bundle提供客户端实例化能力
 */
interface ClientFactoryInterface
{
    /**
     * 创建会议客户端
     */
    public function createMeetingClient(): object;

    /**
     * 创建用户客户端
     */
    public function createUserClient(): object;

    /**
     * 创建会议室客户端
     */
    public function createRoomClient(): object;

    /**
     * 创建录制客户端
     */
    public function createRecordingClient(): object;

    /**
     * 创建Webhook客户端
     */
    public function createWebhookClient(): object;

    /**
     * 创建认证服务客户端
     */
    public function createAuthService(): object;

    /**
     * 创建同步服务客户端
     */
    public function createSyncService(): object;
}

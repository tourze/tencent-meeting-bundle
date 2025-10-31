<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Service;

/**
 * 同步服务接口
 *
 * 定义数据同步的统一接口，为Bundle提供与腾讯会议API的数据同步能力
 */
interface SyncServiceInterface
{
    /**
     * 同步会议数据
     *
     * @return array<string, mixed> 同步结果
     */
    public function syncMeetings(): array;

    /**
     * 同步用户数据
     *
     * @return array<string, mixed> 同步结果
     */
    public function syncUsers(): array;

    /**
     * 同步会议室数据
     *
     * @return array<string, mixed> 同步结果
     */
    public function syncRooms(): array;

    /**
     * 同步录制数据
     *
     * @return array<string, mixed> 同步结果
     */
    public function syncRecordings(): array;

    /**
     * 同步Webhook事件数据
     *
     * @return array<string, mixed> 同步结果
     */
    public function syncWebhookEvents(): array;

    /**
     * 同步所有数据
     *
     * @return array<string, mixed> 同步结果
     */
    public function syncAll(): array;

    /**
     * 获取同步状态
     *
     * @return array<string, mixed> 同步状态信息
     */
    public function getSyncStatus(): array;

    /**
     * 获取同步统计信息
     *
     * @return array<string, mixed> 同步统计数据
     */
    public function getSyncStats(): array;
}

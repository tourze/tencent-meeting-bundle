<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Service;

/**
 * 同步统计计算器
 *
 * 负责计算同步相关的统计数据
 */
final class SyncStatisticsCalculator
{
    /**
     * 计算平均同步时间
     *
     * @param array<string, mixed> $syncStats 同步统计数据
     * @return float 平均同步时间（秒）
     */
    public function calculateAverageSyncDuration(array $syncStats): float
    {
        $successfulSyncs = $syncStats['successful_syncs'];
        if (!is_int($successfulSyncs) || 0 === $successfulSyncs) {
            return 0.0;
        }

        $lastDuration = $syncStats['last_sync_duration'];
        if (!is_int($lastDuration)) {
            return 0.0;
        }

        return $lastDuration / $successfulSyncs;
    }

    /**
     * 计算成功率
     *
     * @param array<string, mixed> $syncStats 同步统计数据
     * @return float 成功率（百分比）
     */
    public function calculateSuccessRate(array $syncStats): float
    {
        $totalSyncs = $syncStats['total_syncs'];
        if (!is_int($totalSyncs) || 0 === $totalSyncs) {
            return 0.0;
        }

        $successfulSyncs = $syncStats['successful_syncs'];
        if (!is_int($successfulSyncs)) {
            return 0.0;
        }

        return ($successfulSyncs / $totalSyncs) * 100;
    }

    /**
     * 计算预计剩余时间
     *
     * @param int $syncProgress 同步进度
     * @param int|null $lastSyncTime 上次同步时间
     * @return int 预计剩余时间（秒）
     */
    public function calculateEstimatedRemainingTime(int $syncProgress, ?int $lastSyncTime): int
    {
        if (0 === $syncProgress) {
            return 0;
        }

        $elapsed = $this->calculateElapsedTime($lastSyncTime);
        $rate = $this->calculateSyncRate($syncProgress, $elapsed);

        return $this->calculateRemainingTimeByRate($syncProgress, $rate);
    }

    /**
     * 计算已运行时间
     *
     * @param int|null $lastSyncTime 上次同步时间
     * @return int 已运行时间（秒）
     */
    private function calculateElapsedTime(?int $lastSyncTime): int
    {
        return time() - ($lastSyncTime ?? time());
    }

    /**
     * 计算同步速率
     *
     * @param int $syncProgress 同步进度
     * @param int $elapsed 已运行时间
     * @return float 同步速率
     */
    private function calculateSyncRate(int $syncProgress, int $elapsed): float
    {
        return $elapsed > 0 ? $syncProgress / $elapsed : 0.0;
    }

    /**
     * 根据速率计算剩余时间
     *
     * @param int $syncProgress 同步进度
     * @param float $rate 同步速率
     * @return int 剩余时间（秒）
     */
    private function calculateRemainingTimeByRate(int $syncProgress, float $rate): int
    {
        if ($rate > 0) {
            return (int) ((100 - $syncProgress) / $rate);
        }

        return 0;
    }
}

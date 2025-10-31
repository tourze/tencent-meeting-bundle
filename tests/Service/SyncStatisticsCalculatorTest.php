<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TencentMeetingBundle\Service\SyncStatisticsCalculator;

/**
 * @internal
 */
#[CoversClass(SyncStatisticsCalculator::class)]
final class SyncStatisticsCalculatorTest extends TestCase
{
    private SyncStatisticsCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new SyncStatisticsCalculator();
    }

    public function testCalculateAverageSyncDuration(): void
    {
        $stats = [
            'successful_syncs' => 5,
            'last_sync_duration' => 100,
        ];

        $result = $this->calculator->calculateAverageSyncDuration($stats);
        self::assertSame(20.0, $result);
    }

    public function testCalculateAverageSyncDurationWithZeroSyncs(): void
    {
        $stats = [
            'successful_syncs' => 0,
            'last_sync_duration' => 100,
        ];

        $result = $this->calculator->calculateAverageSyncDuration($stats);
        self::assertSame(0.0, $result);
    }

    public function testCalculateSuccessRate(): void
    {
        $stats = [
            'total_syncs' => 10,
            'successful_syncs' => 8,
        ];

        $result = $this->calculator->calculateSuccessRate($stats);
        self::assertSame(80.0, $result);
    }

    public function testCalculateSuccessRateWithZeroTotal(): void
    {
        $stats = [
            'total_syncs' => 0,
            'successful_syncs' => 0,
        ];

        $result = $this->calculator->calculateSuccessRate($stats);
        self::assertSame(0.0, $result);
    }

    public function testCalculateEstimatedRemainingTime(): void
    {
        $syncProgress = 50;
        $lastSyncTime = time() - 100;

        $result = $this->calculator->calculateEstimatedRemainingTime($syncProgress, $lastSyncTime);
        self::assertGreaterThanOrEqual(0, $result);
    }

    public function testCalculateEstimatedRemainingTimeWithZeroProgress(): void
    {
        $result = $this->calculator->calculateEstimatedRemainingTime(0, null);
        self::assertSame(0, $result);
    }
}

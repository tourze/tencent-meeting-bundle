<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TencentMeetingBundle\Service\SyncService;
use Tourze\TencentMeetingBundle\Service\SyncServiceInterface;

/**
 * @internal
 */
#[CoversClass(SyncService::class)]
#[RunTestsInSeparateProcesses]
final class SyncServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // Integration test setup
    }

    public function testSyncServiceImplementsInterface(): void
    {
        $syncService = self::getService(SyncService::class);
        $this->assertInstanceOf(SyncServiceInterface::class, $syncService);
    }

    public function testSyncServiceCanBeInstantiated(): void
    {
        $syncService = self::getService(SyncService::class);
        $this->assertInstanceOf(SyncService::class, $syncService);
    }

    public function testSyncServiceInitialState(): void
    {
        $syncService = self::getService(SyncService::class);

        $reflection = new \ReflectionClass($syncService);
        $statusProperty = $reflection->getProperty('syncStatus');
        $statusProperty->setAccessible(true);
        $this->assertEquals('idle', $statusProperty->getValue($syncService));

        $progressProperty = $reflection->getProperty('syncProgress');
        $progressProperty->setAccessible(true);
        $this->assertEquals(0, $progressProperty->getValue($syncService));
    }

    public function testSyncServiceHasRequiredMethods(): void
    {
        $syncService = self::getService(SyncService::class);

        $requiredMethods = [
            'syncMeetings',
            'syncUsers',
            'syncRooms',
            'syncRecordings',
            'syncAll',
            'getSyncStatus',
            'getSyncProgress',
            'pauseSync',
            'resumeSync',
            'cancelSync',
            'getSyncLogs',
            'getSyncStats',
            'configureSync',
            'getLastSyncTime',
            'getNextSyncTime',
        ];

        $classMethods = get_class_methods($syncService);

        foreach ($requiredMethods as $method) {
            $this->assertContains($method, $classMethods, "Method {$method} not found in SyncService");
        }
    }

    public function testGetSyncStatus(): void
    {
        $syncService = self::getService(SyncService::class);
        $status = $syncService->getSyncStatus();
        $this->assertArrayHasKey('status', $status);
        $this->assertArrayHasKey('progress', $status);
        $this->assertArrayHasKey('current_task', $status);
    }

    public function testGetSyncProgress(): void
    {
        $syncService = self::getService(SyncService::class);
        $progress = $syncService->getSyncProgress();
        $this->assertArrayHasKey('progress', $progress);
        $this->assertArrayHasKey('current_task', $progress);
        $this->assertArrayHasKey('estimated_remaining_time', $progress);
        $this->assertArrayHasKey('items_processed', $progress);
        $this->assertArrayHasKey('total_items', $progress);
    }

    public function testPauseSync(): void
    {
        $syncService = self::getService(SyncService::class);
        $result = $syncService->pauseSync();
        $this->assertArrayHasKey('success', $result);
    }

    public function testResumeSync(): void
    {
        $syncService = self::getService(SyncService::class);
        $result = $syncService->resumeSync();
        $this->assertArrayHasKey('success', $result);
    }

    public function testCancelSync(): void
    {
        $syncService = self::getService(SyncService::class);
        $result = $syncService->cancelSync();
        $this->assertArrayHasKey('success', $result);
    }

    public function testGetSyncStats(): void
    {
        $syncService = self::getService(SyncService::class);
        $stats = $syncService->getSyncStats();
        $this->assertArrayHasKey('success', $stats);
        $this->assertArrayHasKey('stats', $stats);
        $this->assertArrayHasKey('average_sync_duration', $stats);
        $this->assertArrayHasKey('success_rate', $stats);

        $nestedStats = $stats['stats'];
        $this->assertIsArray($nestedStats);
        $this->assertArrayHasKey('total_syncs', $nestedStats);
        $this->assertArrayHasKey('successful_syncs', $nestedStats);
        $this->assertArrayHasKey('failed_syncs', $nestedStats);
        $this->assertArrayHasKey('last_sync_duration', $nestedStats);
        $this->assertArrayHasKey('items_synced', $nestedStats);
        $this->assertArrayHasKey('errors_encountered', $nestedStats);
    }

    public function testConfigureSync(): void
    {
        $syncService = self::getService(SyncService::class);
        $config = [
            'auto_sync' => true,
            'sync_interval' => 3600,
            'sync_types' => ['meetings', 'users'],
        ];

        $result = $syncService->configureSync($config);
        $this->assertArrayHasKey('success', $result);
    }

    public function testGetLastSyncTime(): void
    {
        $syncService = self::getService(SyncService::class);
        $lastSyncTime = $syncService->getLastSyncTime();

        // lastSyncTime can be null initially or an integer timestamp
        $this->assertTrue(null === $lastSyncTime || $lastSyncTime >= 0);
    }

    public function testGetNextSyncTime(): void
    {
        $syncService = self::getService(SyncService::class);
        $nextSyncTime = $syncService->getNextSyncTime();

        // nextSyncTime can be null initially or an integer timestamp
        $this->assertTrue(null === $nextSyncTime || $nextSyncTime >= 0);
    }

    public function testGetSyncLogs(): void
    {
        $syncService = self::getService(SyncService::class);
        $logs = $syncService->getSyncLogs();
        // 验证返回的日志数组：getSyncLogs() 方法签名保证返回 array
        // 检查日志数量在合理范围内（可能包含初始化日志）
        $this->assertGreaterThanOrEqual(0, count($logs), '日志数量应该大于等于0');
        $this->assertLessThan(100, count($logs), '日志数量应该在合理范围内');
    }

    public function testCanStartSync(): void
    {
        $syncService = self::getService(SyncService::class);
        $canStart = $syncService->canStartSync();

        $this->assertTrue($canStart);
    }

    public function testSyncAllWithSuccessfulSync(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testSyncMeetingsWithSuccessfulSync(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testSyncUsersWithSuccessfulSync(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testSyncRoomsWithSuccessfulSync(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testSyncRecordingsWithSuccessfulSync(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testSyncWebhookEventsWithSuccessfulSync(): void
    {
        self::markTestSkipped('Integration test requires real API responses');
    }

    public function testIsSyncing(): void
    {
        $syncService = self::getService(SyncService::class);
        $isSyncing = $syncService->isSyncing();

        $this->assertFalse($isSyncing);
    }

    public function testIsSyncPaused(): void
    {
        $syncService = self::getService(SyncService::class);
        $isPaused = $syncService->isSyncPaused();

        $this->assertFalse($isPaused);
    }

    public function testIsSyncCancelled(): void
    {
        $syncService = self::getService(SyncService::class);
        $isCancelled = $syncService->isSyncCancelled();

        $this->assertFalse($isCancelled);
    }

    public function testGetSyncState(): void
    {
        $syncService = self::getService(SyncService::class);
        $state = $syncService->getSyncState();
        $this->assertArrayHasKey('status', $state);
        $this->assertArrayHasKey('progress', $state);
        $this->assertArrayHasKey('current_task', $state);
        $this->assertArrayHasKey('paused', $state);
        $this->assertArrayHasKey('cancelled', $state);
        $this->assertArrayHasKey('last_sync', $state);
        $this->assertArrayHasKey('next_sync', $state);
    }
}

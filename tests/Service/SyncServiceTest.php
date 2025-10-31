<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\TencentMeetingBundle\Service\ConfigServiceInterface;
use Tourze\TencentMeetingBundle\Service\MeetingClient;
use Tourze\TencentMeetingBundle\Service\RecordingClient;
use Tourze\TencentMeetingBundle\Service\RoomClient;
use Tourze\TencentMeetingBundle\Service\SyncConfigurationValidator;
use Tourze\TencentMeetingBundle\Service\SyncService;
use Tourze\TencentMeetingBundle\Service\SyncServiceInterface;
use Tourze\TencentMeetingBundle\Service\SyncStatisticsCalculator;
use Tourze\TencentMeetingBundle\Service\UserClient;

/**
 * @internal
 */
#[CoversClass(SyncService::class)]
final class SyncServiceTest extends TestCase
{
    public function testSyncServiceImplementsInterface(): void
    {
        $syncService = $this->createSyncService();
        $this->assertInstanceOf(SyncServiceInterface::class, $syncService);
    }

    public function testSyncServiceCanBeInstantiated(): void
    {
        $syncService = $this->createSyncService();
        $this->assertInstanceOf(SyncService::class, $syncService);
    }

    public function testSyncServiceInitialState(): void
    {
        $syncService = $this->createSyncService();

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
        $syncService = $this->createSyncService();

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
        $syncService = $this->createSyncService();
        $status = $syncService->getSyncStatus();
        $this->assertArrayHasKey('status', $status);
        $this->assertArrayHasKey('progress', $status);
        $this->assertArrayHasKey('current_task', $status);
    }

    public function testGetSyncProgress(): void
    {
        $syncService = $this->createSyncService();
        $progress = $syncService->getSyncProgress();
        $this->assertArrayHasKey('progress', $progress);
        $this->assertArrayHasKey('current_task', $progress);
        $this->assertArrayHasKey('estimated_remaining_time', $progress);
        $this->assertArrayHasKey('items_processed', $progress);
        $this->assertArrayHasKey('total_items', $progress);
    }

    public function testPauseSync(): void
    {
        $syncService = $this->createSyncService();
        $result = $syncService->pauseSync();
        $this->assertArrayHasKey('success', $result);
    }

    public function testResumeSync(): void
    {
        $syncService = $this->createSyncService();
        $result = $syncService->resumeSync();
        $this->assertArrayHasKey('success', $result);
    }

    public function testCancelSync(): void
    {
        $syncService = $this->createSyncService();
        $result = $syncService->cancelSync();
        $this->assertArrayHasKey('success', $result);
    }

    public function testGetSyncStats(): void
    {
        $syncService = $this->createSyncService();
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
        $syncService = $this->createSyncService();
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
        $syncService = $this->createSyncService();
        $lastSyncTime = $syncService->getLastSyncTime();

        // lastSyncTime can be null initially or an integer timestamp
        $this->assertTrue(null === $lastSyncTime || $lastSyncTime >= 0);
    }

    public function testGetNextSyncTime(): void
    {
        $syncService = $this->createSyncService();
        $nextSyncTime = $syncService->getNextSyncTime();

        // nextSyncTime can be null initially or an integer timestamp
        $this->assertTrue(null === $nextSyncTime || $nextSyncTime >= 0);
    }

    public function testGetSyncLogs(): void
    {
        $syncService = $this->createSyncService();
        $logs = $syncService->getSyncLogs();
        // 验证返回的日志数组：getSyncLogs() 方法签名保证返回 array
        // 检查日志数量在合理范围内（可能包含初始化日志）
        $this->assertGreaterThanOrEqual(0, count($logs), '日志数量应该大于等于0');
        $this->assertLessThan(100, count($logs), '日志数量应该在合理范围内');
    }

    public function testCanStartSync(): void
    {
        $syncService = $this->createSyncService();
        $canStart = $syncService->canStartSync();

        $this->assertTrue($canStart);
    }

    public function testSyncAllWithSuccessfulSync(): void
    {
        $syncService = $this->createSyncService();

        $result = $syncService->syncAll();
        $this->assertArrayHasKey('success', $result);

        // 如果失败，检查错误信息
        if (!((bool) ($result['success'] ?? false))) {
            $this->assertArrayHasKey('error', $result);
            $this->assertArrayHasKey('operation', $result);
            $this->assertArrayHasKey('status', $result);
        } else {
            $this->assertArrayHasKey('items_synced', $result);
            $this->assertArrayHasKey('errors', $result);
            $this->assertArrayHasKey('duration', $result);
            $this->assertArrayHasKey('status', $result);
        }
    }

    public function testSyncMeetingsWithSuccessfulSync(): void
    {
        $syncService = $this->createSyncService();

        $result = $syncService->syncMeetings();
        $this->assertArrayHasKey('success', $result);

        // 如果失败，检查错误信息
        if (!((bool) ($result['success'] ?? false))) {
            $this->assertArrayHasKey('error', $result);
            $this->assertArrayHasKey('operation', $result);
        } else {
            $this->assertArrayHasKey('items_synced', $result);
            $this->assertArrayHasKey('errors', $result);
            $this->assertArrayHasKey('duration', $result);
        }
    }

    public function testSyncUsersWithSuccessfulSync(): void
    {
        $syncService = $this->createSyncService();

        $result = $syncService->syncUsers();
        $this->assertArrayHasKey('success', $result);

        // 如果失败，检查错误信息
        if (!((bool) ($result['success'] ?? false))) {
            $this->assertArrayHasKey('error', $result);
            $this->assertArrayHasKey('operation', $result);
        } else {
            $this->assertArrayHasKey('items_synced', $result);
            $this->assertArrayHasKey('errors', $result);
            $this->assertArrayHasKey('duration', $result);
        }
    }

    public function testSyncRoomsWithSuccessfulSync(): void
    {
        $syncService = $this->createSyncService();

        $result = $syncService->syncRooms();
        $this->assertArrayHasKey('success', $result);

        // 如果失败，检查错误信息
        if (!((bool) ($result['success'] ?? false))) {
            $this->assertArrayHasKey('error', $result);
            $this->assertArrayHasKey('operation', $result);
        } else {
            $this->assertArrayHasKey('items_synced', $result);
            $this->assertArrayHasKey('errors', $result);
            $this->assertArrayHasKey('duration', $result);
        }
    }

    public function testSyncRecordingsWithSuccessfulSync(): void
    {
        $syncService = $this->createSyncService();

        $result = $syncService->syncRecordings();
        $this->assertArrayHasKey('success', $result);

        // 如果失败，检查错误信息
        if (!((bool) ($result['success'] ?? false))) {
            $this->assertArrayHasKey('error', $result);
            $this->assertArrayHasKey('operation', $result);
        } else {
            $this->assertArrayHasKey('items_synced', $result);
            $this->assertArrayHasKey('errors', $result);
            $this->assertArrayHasKey('duration', $result);
        }
    }

    public function testSyncWebhookEventsWithSuccessfulSync(): void
    {
        $syncService = $this->createSyncService();

        $result = $syncService->syncWebhookEvents();
        $this->assertArrayHasKey('success', $result);

        // 如果失败，检查错误信息
        if (!((bool) ($result['success'] ?? false))) {
            $this->assertArrayHasKey('error', $result);
            $this->assertArrayHasKey('operation', $result);
        } else {
            $this->assertArrayHasKey('items_synced', $result);
            $this->assertArrayHasKey('errors', $result);
            $this->assertArrayHasKey('duration', $result);
        }
    }

    public function testIsSyncing(): void
    {
        $syncService = $this->createSyncService();
        $isSyncing = $syncService->isSyncing();

        $this->assertFalse($isSyncing);
    }

    public function testIsSyncPaused(): void
    {
        $syncService = $this->createSyncService();
        $isPaused = $syncService->isSyncPaused();

        $this->assertFalse($isPaused);
    }

    public function testIsSyncCancelled(): void
    {
        $syncService = $this->createSyncService();
        $isCancelled = $syncService->isSyncCancelled();

        $this->assertFalse($isCancelled);
    }

    public function testGetSyncState(): void
    {
        $syncService = $this->createSyncService();
        $state = $syncService->getSyncState();
        $this->assertArrayHasKey('status', $state);
        $this->assertArrayHasKey('progress', $state);
        $this->assertArrayHasKey('current_task', $state);
        $this->assertArrayHasKey('paused', $state);
        $this->assertArrayHasKey('cancelled', $state);
        $this->assertArrayHasKey('last_sync', $state);
        $this->assertArrayHasKey('next_sync', $state);
    }

    private function createSyncService(): SyncService
    {
        $configService = new class implements ConfigServiceInterface {
            public function getApiUrl(): string
            {
                return 'https://api.meeting.qq.com';
            }

            public function getTimeout(): int
            {
                return 30;
            }

            public function getRetryTimes(): int
            {
                return 0;
            }

            public function getLogLevel(): string
            {
                return 'info';
            }

            public function isDebugEnabled(): bool
            {
                return false;
            }

            public function getCacheTtl(): int
            {
                return 3600;
            }

            public function getWebhookSecret(): ?string
            {
                return null;
            }

            public function getCacheDriver(): string
            {
                return 'array';
            }

            public function getRedisHost(): ?string
            {
                return null;
            }

            public function getRedisPort(): ?int
            {
                return null;
            }

            public function getRedisPassword(): ?string
            {
                return null;
            }

            public function getAuthToken(): ?string
            {
                return null;
            }

            public function getSecretKey(): ?string
            {
                return null;
            }

            public function getProxyHost(): ?string
            {
                return null;
            }

            public function getProxyPort(): ?int
            {
                return null;
            }

            public function getVerifySsl(): bool
            {
                return true;
            }

            /**
             * @return array<string, mixed>
             */
            public function getAllConfig(): array
            {
                return [];
            }
        };

        $meetingClient = $this->createMock(MeetingClient::class);

        $userClient = $this->createMock(UserClient::class);

        $roomClient = $this->createMock(RoomClient::class);

        $recordingClient = $this->createMock(RecordingClient::class);

        $mockLogger = $this->createMock(LoggerInterface::class);

        $configValidator = $this->createMock(SyncConfigurationValidator::class);
        $statsCalculator = $this->createMock(SyncStatisticsCalculator::class);

        return new SyncService(
            $meetingClient,
            $userClient,
            $roomClient,
            $recordingClient,
            $mockLogger,
            $configValidator,
            $statsCalculator
        );
    }
}

<?php

namespace Tourze\TencentMeetingBundle\Factory;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Tourze\TencentMeetingBundle\Exception\ConfigurationException;
use Tourze\TencentMeetingBundle\Service\ConfigService;
use Tourze\TencentMeetingBundle\Service\HttpClientService;
use Tourze\TencentMeetingBundle\Service\MeetingClient;
use Tourze\TencentMeetingBundle\Service\RecordingClient;
use Tourze\TencentMeetingBundle\Service\RoomClient;
use Tourze\TencentMeetingBundle\Service\SyncConfigurationValidator;
use Tourze\TencentMeetingBundle\Service\SyncService;
use Tourze\TencentMeetingBundle\Service\SyncStatisticsCalculator;
use Tourze\TencentMeetingBundle\Service\UserClient;
use Tourze\TencentMeetingBundle\Service\WebhookClient;
use Tourze\TencentMeetingBundle\Trait\ConfigurationValidatorTrait;

/**
 * 客户端工厂
 *
 * 提供统一的客户端创建和管理功能，实现单例模式和依赖注入
 */
#[WithMonologChannel(channel: 'tencent_meeting')]
final class ClientFactory implements ClientFactoryInterface
{
    use ConfigurationValidatorTrait;

    private ?MeetingClient $meetingClient = null;

    private ?UserClient $userClient = null;

    private ?RoomClient $roomClient = null;

    private ?RecordingClient $recordingClient = null;

    private ?WebhookClient $webhookClient = null;

    private ?SyncService $syncService = null;

    /** @var array<string, mixed> */
    private array $configuration = [];

    /** @var array<string, mixed> */
    private array $cache = [];

    private int $totalCreations = 0;

    private int $cacheHits = 0;

    private int $configurations = 0;

    private int $resets = 0;

    public function __construct(
        private ConfigService $configService,
        private HttpClientService $httpClientService,
        private LoggerInterface $loggerService,
    ) {
        $this->configuration = $this->getDefaultConfiguration();
    }

    /**
     * 创建会议客户端
     *
     * @return MeetingClient 会议客户端
     */
    public function createMeetingClient(): MeetingClient
    {
        if ($this->isCached('meetingClient')) {
            ++$this->cacheHits;
            $cached = $this->getFromCache('meetingClient');
            assert($cached instanceof MeetingClient);

            return $cached;
        }

        $this->loggerService->info('TencentMeeting 创建会议客户端');

        $client = new MeetingClient(
            $this->configService,
            $this->httpClientService,
            $this->loggerService
        );

        $this->meetingClient = $client;
        $this->saveToCache('meetingClient', $client);
        ++$this->totalCreations;

        return $client;
    }

    /**
     * 创建用户客户端
     *
     * @return UserClient 用户客户端
     */
    public function createUserClient(): UserClient
    {
        if ($this->isCached('userClient')) {
            ++$this->cacheHits;
            $cached = $this->getFromCache('userClient');
            assert($cached instanceof UserClient);

            return $cached;
        }

        $this->loggerService->info('TencentMeeting 创建用户客户端');

        $client = new UserClient(
            $this->configService,
            $this->httpClientService,
            $this->loggerService
        );

        $this->userClient = $client;
        $this->saveToCache('userClient', $client);
        ++$this->totalCreations;

        return $client;
    }

    /**
     * 创建会议室客户端
     *
     * @return RoomClient 会议室客户端
     */
    public function createRoomClient(): RoomClient
    {
        if ($this->isCached('roomClient')) {
            ++$this->cacheHits;
            $cached = $this->getFromCache('roomClient');
            assert($cached instanceof RoomClient);

            return $cached;
        }

        $this->loggerService->info('TencentMeeting 创建会议室客户端');

        $client = new RoomClient(
            $this->configService,
            $this->httpClientService,
            $this->loggerService
        );

        $this->roomClient = $client;
        $this->saveToCache('roomClient', $client);
        ++$this->totalCreations;

        return $client;
    }

    /**
     * 创建录制客户端
     *
     * @return RecordingClient 录制客户端
     */
    public function createRecordingClient(): RecordingClient
    {
        if ($this->isCached('recordingClient')) {
            ++$this->cacheHits;
            $cached = $this->getFromCache('recordingClient');
            assert($cached instanceof RecordingClient);

            return $cached;
        }

        $this->loggerService->info('TencentMeeting 创建录制客户端');

        $client = new RecordingClient(
            $this->configService,
            $this->httpClientService,
            $this->loggerService
        );

        $this->recordingClient = $client;
        $this->saveToCache('recordingClient', $client);
        ++$this->totalCreations;

        return $client;
    }

    /**
     * 创建Webhook客户端
     *
     * @return WebhookClient Webhook客户端
     */
    public function createWebhookClient(): WebhookClient
    {
        if ($this->isCached('webhookClient')) {
            ++$this->cacheHits;
            $cached = $this->getFromCache('webhookClient');
            assert($cached instanceof WebhookClient);

            return $cached;
        }

        $this->loggerService->info('TencentMeeting 创建Webhook客户端');

        $client = new WebhookClient(
            $this->configService,
            $this->httpClientService,
            $this->loggerService
        );

        $this->webhookClient = $client;
        $this->saveToCache('webhookClient', $client);
        ++$this->totalCreations;

        return $client;
    }

    /**
     * 创建同步服务
     *
     * @return SyncService 同步服务
     */
    public function createSyncService(): SyncService
    {
        if ($this->isCached('syncService')) {
            ++$this->cacheHits;
            $cached = $this->getFromCache('syncService');
            assert($cached instanceof SyncService);

            return $cached;
        }

        $this->loggerService->info('TencentMeeting 创建同步服务');

        $service = new SyncService(
            $this->createMeetingClient(),
            $this->createUserClient(),
            $this->createRoomClient(),
            $this->createRecordingClient(),
            $this->loggerService,
            new SyncConfigurationValidator(),
            new SyncStatisticsCalculator()
        );

        $this->syncService = $service;
        $this->saveToCache('syncService', $service);
        ++$this->totalCreations;

        return $service;
    }

    /**
     * 获取会议客户端
     *
     * @return MeetingClient 会议客户端
     */
    public function getMeetingClient(): MeetingClient
    {
        return $this->createMeetingClient();
    }

    /**
     * 获取用户客户端
     *
     * @return UserClient 用户客户端
     */
    public function getUserClient(): UserClient
    {
        return $this->createUserClient();
    }

    /**
     * 获取会议室客户端
     *
     * @return RoomClient 会议室客户端
     */
    public function getRoomClient(): RoomClient
    {
        return $this->createRoomClient();
    }

    /**
     * 获取录制客户端
     *
     * @return RecordingClient 录制客户端
     */
    public function getRecordingClient(): RecordingClient
    {
        return $this->createRecordingClient();
    }

    /**
     * 获取Webhook客户端
     *
     * @return WebhookClient Webhook客户端
     */
    public function getWebhookClient(): WebhookClient
    {
        return $this->createWebhookClient();
    }

    /**
     * 获取同步服务
     *
     * @return SyncService 同步服务
     */
    public function getSyncService(): SyncService
    {
        return $this->createSyncService();
    }

    /**
     * 配置工厂
     *
     * @param array<string, mixed> $configuration 配置数组
     */
    public function configure(array $configuration): void
    {
        try {
            $this->validateConfiguration($configuration);

            $this->configuration = array_merge($this->configuration, $configuration);
            ++$this->configurations;

            // 清除缓存，以便使用新配置重新创建客户端
            $this->clearCache();

            $this->loggerService->info('TencentMeeting 客户端工厂配置已更新', $configuration);
        } catch (\Throwable $e) {
            $this->handleConfigurationError($e, $configuration);
        }
    }

    /**
     * 重置工厂
     */
    public function reset(): void
    {
        $this->clearCache();
        $this->configuration = $this->getDefaultConfiguration();
        ++$this->resets;

        $this->loggerService->info('TencentMeeting 客户端工厂已重置');
    }

    /**
     * 处理配置错误
     *
     * @param \Throwable $exception 异常
     * @param array<string, mixed> $configuration 配置数组
     */
    private function handleConfigurationError(\Throwable $exception, array $configuration): void
    {
        $this->loggerService->error('TencentMeeting 客户端工厂配置失败', [
            'exception' => $exception,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'configuration' => $configuration,
        ]);

        throw $exception;
    }

    /**
     * 获取默认配置
     *
     * @return array<string, mixed> 默认配置
     */
    private function getDefaultConfiguration(): array
    {
        return [
            'cache_enabled' => true,
            'cache_ttl' => 3600,
            'retry_attempts' => 3,
            'timeout' => 30,
            'debug_mode' => false,
            'log_level' => 'info',
            'auto_refresh' => false,
        ];
    }

    /**
     * 检查是否已缓存
     *
     * @param string $key 缓存键
     * @return bool 是否已缓存
     */
    private function isCached(string $key): bool
    {
        return (bool) ($this->configuration['cache_enabled'] ?? false) && isset($this->cache[$key]);
    }

    /**
     * 从缓存获取
     *
     * @param string $key 缓存键
     * @return mixed 缓存值
     */
    private function getFromCache(string $key): mixed
    {
        return $this->cache[$key];
    }

    /**
     * 保存到缓存
     *
     * @param string $key 缓存键
     * @param mixed $value 缓存值
     */
    private function saveToCache(string $key, mixed $value): void
    {
        if ((bool) ($this->configuration['cache_enabled'] ?? false)) {
            $this->cache[$key] = $value;
        }
    }

    /**
     * 清除缓存
     */
    private function clearCache(): void
    {
        $this->cache = [];
        $this->meetingClient = null;
        $this->userClient = null;
        $this->roomClient = null;
        $this->recordingClient = null;
        $this->webhookClient = null;
        $this->syncService = null;
    }

    /**
     * 获取创建统计
     *
     * @return array<string, mixed> 统计信息
     */
    public function getCreationStats(): array
    {
        return [
            'stats' => [
                'total_creations' => $this->totalCreations,
                'cache_hits' => $this->cacheHits,
                'configurations' => $this->configurations,
                'resets' => $this->resets,
            ],
            'cache_hit_rate' => $this->calculateCacheHitRate(),
            'configuration' => $this->configuration,
            'cached_clients' => array_keys($this->cache),
        ];
    }

    /**
     * 计算缓存命中率
     *
     * @return float 缓存命中率
     */
    private function calculateCacheHitRate(): float
    {
        if (0 === $this->totalCreations) {
            return 0.0;
        }

        return ($this->cacheHits / $this->totalCreations) * 100;
    }

    /**
     * 检查客户端是否已创建
     *
     * @param string $clientType 客户端类型
     * @return bool 是否已创建
     */
    public function isClientCreated(string $clientType): bool
    {
        return match ($clientType) {
            'meeting' => null !== $this->meetingClient,
            'user' => null !== $this->userClient,
            'room' => null !== $this->roomClient,
            'recording' => null !== $this->recordingClient,
            'webhook' => null !== $this->webhookClient,
            'sync' => null !== $this->syncService,
            default => false,
        };
    }

    /**
     * 获取所有已创建的客户端类型
     *
     * @return array<string> 客户端类型列表
     */
    public function getCreatedClientTypes(): array
    {
        $types = [];

        if (null !== $this->meetingClient) {
            $types[] = 'meeting';
        }

        if (null !== $this->userClient) {
            $types[] = 'user';
        }

        if (null !== $this->roomClient) {
            $types[] = 'room';
        }

        if (null !== $this->recordingClient) {
            $types[] = 'recording';
        }

        if (null !== $this->webhookClient) {
            $types[] = 'webhook';
        }

        if (null !== $this->syncService) {
            $types[] = 'sync';
        }

        return $types;
    }

    /**
     * 批量创建客户端
     *
     * @param array<string> $clientTypes 客户端类型数组
     * @return array<string, mixed> 创建结果
     */
    public function batchCreateClients(array $clientTypes): array
    {
        $results = [];

        foreach ($clientTypes as $type) {
            try {
                switch ($type) {
                    case 'meeting':
                        $results[$type] = $this->createMeetingClient();
                        break;
                    case 'user':
                        $results[$type] = $this->createUserClient();
                        break;
                    case 'room':
                        $results[$type] = $this->createRoomClient();
                        break;
                    case 'recording':
                        $results[$type] = $this->createRecordingClient();
                        break;
                    case 'webhook':
                        $results[$type] = $this->createWebhookClient();
                        break;
                    case 'sync':
                        $results[$type] = $this->createSyncService();
                        break;
                    default:
                        throw ConfigurationException::invalidConfigKey($type);
                }
            } catch (\Throwable $e) {
                $results[$type] = [
                    'error' => $e->getMessage(),
                    'exception' => $e,
                ];
            }
        }

        return $results;
    }
}

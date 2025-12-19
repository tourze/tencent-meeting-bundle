<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Service;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

final class CacheService
{
    private TagAwareAdapter $cache;

    private CacheItemPoolInterface $innerCache;

    public function __construct()
    {
        $this->innerCache = $this->createCachePool();
        if ($this->innerCache instanceof AdapterInterface) {
            $this->cache = new TagAwareAdapter($this->innerCache);
        } else {
            $fallbackAdapter = new FilesystemAdapter('tencent_meeting_', 3600);
            $this->cache = new TagAwareAdapter($fallbackAdapter);
        }
    }

    /**
     * 创建缓存池
     */
    private function createCachePool(): CacheItemPoolInterface
    {
        $configService = new ConfigService();
        $cacheDriver = $configService->getCacheDriver();
        $cacheTtl = $configService->getCacheTtl();

        return match ($cacheDriver) {
            'redis' => $this->createRedisCache($cacheTtl),
            'file' => $this->createFileCache($cacheTtl),
            default => $this->createFileCache($cacheTtl),
        };
    }

    /**
     * 创建Redis缓存
     */
    private function createRedisCache(int $ttl): CacheItemPoolInterface
    {
        $configService = new ConfigService();
        $redisHost = $configService->getRedisHost() ?? '127.0.0.1';
        $redisPort = $configService->getRedisPort() ?? 6379;
        $redisPassword = $configService->getRedisPassword();

        $redisConnection = RedisAdapter::createConnection(
            "redis://{$redisHost}:{$redisPort}",
            [
                'password' => $redisPassword,
                'timeout' => 2.0,
            ]
        );

        return new RedisAdapter($redisConnection, 'tencent_meeting_', $ttl);
    }

    /**
     * 创建文件缓存
     */
    private function createFileCache(int $ttl): CacheItemPoolInterface
    {
        return new FilesystemAdapter('tencent_meeting_', $ttl);
    }

    /**
     * 设置缓存值
     * @param array<string> $tags
     */
    public function set(string $key, mixed $value, int $ttl = 3600, array $tags = []): void
    {
        $item = $this->cache->getItem($key);
        $item->set($value);
        $item->expiresAfter($ttl);

        if ([] !== $tags) {
            $item->tag($tags);
        }

        $this->cache->save($item);
    }

    /**
     * 获取缓存值
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->has($key)) {
            return $default;
        }

        $item = $this->cache->getItem($key);

        return $item->get();
    }

    /**
     * 检查缓存是否存在
     */
    public function has(string $key): bool
    {
        return $this->cache->hasItem($key);
    }

    /**
     * 删除缓存
     */
    public function delete(string $key): bool
    {
        return $this->cache->delete($key);
    }

    /**
     * 清空所有缓存
     */
    public function clear(): bool
    {
        return $this->cache->clear();
    }

    /**
     * 根据标签使缓存失效
     */
    public function invalidateTag(string $tag): bool
    {
        return $this->cache->invalidateTags([$tag]);
    }

    /**
     * 获取缓存统计信息
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        // TagAwareAdapter 没有 getStats 方法，返回默认值
        return [
            'hits' => 0,
            'misses' => 0,
            'uptime' => 0,
            'memory_usage' => 0,
            'memory_available' => 0,
        ];
    }

    /**
     * 缓存会议数据
     * @param array<string, mixed> $meetingData
     */
    public function cacheMeeting(string $meetingId, array $meetingData, int $ttl = 3600): void
    {
        $this->set("meeting_{$meetingId}", $meetingData, $ttl, ['meeting', "meeting_{$meetingId}"]);
    }

    /**
     * 获取缓存的会议数据
     * @return array<string, mixed>|null
     */
    public function getCachedMeeting(string $meetingId): ?array
    {
        $result = $this->get("meeting_{$meetingId}");
        if (!is_array($result)) {
            return null;
        }

        /** @var array<string, mixed> */
        return $result;
    }

    /**
     * 使会议缓存失效
     */
    public function invalidateMeetingCache(string $meetingId): void
    {
        $this->delete("meeting_{$meetingId}");
        $this->invalidateTag("meeting_{$meetingId}");
    }

    /**
     * 缓存用户数据
     * @param array<string, mixed> $userData
     */
    public function cacheUser(string $userId, array $userData, int $ttl = 3600): void
    {
        $this->set("user_{$userId}", $userData, $ttl, ['user', "user_{$userId}"]);
    }

    /**
     * 获取缓存的用户数据
     * @return array<string, mixed>|null
     */
    public function getCachedUser(string $userId): ?array
    {
        $result = $this->get("user_{$userId}");
        if (!is_array($result)) {
            return null;
        }

        /** @var array<string, mixed> */
        return $result;
    }

    /**
     * 使用户缓存失效
     */
    public function invalidateUserCache(string $userId): void
    {
        $this->delete("user_{$userId}");
        $this->invalidateTag("user_{$userId}");
    }

    /**
     * 缓存API响应
     * @param array<string, mixed> $responseData
     */
    public function cacheApiResponse(string $apiPath, array $responseData, int $ttl = 300): void
    {
        $cacheKey = 'api_' . md5($apiPath);
        $this->set($cacheKey, $responseData, $ttl, ['api_response']);
    }

    /**
     * 获取缓存的API响应
     * @return array<string, mixed>|null
     */
    public function getCachedApiResponse(string $apiPath): ?array
    {
        $cacheKey = 'api_' . md5($apiPath);
        $result = $this->get($cacheKey);
        if (!is_array($result)) {
            return null;
        }

        /** @var array<string, mixed> */
        return $result;
    }

    /**
     * 使API响应缓存失效
     */
    public function invalidateApiResponseCache(): void
    {
        $this->invalidateTag('api_response');
    }

    /**
     * 缓存配置数据
     */
    public function cacheConfig(string $configKey, mixed $configValue, int $ttl = 1800): void
    {
        $this->set("config_{$configKey}", $configValue, $ttl, ['config']);
    }

    /**
     * 获取缓存的配置数据
     */
    public function getCachedConfig(string $configKey): mixed
    {
        return $this->get("config_{$configKey}");
    }

    /**
     * 使配置缓存失效
     */
    public function invalidateConfigCache(): void
    {
        $this->invalidateTag('config');
    }

    /**
     * 批量删除缓存
     * @param array<string> $keys
     */
    public function deleteMultiple(array $keys): bool
    {
        return $this->cache->deleteItems($keys);
    }

    /**
     * 批量获取缓存
     * @param array<string> $keys
     * @return array<string, mixed>
     */
    public function getMultiple(array $keys, mixed $default = null): array
    {
        $items = $this->cache->getItems($keys);
        $result = [];

        foreach ($items as $key => $item) {
            $result[$key] = $item->isHit() ? $item->get() : $default;
        }

        return $result;
    }

    /**
     * 批量设置缓存
     * @param array<string, mixed> $values
     * @param array<string> $tags
     */
    public function setMultiple(array $values, int $ttl = 3600, array $tags = []): void
    {
        $items = [];

        foreach ($values as $key => $value) {
            $item = $this->cache->getItem($key);
            $item->set($value);
            $item->expiresAfter($ttl);

            if ([] !== $tags) {
                $item->tag($tags);
            }

            $items[] = $item;
        }

        foreach ($items as $item) {
            $this->cache->save($item);
        }
    }
}

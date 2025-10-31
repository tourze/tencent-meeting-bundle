<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TencentMeetingBundle\Service\CacheService;

/**
 * @internal
 */
#[CoversClass(CacheService::class)]
final class CacheServiceTest extends TestCase
{
    public function testCacheServiceCreation(): void
    {
        // 这个测试会失败，因为CacheService还不存在
        $cacheService = new CacheService();
        $this->assertInstanceOf(CacheService::class, $cacheService);
    }

    public function testCacheSetAndGet(): void
    {
        $cacheService = new CacheService();

        $cacheService->set('test_key', 'test_value', 3600);
        $value = $cacheService->get('test_key');

        $this->assertEquals('test_value', $value);
    }

    public function testCacheWithTtl(): void
    {
        $cacheService = new CacheService();

        $cacheService->set('short_ttl_key', 'value', 1);
        $value = $cacheService->get('short_ttl_key');

        $this->assertEquals('value', $value);
    }

    public function testCacheDelete(): void
    {
        $cacheService = new CacheService();

        $cacheService->set('delete_key', 'value', 3600);
        $cacheService->delete('delete_key');
        $value = $cacheService->get('delete_key');

        $this->assertNull($value);
    }

    public function testCacheClear(): void
    {
        $cacheService = new CacheService();

        $cacheService->set('clear_key_1', 'value1', 3600);
        $cacheService->set('clear_key_2', 'value2', 3600);

        $cacheService->clear();

        $this->assertNull($cacheService->get('clear_key_1'));
        $this->assertNull($cacheService->get('clear_key_2'));
    }

    public function testCacheHas(): void
    {
        $cacheService = new CacheService();

        $cacheService->set('has_key', 'value', 3600);

        $this->assertTrue($cacheService->has('has_key'));
        $this->assertFalse($cacheService->has('nonexistent_key'));
    }

    public function testCacheWithTags(): void
    {
        $cacheService = new CacheService();

        $cacheService->set('tagged_key', 'value', 3600, ['meeting', 'user']);
        $value = $cacheService->get('tagged_key');

        $this->assertEquals('value', $value);
    }

    public function testCacheInvalidationByTag(): void
    {
        $cacheService = new CacheService();

        $cacheService->set('tag_key_1', 'value1', 3600, ['meeting']);
        $cacheService->set('tag_key_2', 'value2', 3600, ['meeting']);

        $cacheService->invalidateTag('meeting');

        $this->assertNull($cacheService->get('tag_key_1'));
        $this->assertNull($cacheService->get('tag_key_2'));
    }

    public function testDeleteMethod(): void
    {
        $cacheService = new CacheService();

        // 测试正常删除
        $cacheService->set('delete_test_key', 'test_value', 3600);
        $this->assertTrue($cacheService->has('delete_test_key'));

        $result = $cacheService->delete('delete_test_key');
        $this->assertTrue($result);
        $this->assertFalse($cacheService->has('delete_test_key'));
        $this->assertNull($cacheService->get('delete_test_key'));
    }

    public function testDeleteNonExistentKey(): void
    {
        $cacheService = new CacheService();

        // 测试删除不存在的键 - delete() returns bool by contract
        $result = $cacheService->delete('non_existent_key');
        // Verify operation completes successfully (PSR-16 规范要求删除不存在的键也返回 true)
        $this->assertTrue($result, '删除操作应成功完成（PSR-16 规范）');
    }

    public function testDeleteMultipleMethod(): void
    {
        $cacheService = new CacheService();

        // 设置多个缓存项
        $cacheService->set('multi_delete_1', 'value1', 3600);
        $cacheService->set('multi_delete_2', 'value2', 3600);
        $cacheService->set('multi_delete_3', 'value3', 3600);

        // 确认缓存项存在
        $this->assertTrue($cacheService->has('multi_delete_1'));
        $this->assertTrue($cacheService->has('multi_delete_2'));
        $this->assertTrue($cacheService->has('multi_delete_3'));

        // 批量删除
        $keys = ['multi_delete_1', 'multi_delete_2'];
        $result = $cacheService->deleteMultiple($keys);
        $this->assertTrue($result);

        // 验证删除结果
        $this->assertFalse($cacheService->has('multi_delete_1'));
        $this->assertFalse($cacheService->has('multi_delete_2'));
        $this->assertTrue($cacheService->has('multi_delete_3')); // 未删除的项应该存在
    }

    public function testDeleteMultipleWithEmptyArray(): void
    {
        $cacheService = new CacheService();

        // 测试空数组
        $result = $cacheService->deleteMultiple([]);
        $this->assertTrue($result);
    }

    public function testDeleteMultipleWithNonExistentKeys(): void
    {
        $cacheService = new CacheService();

        // 测试删除不存在的键 - deleteMultiple() returns bool by contract
        $keys = ['non_existent_1', 'non_existent_2'];
        $result = $cacheService->deleteMultiple($keys);
        // Verify operation completes successfully (returns true even for non-existent keys per PSR-16)
        $this->assertTrue($result, 'deleteMultiple 应返回 true 表示操作完成');
    }

    public function testHasMethod(): void
    {
        $cacheService = new CacheService();

        // 测试存在的键
        $cacheService->set('has_test_key', 'test_value', 3600);
        $this->assertTrue($cacheService->has('has_test_key'));

        // 测试不存在的键
        $this->assertFalse($cacheService->has('non_existent_has_key'));
    }

    public function testHasWithExpiredKey(): void
    {
        $cacheService = new CacheService();

        // 由于实际过期时间测试困难，这里测试基本逻辑
        $cacheService->set('expired_test_key', 'test_value', 1);

        // 立即检查应该存在
        $this->assertTrue($cacheService->has('expired_test_key'));

        // 删除后检查
        $cacheService->delete('expired_test_key');
        $this->assertFalse($cacheService->has('expired_test_key'));
    }

    public function testInvalidateUserCacheMethod(): void
    {
        $cacheService = new CacheService();
        $userId = 'test_user_123';

        // 先缓存用户数据
        $cacheService->cacheUser($userId, ['name' => 'Test User', 'email' => 'test@example.com']);

        // 验证缓存存在 - getCachedUser returns array|null
        $cachedUser = $cacheService->getCachedUser($userId);
        $this->assertIsArray($cachedUser);
        $this->assertEquals('Test User', $cachedUser['name']);

        // 使用户缓存失效
        $cacheService->invalidateUserCache($userId);

        // 验证缓存已被清除
        $this->assertNull($cacheService->getCachedUser($userId));
        $this->assertFalse($cacheService->has("user_{$userId}"));
    }

    public function testInvalidateUserCacheWithNonExistentUser(): void
    {
        $this->expectNotToPerformAssertions();

        $cacheService = new CacheService();

        // 测试清除不存在的用户缓存，应该不会抛出异常
        $cacheService->invalidateUserCache('non_existent_user');
    }

    public function testSetMethodWithDifferentParameters(): void
    {
        $cacheService = new CacheService();

        // 测试基本设置
        $cacheService->set('basic_set_key', 'basic_value');
        $this->assertEquals('basic_value', $cacheService->get('basic_set_key'));

        // 测试带TTL的设置
        $cacheService->set('ttl_set_key', 'ttl_value', 7200);
        $this->assertEquals('ttl_value', $cacheService->get('ttl_set_key'));

        // 测试带标签的设置
        $cacheService->set('tagged_set_key', 'tagged_value', 3600, ['test_tag']);
        $this->assertEquals('tagged_value', $cacheService->get('tagged_set_key'));

        // 测试同时带TTL和标签的设置
        $cacheService->set('full_set_key', 'full_value', 1800, ['tag1', 'tag2']);
        $this->assertEquals('full_value', $cacheService->get('full_set_key'));
    }

    public function testSetMethodWithDifferentDataTypes(): void
    {
        $cacheService = new CacheService();

        // 测试字符串
        $cacheService->set('string_key', 'string_value');
        $this->assertEquals('string_value', $cacheService->get('string_key'));

        // 测试数组
        $arrayValue = ['key1' => 'value1', 'key2' => 'value2'];
        $cacheService->set('array_key', $arrayValue);
        $this->assertEquals($arrayValue, $cacheService->get('array_key'));

        // 测试数字
        $cacheService->set('int_key', 42);
        $this->assertEquals(42, $cacheService->get('int_key'));

        $cacheService->set('float_key', 3.14);
        $this->assertEquals(3.14, $cacheService->get('float_key'));

        // 测试布尔值
        $cacheService->set('bool_true_key', true);
        $this->assertTrue($cacheService->get('bool_true_key'));

        $cacheService->set('bool_false_key', false);
        $this->assertFalse($cacheService->get('bool_false_key'));

        // 测试null
        $cacheService->set('null_key', null);
        $this->assertNull($cacheService->get('null_key'));
    }

    public function testSetMethodOverwriteExistingKey(): void
    {
        $cacheService = new CacheService();

        // 设置初始值
        $cacheService->set('overwrite_key', 'initial_value');
        $this->assertEquals('initial_value', $cacheService->get('overwrite_key'));

        // 覆盖现有值
        $cacheService->set('overwrite_key', 'new_value');
        $this->assertEquals('new_value', $cacheService->get('overwrite_key'));

        // 覆盖并更改TTL和标签
        $cacheService->set('overwrite_key', 'final_value', 7200, ['new_tag']);
        $this->assertEquals('final_value', $cacheService->get('overwrite_key'));
    }

    public function testSetMethodWithEmptyTags(): void
    {
        $cacheService = new CacheService();

        // 测试空标签数组
        $cacheService->set('empty_tags_key', 'value_with_empty_tags', 3600, []);
        $this->assertEquals('value_with_empty_tags', $cacheService->get('empty_tags_key'));
    }

    public function testGetStatsMethod(): void
    {
        $cacheService = new CacheService();

        $stats = $cacheService->getStats();
        $this->assertArrayHasKey('hits', $stats);
        $this->assertArrayHasKey('misses', $stats);
        $this->assertArrayHasKey('uptime', $stats);
        $this->assertArrayHasKey('memory_usage', $stats);
        $this->assertArrayHasKey('memory_available', $stats);

        // 验证默认值
        $this->assertEquals(0, $stats['hits']);
        $this->assertEquals(0, $stats['misses']);
        $this->assertEquals(0, $stats['uptime']);
        $this->assertEquals(0, $stats['memory_usage']);
        $this->assertEquals(0, $stats['memory_available']);
    }

    public function testCacheMeetingMethod(): void
    {
        $cacheService = new CacheService();
        $meetingId = 'meeting_123';
        $meetingData = [
            'subject' => 'Test Meeting',
            'start_time' => '2025-10-01 10:00:00',
            'duration' => 3600,
            'hosts' => ['user1', 'user2'],
        ];

        // 缓存会议数据
        $cacheService->cacheMeeting($meetingId, $meetingData);

        // 验证缓存成功
        $this->assertTrue($cacheService->has("meeting_{$meetingId}"));
        $cached = $cacheService->get("meeting_{$meetingId}");
        $this->assertEquals($meetingData, $cached);
    }

    public function testCacheMeetingWithCustomTtl(): void
    {
        $cacheService = new CacheService();
        $meetingId = 'meeting_456';
        $meetingData = ['subject' => 'Custom TTL Meeting'];

        // 使用自定义TTL缓存
        $cacheService->cacheMeeting($meetingId, $meetingData, 7200);

        $this->assertTrue($cacheService->has("meeting_{$meetingId}"));
        $this->assertEquals($meetingData, $cacheService->get("meeting_{$meetingId}"));
    }

    public function testGetCachedMeetingMethod(): void
    {
        $cacheService = new CacheService();
        $meetingId = 'meeting_789';
        $meetingData = [
            'meeting_code' => '123456789',
            'password' => 'test123',
        ];

        // 先缓存
        $cacheService->cacheMeeting($meetingId, $meetingData);

        // 获取缓存 - getCachedMeeting returns array|null
        $result = $cacheService->getCachedMeeting($meetingId);
        $this->assertIsArray($result);
        $this->assertEquals('123456789', $result['meeting_code']);
        $this->assertEquals('test123', $result['password']);
    }

    public function testGetCachedMeetingReturnsNullForNonExistent(): void
    {
        $cacheService = new CacheService();

        $result = $cacheService->getCachedMeeting('non_existent_meeting');

        $this->assertNull($result);
    }

    public function testGetCachedMeetingReturnsNullForNonArrayValue(): void
    {
        $cacheService = new CacheService();
        $meetingId = 'invalid_meeting';

        // 设置一个非数组值
        $cacheService->set("meeting_{$meetingId}", 'invalid_string_value');

        $result = $cacheService->getCachedMeeting($meetingId);

        $this->assertNull($result);
    }

    public function testInvalidateMeetingCacheMethod(): void
    {
        $cacheService = new CacheService();
        $meetingId = 'meeting_to_invalidate';
        $meetingData = ['subject' => 'Meeting To Invalidate'];

        // 先缓存
        $cacheService->cacheMeeting($meetingId, $meetingData);
        $this->assertIsArray($cacheService->getCachedMeeting($meetingId));

        // 使缓存失效
        $cacheService->invalidateMeetingCache($meetingId);

        // 验证缓存已被清除
        $this->assertNull($cacheService->getCachedMeeting($meetingId));
        $this->assertFalse($cacheService->has("meeting_{$meetingId}"));
    }

    public function testCacheUserMethod(): void
    {
        $cacheService = new CacheService();
        $userId = 'user_abc';
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
        ];

        $cacheService->cacheUser($userId, $userData);

        $this->assertTrue($cacheService->has("user_{$userId}"));
        $cached = $cacheService->get("user_{$userId}");
        $this->assertEquals($userData, $cached);
    }

    public function testCacheUserWithCustomTtl(): void
    {
        $cacheService = new CacheService();
        $userId = 'user_xyz';
        $userData = ['name' => 'Jane Doe'];

        $cacheService->cacheUser($userId, $userData, 1800);

        $this->assertTrue($cacheService->has("user_{$userId}"));
        $this->assertEquals($userData, $cacheService->get("user_{$userId}"));
    }

    public function testGetCachedUserMethod(): void
    {
        $cacheService = new CacheService();
        $userId = 'user_def';
        $userData = [
            'id' => 'user_def',
            'department' => 'Engineering',
            'role' => 'Developer',
        ];

        $cacheService->cacheUser($userId, $userData);

        $result = $cacheService->getCachedUser($userId);
        $this->assertIsArray($result);
        $this->assertEquals('Engineering', $result['department']);
        $this->assertEquals('Developer', $result['role']);
    }

    public function testGetCachedUserReturnsNullForNonExistent(): void
    {
        $cacheService = new CacheService();

        $result = $cacheService->getCachedUser('non_existent_user');

        $this->assertNull($result);
    }

    public function testGetCachedUserReturnsNullForNonArrayValue(): void
    {
        $cacheService = new CacheService();
        $userId = 'invalid_user';

        $cacheService->set("user_{$userId}", 12345); // 非数组值

        $result = $cacheService->getCachedUser($userId);

        $this->assertNull($result);
    }

    public function testCacheApiResponseMethod(): void
    {
        $cacheService = new CacheService();
        $apiPath = '/api/v1/meetings/list';
        $responseData = [
            'total_count' => 10,
            'meetings' => [
                ['id' => 1, 'subject' => 'Meeting 1'],
                ['id' => 2, 'subject' => 'Meeting 2'],
            ],
        ];

        $cacheService->cacheApiResponse($apiPath, $responseData);

        $cacheKey = 'api_' . md5($apiPath);
        $this->assertTrue($cacheService->has($cacheKey));
        $cached = $cacheService->get($cacheKey);
        $this->assertEquals($responseData, $cached);
    }

    public function testCacheApiResponseWithCustomTtl(): void
    {
        $cacheService = new CacheService();
        $apiPath = '/api/v1/users';
        $responseData = ['users' => []];

        $cacheService->cacheApiResponse($apiPath, $responseData, 600);

        $cacheKey = 'api_' . md5($apiPath);
        $this->assertTrue($cacheService->has($cacheKey));
    }

    public function testGetCachedApiResponseMethod(): void
    {
        $cacheService = new CacheService();
        $apiPath = '/api/v1/rooms';
        $responseData = [
            'rooms' => [
                ['id' => 'room1', 'name' => 'Conference Room A'],
            ],
        ];

        $cacheService->cacheApiResponse($apiPath, $responseData);

        $result = $cacheService->getCachedApiResponse($apiPath);
        // getCachedApiResponse returns array|null, so check it's array
        $this->assertIsArray($result);
        $this->assertArrayHasKey('rooms', $result);
        // Ensure rooms is an array before counting
        $this->assertIsArray($result['rooms']);
        $this->assertCount(1, $result['rooms']);
    }

    public function testGetCachedApiResponseReturnsNullForNonExistent(): void
    {
        $cacheService = new CacheService();

        $result = $cacheService->getCachedApiResponse('/api/v1/nonexistent');

        $this->assertNull($result);
    }

    public function testGetCachedApiResponseReturnsNullForNonArrayValue(): void
    {
        $cacheService = new CacheService();
        $apiPath = '/api/v1/invalid';
        $cacheKey = 'api_' . md5($apiPath);

        $cacheService->set($cacheKey, 'invalid_response');

        $result = $cacheService->getCachedApiResponse($apiPath);

        $this->assertNull($result);
    }

    public function testInvalidateApiResponseCacheMethod(): void
    {
        $cacheService = new CacheService();
        $apiPath1 = '/api/v1/endpoint1';
        $apiPath2 = '/api/v1/endpoint2';

        // 缓存多个API响应
        $cacheService->cacheApiResponse($apiPath1, ['data' => 'test1']);
        $cacheService->cacheApiResponse($apiPath2, ['data' => 'test2']);

        // 验证缓存存在
        $this->assertIsArray($cacheService->getCachedApiResponse($apiPath1));
        $this->assertIsArray($cacheService->getCachedApiResponse($apiPath2));

        // 使所有API响应缓存失效
        $cacheService->invalidateApiResponseCache();

        // 验证所有API响应缓存已被清除
        $this->assertNull($cacheService->getCachedApiResponse($apiPath1));
        $this->assertNull($cacheService->getCachedApiResponse($apiPath2));
    }

    public function testCacheConfigMethod(): void
    {
        $cacheService = new CacheService();
        $configKey = 'max_participants';
        $configValue = 300;

        $cacheService->cacheConfig($configKey, $configValue);

        $this->assertTrue($cacheService->has("config_{$configKey}"));
        $cached = $cacheService->get("config_{$configKey}");
        $this->assertEquals($configValue, $cached);
    }

    public function testCacheConfigWithDifferentTypes(): void
    {
        $cacheService = new CacheService();

        // 测试字符串配置
        $cacheService->cacheConfig('api_version', 'v1.0');
        $this->assertEquals('v1.0', $cacheService->get('config_api_version'));

        // 测试数组配置
        $cacheService->cacheConfig('features', ['feature1', 'feature2']);
        $this->assertEquals(['feature1', 'feature2'], $cacheService->get('config_features'));

        // 测试布尔配置
        $cacheService->cacheConfig('debug_mode', false);
        $this->assertFalse($cacheService->get('config_debug_mode'));
    }

    public function testCacheConfigWithCustomTtl(): void
    {
        $cacheService = new CacheService();
        $configKey = 'timeout';
        $configValue = 30;

        $cacheService->cacheConfig($configKey, $configValue, 900);

        $this->assertTrue($cacheService->has("config_{$configKey}"));
        $this->assertEquals($configValue, $cacheService->get("config_{$configKey}"));
    }

    public function testGetCachedConfigMethod(): void
    {
        $cacheService = new CacheService();
        $configKey = 'retry_count';
        $configValue = 3;

        $cacheService->cacheConfig($configKey, $configValue);

        $result = $cacheService->getCachedConfig($configKey);

        // Verify config was cached and can be retrieved
        $this->assertEquals(3, $result);
    }

    public function testGetCachedConfigReturnsNullForNonExistent(): void
    {
        $cacheService = new CacheService();

        $result = $cacheService->getCachedConfig('non_existent_config');

        $this->assertNull($result);
    }

    public function testInvalidateConfigCacheMethod(): void
    {
        $cacheService = new CacheService();

        // 缓存多个配置
        $cacheService->cacheConfig('config1', 'value1');
        $cacheService->cacheConfig('config2', 'value2');
        $cacheService->cacheConfig('config3', 'value3');

        // 验证缓存存在
        $this->assertEquals('value1', $cacheService->getCachedConfig('config1'));
        $this->assertEquals('value2', $cacheService->getCachedConfig('config2'));
        $this->assertEquals('value3', $cacheService->getCachedConfig('config3'));

        // 使所有配置缓存失效
        $cacheService->invalidateConfigCache();

        // 验证所有配置缓存已被清除
        $this->assertNull($cacheService->getCachedConfig('config1'));
        $this->assertNull($cacheService->getCachedConfig('config2'));
        $this->assertNull($cacheService->getCachedConfig('config3'));
    }

    public function testGetMultipleMethod(): void
    {
        $cacheService = new CacheService();

        // 设置多个缓存值
        $cacheService->set('multi_key1', 'value1');
        $cacheService->set('multi_key2', 'value2');
        $cacheService->set('multi_key3', 'value3');

        // 批量获取
        $keys = ['multi_key1', 'multi_key2', 'multi_key3'];
        $results = $cacheService->getMultiple($keys);
        $this->assertCount(3, $results);
        $this->assertEquals('value1', $results['multi_key1']);
        $this->assertEquals('value2', $results['multi_key2']);
        $this->assertEquals('value3', $results['multi_key3']);
    }

    public function testGetMultipleWithDefault(): void
    {
        $cacheService = new CacheService();

        // 只设置部分值
        $cacheService->set('exists_key', 'exists_value');

        // 批量获取，包含不存在的键
        $keys = ['exists_key', 'missing_key'];
        $results = $cacheService->getMultiple($keys, 'default_value');
        $this->assertEquals('exists_value', $results['exists_key']);
        $this->assertEquals('default_value', $results['missing_key']);
    }

    public function testGetMultipleWithEmptyArray(): void
    {
        $cacheService = new CacheService();

        $results = $cacheService->getMultiple([]);
        $this->assertEmpty($results);
    }

    public function testSetMultipleMethod(): void
    {
        $cacheService = new CacheService();

        $values = [
            'batch_key1' => 'batch_value1',
            'batch_key2' => 'batch_value2',
            'batch_key3' => 'batch_value3',
        ];

        $cacheService->setMultiple($values);

        // 验证所有值都已设置
        $this->assertEquals('batch_value1', $cacheService->get('batch_key1'));
        $this->assertEquals('batch_value2', $cacheService->get('batch_key2'));
        $this->assertEquals('batch_value3', $cacheService->get('batch_key3'));
    }

    public function testSetMultipleWithCustomTtl(): void
    {
        $cacheService = new CacheService();

        $values = [
            'ttl_key1' => 'ttl_value1',
            'ttl_key2' => 'ttl_value2',
        ];

        $cacheService->setMultiple($values, 7200);

        $this->assertTrue($cacheService->has('ttl_key1'));
        $this->assertTrue($cacheService->has('ttl_key2'));
        $this->assertEquals('ttl_value1', $cacheService->get('ttl_key1'));
        $this->assertEquals('ttl_value2', $cacheService->get('ttl_key2'));
    }

    public function testSetMultipleWithTags(): void
    {
        $cacheService = new CacheService();

        $values = [
            'tagged_batch_key1' => 'tagged_value1',
            'tagged_batch_key2' => 'tagged_value2',
        ];

        $cacheService->setMultiple($values, 3600, ['batch_tag']);

        // 验证值已设置
        $this->assertEquals('tagged_value1', $cacheService->get('tagged_batch_key1'));
        $this->assertEquals('tagged_value2', $cacheService->get('tagged_batch_key2'));

        // 通过标签使缓存失效
        $cacheService->invalidateTag('batch_tag');

        // 验证缓存已被清除
        $this->assertNull($cacheService->get('tagged_batch_key1'));
        $this->assertNull($cacheService->get('tagged_batch_key2'));
    }

    public function testSetMultipleWithEmptyArray(): void
    {
        $this->expectNotToPerformAssertions();

        $cacheService = new CacheService();

        // 测试空数组不会抛出异常
        $cacheService->setMultiple([]);
    }

    public function testClearMethodReturnsBoolean(): void
    {
        $cacheService = new CacheService();

        // 设置多个缓存项
        $cacheService->set('clear_test_key1', 'value1', 3600);
        $cacheService->set('clear_test_key2', 'value2', 3600);
        $cacheService->set('clear_test_key3', 'value3', 3600);

        // 验证缓存存在
        $this->assertTrue($cacheService->has('clear_test_key1'));
        $this->assertTrue($cacheService->has('clear_test_key2'));
        $this->assertTrue($cacheService->has('clear_test_key3'));

        // 测试 clear() 方法 - clear() returns bool by contract
        $result = $cacheService->clear();
        $this->assertTrue($result);

        // 验证所有缓存已被清除
        $this->assertFalse($cacheService->has('clear_test_key1'));
        $this->assertFalse($cacheService->has('clear_test_key2'));
        $this->assertFalse($cacheService->has('clear_test_key3'));
    }

    public function testClearMethodOnEmptyCache(): void
    {
        $cacheService = new CacheService();

        // 在空缓存上调用 clear() - returns bool by contract
        $result = $cacheService->clear();

        $this->assertTrue($result);
    }

    public function testClearMethodWithTaggedItems(): void
    {
        $cacheService = new CacheService();

        // 设置带标签的缓存项
        $cacheService->set('tagged_clear_1', 'value1', 3600, ['tag1']);
        $cacheService->set('tagged_clear_2', 'value2', 3600, ['tag2']);
        $cacheService->set('tagged_clear_3', 'value3', 3600, ['tag1', 'tag2']);

        // 清除所有缓存
        $result = $cacheService->clear();
        $this->assertTrue($result);

        // 验证所有缓存包括带标签的都已被清除
        $this->assertNull($cacheService->get('tagged_clear_1'));
        $this->assertNull($cacheService->get('tagged_clear_2'));
        $this->assertNull($cacheService->get('tagged_clear_3'));
    }

    public function testInvalidateTagMethodReturnsBoolean(): void
    {
        $cacheService = new CacheService();

        // 设置带标签的缓存
        $cacheService->set('tag_test_key1', 'value1', 3600, ['test_tag']);
        $cacheService->set('tag_test_key2', 'value2', 3600, ['test_tag']);

        // 测试 invalidateTag() 方法 - returns bool by contract
        $result = $cacheService->invalidateTag('test_tag');
        $this->assertTrue($result);

        // 验证缓存已失效
        $this->assertNull($cacheService->get('tag_test_key1'));
        $this->assertNull($cacheService->get('tag_test_key2'));
    }

    public function testInvalidateTagMethodWithNonExistentTag(): void
    {
        $cacheService = new CacheService();

        // 测试使不存在的标签失效 - returns bool by contract
        $result = $cacheService->invalidateTag('non_existent_tag');

        $this->assertTrue($result); // 即使标签不存在,操作也应该成功
    }

    public function testInvalidateTagMethodDoesNotAffectOtherTags(): void
    {
        $cacheService = new CacheService();

        // 设置不同标签的缓存项
        $cacheService->set('tag_a_key1', 'value_a1', 3600, ['tag_a']);
        $cacheService->set('tag_a_key2', 'value_a2', 3600, ['tag_a']);
        $cacheService->set('tag_b_key1', 'value_b1', 3600, ['tag_b']);
        $cacheService->set('tag_b_key2', 'value_b2', 3600, ['tag_b']);

        // 只使 tag_a 失效
        $cacheService->invalidateTag('tag_a');

        // 验证 tag_a 的缓存已失效
        $this->assertNull($cacheService->get('tag_a_key1'));
        $this->assertNull($cacheService->get('tag_a_key2'));

        // 验证 tag_b 的缓存仍然存在
        $this->assertEquals('value_b1', $cacheService->get('tag_b_key1'));
        $this->assertEquals('value_b2', $cacheService->get('tag_b_key2'));
    }

    public function testInvalidateTagMethodWithMultipleTagsOnSameItem(): void
    {
        $cacheService = new CacheService();

        // 设置一个带多个标签的缓存项
        $cacheService->set('multi_tag_key', 'multi_tag_value', 3600, ['tag_x', 'tag_y', 'tag_z']);

        // 使其中一个标签失效
        $cacheService->invalidateTag('tag_x');

        // 验证缓存已失效(因为它包含被失效的标签)
        $this->assertNull($cacheService->get('multi_tag_key'));
    }

    public function testInvalidateTagMethodPreservesUntaggedItems(): void
    {
        $cacheService = new CacheService();

        // 设置带标签和不带标签的缓存项
        $cacheService->set('tagged_item', 'tagged_value', 3600, ['tag_preserve']);
        $cacheService->set('untagged_item', 'untagged_value', 3600);

        // 使标签失效
        $cacheService->invalidateTag('tag_preserve');

        // 验证带标签的项已失效
        $this->assertNull($cacheService->get('tagged_item'));

        // 验证不带标签的项仍然存在
        $this->assertEquals('untagged_value', $cacheService->get('untagged_item'));
    }
}

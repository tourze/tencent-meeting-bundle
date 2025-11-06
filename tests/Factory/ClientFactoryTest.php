<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Factory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\TencentMeetingBundle\Exception\ConfigurationException;
use Tourze\TencentMeetingBundle\Factory\ClientFactory;
use Tourze\TencentMeetingBundle\Factory\ClientFactoryInterface;
use Tourze\TencentMeetingBundle\Service\ConfigService;
use Tourze\TencentMeetingBundle\Service\ConfigServiceInterface;
use Tourze\TencentMeetingBundle\Service\HttpClientService;
use Tourze\TencentMeetingBundle\Service\MeetingClient;
use Tourze\TencentMeetingBundle\Service\RecordingClient;
use Tourze\TencentMeetingBundle\Service\RoomClient;
use Tourze\TencentMeetingBundle\Service\SyncService;
use Tourze\TencentMeetingBundle\Service\UserClient;
use Tourze\TencentMeetingBundle\Service\WebhookClient;

/** @phpstan-ignore service.test.shouldUseAbstractIntegrationTestCase */
/**
 * @internal
 */
#[CoversClass(ClientFactory::class)]
final class ClientFactoryTest extends TestCase // phpstan-ignore-line
{
    private ClientFactory $clientFactory;

    protected function setUp(): void
    {
        $this->clientFactory = $this->createClientFactory();
    }

    public function testClientFactoryCanBeCreatedFromContainer(): void
    {
        $clientFactory = $this->createClientFactory();
        $this->assertInstanceOf(ClientFactory::class, $clientFactory);
        $this->assertInstanceOf(ClientFactoryInterface::class, $clientFactory);
    }

    public function testClientFactoryHasRequiredMethods(): void
    {
        $clientFactory = $this->createClientFactory();

        $requiredMethods = [
            'createMeetingClient',
            'createUserClient',
            'createRoomClient',
            'createRecordingClient',
            'createWebhookClient',
            'createSyncService',
            'getMeetingClient',
            'getUserClient',
            'getRoomClient',
            'getRecordingClient',
            'getWebhookClient',
            'getSyncService',
            'configure',
            'reset',
        ];

        $classMethods = get_class_methods($clientFactory);

        foreach ($requiredMethods as $method) {
            $this->assertContains($method, $classMethods, "Method {$method} not found in ClientFactory");
        }
    }

    public function testCreateMeetingClient(): void
    {
        $client = $this->clientFactory->createMeetingClient();

        $this->assertInstanceOf(MeetingClient::class, $client);
        $this->assertTrue($this->clientFactory->isClientCreated('meeting'));
        $this->assertContains('meeting', $this->clientFactory->getCreatedClientTypes());
    }

    public function testCreateUserClient(): void
    {
        $client = $this->clientFactory->createUserClient();

        $this->assertInstanceOf(UserClient::class, $client);
        $this->assertTrue($this->clientFactory->isClientCreated('user'));
        $this->assertContains('user', $this->clientFactory->getCreatedClientTypes());
    }

    public function testCreateRoomClient(): void
    {
        $client = $this->clientFactory->createRoomClient();

        $this->assertInstanceOf(RoomClient::class, $client);
        $this->assertTrue($this->clientFactory->isClientCreated('room'));
        $this->assertContains('room', $this->clientFactory->getCreatedClientTypes());
    }

    public function testCreateRecordingClient(): void
    {
        $client = $this->clientFactory->createRecordingClient();

        $this->assertInstanceOf(RecordingClient::class, $client);
        $this->assertTrue($this->clientFactory->isClientCreated('recording'));
        $this->assertContains('recording', $this->clientFactory->getCreatedClientTypes());
    }

    public function testCreateWebhookClient(): void
    {
        $client = $this->clientFactory->createWebhookClient();

        $this->assertInstanceOf(WebhookClient::class, $client);
        $this->assertTrue($this->clientFactory->isClientCreated('webhook'));
        $this->assertContains('webhook', $this->clientFactory->getCreatedClientTypes());
    }

    public function testCreateSyncService(): void
    {
        $service = $this->clientFactory->createSyncService();

        $this->assertInstanceOf(SyncService::class, $service);
        $this->assertTrue($this->clientFactory->isClientCreated('sync'));
        $this->assertContains('sync', $this->clientFactory->getCreatedClientTypes());
    }

    public function testGetterMethodsReturnSameInstance(): void
    {
        $meetingClient1 = $this->clientFactory->getMeetingClient();
        $meetingClient2 = $this->clientFactory->getMeetingClient();

        $this->assertSame($meetingClient1, $meetingClient2, 'Getter methods should return same cached instance');
    }

    public function testCacheConfiguration(): void
    {
        // 测试启用缓存
        $this->clientFactory->configure(['cache_enabled' => true]);

        $client1 = $this->clientFactory->createMeetingClient();
        $client2 = $this->clientFactory->createMeetingClient();

        $this->assertSame($client1, $client2, 'Should return cached instance when cache enabled');

        // 测试禁用缓存
        $this->clientFactory->configure(['cache_enabled' => false]);

        // 注意：由于缓存被清除，需要重新创建来测试
        $client3 = $this->clientFactory->createMeetingClient();
        $this->assertNotSame($client1, $client3, 'Should create new instance after disabling cache');
    }

    public function testConfigurationValidation(): void
    {
        $this->expectException(\Throwable::class);

        // 这里测试无效配置，具体根据 validateConfiguration 方法的实现
        $this->clientFactory->configure(['invalid_key' => 'invalid_value']);
    }

    public function testReset(): void
    {
        // 创建一些客户端
        $this->clientFactory->createMeetingClient();
        $this->clientFactory->createUserClient();

        $this->assertNotEmpty($this->clientFactory->getCreatedClientTypes());

        // 重置工厂
        $this->clientFactory->reset();

        // 验证所有客户端状态被重置
        $this->assertFalse($this->clientFactory->isClientCreated('meeting'));
        $this->assertFalse($this->clientFactory->isClientCreated('user'));
        $this->assertEmpty($this->clientFactory->getCreatedClientTypes());
    }

    public function testCreationStats(): void
    {
        $stats = $this->clientFactory->getCreationStats();
        $this->assertArrayHasKey('stats', $stats);
        $this->assertArrayHasKey('cache_hit_rate', $stats);
        $this->assertArrayHasKey('configuration', $stats);
        $this->assertArrayHasKey('cached_clients', $stats);

        // 创建客户端后验证统计更新
        $this->clientFactory->createMeetingClient();

        $updatedStats = $this->clientFactory->getCreationStats();
        $this->assertArrayHasKey('stats', $updatedStats);
        $statsData = $updatedStats['stats'];
        $this->assertIsArray($statsData);
        $this->assertArrayHasKey('total_creations', $statsData);
        $this->assertGreaterThan(0, $statsData['total_creations']);
    }

    public function testIsClientCreated(): void
    {
        $this->assertFalse($this->clientFactory->isClientCreated('meeting'));
        $this->assertFalse($this->clientFactory->isClientCreated('invalid_type'));

        $this->clientFactory->createMeetingClient();
        $this->assertTrue($this->clientFactory->isClientCreated('meeting'));
    }

    public function testBatchCreateClients(): void
    {
        $clientTypes = ['meeting', 'user', 'room'];
        $results = $this->clientFactory->batchCreateClients($clientTypes);
        $this->assertCount(3, $results);

        $this->assertInstanceOf(MeetingClient::class, $results['meeting']);
        $this->assertInstanceOf(UserClient::class, $results['user']);
        $this->assertInstanceOf(RoomClient::class, $results['room']);
    }

    public function testBatchCreateClientsWithInvalidType(): void
    {
        $clientTypes = ['meeting', 'invalid_type'];
        $results = $this->clientFactory->batchCreateClients($clientTypes);

        $this->assertInstanceOf(MeetingClient::class, $results['meeting']);
        $invalidTypeResult = $results['invalid_type'];
        $this->assertIsArray($invalidTypeResult);
        $this->assertArrayHasKey('error', $invalidTypeResult);
        $this->assertArrayHasKey('exception', $invalidTypeResult);
    }

    public function testGetCreatedClientTypes(): void
    {
        $this->assertEmpty($this->clientFactory->getCreatedClientTypes());

        $this->clientFactory->createMeetingClient();
        $this->clientFactory->createUserClient();

        $types = $this->clientFactory->getCreatedClientTypes();
        $this->assertCount(2, $types);
        $this->assertContains('meeting', $types);
        $this->assertContains('user', $types);
    }

    public function testCacheHitRateCalculation(): void
    {
        // 启用缓存
        $this->clientFactory->configure(['cache_enabled' => true]);

        // 第一次创建
        $this->clientFactory->createMeetingClient();

        // 第二次创建应该命中缓存
        $this->clientFactory->createMeetingClient();

        $stats = $this->clientFactory->getCreationStats();
        $this->assertGreaterThan(0, $stats['cache_hit_rate']);
    }

    public function testClientFactoryConstructorParameters(): void
    {
        $reflection = new \ReflectionClass(ClientFactory::class);
        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor, 'ClientFactory should have a constructor');

        $parameters = $constructor->getParameters();
        $this->assertCount(3, $parameters, 'ClientFactory constructor should have 3 parameters');

        // 检查依赖类型
        $this->assertEquals('Tourze\TencentMeetingBundle\Service\ConfigService', (string) $parameters[0]->getType());
        $this->assertEquals('Tourze\TencentMeetingBundle\Service\HttpClientService', (string) $parameters[1]->getType());
        $this->assertEquals('Psr\Log\LoggerInterface', (string) $parameters[2]->getType());
    }

    public function testConfigureUpdatesConfiguration(): void
    {
        $initialStats = $this->clientFactory->getCreationStats();
        $this->assertArrayHasKey('stats', $initialStats);
        $initialStatsData = $initialStats['stats'];
        $this->assertIsArray($initialStatsData);
        $this->assertArrayHasKey('configurations', $initialStatsData);
        $initialConfigurations = $initialStatsData['configurations'];

        $this->clientFactory->configure(['timeout' => 60]);

        $updatedStats = $this->clientFactory->getCreationStats();
        $this->assertArrayHasKey('stats', $updatedStats);
        $updatedStatsData = $updatedStats['stats'];
        $this->assertIsArray($updatedStatsData);
        $this->assertArrayHasKey('configurations', $updatedStatsData);
        $this->assertGreaterThan($initialConfigurations, $updatedStatsData['configurations']);

        // 验证配置已更新
        $this->assertArrayHasKey('configuration', $updatedStats);
        $config = $updatedStats['configuration'];
        $this->assertIsArray($config);
        $this->assertArrayHasKey('timeout', $config);
        $this->assertEquals(60, $config['timeout']);
    }

    public function testResetUpdatesStats(): void
    {
        $initialStats = $this->clientFactory->getCreationStats();
        $this->assertArrayHasKey('stats', $initialStats);
        $initialStatsData = $initialStats['stats'];
        $this->assertIsArray($initialStatsData);
        $this->assertArrayHasKey('resets', $initialStatsData);
        $initialResets = $initialStatsData['resets'];

        $this->clientFactory->reset();

        $updatedStats = $this->clientFactory->getCreationStats();
        $this->assertArrayHasKey('stats', $updatedStats);
        $updatedStatsData = $updatedStats['stats'];
        $this->assertIsArray($updatedStatsData);
        $this->assertArrayHasKey('resets', $updatedStatsData);
        $this->assertGreaterThan($initialResets, $updatedStatsData['resets']);
    }

    private function createClientFactory(): ClientFactory
    {
        $configService = $this->createMock(ConfigService::class);
        $httpClientService = $this->createMock(HttpClientService::class);
        $mockLogger = $this->createMock(LoggerInterface::class);

        return new ClientFactory($configService, $httpClientService, $mockLogger);
    }
}

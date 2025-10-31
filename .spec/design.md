# 腾讯会议Bundle技术设计

## 技术概览

### 架构模式选择
本Bundle采用**扁平化Service层架构**，严格遵循Symfony Bundle标准：

- **扁平化Service层**：直接处理业务逻辑，不使用DDD分层
- **贫血模型实体**：实体只包含数据，业务逻辑在Service中
- **环境变量配置**：所有配置通过`$_ENV`读取，不创建Configuration类
- **HttpClientBundle集成**：基于HTTP客户端封装腾讯会议API
- **事件驱动架构**：将Webhook事件转换为Symfony事件分发

### 核心设计原则
- **KISS**：保持简单直接的实现，避免过度抽象
- **YAGNI**：只实现当前需要的功能，不预设未来需求
- **单一职责**：每个Service类只负责一个业务领域
- **依赖注入**：使用构造函数注入和readonly属性

### 技术决策理由
1. **选择扁平化架构**：避免DDD的复杂性，更适合企业应用快速开发
2. **使用HttpClientBundle**：避免第三方SDK的调试困难，提供更好的可控性
3. **实体存储配置**：支持多租户场景，允许运行时动态切换配置
4. **事件驱动设计**：松耦合的业务系统集成，支持异步处理

## 公共API设计

### 核心服务接口

#### 配置管理服务
```php
interface ConfigServiceInterface
{
    /**
     * 获取当前活动的腾讯会议配置
     */
    public function getActiveConfig(): ?TencentMeetingConfig;
    
    /**
     * 根据上下文选择配置
     */
    public function selectConfig(array $context = []): ?TencentMeetingConfig;
    
    /**
     * 创建新配置
     */
    public function createConfig(array $data): TencentMeetingConfig;
    
    /**
     * 测试配置连接性
     */
    public function testConfig(TencentMeetingConfig $config): bool;
}
```

#### API客户端工厂
```php
interface ClientFactoryInterface
{
    /**
     * 创建会议管理客户端
     */
    public function createMeetingClient(TencentMeetingConfig $config): MeetingClient;
    
    /**
     * 创建用户管理客户端
     */
    public function createUserClient(TencentMeetingConfig $config): UserClient;
    
    /**
     * 创建部门管理客户端
     */
    public function createDepartmentClient(TencentMeetingConfig $config): DepartmentClient;
    
    /**
     * 创建录制管理客户端
     */
    public function createRecordingClient(TencentMeetingConfig $config): RecordingClient;
    
    /**
     * 创建会议室管理客户端
     */
    public function createRoomClient(TencentMeetingConfig $config): RoomClient;
}
```

#### 数据同步服务
```php
interface SyncServiceInterface
{
    /**
     * 同步所有数据
     */
    public function syncAll(TencentMeetingConfig $config): void;
    
    /**
     * 同步会议数据
     */
    public function syncMeetings(TencentMeetingConfig $config, ?\DateTime $since = null): void;
    
    /**
     * 同步用户数据
     */
    public function syncUsers(TencentMeetingConfig $config, ?\DateTime $since = null): void;
    
    /**
     * 同步部门数据
     */
    public function syncDepartments(TencentMeetingConfig $config, ?\DateTime $since = null): void;
    
    /**
     * 同步录制数据
     */
    public function syncRecordings(TencentMeetingConfig $config, ?\DateTime $since = null): void;
    
    /**
     * 获取同步状态
     */
    public function getSyncStatus(TencentMeetingConfig $config): array;
}
```

#### 事件分发服务
```php
interface EventDispatcherInterface
{
    /**
     * 分发Webhook事件
     */
    public function dispatchWebhookEvent(array $payload): void;
    
    /**
     * 注册事件处理器
     */
    public function registerEventHandler(string $eventType, callable $handler): void;
    
    /**
     * 验证Webhook签名
     */
    public function validateWebhookSignature(array $payload, string $signature): bool;
}
```

### 使用示例代码

#### 基本使用
```php
// 获取配置
$config = $configService->getActiveConfig();

// 创建会议
$meetingClient = $clientFactory->createMeetingClient($config);
$meeting = $meetingClient->createMeeting([
    'subject' => '技术评审会议',
    'start_time' => '2024-01-15T14:00:00Z',
    'duration' => 60,
    'type' => 1, // 预约会议
]);

// 同步数据
$syncService->syncMeetings($config);
```

#### 事件处理
```php
// 订阅会议事件
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MeetingEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            MeetingCreatedEvent::class => 'onMeetingCreated',
            MeetingStartedEvent::class => 'onMeetingStarted',
            MeetingEndedEvent::class => 'onMeetingEnded',
        ];
    }
    
    public function onMeetingCreated(MeetingCreatedEvent $event): void
    {
        // 处理会议创建事件
        $meeting = $event->getMeeting();
        // 业务逻辑...
    }
}
```

## 内部架构

### 核心组件划分

#### 1. 实体层（Entity）
```php
// 配置实体
class TencentMeetingConfig
{
    private int $id;
    private string $appId;
    private string $secretId;
    private string $secretKey;
    private string $authType; // JWT or OAuth2
    private ?string $webhookToken;
    private bool $enabled;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;
    
    // 只包含getter/setter方法
}

// 会议实体
class Meeting
{
    private int $id;
    private string $meetingId;
    private string $meetingCode;
    private string $subject;
    private \DateTime $startTime;
    private \DateTime $endTime;
    private string $status;
    private string $userId;
    private TencentMeetingConfig $config;
    
    // 只包含getter/setter方法
}
```

#### 2. 客户端层（Client）
```php
// 基础客户端
abstract class BaseClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly TencentMeetingConfig $config,
        private readonly LoggerInterface $logger
    ) {}
    
    protected function request(string $method, string $path, array $data = []): array
    {
        // 实现签名算法、错误处理、重试逻辑
    }
}

// 会议客户端
class MeetingClient extends BaseClient
{
    public function createMeeting(array $data): array
    {
        return $this->request('POST', '/v1/meetings', $data);
    }
    
    public function getMeeting(string $meetingId): array
    {
        return $this->request('GET', '/v1/meetings/' . $meetingId);
    }
    
    public function updateMeeting(string $meetingId, array $data): array
    {
        return $this->request('PUT', '/v1/meetings/' . $meetingId, $data);
    }
    
    public function cancelMeeting(string $meetingId): array
    {
        return $this->request('DELETE', '/v1/meetings/' . $meetingId);
    }
}
```

#### 3. 服务层（Service）
```php
// 配置服务
class ConfigService implements ConfigServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {}
    
    public function getActiveConfig(): ?TencentMeetingConfig
    {
        return $this->entityManager->getRepository(TencentMeetingConfig::class)
            ->findOneBy(['enabled' => true]);
    }
    
    public function selectConfig(array $context = []): ?TencentMeetingConfig
    {
        // 根据上下文选择配置（如域名、用户等）
        if (isset($context['domain'])) {
            return $this->entityManager->getRepository(TencentMeetingConfig::class)
                ->findOneBy(['domain' => $context['domain'], 'enabled' => true]);
        }
        
        return $this->getActiveConfig();
    }
}
```

#### 4. 事件系统
```php
// 基础事件类
abstract class BaseTencentMeetingEvent extends Event
{
    public function __construct(
        private readonly array $payload,
        private readonly \DateTime $timestamp
    ) {}
    
    public function getPayload(): array
    {
        return $this->payload;
    }
    
    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }
}

// 具体事件类
class MeetingCreatedEvent extends BaseTencentMeetingEvent
{
    public function getMeeting(): array
    {
        return $this->payload['meeting'] ?? [];
    }
}
```

### 内部类图

```
TencentMeetingBundle
├── Entity/
│   ├── TencentMeetingConfig.php
│   ├── Meeting.php
│   ├── User.php
│   ├── Department.php
│   ├── Recording.php
│   ├── WebhookEvent.php
│   └── ... 其他实体
├── Client/
│   ├── BaseClient.php
│   ├── MeetingClient.php
│   ├── UserClient.php
│   ├── DepartmentClient.php
│   ├── RecordingClient.php
│   └── ... 其他客户端
├── Service/
│   ├── ConfigService.php
│   ├── ClientFactory.php
│   ├── SyncService.php
│   ├── EventDispatcher.php
│   └── AuthService.php
├── Event/
│   ├── BaseTencentMeetingEvent.php
│   ├── MeetingCreatedEvent.php
│   ├── MeetingStartedEvent.php
│   ├── MeetingEndedEvent.php
│   ├── UserJoinedEvent.php
│   └── ... 其他事件
├── Controller/
│   └── WebhookController.php
└── Command/
    ├── ConfigCommand.php
    ├── SyncCommand.php
    └── ... 其他命令
```

### 数据流设计

#### API调用流程
```
用户代码 → ClientFactory → 具体Client → BaseClient.request() → 
签名生成 → HttpClient → 腾讯会议API → 响应处理 → 返回数据
```

#### Webhook处理流程
```
腾讯会议Webhook → WebhookController → 签名验证 → 事件解析 → 
EventDispatcher → Symfony事件 → 业务订阅者处理
```

#### 数据同步流程
```
定时任务 → SyncService → 各个Client → 腾讯会议API → 
数据转换 → 实体保存 → 事件触发
```

## 扩展机制

### 扩展点定义

#### 1. 自定义配置选择器
```php
interface ConfigSelectorInterface
{
    public function select(array $context): ?TencentMeetingConfig;
}

// 默认实现
class DefaultConfigSelector implements ConfigSelectorInterface
{
    public function select(array $context = []): ?TencentMeetingConfig
    {
        // 默认选择逻辑
    }
}
```

#### 2. 自定义事件处理器
```php
interface EventHandlerInterface
{
    public function handle(array $payload): void;
    public function supports(string $eventType): bool;
}
```

#### 3. 自定义数据转换器
```php
interface DataTransformerInterface
{
    public function transform(array $apiData): array;
    public function reverseTransform(array $localData): array;
}
```

### 事件系统设计

#### 事件类型映射
```php
const EVENT_MAPPING = [
    'meeting.created' => MeetingCreatedEvent::class,
    'meeting.started' => MeetingStartedEvent::class,
    'meeting.ended' => MeetingEndedEvent::class,
    'meeting.updated' => MeetingUpdatedEvent::class,
    'meeting.cancelled' => MeetingCancelledEvent::class,
    'user.joined' => UserJoinedEvent::class,
    'user.left' => UserLeftEvent::class,
    'recording.started' => RecordingStartedEvent::class,
    'recording.completed' => RecordingCompletedEvent::class,
    'recording.failed' => RecordingFailedEvent::class,
];
```

#### 事件优先级
```php
const EVENT_PRIORITIES = [
    MeetingCreatedEvent::class => 100,
    MeetingStartedEvent::class => 200,
    MeetingEndedEvent::class => 300,
    UserJoinedEvent::class => 150,
    UserLeftEvent::class => 250,
];
```

### 配置架构

#### 环境变量配置
```php
// 在Service中直接使用环境变量
class TencentMeetingConfig
{
    public function __construct()
    {
        $this->apiBaseUrl = $_ENV['TENCENT_MEETING_API_URL'] ?? 'https://api.meeting.qq.com';
        $this->timeout = $_ENV['TENCENT_MEETING_TIMEOUT'] ?? 30;
        $this->retryTimes = $_ENV['TENCENT_MEETING_RETRY_TIMES'] ?? 3;
    }
}
```

## 集成设计

### Symfony集成（Bundle）

#### Bundle注册
```php
// config/bundles.php
return [
    // ...
    Tourze\TencentMeetingBundle\TencentMeetingBundle::class => ['all' => true],
];
```

#### 服务自动装配
```php
// 在services.yaml中配置
services:
    Tourze\TencentMeetingBundle\Service\ConfigServiceInterface:
        class: Tourze\TencentMeetingBundle\Service\ConfigService
        
    Tourze\TencentMeetingBundle\Service\ClientFactoryInterface:
        class: Tourze\TencentMeetingBundle\Service\ClientFactory
        
    Tourze\TencentMeetingBundle\Service\SyncServiceInterface:
        class: Tourze\TencentMeetingBundle\Service\SyncService
```

### 独立使用指南

#### 手动配置
```php
// 手动创建服务实例
$httpClient = new Symfony\Component\HttpClient\HttpClient();
$config = new TencentMeetingConfig();
$config->setAppId('your_app_id');
$config->setSecretId('your_secret_id');
$config->setSecretKey('your_secret_key');

$meetingClient = new MeetingClient($httpClient, $config);
$meeting = $meetingClient->createMeeting([...]);
```

## 测试策略

### 单元测试方案

#### 实体测试
```php
class TencentMeetingConfigTest extends TestCase
{
    public function testSetAndGetAppId(): void
    {
        $config = new TencentMeetingConfig();
        $config->setAppId('test_app_id');
        $this->assertEquals('test_app_id', $config->getAppId());
    }
}
```

#### 服务测试
```php
class ConfigServiceTest extends TestCase
{
    public function testGetActiveConfig(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(EntityRepository::class);
        
        $entityManager->method('getRepository')->willReturn($repository);
        $repository->method('findOneBy')->willReturn(new TencentMeetingConfig());
        
        $service = new ConfigService($entityManager, new NullLogger());
        $config = $service->getActiveConfig();
        
        $this->assertInstanceOf(TencentMeetingConfig::class, $config);
    }
}
```

#### 客户端测试
```php
class MeetingClientTest extends TestCase
{
    public function testCreateMeeting(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $config = new TencentMeetingConfig();
        
        $httpClient->method('request')->willReturn(
            new Response(200, [], json_encode(['meeting_id' => '123456']))
        );
        
        $client = new MeetingClient($httpClient, $config, new NullLogger());
        $result = $client->createMeeting(['subject' => 'Test Meeting']);
        
        $this->assertEquals('123456', $result['meeting_id']);
    }
}
```

### 集成测试方案

#### 数据库集成测试
```php
class ConfigServiceIntegrationTest extends KernelTestCase
{
    public function testCreateAndRetrieveConfig(): void
    {
        // 使用测试数据库
        $container = static::getContainer();
        $configService = $container->get(ConfigServiceInterface::class);
        
        $config = $configService->createConfig([
            'appId' => 'test_app',
            'secretId' => 'test_secret_id',
            'secretKey' => 'test_secret_key',
            'authType' => 'JWT',
            'enabled' => true,
        ]);
        
        $this->assertNotNull($config->getId());
        
        $retrieved = $configService->getActiveConfig();
        $this->assertEquals('test_app', $retrieved->getAppId());
    }
}
```

#### API集成测试
```php
class MeetingClientIntegrationTest extends AbstractWebTestCase
{
    public function testRealApiCall(): void
    {
        // 需要真实的腾讯会议配置
        $this->markTestSkipped('需要真实配置');
        
        $container = static::getContainer();
        $configService = $container->get(ConfigServiceInterface::class);
        $clientFactory = $container->get(ClientFactoryInterface::class);
        
        $config = $configService->getActiveConfig();
        $meetingClient = $clientFactory->createMeetingClient($config);
        
        $meeting = $meetingClient->createMeeting([
            'subject' => 'Integration Test Meeting',
            'start_time' => date('c', strtotime('+1 hour')),
            'duration' => 30,
        ]);
        
        $this->assertArrayHasKey('meeting_id', $meeting);
    }
}
```

### 性能基准测试

#### 并发测试
```php
class PerformanceTest extends TestCase
{
    public function testConcurrentRequests(): void
    {
        $httpClient = new MockHttpClient();
        $config = new TencentMeetingConfig();
        $client = new MeetingClient($httpClient, $config, new NullLogger());
        
        $start = microtime(true);
        $promises = [];
        
        for ($i = 0; $i < 10; $i++) {
            $promises[] = $client->createMeetingAsync([
                'subject' => "Test Meeting $i",
                'start_time' => date('c', strtotime('+1 hour')),
                'duration' => 30,
            ]);
        }
        
        $results = Promise\Utils::settle($promises)->wait();
        $duration = microtime(true) - $start;
        
        $this->assertLessThan(5, $duration, '并发请求应该在5秒内完成');
    }
}
```

## 关键技术实现

### 签名算法实现

#### TC3-HMAC-SHA256签名
```php
class SignatureService
{
    public function generateSignature(
        string $method,
        string $path,
        array $params,
        array $headers,
        string $secretKey,
        string $secretId,
        string $service = 'meeting',
        string $region = 'ap-guangzhou'
    ): string {
        // 1. 构造规范请求串
        $canonicalRequest = $this->buildCanonicalRequest($method, $path, $params, $headers);
        
        // 2. 构造待签字符串
        $stringToSign = $this->buildStringToSign($canonicalRequest, $service, $region);
        
        // 3. 计算签名
        $signature = $this->calculateSignature($stringToSign, $secretKey, $service, $region);
        
        // 4. 拼接Authorization头
        return $this->buildAuthorization($secretId, $service, $region, $signature);
    }
    
    private function buildCanonicalRequest(string $method, string $path, array $params, array $headers): string
    {
        // 实现规范请求串构造
    }
    
    private function buildStringToSign(string $canonicalRequest, string $service, string $region): string
    {
        // 实现待签字符串构造
    }
    
    private function calculateSignature(string $stringToSign, string $secretKey, string $service, string $region): string
    {
        // 实现签名计算
    }
}
```

### JWT鉴权实现
```php
class JwtAuthService
{
    public function generateToken(string $secretId, string $secretKey, int $expiresIn = 3600): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'secret_id' => $secretId,
            'timestamp' => time(),
            'expired' => time() + $expiresIn,
        ];
        
        return $this->encodeJwt($header, $payload, $secretKey);
    }
    
    private function encodeJwt(array $header, array $payload, string $key): string
    {
        // JWT编码实现
    }
}
```

### OAuth2.0鉴权实现
```php
class OAuth2AuthService
{
    private ?string $accessToken = null;
    private ?int $expiresAt = null;
    
    public function getAccessToken(string $appId, string $appSecret): string
    {
        if ($this->accessToken && $this->expiresAt > time()) {
            return $this->accessToken;
        }
        
        $response = $this->httpClient->request('POST', '/v1/oauth2/token', [
            'json' => [
                'app_id' => $appId,
                'app_secret' => $appSecret,
                'grant_type' => 'client_credentials',
            ],
        ]);
        
        $data = $response->toArray();
        $this->accessToken = $data['access_token'];
        $this->expiresAt = time() + $data['expires_in'];
        
        return $this->accessToken;
    }
}
```

### 重试机制实现
```php
class RetryService
{
    public function __construct(
        private readonly int $maxRetries = 3,
        private readonly int $retryDelay = 1000
    ) {}
    
    public function execute(callable $operation): mixed
    {
        $attempts = 0;
        $lastException = null;
        
        while ($attempts < $this->maxRetries) {
            try {
                return $operation();
            } catch (\Exception $e) {
                $lastException = $e;
                $attempts++;
                
                if ($attempts < $this->maxRetries) {
                    usleep($this->retryDelay * 1000);
                }
            }
        }
        
        throw $lastException;
    }
}
```

## 安全考虑

### 敏感数据保护
```php
class EncryptionService
{
    public function encrypt(string $data): string
    {
        $key = hash('sha256', $_ENV['APP_SECRET'], true);
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    public function decrypt(string $encrypted): string
    {
        $key = hash('sha256', $_ENV['APP_SECRET'], true);
        $data = base64_decode($encrypted);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
    }
}
```

### Webhook签名验证
```php
class WebhookValidator
{
    public function validate(array $payload, string $signature, string $token): bool
    {
        $expectedSignature = hash_hmac('sha256', json_encode($payload), $token);
        return hash_equals($expectedSignature, $signature);
    }
}
```

### 日志脱敏
```php
class SanitizedLogger
{
    public function __construct(private readonly LoggerInterface $logger) {}
    
    public function logRequest(string $method, string $url, array $data): void
    {
        $sanitized = $this->sanitizeData($data);
        $this->logger->info("API Request: $method $url", $sanitized);
    }
    
    private function sanitizeData(array $data): array
    {
        $sensitiveKeys = ['secret_key', 'secret_id', 'app_secret', 'password'];
        
        foreach ($sensitiveKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '******';
            }
        }
        
        return $data;
    }
}
```

## 性能优化

### 缓存策略
```php
class CacheService
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly int $defaultTtl = 3600
    ) {}
    
    public function get(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $item = $this->cache->getItem($key);
        
        if ($item->isHit()) {
            return $item->get();
        }
        
        $value = $callback();
        $item->set($value);
        $item->expiresAfter($ttl ?? $this->defaultTtl);
        $this->cache->save($item);
        
        return $value;
    }
}
```

### 批量处理
```php
class BatchProcessor
{
    public function processBatch(array $items, callable $processor, int $batchSize = 100): array
    {
        $results = [];
        $batches = array_chunk($items, $batchSize);
        
        foreach ($batches as $batch) {
            $results = array_merge($results, $processor($batch));
        }
        
        return $results;
    }
}
```

## 错误处理

### 异常体系
```php
class TencentMeetingException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly array $apiResponse = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
    
    public function getApiResponse(): array
    {
        return $this->apiResponse;
    }
}

class AuthenticationException extends TencentMeetingException
{
}

class RateLimitException extends TencentMeetingException
{
}

class ValidationException extends TencentMeetingException
{
}
```

### 错误处理中间件
```php
class ErrorHandlingMiddleware
{
    public function handle(callable $operation): mixed
    {
        try {
            return $operation();
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $data = $response->toArray(false);
            
            throw match ($data['code'] ?? 0) {
                40001 => new AuthenticationException('认证失败', $data, $e),
                40002 => new ValidationException('参数错误', $data, $e),
                40004 => new RateLimitException('请求频率限制', $data, $e),
                default => new TencentMeetingException($data['message'] ?? '未知错误', $data, $e),
            };
        }
    }
}
```

## 设计验证

### 架构合规性检查

- [x] **不使用** DDD 分层架构（Domain/Application/Infrastructure）
- [x] **不创建** 值对象（ValueObject）目录
- [x] **不使用** 富领域模型（实体只有 getter/setter）
- [x] **使用** 扁平化的 Service 层
- [x] **遵循** symfony-bundle-standards.md 的目录结构
- [x] **不创建** Configuration 类（配置通过 $_ENV 读取）
- [x] **不主动创建** HTTP API 端点（除非用户明确要求）

### 需求满足检查

- [x] 使用 HttpClientBundle 作为 HTTP 客户端基础
- [x] 支持多租户配置管理
- [x] 实现完整的数据同步机制
- [x] 支持事件驱动的 Webhook 处理
- [x] 提供丰富的命令行工具
- [x] 实现企业级的安全和可靠性特性

### 质量标准

- [x] 支持 PHPStan Level 8 检查
- [x] 提供完整的类型声明
- [x] 实现全面的测试覆盖
- [x] 遵循 PSR-12 编码规范
- [x] 提供详细的错误处理和日志记录

### 扩展性考虑

- [x] 提供清晰的接口定义
- [x] 支持自定义配置选择器
- [x] 支持自定义事件处理器
- [x] 支持插件机制扩展功能
- [x] 提供缓存和性能优化接口

这个技术设计完全符合 Symfony Bundle 开发标准，采用扁平化架构，避免了过度设计，同时提供了完整的功能实现和扩展机制。设计满足了所有需求，并且考虑了性能、安全、可靠性等企业级要求。
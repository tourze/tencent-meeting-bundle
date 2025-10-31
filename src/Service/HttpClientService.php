<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Retry\GenericRetryStrategy;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface as ContractsHttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpClientService
{
    private ContractsHttpClientInterface $client;

    public function __construct(
        private ConfigServiceInterface $config,
        private LoggerInterface $logger,
        ?ContractsHttpClientInterface $httpClient = null,
    ) {
        $this->client = $httpClient ?? $this->createHttpClient();
    }

    /**
     * 创建HTTP客户端
     */
    private function createHttpClient(): ContractsHttpClientInterface
    {
        $baseClient = HttpClient::create([
            'base_uri' => $this->config->getApiUrl(),
            'timeout' => $this->config->getTimeout(),
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'TencentMeetingBundle/1.0',
            ],
        ]);

        // 如果配置了重试，则包装为重试客户端
        if ($this->config->getRetryTimes() > 0) {
            $retryStrategy = new GenericRetryStrategy([500, 502, 503, 504], $this->config->getRetryTimes());

            return new RetryableHttpClient($baseClient, $retryStrategy);
        }

        return $baseClient;
    }

    /**
     * 发送GET请求
     * @param array<string, mixed> $headers
     * @return array<string, mixed>
     */
    public function get(string $url, array $headers = [], ?int $timeout = null): array
    {
        return $this->request('GET', $url, null, $headers, $timeout);
    }

    /**
     * 发送POST请求
     * @param array<string, mixed> $data
     * @param array<string, mixed> $headers
     * @return array<string, mixed>
     */
    public function post(string $url, array $data = [], array $headers = [], ?int $timeout = null): array
    {
        return $this->request('POST', $url, $data, $headers, $timeout);
    }

    /**
     * 发送PUT请求
     * @param array<string, mixed> $data
     * @param array<string, mixed> $headers
     * @return array<string, mixed>
     */
    public function put(string $url, array $data = [], array $headers = [], ?int $timeout = null): array
    {
        return $this->request('PUT', $url, $data, $headers, $timeout);
    }

    /**
     * 发送DELETE请求
     * @param array<string, mixed> $headers
     * @return array<string, mixed>
     */
    public function delete(string $url, array $headers = [], ?int $timeout = null): array
    {
        return $this->request('DELETE', $url, null, $headers, $timeout);
    }

    /**
     * 发送PATCH请求
     * @param array<string, mixed> $data
     * @param array<string, mixed> $headers
     * @return array<string, mixed>
     */
    public function patch(string $url, array $data = [], array $headers = [], ?int $timeout = null): array
    {
        return $this->request('PATCH', $url, $data, $headers, $timeout);
    }

    /**
     * 发送HTTP请求
     * @param array<string, mixed>|null $data
     * @param array<string, mixed> $headers
     * @return array<string, mixed>
     */
    public function request(string $method, string $url, ?array $data = null, array $headers = [], ?int $timeout = null): array
    {
        $startTime = microtime(true);

        try {
            $options = [];

            // 合并自定义headers
            if ([] !== $headers) {
                $options['headers'] = $headers;
            }

            // 设置body数据
            if (null !== $data) {
                $options['json'] = $data;
            }

            // 设置超时
            if (null !== $timeout) {
                $options['timeout'] = $timeout;
            }

            // 添加认证信息
            $options = $this->addAuthentication($options);

            // 记录请求日志
            $this->logger->info('TencentMeeting API Request', [
                'method' => $method,
                'url' => $url,
                'data' => $data ?? [],
            ]);

            // 发送请求
            $response = $this->client->request($method, $url, $options);

            // 记录响应日志
            $this->logger->info('TencentMeeting HTTP Response', [
                'method' => $method,
                'url' => $url,
                'status_code' => $response->getStatusCode(),
                'headers' => $response->getHeaders(false),
            ]);

            // 转换响应为数组
            return $this->convertResponseToArray($response);
        } catch (HttpExceptionInterface $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->logger->error('TencentMeeting HTTP请求失败', [
                'message' => $e->getMessage(),
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'method' => $method,
                'url' => $url,
                'data' => $data,
                'duration' => $duration,
            ]);

            return [
                'success' => false,
                'status_code' => $e->getResponse()->getStatusCode(),
                'error' => $e->getMessage(),
                'response' => $this->convertResponseToArray($e->getResponse()),
                'duration' => $duration,
            ];
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->logger->error('TencentMeeting HTTP请求异常', [
                'message' => $e->getMessage(),
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'method' => $method,
                'url' => $url,
                'data' => $data,
                'duration' => $duration,
            ]);

            return [
                'success' => false,
                'status_code' => 0,
                'error' => $e->getMessage(),
                'duration' => $duration,
            ];
        }
    }

    /**
     * 添加认证信息
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function addAuthentication(array $options): array
    {
        // 这里可以根据不同的认证方式添加不同的认证头
        // 例如：JWT Token、OAuth2 Bearer Token等

        // 可以从配置中获取认证信息
        $authToken = $this->config->getAuthToken();
        if (null !== $authToken && '' !== $authToken) {
            if (!isset($options['headers'])) {
                $options['headers'] = [];
            }
            assert(is_array($options['headers']));
            $options['headers']['Authorization'] = 'Bearer ' . $authToken;
        }

        return $options;
    }

    /**
     * 将响应转换为数组
     * @return array<string, mixed>
     */
    private function convertResponseToArray(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);
        $headers = [];

        foreach ($response->getHeaders(false) as $name => $values) {
            $headers[$name] = $values[0];
        }

        $data = null;
        if ('' !== $content) {
            $decoded = json_decode($content, true);
            if (JSON_ERROR_NONE === json_last_error()) {
                $data = $decoded;
            } else {
                $data = $content;
            }
        }

        return [
            'success' => $statusCode >= 200 && $statusCode < 300,
            'status_code' => $statusCode,
            'headers' => $headers,
            'data' => $data,
            'content_type' => $response->getHeaders(false)['content-type'][0] ?? null,
        ];
    }

    /**
     * 异步发送请求
     * @param array<string, mixed>|null $data
     * @param array<string, mixed> $headers
     */
    public function requestAsync(string $method, string $url, ?array $data = null, array $headers = [], ?int $timeout = null): ResponseInterface
    {
        $options = [];

        if ([] !== $headers) {
            $options['headers'] = $headers;
        }

        if (null !== $data) {
            $options['json'] = $data;
        }

        if (null !== $timeout) {
            $options['timeout'] = $timeout;
        }

        $options = $this->addAuthentication($options);

        return $this->client->request($method, $url, $options);
    }

    /**
     * 批量发送请求
     * @param array<string, array<string, mixed>> $requests
     * @return array<string, array<string, mixed>>
     */
    public function requestMultiple(array $requests): array
    {
        $responses = [];

        foreach ($requests as $key => $request) {
            $responses[$key] = $this->processSingleRequest($request);
        }

        return $responses;
    }

    /**
     * 处理单个请求
     * @param array<string, mixed> $request
     * @return array<string, mixed>
     */
    private function processSingleRequest(array $request): array
    {
        $method = isset($request['method']) && is_string($request['method']) ? $request['method'] : 'GET';
        $url = isset($request['url']) && is_string($request['url']) ? $request['url'] : '';
        $data = $this->extractRequestData($request);
        $headers = $this->extractRequestHeaders($request);
        $timeout = isset($request['timeout']) && is_int($request['timeout']) ? $request['timeout'] : null;

        return $this->request($method, $url, $data, $headers, $timeout);
    }

    /**
     * 提取请求数据
     * @param array<string, mixed> $request
     * @return array<string, mixed>|null
     */
    private function extractRequestData(array $request): ?array
    {
        if (!isset($request['data']) || !is_array($request['data'])) {
            return null;
        }

        $data = [];
        foreach ($request['data'] as $key => $value) {
            if (is_string($key)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * 提取请求头
     * @param array<string, mixed> $request
     * @return array<string, mixed>
     */
    private function extractRequestHeaders(array $request): array
    {
        if (!isset($request['headers']) || !is_array($request['headers'])) {
            return [];
        }

        $headers = [];
        foreach ($request['headers'] as $key => $value) {
            if (is_string($key)) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    /**
     * 流式响应处理
     * @param array<string, mixed>|null $data
     * @param array<string, mixed> $headers
     */
    public function stream(string $method, string $url, ?array $data = null, array $headers = [], ?int $timeout = null): ResponseInterface
    {
        $options = [];

        if ([] !== $headers) {
            $options['headers'] = $headers;
        }

        if (null !== $data) {
            $options['json'] = $data;
        }

        if (null !== $timeout) {
            $options['timeout'] = $timeout;
        }

        $options = $this->addAuthentication($options);

        return $this->client->request($method, $url, $options);
    }

    /**
     * 获取HTTP客户端统计信息
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        // 这里可以收集HTTP客户端的统计信息
        // 例如：请求次数、响应时间、错误率等
        return [
            'total_requests' => 0,
            'successful_requests' => 0,
            'failed_requests' => 0,
            'average_response_time' => 0,
            'cache_hits' => 0,
        ];
    }

    /**
     * 重置HTTP客户端
     */
    public function reset(): void
    {
        $this->client = $this->createHttpClient();
    }

    /**
     * 检查服务可用性
     */
    public function checkAvailability(): bool
    {
        try {
            $response = $this->get('/health', [], 5);

            return isset($response['success']) && true === $response['success'] && 200 === $response['status_code'];
        } catch (\Throwable $e) {
            $this->logger->error('TencentMeeting 服务可用性检查失败', [
                'message' => $e->getMessage(),
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return false;
        }
    }

    /**
     * 获取客户端版本信息
     * @return array<string, mixed>
     */
    public function getClientInfo(): array
    {
        return [
            'version' => '1.0.0',
            'base_uri' => $this->config->getApiUrl(),
            'timeout' => $this->config->getTimeout(),
            'retry_times' => $this->config->getRetryTimes(),
        ];
    }
}

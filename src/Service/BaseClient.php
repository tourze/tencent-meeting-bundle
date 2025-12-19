<?php

namespace Tourze\TencentMeetingBundle\Service;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Tourze\TencentMeetingBundle\Exception\ApiException;
use Tourze\TencentMeetingBundle\Exception\NetworkException;

/**
 * 基础客户端
 *
 * 为所有API客户端提供基础功能，包括请求发送、认证、错误处理等
 */
#[WithMonologChannel(channel: 'tencent_meeting')]
abstract class BaseClient
{
    private string $baseUrl;

    /** @var array<string, string> */
    private array $headers;

    private int $timeout;

    private int $retryTimes;

    /** @var array<string, mixed> */
    private array $authentication;

    /** @var array<string, int|float> */
    private array $stats;

    public function __construct(
        private ConfigService $configService,
        private HttpClientService $httpClientService,
        protected LoggerInterface $loggerService,
    ) {
        $this->baseUrl = $configService->getApiUrl();
        $this->headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'TencentMeetingBundle/1.0',
        ];
        $this->timeout = $configService->getTimeout();
        $this->retryTimes = $configService->getRetryTimes();
        $this->authentication = [];
        $this->stats = [
            'total_requests' => 0,
            'successful_requests' => 0,
            'failed_requests' => 0,
            'total_response_time' => 0,
            'retries' => 0,
        ];
    }

    /**
     * 发送HTTP请求
     *
     * @param string $method HTTP方法
     * @param string $path 请求路径
     * @param array<string, mixed> $data 请求数据
     * @return array<string, mixed> 响应数据
     */
    public function request(string $method, string $path, array $data = []): array
    {
        $startTime = microtime(true);
        ++$this->stats['total_requests'];

        try {
            $url = $this->buildUrl($path);
            $normalizedHeaders = $this->normalizeHeaders();

            // 添加认证信息
            $headers = $this->addAuthentication($normalizedHeaders);

            // 记录请求日志
            $this->loggerService->info('TencentMeeting API Request', [
                'method' => $method,
                'url' => $url,
                'data' => $data,
            ]);

            $response = $this->httpClientService->request($method, $url, $data, $headers, $this->timeout);

            // 记录响应日志
            $this->loggerService->info('TencentMeeting API Response', [
                'method' => $method,
                'url' => $url,
                'response' => $response,
            ]);

            // 更新统计信息
            $this->updateStats($startTime, $response);

            return $response;
        } catch (\Throwable $e) {
            // 更新失败统计
            ++$this->stats['failed_requests'];

            // 记录错误日志
            $this->loggerService->error('TencentMeeting HTTP请求失败', [
                'exception' => $e,
                'method' => $method,
                'path' => $path,
                'data' => $data,
                'duration' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            // 处理错误
            return $this->handleError($e, $method, $path, $data);
        }
    }

    /**
     * 发送GET请求
     *
     * @param string $path 请求路径
     * @return array<string, mixed> 响应数据
     */
    public function get(string $path): array
    {
        return $this->request('GET', $path);
    }

    /**
     * 发送POST请求
     *
     * @param string $path 请求路径
     * @param array<string, mixed> $data 请求数据
     * @return array<string, mixed> 响应数据
     */
    public function post(string $path, array $data = []): array
    {
        return $this->request('POST', $path, $data);
    }

    /**
     * 发送PUT请求
     *
     * @param string $path 请求路径
     * @param array<string, mixed> $data 请求数据
     * @return array<string, mixed> 响应数据
     */
    public function put(string $path, array $data = []): array
    {
        return $this->request('PUT', $path, $data);
    }

    /**
     * 发送DELETE请求
     *
     * @param string $path 请求路径
     * @return array<string, mixed> 响应数据
     */
    public function delete(string $path): array
    {
        return $this->request('DELETE', $path);
    }

    /**
     * 发送PATCH请求
     *
     * @param string $path 请求路径
     * @param array<string, mixed> $data 请求数据
     * @return array<string, mixed> 响应数据
     */
    public function patch(string $path, array $data = []): array
    {
        return $this->request('PATCH', $path, $data);
    }

    /**
     * 设置认证信息
     *
     * @param array<string, mixed> $authentication 认证信息
     */
    public function setAuthentication(array $authentication): void
    {
        $this->authentication = $authentication;
    }

    /**
     * 设置认证Token
     *
     * @param string $token 认证Token
     */
    public function setAuthToken(string $token): void
    {
        $this->authentication = [
            'type' => 'Bearer',
            'token' => $token,
        ];
    }

    /**
     * 设置OAuth2 Token
     *
     * @param string $accessToken Access Token
     */
    public function setOAuth2Token(string $accessToken): void
    {
        $this->authentication = [
            'type' => 'Bearer',
            'token' => $accessToken,
        ];
    }

    /**
     * 清除认证信息
     */
    public function clearAuthentication(): void
    {
        $this->authentication = [];
    }

    /**
     * 设置请求头
     *
     * @param array<string, mixed> $headers 请求头
     */
    public function setHeaders(array $headers): void
    {
        foreach ($headers as $name => $value) {
            if (is_string($value)) {
                $this->headers[$name] = $value;
            }
        }
    }

    /**
     * 添加请求头
     *
     * @param string $name 头名称
     * @param string $value 头值
     */
    public function addHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    /**
     * 移除请求头
     *
     * @param string $name 头名称
     */
    public function removeHeader(string $name): void
    {
        unset($this->headers[$name]);
    }

    /**
     * 设置超时时间
     *
     * @param int $timeout 超时时间（秒）
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * 设置重试次数
     *
     * @param int $retryTimes 重试次数
     */
    public function setRetryTimes(int $retryTimes): void
    {
        $this->retryTimes = $retryTimes;
    }

    /**
     * 设置基础URL
     *
     * @param string $baseUrl 基础URL
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * 获取统计信息
     *
     * @return array<string, mixed> 统计信息
     */
    public function getStats(): array
    {
        return $this->stats;
    }

    /**
     * 重置统计信息
     */
    public function resetStats(): void
    {
        $this->stats = [
            'total_requests' => 0,
            'successful_requests' => 0,
            'failed_requests' => 0,
            'total_response_time' => 0,
            'retries' => 0,
        ];
    }

    /**
     * 重置客户端状态
     */
    public function reset(): void
    {
        $this->headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'TencentMeetingBundle/1.0',
        ];
        $this->timeout = $this->configService->getTimeout();
        $this->retryTimes = $this->configService->getRetryTimes();
        $this->authentication = [];
        $this->resetStats();
    }

    /**
     * 增加统计计数
     *
     * @param string $key 统计键
     * @param int $value 增加值
     */
    public function incrementStat(string $key, int $value = 1): void
    {
        if (isset($this->stats[$key])) {
            $this->stats[$key] += $value;
        }
    }

    /**
     * 获取总请求数
     */
    public function getTotalRequests(): int
    {
        return (int) $this->stats['total_requests'];
    }

    /**
     * 获取成功率
     */
    public function getSuccessRate(): float
    {
        $totalRequests = (int) ($this->stats['total_requests'] ?? 0);
        if (0 === $totalRequests) {
            return 0.0;
        }

        $successfulRequests = (int) ($this->stats['successful_requests'] ?? 0);

        return ($successfulRequests / $totalRequests) * 100;
    }

    /**
     * 获取平均响应时间
     */
    public function getAverageResponseTime(): float
    {
        $totalRequests = (int) ($this->stats['total_requests'] ?? 0);
        if (0 === $totalRequests) {
            return 0.0;
        }

        $totalResponseTime = (float) ($this->stats['total_response_time'] ?? 0);

        return $totalResponseTime / $totalRequests;
    }

    /**
     * 构建完整URL
     */
    private function buildUrl(string $path): string
    {
        return $this->baseUrl . '/' . ltrim($path, '/');
    }

    /**
     * 规范化请求头
     *
     * @return array<string, string> 规范化的请求头
     */
    private function normalizeHeaders(): array
    {
        return $this->headers;
    }

    /**
     * 添加认证信息到请求头
     *
     * @param array<string, string> $headers
     * @return array<string, string>
     */
    private function addAuthentication(array $headers): array
    {
        if ([] !== $this->authentication) {
            $type = $this->authentication['type'] ?? 'Bearer';
            $token = $this->authentication['token'] ?? '';

            if (is_string($type) && is_string($token) && '' !== $token) {
                $headers['Authorization'] = $type . ' ' . $token;
            }
        }

        return $headers;
    }

    /**
     * 更新统计信息
     */
    /**
     * @param array<string, mixed> $response
     */
    private function updateStats(float $startTime, array $response): void
    {
        $duration = (microtime(true) - $startTime) * 1000;

        $totalResponseTime = (float) ($this->stats['total_response_time'] ?? 0);
        $this->stats['total_response_time'] = $totalResponseTime + $duration;

        if ((bool) ($response['success'] ?? false)) {
            $successfulRequests = (int) ($this->stats['successful_requests'] ?? 0);
            $this->stats['successful_requests'] = $successfulRequests + 1;
        } else {
            $failedRequests = (int) ($this->stats['failed_requests'] ?? 0);
            $this->stats['failed_requests'] = $failedRequests + 1;
        }
    }

    /**
     * 处理错误
     *
     * @param \Throwable $exception 异常
     * @param string $method HTTP方法
     * @param string $path 请求路径
     * @param array<string, mixed> $data 请求数据
     * @return array<string, mixed> 错误响应
     */
    private function handleError(\Throwable $exception, string $method, string $path, array $data): array
    {
        // 检查是否需要重试
        if ($this->shouldRetry($exception) && $this->retryTimes > 0) {
            $retries = (int) ($this->stats['retries'] ?? 0);
            $this->stats['retries'] = $retries + 1;

            // 等待一段时间后重试
            sleep($this->getRetryDelay());

            return $this->request($method, $path, $data);
        }

        // 转换为具体的异常类型
        if ($exception instanceof \InvalidArgumentException) {
            throw new ApiException('无效的请求参数: ' . $exception->getMessage(), 400, $exception);
        }

        if ($exception instanceof \RuntimeException) {
            if (false !== strpos($exception->getMessage(), 'timeout')) {
                throw new NetworkException('请求超时: ' . $exception->getMessage(), 408, $exception);
            }

            if (false !== strpos($exception->getMessage(), 'connection')) {
                throw new NetworkException('网络连接失败: ' . $exception->getMessage(), 503, $exception);
            }
        }

        // 默认异常处理
        throw new ApiException('API请求失败: ' . $exception->getMessage(), 500, $exception);
    }

    /**
     * 判断是否需要重试
     */
    private function shouldRetry(\Throwable $exception): bool
    {
        // 网络错误和超时可以重试
        if ($exception instanceof \RuntimeException) {
            $message = $exception->getMessage();

            return false !== strpos($message, 'timeout')
                   || false !== strpos($message, 'connection')
                   || false !== strpos($message, 'network');
        }

        // 5xx服务器错误可以重试
        if ($exception->getCode() >= 500) {
            return true;
        }

        return false;
    }

    /**
     * 获取重试延迟时间
     */
    private function getRetryDelay(): int
    {
        // 指数退避算法
        $retries = (int) ($this->stats['retries'] ?? 0);

        return min(30, (int) pow(2, $retries));
    }
}

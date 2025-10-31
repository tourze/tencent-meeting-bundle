<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Service;

/**
 * 配置服务接口
 *
 * 定义配置管理的统一接口，为Bundle提供运行时配置访问能力
 */
interface ConfigServiceInterface
{
    /**
     * 获取API URL
     */
    public function getApiUrl(): string;

    /**
     * 获取请求超时时间（秒）
     */
    public function getTimeout(): int;

    /**
     * 获取重试次数
     */
    public function getRetryTimes(): int;

    /**
     * 获取日志级别
     */
    public function getLogLevel(): string;

    /**
     * 是否启用调试模式
     */
    public function isDebugEnabled(): bool;

    /**
     * 获取缓存TTL（秒）
     */
    public function getCacheTtl(): int;

    /**
     * 获取Webhook密钥
     */
    public function getWebhookSecret(): ?string;

    /**
     * 获取缓存驱动
     */
    public function getCacheDriver(): string;

    /**
     * 获取Redis主机
     */
    public function getRedisHost(): ?string;

    /**
     * 获取Redis端口
     */
    public function getRedisPort(): ?int;

    /**
     * 获取Redis密码
     */
    public function getRedisPassword(): ?string;

    /**
     * 获取认证Token
     */
    public function getAuthToken(): ?string;

    /**
     * 获取密钥
     */
    public function getSecretKey(): ?string;

    /**
     * 获取代理主机
     */
    public function getProxyHost(): ?string;

    /**
     * 获取代理端口
     */
    public function getProxyPort(): ?int;

    /**
     * 是否验证SSL证书
     */
    public function getVerifySsl(): bool;

    /**
     * 获取所有配置
     *
     * @return array<string, mixed>
     */
    public function getAllConfig(): array;
}

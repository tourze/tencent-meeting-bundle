<?php

namespace Tourze\TencentMeetingBundle\Service;

/**
 * 事件分发接口
 *
 * 定义Webhook事件分发的统一接口，为Bundle提供事件处理和分发能力
 */
interface EventDispatcherInterface
{
    /**
     * 分发事件
     *
     * @param object $event 事件对象
     * @return bool 分发是否成功
     */
    public function dispatch(object $event): bool;

    /**
     * 添加事件监听器
     *
     * @param string $eventName 事件名称
     * @param callable $listener 监听器回调
     */
    public function addListener(string $eventName, callable $listener): void;

    /**
     * 移除事件监听器
     *
     * @param string $eventName 事件名称
     * @param callable $listener 监听器回调
     */
    public function removeListener(string $eventName, callable $listener): void;

    /**
     * 获取事件监听器
     *
     * @param string|null $eventName 事件名称，null表示所有监听器
     * @return array<callable> 监听器列表
     */
    public function getListeners(?string $eventName = null): array;

    /**
     * 检查是否有监听器
     *
     * @param string|null $eventName 事件名称，null表示检查所有事件
     * @return bool 是否有监听器
     */
    public function hasListeners(?string $eventName = null): bool;

    /**
     * 验证事件
     *
     * @param object $event 事件对象
     * @return bool 验证是否通过
     */
    public function validateEvent(object $event): bool;

    /**
     * 验证签名
     *
     * @param string $signature 签名
     * @param string $payload 载荷
     * @return bool 验证是否通过
     */
    public function validateSignature(string $signature, string $payload): bool;

    /**
     * 处理事件
     *
     * @param object $event 事件对象
     */
    public function processEvent(object $event): void;
}

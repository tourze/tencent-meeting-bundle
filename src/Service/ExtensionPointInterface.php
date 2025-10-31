<?php

namespace Tourze\TencentMeetingBundle\Service;

/**
 * 扩展点接口
 *
 * 定义Bundle可扩展架构的统一接口，为插件和扩展提供标准化的集成能力
 */
interface ExtensionPointInterface
{
    /**
     * 执行扩展逻辑
     *
     * @param mixed $context 执行上下文
     * @return mixed 执行结果
     */
    public function execute($context): mixed;

    /**
     * 检查是否支持给定的上下文
     *
     * @param mixed $context 执行上下文
     * @return bool 是否支持
     */
    public function supports($context): bool;

    /**
     * 获取扩展优先级
     *
     * @return int 优先级，数值越大优先级越高
     */
    public function getPriority(): int;

    /**
     * 获取扩展名称
     *
     * @return string 扩展名称
     */
    public function getName(): string;

    /**
     * 获取扩展描述
     *
     * @return string 扩展描述
     */
    public function getDescription(): string;

    /**
     * 获取扩展点类型
     *
     * @return string 扩展点类型
     */
    public function getExtensionPointType(): string;

    /**
     * 执行前钩子
     *
     * @param mixed $context 执行上下文
     */
    public function beforeExecute($context): void;

    /**
     * 执行后钩子
     *
     * @param mixed $context 执行上下文
     * @param mixed $result 执行结果
     */
    public function afterExecute($context, $result): void;

    /**
     * 错误处理钩子
     *
     * @param mixed $context 执行上下文
     * @param \Throwable $exception 异常对象
     */
    public function onError($context, \Throwable $exception): void;
}

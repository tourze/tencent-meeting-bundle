<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Repository\TencentMeetingConfigRepository;

/**
 * @internal
 */
#[CoversClass(TencentMeetingConfigRepository::class)]
#[RunTestsInSeparateProcesses]
final class TencentMeetingConfigRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // Repository 测试基类不需要特殊的初始化
    }

    public function testFindOneByAppId(): void
    {
        /** @var TencentMeetingConfigRepository $repository */
        $repository = $this->getRepository();

        // 测试不存在的AppId
        $result = $repository->findOneByAppId('non-existent-app-id');
        self::assertNull($result);

        // 创建并保存一个配置
        $config = $this->createNewEntity();
        self::assertInstanceOf(TencentMeetingConfig::class, $config);
        $appId = $config->getAppId();
        $repository->save($config, true);

        // 测试能找到刚创建的配置
        $found = $repository->findOneByAppId($appId);
        self::assertInstanceOf(TencentMeetingConfig::class, $found);
        self::assertSame($appId, $found->getAppId());
    }

    public function testFindEnabledConfigs(): void
    {
        /** @var TencentMeetingConfigRepository $repository */
        $repository = $this->getRepository();

        // 创建一个启用的配置
        $enabledConfig = $this->createNewEntity();
        self::assertInstanceOf(TencentMeetingConfig::class, $enabledConfig);
        $enabledConfig->setEnabled(true);
        $repository->save($enabledConfig, true);

        // 创建一个禁用的配置
        $disabledConfig = new TencentMeetingConfig();
        $disabledConfig->setAppId('disabled-app-id-' . uniqid());
        $disabledConfig->setSecretId('test-secret-id-' . uniqid());
        $disabledConfig->setSecretKey('test-secret-key-' . uniqid());
        $disabledConfig->setAuthType('JWT');
        $disabledConfig->setEnabled(false);
        $repository->save($disabledConfig, true);

        // 测试只返回启用的配置
        $enabledConfigs = $repository->findEnabledConfigs();
        self::assertNotEmpty($enabledConfigs);

        foreach ($enabledConfigs as $config) {
            self::assertTrue($config->isEnabled());
        }
    }

    protected function createNewEntity(): object
    {
        $config = new TencentMeetingConfig();
        $config->setAppId('test-app-id-' . uniqid());
        $config->setSecretId('test-secret-id-' . uniqid());
        $config->setSecretKey('test-secret-key-' . uniqid());
        $config->setAuthType('JWT');
        $config->setEnabled(true);

        return $config;
    }

    /**
     * @return ServiceEntityRepository<TencentMeetingConfig>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(TencentMeetingConfigRepository::class);
    }
}

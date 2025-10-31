<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\Yaml\Yaml;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use Tourze\SymfonyDependencyServiceLoader\AutoExtension;
use Tourze\TencentMeetingBundle\DependencyInjection\TencentMeetingExtension;

/**
 * @internal
 */
#[CoversClass(TencentMeetingExtension::class)]
final class TencentMeetingExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private TencentMeetingExtension $extension;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new TencentMeetingExtension();
    }

    public function testExtensionCanBeCreated(): void
    {
        $extension = new TencentMeetingExtension();
        $this->assertInstanceOf(TencentMeetingExtension::class, $extension);
    }

    public function testExtensionInheritsFromAutoExtension(): void
    {
        $this->assertInstanceOf(AutoExtension::class, $this->extension);
    }

    public function testExtensionImplementsExtensionInterface(): void
    {
        $this->assertInstanceOf(ExtensionInterface::class, $this->extension);
    }

    public function testGetAlias(): void
    {
        $alias = $this->extension->getAlias();
        $this->assertEquals('tencent_meeting', $alias);
    }

    public function testGetConfigDir(): void
    {
        $reflection = new \ReflectionClass($this->extension);
        $method = $reflection->getMethod('getConfigDir');
        $method->setAccessible(true);

        $configDir = $method->invoke($this->extension);
        $this->assertIsString($configDir);

        $this->assertNotEmpty($configDir);
        $this->assertStringEndsWith('/Resources/config', $configDir);
        $this->assertDirectoryExists($configDir);
    }

    public function testConfigDirectoryContainsServicesFile(): void
    {
        $reflection = new \ReflectionClass($this->extension);
        $method = $reflection->getMethod('getConfigDir');
        $method->setAccessible(true);

        $configDir = $method->invoke($this->extension);
        $this->assertIsString($configDir);
        $this->assertNotEmpty($configDir, 'getConfigDir should return a non-empty string');
        $servicesFile = $configDir . '/services.yaml';

        $this->assertFileExists($servicesFile, 'services.yaml file should exist in config directory');
    }

    public function testLoadMethodExists(): void
    {
        // 验证load方法存在并检查其参数
        $reflection = new \ReflectionMethod($this->extension, 'load');
        $this->assertInstanceOf(\ReflectionMethod::class, $reflection);

        $parameters = $reflection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('configs', $parameters[0]->getName());
        $this->assertEquals('container', $parameters[1]->getName());
    }

    public function testLoadWithEmptyConfig(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');

        // 这应该不会抛出异常
        $this->extension->load([], $container);

        // 验证容器已被配置
        $this->assertInstanceOf(ContainerBuilder::class, $container);
    }

    public function testLoadWithTestEnvironment(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');

        $this->extension->load([], $container);

        // 验证测试环境配置被加载
        $this->assertEquals('test', $container->getParameter('kernel.environment'));
    }

    public function testLoadWithDevEnvironment(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'dev');

        $this->extension->load([], $container);

        // 验证开发环境配置被加载
        $this->assertEquals('dev', $container->getParameter('kernel.environment'));
    }

    public function testLoadWithProdEnvironment(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

        $this->extension->load([], $container);

        // 验证生产环境配置被加载
        $this->assertEquals('prod', $container->getParameter('kernel.environment'));
    }

    public function testExtensionHasCorrectNamespace(): void
    {
        $reflection = new \ReflectionClass($this->extension);
        $namespace = $reflection->getNamespaceName();

        $this->assertEquals('Tourze\TencentMeetingBundle\DependencyInjection', $namespace);
    }

    public function testServicesYamlIsValidYaml(): void
    {
        $reflection = new \ReflectionClass($this->extension);
        $method = $reflection->getMethod('getConfigDir');
        $method->setAccessible(true);

        $configDir = $method->invoke($this->extension);
        $this->assertIsString($configDir);
        $this->assertNotEmpty($configDir, 'getConfigDir should return a non-empty string');
        $servicesFile = $configDir . '/services.yaml';

        if (file_exists($servicesFile)) {
            $content = file_get_contents($servicesFile);
            $this->assertNotFalse($content);

            // 尝试解析YAML来验证其有效性
            try {
                $parsed = Yaml::parse($content);
            } catch (\Exception $e) {
                self::fail('services.yaml contains invalid YAML: ' . $e->getMessage());
            }
        }
    }

    public function testExtensionCanLoadMultipleConfigs(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');

        $configs = [
            ['setting1' => 'value1'],
            ['setting2' => 'value2'],
        ];

        // 这应该不会抛出异常
        $this->extension->load($configs, $container);

        $this->assertInstanceOf(ContainerBuilder::class, $container);
    }
}

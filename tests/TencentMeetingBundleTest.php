<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use HttpClientBundle\HttpClientBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use Tourze\TencentMeetingBundle\TencentMeetingBundle;

/**
 * @internal
 */
#[CoversClass(TencentMeetingBundle::class)]
#[RunTestsInSeparateProcesses]
final class TencentMeetingBundleTest extends AbstractBundleTestCase
{
    public function testBundleCanBeInstantiated(): void
    {
        $reflection = new \ReflectionClass(TencentMeetingBundle::class);
        $this->assertTrue($reflection->isInstantiable());
        $this->assertFalse($reflection->isAbstract());
    }

    public function testBundleExtendsSymfonyBundle(): void
    {
        $reflection = new \ReflectionClass(TencentMeetingBundle::class);
        $parentClass = $reflection->getParentClass();
        $this->assertNotFalse($parentClass, 'Bundle should have a parent class');
        $this->assertSame(Bundle::class, $parentClass->getName());
    }

    public function testBundleImplementsBundleDependencyInterface(): void
    {
        $reflection = new \ReflectionClass(TencentMeetingBundle::class);
        $interfaces = $reflection->getInterfaceNames();
        $this->assertContains(BundleDependencyInterface::class, $interfaces);
    }

    public function testDoctrineBundleDependencyConfiguration(): void
    {
        $dependencies = TencentMeetingBundle::getBundleDependencies();

        $this->assertSame(['all' => true], $dependencies[DoctrineBundle::class]);
    }

    public function testHttpClientBundleDependencyConfiguration(): void
    {
        $dependencies = TencentMeetingBundle::getBundleDependencies();

        $this->assertSame(['all' => true], $dependencies[HttpClientBundle::class]);
    }

    public function testBundleHasCorrectName(): void
    {
        $reflection = new \ReflectionClass(TencentMeetingBundle::class);

        $this->assertSame('TencentMeetingBundle', $reflection->getShortName());
        $this->assertSame('Tourze\TencentMeetingBundle', $reflection->getNamespaceName());
    }

    public function testBundleDependenciesAreValidClasses(): void
    {
        $dependencies = TencentMeetingBundle::getBundleDependencies();

        foreach (array_keys($dependencies) as $bundleClass) {
            $instance = new $bundleClass();
            $this->assertInstanceOf(Bundle::class, $instance, "Bundle class {$bundleClass} should be a valid Bundle");
        }
    }

    public function testDoctrineBundleIsValidBundle(): void
    {
        $doctrineBundle = new DoctrineBundle();
        $this->assertInstanceOf(Bundle::class, $doctrineBundle);
        $this->assertInstanceOf(DoctrineBundle::class, $doctrineBundle);
    }

    public function testHttpClientBundleIsValidBundle(): void
    {
        $httpClientBundle = new HttpClientBundle();
        $this->assertInstanceOf(Bundle::class, $httpClientBundle);
        $this->assertInstanceOf(HttpClientBundle::class, $httpClientBundle);
    }

    public function testBundleDependencyEnvironmentConfiguration(): void
    {
        $dependencies = TencentMeetingBundle::getBundleDependencies();

        foreach ($dependencies as $config) {
            $this->assertArrayHasKey('all', $config);
            $this->assertTrue($config['all']);
        }
    }
}

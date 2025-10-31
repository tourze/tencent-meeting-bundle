<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;
use Tourze\TencentMeetingBundle\Service\AttributeControllerLoader;

/**
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
final class AttributeControllerLoaderTest extends TestCase
{
    private AttributeControllerLoader $loader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loader = new AttributeControllerLoader();
    }

    public function testLoaderCanBeInstantiated(): void
    {
        $this->assertInstanceOf(AttributeControllerLoader::class, $this->loader);
    }

    public function testImplementsLoaderInterface(): void
    {
        $this->assertInstanceOf(Loader::class, $this->loader);
    }

    public function testImplementsRoutingAutoLoaderInterface(): void
    {
        $this->assertInstanceOf(RoutingAutoLoaderInterface::class, $this->loader);
    }

    public function testLoadMethodReturnsRouteCollection(): void
    {
        $result = $this->loader->load('resource');
        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function testLoadMethodWithNullType(): void
    {
        $result = $this->loader->load('resource', null);
        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function testLoadMethodWithStringType(): void
    {
        $result = $this->loader->load('resource', 'some_type');
        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function testSupportsMethodReturnsFalse(): void
    {
        $this->assertFalse($this->loader->supports('resource'));
        $this->assertFalse($this->loader->supports('resource', null));
        $this->assertFalse($this->loader->supports('resource', 'some_type'));
    }

    public function testAutoloadMethodReturnsRouteCollection(): void
    {
        $result = $this->loader->autoload();
        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function testLoadAndAutoloadReturnSameCollection(): void
    {
        $loadResult = $this->loader->load('resource');
        $autoloadResult = $this->loader->autoload();

        // 两个方法应该返回相同的路由集合
        $this->assertEquals($loadResult->count(), $autoloadResult->count());
    }

    public function testClassStructure(): void
    {
        $reflection = new \ReflectionClass(AttributeControllerLoader::class);

        // 检查类是否可实例化
        $this->assertTrue($reflection->isInstantiable());
        $this->assertFalse($reflection->isAbstract());

        // 检查继承关系
        $this->assertTrue($reflection->isSubclassOf(Loader::class));
    }

    public function testRequiredMethodsExist(): void
    {
        $reflection = new \ReflectionClass(AttributeControllerLoader::class);

        $expectedMethods = ['load', 'supports', 'autoload'];

        foreach ($expectedMethods as $method) {
            $this->assertTrue($reflection->hasMethod($method), "Method {$method} should exist");
            $methodReflection = $reflection->getMethod($method);
            $this->assertTrue($methodReflection->isPublic(), "Method {$method} should be public");
        }
    }

    public function testLoadMethodSignature(): void
    {
        $reflection = new \ReflectionClass(AttributeControllerLoader::class);
        $method = $reflection->getMethod('load');

        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);

        $this->assertEquals('resource', $parameters[0]->getName());
        $this->assertEquals('type', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->allowsNull());

        // 检查返回类型
        $returnType = $method->getReturnType();
        if ($returnType instanceof \ReflectionNamedType) {
            $this->assertEquals(RouteCollection::class, $returnType->getName());
        }
    }

    public function testSupportsMethodSignature(): void
    {
        $reflection = new \ReflectionClass(AttributeControllerLoader::class);
        $method = $reflection->getMethod('supports');

        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);

        $this->assertEquals('resource', $parameters[0]->getName());
        $this->assertEquals('type', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->allowsNull());

        // 检查返回类型
        $returnType = $method->getReturnType();
        if ($returnType instanceof \ReflectionNamedType) {
            $this->assertEquals('bool', $returnType->getName());
        }
    }

    public function testAutoloadMethodSignature(): void
    {
        $reflection = new \ReflectionClass(AttributeControllerLoader::class);
        $method = $reflection->getMethod('autoload');

        $parameters = $method->getParameters();
        $this->assertCount(0, $parameters);

        // 检查返回类型
        $returnType = $method->getReturnType();
        if ($returnType instanceof \ReflectionNamedType) {
            $this->assertEquals(RouteCollection::class, $returnType->getName());
        }
    }

    public function testConstructorCreatesValidState(): void
    {
        $loader = new AttributeControllerLoader();

        // 验证构造后能正常工作
        $this->assertInstanceOf(RouteCollection::class, $loader->load('test'));
        $this->assertInstanceOf(RouteCollection::class, $loader->autoload());
        $this->assertFalse($loader->supports('test'));
    }

    public function testHasAutoconfigureTagAttribute(): void
    {
        $reflection = new \ReflectionClass(AttributeControllerLoader::class);
        $attributes = $reflection->getAttributes();

        $hasAutoconfigureTag = false;
        foreach ($attributes as $attribute) {
            if (str_contains($attribute->getName(), 'AutoconfigureTag')) {
                $hasAutoconfigureTag = true;
                break;
            }
        }

        // Currently disabled to avoid route conflicts with EasyAdmin auto-discovery
        $this->assertFalse($hasAutoconfigureTag, 'AutoconfigureTag attribute is temporarily disabled');
    }
}

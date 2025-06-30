<?php

namespace Tourze\TencentMeetingBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\TencentMeetingBundle\DependencyInjection\TencentMeetingExtension;

class TencentMeetingExtensionTest extends TestCase
{
    public function testLoadServicesConfiguration(): void
    {
        $container = new ContainerBuilder();
        $extension = new TencentMeetingExtension();
        
        $extension->load([], $container);
        
        // 验证容器已成功加载配置
        $this->assertNotEmpty($container->getDefinitions());
    }
}
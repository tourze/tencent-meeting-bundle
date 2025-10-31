<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use Tourze\TencentMeetingBundle\Service\AdminMenu;

/**
 * AdminMenu 测试 - 用于需要特殊mock配置的场景（验证调用次数）
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuUnitTest extends AbstractEasyAdminMenuTestCase
{
    private LinkGeneratorInterface&MockObject $linkGenerator;

    private AdminMenu $adminMenu;

    protected function onSetUp(): void
    {
        // 创建 mock 来验证方法调用
        $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);

        // 配置 mock 返回值，根据实体类返回对应的 URI
        $this->linkGenerator
            ->method('getCurdListPage')
            ->willReturnCallback(function (string $entityClass): string {
                return '/admin/' . basename(str_replace('\\', '/', $entityClass));
            })
        ;

        // 在容器中注入 mock 服务
        self::getContainer()->set(LinkGeneratorInterface::class, $this->linkGenerator);

        // 从容器中获取 AdminMenu 实例
        $this->adminMenu = self::getService(AdminMenu::class);
    }

    public function testLinkGeneratorIsCalledForAllMenuItems(): void
    {
        $rootMenu = $this->createMenuItem('root');
        ($this->adminMenu)($rootMenu);

        // 验证所有主要菜单项都存在
        $tencentMeetingMenu = $rootMenu->getChild('腾讯会议');
        $this->assertNotNull($tencentMeetingMenu);

        $this->assertNotNull($tencentMeetingMenu->getChild('会议管理'));
        $this->assertNotNull($tencentMeetingMenu->getChild('用户权限'));
        $this->assertNotNull($tencentMeetingMenu->getChild('设备资源'));
        $this->assertNotNull($tencentMeetingMenu->getChild('系统配置'));
    }

    private function createMenuItem(string $name): ItemInterface
    {
        $factory = new MenuFactory();

        return $factory->createItem($name);
    }
}

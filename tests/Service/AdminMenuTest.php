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
 * AdminMenu服务测试
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $adminMenu;

    private MenuFactory $menuFactory;

    private LinkGeneratorInterface&MockObject $linkGenerator;

    protected function onSetUp(): void
    {
        // 创建 mock 服务，因为我们需要控制方法返回值
        $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);

        // 配置 mock 返回值，确保菜单项 URI 不为空且包含类名
        $this->linkGenerator->method('getCurdListPage')
            ->willReturnCallback(function (string $entityClass): string {
                // 根据实体类返回对应的 URI，确保包含类名以满足测试断言
                return '/admin/' . basename(str_replace('\\', '/', $entityClass));
            })
        ;

        $this->menuFactory = new MenuFactory();

        // 在容器中设置 mock 服务
        self::getContainer()->set(LinkGeneratorInterface::class, $this->linkGenerator);

        // 从容器中获取服务实例
        $this->adminMenu = self::getService(AdminMenu::class);
    }

    private function createMenuItem(string $name): ItemInterface
    {
        return $this->menuFactory->createItem($name);
    }

    public function testInvokeAddsMenuItems(): void
    {
        $rootMenu = $this->createMenuItem('root');

        // 调用AdminMenu
        ($this->adminMenu)($rootMenu);

        // 验证腾讯会议菜单已添加
        $tencentMeetingMenu = $rootMenu->getChild('腾讯会议');
        $this->assertNotNull($tencentMeetingMenu, '腾讯会议菜单应该存在');
        $this->assertSame('fas fa-video', $tencentMeetingMenu->getAttribute('icon'));
    }

    public function testMenuItemsHaveCorrectIcons(): void
    {
        $rootMenu = $this->createMenuItem('root');

        ($this->adminMenu)($rootMenu);

        $tencentMeetingMenu = $rootMenu->getChild('腾讯会议');
        $this->assertNotNull($tencentMeetingMenu);

        // 检查主要菜单项的图标
        $meetingMenu = $tencentMeetingMenu->getChild('会议管理');
        $this->assertNotNull($meetingMenu);
        $this->assertSame('fas fa-calendar-alt', $meetingMenu->getAttribute('icon'));

        $userMenu = $tencentMeetingMenu->getChild('用户权限');
        $this->assertNotNull($userMenu);
        $this->assertSame('fas fa-users-cog', $userMenu->getAttribute('icon'));

        $resourceMenu = $tencentMeetingMenu->getChild('设备资源');
        $this->assertNotNull($resourceMenu);
        $this->assertSame('fas fa-server', $resourceMenu->getAttribute('icon'));

        $systemMenu = $tencentMeetingMenu->getChild('系统配置');
        $this->assertNotNull($systemMenu);
        $this->assertSame('fas fa-cogs', $systemMenu->getAttribute('icon'));
    }

    public function testMenuItemsHaveCorrectUris(): void
    {
        $rootMenu = $this->createMenuItem('root');

        ($this->adminMenu)($rootMenu);

        $tencentMeetingMenu = $rootMenu->getChild('腾讯会议');
        $this->assertNotNull($tencentMeetingMenu);

        // 检查会议管理菜单项的URI
        $meetingMenu = $tencentMeetingMenu->getChild('会议管理');
        $this->assertNotNull($meetingMenu);

        $meetingList = $meetingMenu->getChild('会议列表');
        $this->assertNotNull($meetingList);
        $uri = $meetingList->getUri();
        $this->assertIsString($uri, '会议列表 URI 应该是字符串');
        $this->assertNotEmpty($uri);
        $this->assertStringContainsString('Meeting', $uri);

        $userMenu = $tencentMeetingMenu->getChild('用户权限');
        $this->assertNotNull($userMenu);
        $userManagement = $userMenu->getChild('用户管理');
        $this->assertNotNull($userManagement);
        $userUri = $userManagement->getUri();
        $this->assertIsString($userUri, '用户管理 URI 应该是字符串');
        $this->assertNotEmpty($userUri);
        $this->assertStringContainsString('User', $userUri);
    }
}

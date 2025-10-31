<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\TencentMeetingBundle\Entity\Background;
use Tourze\TencentMeetingBundle\Entity\Department;
use Tourze\TencentMeetingBundle\Entity\Device;
use Tourze\TencentMeetingBundle\Entity\Layout;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\MeetingBackground;
use Tourze\TencentMeetingBundle\Entity\MeetingDocument;
use Tourze\TencentMeetingBundle\Entity\MeetingGuest;
use Tourze\TencentMeetingBundle\Entity\MeetingLayout;
use Tourze\TencentMeetingBundle\Entity\MeetingRole;
use Tourze\TencentMeetingBundle\Entity\MeetingRoom;
use Tourze\TencentMeetingBundle\Entity\MeetingUser;
use Tourze\TencentMeetingBundle\Entity\MeetingVote;
use Tourze\TencentMeetingBundle\Entity\Permission;
use Tourze\TencentMeetingBundle\Entity\Recording;
use Tourze\TencentMeetingBundle\Entity\Role;
use Tourze\TencentMeetingBundle\Entity\Room;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Entity\User;
use Tourze\TencentMeetingBundle\Entity\UserRole;
use Tourze\TencentMeetingBundle\Entity\WebhookEvent;

/**
 * 腾讯会议管理后台菜单提供者
 */
#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        // 创建腾讯会议主菜单
        if (null === $item->getChild('腾讯会议')) {
            $item->addChild('腾讯会议')
                ->setAttribute('icon', 'fas fa-video')
            ;
        }

        $tencentMeetingMenu = $item->getChild('腾讯会议');
        if (null === $tencentMeetingMenu) {
            return;
        }

        // 会议管理
        $this->addMeetingMenu($tencentMeetingMenu);

        // 用户权限管理
        $this->addUserPermissionMenu($tencentMeetingMenu);

        // 设备资源管理
        $this->addResourceMenu($tencentMeetingMenu);

        // 系统配置
        $this->addSystemMenu($tencentMeetingMenu);
    }

    /**
     * 添加会议管理菜单
     */
    private function addMeetingMenu(ItemInterface $parent): void
    {
        if (null === $parent->getChild('会议管理')) {
            $parent->addChild('会议管理')
                ->setAttribute('icon', 'fas fa-calendar-alt')
            ;
        }

        $meetingMenu = $parent->getChild('会议管理');
        if (null === $meetingMenu) {
            return;
        }

        $meetingMenu->addChild('会议列表')
            ->setUri($this->linkGenerator->getCurdListPage(Meeting::class))
            ->setAttribute('icon', 'fas fa-list')
        ;

        $meetingMenu->addChild('会议参会者')
            ->setUri($this->linkGenerator->getCurdListPage(MeetingUser::class))
            ->setAttribute('icon', 'fas fa-users')
        ;

        $meetingMenu->addChild('会议嘉宾')
            ->setUri($this->linkGenerator->getCurdListPage(MeetingGuest::class))
            ->setAttribute('icon', 'fas fa-user-tie')
        ;

        $meetingMenu->addChild('会议文档')
            ->setUri($this->linkGenerator->getCurdListPage(MeetingDocument::class))
            ->setAttribute('icon', 'fas fa-file-alt')
        ;

        $meetingMenu->addChild('会议录制')
            ->setUri($this->linkGenerator->getCurdListPage(Recording::class))
            ->setAttribute('icon', 'fas fa-video')
        ;

        $meetingMenu->addChild('会议投票')
            ->setUri($this->linkGenerator->getCurdListPage(MeetingVote::class))
            ->setAttribute('icon', 'fas fa-poll')
        ;

        $meetingMenu->addChild('会议角色')
            ->setUri($this->linkGenerator->getCurdListPage(MeetingRole::class))
            ->setAttribute('icon', 'fas fa-user-shield')
        ;

        $meetingMenu->addChild('会议布局')
            ->setUri($this->linkGenerator->getCurdListPage(MeetingLayout::class))
            ->setAttribute('icon', 'fas fa-th-large')
        ;

        $meetingMenu->addChild('会议背景')
            ->setUri($this->linkGenerator->getCurdListPage(MeetingBackground::class))
            ->setAttribute('icon', 'fas fa-image')
        ;
    }

    /**
     * 添加用户权限管理菜单
     */
    private function addUserPermissionMenu(ItemInterface $parent): void
    {
        if (null === $parent->getChild('用户权限')) {
            $parent->addChild('用户权限')
                ->setAttribute('icon', 'fas fa-users-cog')
            ;
        }

        $userMenu = $parent->getChild('用户权限');
        if (null === $userMenu) {
            return;
        }

        $userMenu->addChild('用户管理')
            ->setUri($this->linkGenerator->getCurdListPage(User::class))
            ->setAttribute('icon', 'fas fa-user')
        ;

        $userMenu->addChild('角色管理')
            ->setUri($this->linkGenerator->getCurdListPage(Role::class))
            ->setAttribute('icon', 'fas fa-user-tag')
        ;

        $userMenu->addChild('权限管理')
            ->setUri($this->linkGenerator->getCurdListPage(Permission::class))
            ->setAttribute('icon', 'fas fa-key')
        ;

        $userMenu->addChild('用户角色关联')
            ->setUri($this->linkGenerator->getCurdListPage(UserRole::class))
            ->setAttribute('icon', 'fas fa-link')
        ;

        $userMenu->addChild('部门管理')
            ->setUri($this->linkGenerator->getCurdListPage(Department::class))
            ->setAttribute('icon', 'fas fa-sitemap')
        ;
    }

    /**
     * 添加设备资源管理菜单
     */
    private function addResourceMenu(ItemInterface $parent): void
    {
        if (null === $parent->getChild('设备资源')) {
            $parent->addChild('设备资源')
                ->setAttribute('icon', 'fas fa-server')
            ;
        }

        $resourceMenu = $parent->getChild('设备资源');
        if (null === $resourceMenu) {
            return;
        }

        $resourceMenu->addChild('会议室管理')
            ->setUri($this->linkGenerator->getCurdListPage(Room::class))
            ->setAttribute('icon', 'fas fa-door-open')
        ;

        $resourceMenu->addChild('会议室关联')
            ->setUri($this->linkGenerator->getCurdListPage(MeetingRoom::class))
            ->setAttribute('icon', 'fas fa-link')
        ;

        $resourceMenu->addChild('设备管理')
            ->setUri($this->linkGenerator->getCurdListPage(Device::class))
            ->setAttribute('icon', 'fas fa-desktop')
        ;

        $resourceMenu->addChild('背景管理')
            ->setUri($this->linkGenerator->getCurdListPage(Background::class))
            ->setAttribute('icon', 'fas fa-images')
        ;

        $resourceMenu->addChild('布局管理')
            ->setUri($this->linkGenerator->getCurdListPage(Layout::class))
            ->setAttribute('icon', 'fas fa-table')
        ;
    }

    /**
     * 添加系统配置菜单
     */
    private function addSystemMenu(ItemInterface $parent): void
    {
        if (null === $parent->getChild('系统配置')) {
            $parent->addChild('系统配置')
                ->setAttribute('icon', 'fas fa-cogs')
            ;
        }

        $systemMenu = $parent->getChild('系统配置');
        if (null === $systemMenu) {
            return;
        }

        $systemMenu->addChild('腾讯会议配置')
            ->setUri($this->linkGenerator->getCurdListPage(TencentMeetingConfig::class))
            ->setAttribute('icon', 'fas fa-wrench')
        ;

        $systemMenu->addChild('Webhook事件')
            ->setUri($this->linkGenerator->getCurdListPage(WebhookEvent::class))
            ->setAttribute('icon', 'fas fa-exchange-alt')
        ;
    }
}

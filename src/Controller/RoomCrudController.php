<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TencentMeetingBundle\Entity\Room;

/**
 * @extends AbstractCrudController<Room>
 */
#[AdminCrud(routePath: '/tencent-meeting/room', routeName: 'tencent_meeting_room')]
final class RoomCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Room::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('会议室')
            ->setEntityLabelInPlural('会议室管理')
            ->setPageTitle('index', '会议室管理')
            ->setPageTitle('new', '新建会议室')
            ->setPageTitle('edit', '编辑会议室')
            ->setPageTitle('detail', '会议室详情')
            ->setDefaultSort(['orderWeight' => 'ASC', 'id' => 'DESC'])
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->hideOnForm()
        ;

        yield TextField::new('roomId', '会议室ID')
            ->setHelp('会议室的唯一标识符')
            ->setRequired(true)
        ;

        yield TextField::new('name', '会议室名称')
            ->setHelp('会议室的显示名称')
            ->setRequired(true)
        ;

        yield TextareaField::new('description', '会议室描述')
            ->setHelp('会议室的详细描述信息')
            ->hideOnIndex()
        ;

        yield ChoiceField::new('roomType', '会议室类型')
            ->setChoices([
                '物理会议室' => 'physical',
                '虚拟会议室' => 'virtual',
                '混合会议室' => 'hybrid',
            ])
            ->setHelp('会议室的类型')
            ->renderAsBadges([
                'physical' => 'primary',
                'virtual' => 'success',
                'hybrid' => 'info',
            ])
        ;

        yield ChoiceField::new('status', '会议室状态')
            ->setChoices([
                '可用' => 'available',
                '占用' => 'occupied',
                '维护中' => 'maintenance',
                '停用' => 'inactive',
            ])
            ->setHelp('会议室当前状态')
            ->renderAsBadges([
                'available' => 'success',
                'occupied' => 'warning',
                'maintenance' => 'info',
                'inactive' => 'danger',
            ])
        ;

        yield IntegerField::new('capacity', '容量')
            ->setHelp('会议室可容纳的人数')
            ->setRequired(true)
        ;

        yield TextField::new('location', '位置')
            ->setHelp('会议室的物理位置')
            ->hideOnIndex()
        ;

        yield TextField::new('equipment', '设备信息')
            ->setHelp('会议室配备的设备')
            ->hideOnIndex()
        ;

        yield TextField::new('bookingRules', '预订规则')
            ->setHelp('会议室的预订规则说明')
            ->hideOnIndex()
        ;

        yield IntegerField::new('orderWeight', '排序权重')
            ->setHelp('数值越小排序越靠前')
            ->hideOnIndex()
        ;

        yield BooleanField::new('requiresApproval', '需要审批')
            ->setHelp('预订此会议室是否需要审批')
            ->renderAsSwitch(false)
        ;

        yield DateTimeField::new('expirationTime', '过期时间')
            ->setHelp('会议室的有效期')
            ->hideOnIndex()
        ;

        yield ArrayField::new('roomConfig', '会议室配置')
            ->setHelp('会议室的详细配置信息')
            ->hideOnIndex()
            ->onlyOnDetail()
        ;

        yield AssociationField::new('config', '配置')
            ->setHelp('腾讯会议配置信息')
            ->hideOnIndex()
        ;

        yield DateTimeField::new('createdAt', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        yield DateTimeField::new('updatedAt', '更新时间')
            ->hideOnForm()
            ->hideOnIndex()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('roomId', '会议室ID'))
            ->add(TextFilter::new('name', '会议室名称'))
            ->add(
                ChoiceFilter::new('roomType', '会议室类型')
                    ->setChoices([
                        '物理会议室' => 'physical',
                        '虚拟会议室' => 'virtual',
                        '混合会议室' => 'hybrid',
                    ])
            )
            ->add(
                ChoiceFilter::new('status', '会议室状态')
                    ->setChoices([
                        '可用' => 'available',
                        '占用' => 'occupied',
                        '维护中' => 'maintenance',
                        '停用' => 'inactive',
                    ])
            )
            ->add(NumericFilter::new('capacity', '容量'))
            ->add(TextFilter::new('location', '位置'))
            ->add(BooleanFilter::new('requiresApproval', '需要审批'))
        ;
    }
}

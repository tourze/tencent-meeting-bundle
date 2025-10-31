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
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TencentMeetingBundle\Entity\Permission;

/**
 * @extends AbstractCrudController<Permission>
 */
#[AdminCrud(routePath: '/tencent-meeting/permission', routeName: 'tencent_meeting_permission')]
final class PermissionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Permission::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('权限')
            ->setEntityLabelInPlural('权限管理')
            ->setPageTitle('index', '权限管理')
            ->setPageTitle('new', '新建权限')
            ->setPageTitle('edit', '编辑权限')
            ->setPageTitle('detail', '权限详情')
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

        yield TextField::new('permissionId', '权限ID')
            ->setHelp('权限的唯一标识符')
            ->setRequired(true)
        ;

        yield TextField::new('name', '权限名称')
            ->setHelp('权限的显示名称')
            ->setRequired(true)
        ;

        yield TextareaField::new('description', '权限描述')
            ->setHelp('详细说明权限的作用和范围')
            ->hideOnIndex()
        ;

        yield ChoiceField::new('permissionType', '权限类型')
            ->setChoices([
                '系统权限' => 'system',
                '用户权限' => 'user',
                '会议权限' => 'meeting',
                '录制权限' => 'recording',
                '文档权限' => 'document',
                '会议室权限' => 'room',
            ])
            ->setHelp('权限的分类类型')
            ->renderAsBadges([
                'system' => 'primary',
                'user' => 'secondary',
                'meeting' => 'info',
                'recording' => 'warning',
                'document' => 'success',
                'room' => 'dark',
            ])
        ;

        yield TextField::new('permissionCode', '权限代码')
            ->setHelp('权限的编程标识符')
            ->setRequired(true)
        ;

        yield ChoiceField::new('status', '权限状态')
            ->setChoices([
                '激活' => 'active',
                '停用' => 'inactive',
            ])
            ->setHelp('权限当前状态')
            ->renderAsBadges([
                'active' => 'success',
                'inactive' => 'warning',
            ])
        ;

        yield IntegerField::new('orderWeight', '排序权重')
            ->setHelp('数值越小排序越靠前')
            ->hideOnIndex()
        ;

        yield BooleanField::new('isBuiltIn', '内置权限')
            ->setHelp('是否为系统内置权限')
            ->renderAsSwitch(false)
        ;

        yield ArrayField::new('permissionConfig', '权限配置')
            ->setHelp('权限的详细配置信息')
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
            ->add(TextFilter::new('permissionId', '权限ID'))
            ->add(TextFilter::new('name', '权限名称'))
            ->add(TextFilter::new('permissionCode', '权限代码'))
            ->add(
                ChoiceFilter::new('permissionType', '权限类型')
                    ->setChoices([
                        '系统权限' => 'system',
                        '用户权限' => 'user',
                        '会议权限' => 'meeting',
                        '录制权限' => 'recording',
                        '文档权限' => 'document',
                        '会议室权限' => 'room',
                    ])
            )
            ->add(
                ChoiceFilter::new('status', '权限状态')
                    ->setChoices([
                        '激活' => 'active',
                        '停用' => 'inactive',
                    ])
            )
            ->add(BooleanFilter::new('isBuiltIn', '内置权限'))
        ;
    }
}

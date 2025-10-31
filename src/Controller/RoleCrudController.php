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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TencentMeetingBundle\Entity\Role;

/**
 * @extends AbstractCrudController<Role>
 */
#[AdminCrud(routePath: '/tencent-meeting/role', routeName: 'tencent_meeting_role')]
final class RoleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Role::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('角色')
            ->setEntityLabelInPlural('角色管理')
            ->setPageTitle('index', '角色管理')
            ->setPageTitle('new', '新建角色')
            ->setPageTitle('edit', '编辑角色')
            ->setPageTitle('detail', '角色详情')
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

        yield TextField::new('roleId', '角色ID')
            ->setHelp('角色的唯一标识符')
            ->setRequired(true)
        ;

        yield TextField::new('name', '角色名称')
            ->setHelp('角色显示名称')
            ->setRequired(true)
        ;

        yield TextareaField::new('description', '角色描述')
            ->setHelp('详细说明角色的用途和权限范围')
            ->hideOnIndex()
        ;

        yield ChoiceField::new('roleType', '角色类型')
            ->setChoices([
                '系统角色' => 'system',
                '自定义角色' => 'custom',
                '会议角色' => 'meeting',
                '部门角色' => 'department',
            ])
            ->setHelp('角色的分类类型')
            ->renderAsBadges([
                'system' => 'primary',
                'custom' => 'secondary',
                'meeting' => 'info',
                'department' => 'warning',
            ])
        ;

        yield ChoiceField::new('status', '角色状态')
            ->setChoices([
                '激活' => 'active',
                '停用' => 'inactive',
                '已删除' => 'deleted',
            ])
            ->setHelp('角色当前状态')
            ->renderAsBadges([
                'active' => 'success',
                'inactive' => 'warning',
                'deleted' => 'danger',
            ])
        ;

        yield IntegerField::new('orderWeight', '排序权重')
            ->setHelp('数值越小排序越靠前')
            ->hideOnIndex()
        ;

        yield TextField::new('parentRoleId', '父角色ID')
            ->setHelp('角色继承的父角色标识')
            ->hideOnIndex()
        ;

        yield ArrayField::new('permissions', '权限列表')
            ->setHelp('角色拥有的权限配置')
            ->hideOnIndex()
            ->onlyOnDetail()
        ;

        yield ArrayField::new('attributes', '角色属性')
            ->setHelp('角色的扩展属性配置')
            ->hideOnIndex()
            ->onlyOnDetail()
        ;

        yield AssociationField::new('config', '配置')
            ->setHelp('腾讯会议配置信息')
            ->hideOnIndex()
        ;

        yield AssociationField::new('userRoles', '用户关联')
            ->setHelp('使用此角色的用户')
            ->hideOnIndex()
            ->onlyOnDetail()
        ;

        yield AssociationField::new('meetingRoles', '会议关联')
            ->setHelp('使用此角色的会议')
            ->hideOnIndex()
            ->onlyOnDetail()
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
            ->add(TextFilter::new('roleId', '角色ID'))
            ->add(TextFilter::new('name', '角色名称'))
            ->add(
                ChoiceFilter::new('roleType', '角色类型')
                    ->setChoices([
                        '系统角色' => 'system',
                        '自定义角色' => 'custom',
                        '会议角色' => 'meeting',
                        '部门角色' => 'department',
                    ])
            )
            ->add(
                ChoiceFilter::new('status', '角色状态')
                    ->setChoices([
                        '激活' => 'active',
                        '停用' => 'inactive',
                        '已删除' => 'deleted',
                    ])
            )
            ->add(TextFilter::new('parentRoleId', '父角色ID'))
        ;
    }
}

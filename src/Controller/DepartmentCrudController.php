<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TencentMeetingBundle\Entity\Department;

/**
 * @extends AbstractCrudController<Department>
 */
#[AdminCrud(routePath: '/tencent-meeting/department', routeName: 'tencent_meeting_department')]
final class DepartmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Department::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('部门')
            ->setEntityLabelInPlural('部门管理')
            ->setPageTitle('index', '部门管理')
            ->setPageTitle('new', '新建部门')
            ->setPageTitle('edit', '编辑部门')
            ->setPageTitle('detail', '部门详情')
            ->setDefaultSort(['orderWeight' => 'ASC', 'level' => 'ASC', 'id' => 'ASC'])
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

        yield TextField::new('departmentId', '部门ID')
            ->setHelp('部门的唯一标识符')
            ->setRequired(true)
        ;

        yield TextField::new('name', '部门名称')
            ->setHelp('部门的显示名称')
            ->setRequired(true)
        ;

        yield TextareaField::new('description', '部门描述')
            ->setHelp('部门的详细描述信息')
            ->hideOnIndex()
        ;

        yield AssociationField::new('parent', '上级部门')
            ->setHelp('部门的上级部门')
            ->setCrudController(self::class)
        ;

        yield AssociationField::new('children', '下级部门')
            ->setHelp('部门的下级部门列表')
            ->hideOnIndex()
            ->hideOnForm()
            ->onlyOnDetail()
        ;

        yield AssociationField::new('users', '部门用户')
            ->setHelp('属于此部门的用户列表')
            ->hideOnIndex()
            ->onlyOnDetail()
        ;

        yield TextField::new('path', '层级路径')
            ->setHelp('部门在组织架构中的完整路径')
            ->hideOnIndex()
        ;

        yield IntegerField::new('level', '层级深度')
            ->setHelp('部门在组织架构中的深度级别')
            ->setFormTypeOption('attr', ['min' => 0])
        ;

        yield IntegerField::new('orderWeight', '排序权重')
            ->setHelp('数值越小排序越靠前')
            ->hideOnIndex()
        ;

        yield ChoiceField::new('status', '部门状态')
            ->setChoices([
                '正常' => 'active',
                '停用' => 'inactive',
            ])
            ->setHelp('部门当前状态')
            ->renderAsBadges([
                'active' => 'success',
                'inactive' => 'warning',
            ])
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
            ->add(TextFilter::new('departmentId', '部门ID'))
            ->add(TextFilter::new('name', '部门名称'))
            ->add(EntityFilter::new('parent', '上级部门'))
            ->add(NumericFilter::new('level', '层级深度'))
            ->add(
                ChoiceFilter::new('status', '部门状态')
                    ->setChoices([
                        '正常' => 'active',
                        '停用' => 'inactive',
                    ])
            )
        ;
    }
}

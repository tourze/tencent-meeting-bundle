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
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TencentMeetingBundle\Entity\User;

/**
 * @extends AbstractCrudController<User>
 */
#[AdminCrud(routePath: '/tencent-meeting/user', routeName: 'tencent_meeting_user')]
final class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('用户')
            ->setEntityLabelInPlural('用户管理')
            ->setPageTitle('index', '用户管理')
            ->setPageTitle('new', '新建用户')
            ->setPageTitle('edit', '编辑用户')
            ->setPageTitle('detail', '用户详情')
            ->setDefaultSort(['id' => 'DESC'])
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

        yield TextField::new('userid', '用户ID')
            ->setHelp('企业内部用户唯一标识')
            ->setRequired(true)
        ;

        yield TextField::new('uuid', 'UUID')
            ->setHelp('系统分配的用户UUID')
            ->hideOnIndex()
        ;

        yield TextField::new('username', '用户姓名')
            ->setHelp('用户真实姓名')
            ->setRequired(true)
        ;

        yield EmailField::new('email', '邮箱地址')
            ->setHelp('用户邮箱，用于接收会议通知')
        ;

        yield TelephoneField::new('phone', '手机号码')
            ->setHelp('用户联系手机号')
        ;

        yield ChoiceField::new('userType', '用户类型')
            ->setChoices([
                '企业用户' => 'enterprise',
                '个人用户' => 'personal',
            ])
            ->setHelp('用户账号类型')
            ->renderAsBadges([
                'enterprise' => 'primary',
                'personal' => 'secondary',
            ])
        ;

        yield ChoiceField::new('status', '用户状态')
            ->setChoices([
                '正常' => 'active',
                '停用' => 'inactive',
                '禁用' => 'disabled',
            ])
            ->setHelp('用户当前状态')
            ->renderAsBadges([
                'active' => 'success',
                'inactive' => 'warning',
                'disabled' => 'danger',
            ])
        ;

        yield AssociationField::new('department', '所属部门')
            ->setHelp('用户所属的组织部门')
            ->setCrudController(DepartmentCrudController::class)
        ;

        yield AssociationField::new('config', '配置')
            ->setHelp('腾讯会议配置信息')
            ->hideOnIndex()
        ;

        yield AssociationField::new('userRoles', '用户角色')
            ->setHelp('用户拥有的角色权限')
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
            ->add(TextFilter::new('userid', '用户ID'))
            ->add(TextFilter::new('username', '用户姓名'))
            ->add(TextFilter::new('email', '邮箱'))
            ->add(
                ChoiceFilter::new('userType', '用户类型')
                    ->setChoices([
                        '企业用户' => 'enterprise',
                        '个人用户' => 'personal',
                    ])
            )
            ->add(
                ChoiceFilter::new('status', '用户状态')
                    ->setChoices([
                        '正常' => 'active',
                        '停用' => 'inactive',
                        '禁用' => 'disabled',
                    ])
            )
            ->add(EntityFilter::new('department', '所属部门'))
        ;
    }
}

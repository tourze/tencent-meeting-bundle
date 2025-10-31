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
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TencentMeetingBundle\Entity\MeetingRole;

/**
 * @extends AbstractCrudController<MeetingRole>
 */
#[AdminCrud(routePath: '/tencent-meeting/meeting-role', routeName: 'tencent_meeting_meeting_role')]
final class MeetingRoleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MeetingRole::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('会议角色关联')
            ->setEntityLabelInPlural('会议角色关联管理')
            ->setPageTitle('index', '会议角色关联列表')
            ->setPageTitle('new', '创建角色关联')
            ->setPageTitle('edit', '编辑角色关联')
            ->setPageTitle('detail', '角色关联详情')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setTimezone('Asia/Shanghai')
            ->showEntityActionsInlined()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IntegerField::new('id', 'ID')
            ->onlyOnIndex()
            ->setTextAlign('center')
        ;

        $meeting = AssociationField::new('meeting', '所属会议')
            ->setHelp('角色关联的会议')
            ->setColumns(6)
        ;

        $role = AssociationField::new('role', '角色')
            ->setHelp('关联的角色对象')
            ->setRequired(false)
            ->setColumns(6)
        ;

        $userId = TextField::new('userId', '用户ID')
            ->setHelp('被分配角色的用户标识')
            ->setRequired(false)
            ->setColumns(6)
        ;

        $assignmentTime = DateTimeField::new('assignmentTime', '分配时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setHelp('角色被分配的时间')
            ->setRequired(false)
            ->setColumns(6)
        ;

        $status = ChoiceField::new('status', '状态')
            ->setChoices([
                '活跃' => 'active',
                '已撤销' => 'revoked',
            ])
            ->setHelp('角色关联的当前状态')
            ->setColumns(6)
        ;

        $assignedBy = TextField::new('assignedBy', '分配者')
            ->setHelp('执行角色分配操作的用户')
            ->setRequired(false)
            ->setColumns(6)
        ;

        $remark = TextareaField::new('remark', '备注')
            ->setHelp('关于角色分配的备注信息')
            ->setNumOfRows(3)
            ->setColumns(12)
            ->setRequired(false)
            ->hideOnIndex()
        ;

        $config = AssociationField::new('config', '配置')
            ->setHelp('关联的腾讯会议配置')
            ->hideOnIndex()
        ;

        $createdAt = DateTimeField::new('createdAt', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->onlyOnDetail()
        ;

        $updatedAt = DateTimeField::new('updatedAt', '更新时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->onlyOnDetail()
        ;

        if (Crud::PAGE_INDEX === $pageName) {
            return [
                $id,
                $meeting,
                $role,
                $userId,
                $assignmentTime,
                $status,
                $assignedBy,
            ];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [
                $id,
                $meeting,
                $role,
                $userId,
                $assignmentTime,
                $status,
                $assignedBy,
                $remark,
                $config,
                $createdAt,
                $updatedAt,
            ];
        }

        return [
            $meeting,
            $role,
            $userId,
            $assignmentTime,
            $status,
            $assignedBy,
            $remark,
            $config,
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('meeting', '所属会议'))
            ->add(EntityFilter::new('role', '角色'))
            ->add(TextFilter::new('userId', '用户ID'))
            ->add(ChoiceFilter::new('status', '状态')->setChoices([
                '活跃' => 'active',
                '已撤销' => 'revoked',
            ]))
            ->add(DateTimeFilter::new('assignmentTime', '分配时间'))
            ->add(TextFilter::new('assignedBy', '分配者'))
            ->add(EntityFilter::new('config', '配置'))
        ;
    }
}

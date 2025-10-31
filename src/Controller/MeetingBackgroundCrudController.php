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
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TencentMeetingBundle\Entity\MeetingBackground;

/**
 * @extends AbstractCrudController<MeetingBackground>
 */
#[AdminCrud(routePath: '/tencent-meeting/meeting-background', routeName: 'tencent_meeting_meeting_background')]
final class MeetingBackgroundCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MeetingBackground::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('会议背景关联')
            ->setEntityLabelInPlural('会议背景关联管理')
            ->setPageTitle('index', '会议背景关联列表')
            ->setPageTitle('new', '创建背景关联')
            ->setPageTitle('edit', '编辑背景关联')
            ->setPageTitle('detail', '背景关联详情')
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
            ->setHelp('背景关联的会议')
            ->setColumns(6)
        ;

        $background = AssociationField::new('background', '背景')
            ->setHelp('关联的背景对象')
            ->setColumns(6)
        ;

        $applicationTime = DateTimeField::new('applicationTime', '应用时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setHelp('背景被应用的时间')
            ->setRequired(false)
            ->setColumns(6)
        ;

        $status = ChoiceField::new('status', '状态')
            ->setChoices([
                '活跃' => 'active',
                '非活跃' => 'inactive',
            ])
            ->setHelp('背景关联的当前状态')
            ->setColumns(6)
        ;

        $appliedBy = TextField::new('appliedBy', '应用者')
            ->setHelp('执行背景应用操作的用户')
            ->setRequired(false)
            ->setColumns(6)
        ;

        $customConfig = CodeEditorField::new('customConfig', '自定义配置')
            ->setLanguage('javascript')
            ->setHelp('自定义的背景配置，JSON格式')
            ->setRequired(false)
            ->hideOnIndex()
            ->formatValue(function ($value) {
                if (is_array($value)) {
                    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                }

                return $value;
            })
        ;

        $remark = TextareaField::new('remark', '备注')
            ->setHelp('关于背景应用的备注信息')
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
                $background,
                $applicationTime,
                $status,
                $appliedBy,
            ];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [
                $id,
                $meeting,
                $background,
                $applicationTime,
                $status,
                $appliedBy,
                $customConfig,
                $remark,
                $config,
                $createdAt,
                $updatedAt,
            ];
        }

        return [
            $meeting,
            $background,
            $applicationTime,
            $status,
            $appliedBy,
            $customConfig,
            $remark,
            $config,
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('meeting', '所属会议'))
            ->add(EntityFilter::new('background', '背景'))
            ->add(ChoiceFilter::new('status', '状态')->setChoices([
                '活跃' => 'active',
                '非活跃' => 'inactive',
            ]))
            ->add(DateTimeFilter::new('applicationTime', '应用时间'))
            ->add(TextFilter::new('appliedBy', '应用者'))
            ->add(EntityFilter::new('config', '配置'))
        ;
    }
}

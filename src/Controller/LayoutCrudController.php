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
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TencentMeetingBundle\Entity\Layout;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

/**
 * @extends AbstractCrudController<Layout>
 */
#[AdminCrud(routePath: '/tencent-meeting/layout', routeName: 'tencent_meeting_layout')]
final class LayoutCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Layout::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('会议布局')
            ->setEntityLabelInPlural('会议布局管理')
            ->setSearchFields(['layoutId', 'name', 'description', 'applicableScope'])
            ->setDefaultSort(['orderWeight' => 'ASC', 'id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setHelp('index', '管理腾讯会议的界面布局，包括画廊视图、演讲者视图等')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield TextField::new('layoutId', '布局ID')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('唯一的布局标识符')
        ;

        yield TextField::new('name', '布局名称')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('显示给用户的布局名称')
        ;

        yield TextareaField::new('description', '布局描述')
            ->setColumns(12)
            ->setNumOfRows(3)
            ->setHelp('对布局的详细描述')
            ->hideOnIndex()
        ;

        yield ChoiceField::new('layoutType', '布局类型')
            ->setChoices([
                '画廊视图' => 'gallery',
                '演讲者视图' => 'speaker',
                '活跃演讲者' => 'active_speaker',
                '网格视图' => 'grid',
                '焦点视图' => 'focus',
                '自定义' => 'custom',
            ])
            ->setRequired(true)
            ->setColumns(4)
            ->setHelp('选择布局的类型')
        ;

        yield ChoiceField::new('status', '状态')
            ->setChoices([
                '启用' => 'active',
                '禁用' => 'inactive',
                '已删除' => 'deleted',
            ])
            ->setRequired(true)
            ->setColumns(4)
        ;

        yield IntegerField::new('orderWeight', '排序权重')
            ->setColumns(4)
            ->setHelp('数字越小排序越靠前')
        ;

        yield BooleanField::new('isDefault', '默认布局')
            ->setColumns(3)
            ->setHelp('是否设为默认布局')
        ;

        yield BooleanField::new('isBuiltIn', '内置布局')
            ->setColumns(3)
            ->setHelp('是否为系统内置布局')
        ;

        yield IntegerField::new('maxParticipants', '最大参与者数')
            ->setColumns(6)
            ->setHelp('该布局支持的最大参与者数量')
            ->hideOnIndex()
        ;

        yield TextField::new('applicableScope', '适用场景')
            ->setColumns(12)
            ->setHelp('布局的适用场景或范围')
            ->hideOnIndex()
        ;

        yield UrlField::new('thumbnailUrl', '缩略图URL')
            ->setColumns(12)
            ->setHelp('布局的预览缩略图URL')
            ->hideOnIndex()
        ;

        yield AssociationField::new('config', '关联配置')
            ->setFormTypeOptions([
                'choice_label' => function (TencentMeetingConfig $config) {
                    return sprintf('配置 #%d - %s', $config->getId(), $config->getAppId());
                },
            ])
            ->setColumns(6)
            ->setHelp('关联的腾讯会议配置')
            ->hideOnIndex()
        ;

        // 隐藏layoutConfig字段以避免EasyAdmin处理JSON类型的问题
        // 可以通过API或其他方式单独管理此字段

        yield DateTimeField::new('createTime', '创建时间')
            ->onlyOnIndex()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->onlyOnIndex()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('layoutId', '布局ID'))
            ->add(TextFilter::new('name', '布局名称'))
            ->add(
                ChoiceFilter::new('layoutType', '布局类型')
                    ->setChoices([
                        '画廊视图' => 'gallery',
                        '演讲者视图' => 'speaker',
                        '活跃演讲者' => 'active_speaker',
                        '网格视图' => 'grid',
                        '焦点视图' => 'focus',
                        '自定义' => 'custom',
                    ])
            )
            ->add(
                ChoiceFilter::new('status', '状态')
                    ->setChoices([
                        '启用' => 'active',
                        '禁用' => 'inactive',
                        '已删除' => 'deleted',
                    ])
            )
            ->add(BooleanFilter::new('isDefault', '默认布局'))
            ->add(BooleanFilter::new('isBuiltIn', '内置布局'))
            ->add(EntityFilter::new('config', '关联配置'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}

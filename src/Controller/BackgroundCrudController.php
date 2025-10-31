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
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
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
use Tourze\TencentMeetingBundle\Entity\Background;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

/**
 * @extends AbstractCrudController<Background>
 */
#[AdminCrud(routePath: '/tencent-meeting/background', routeName: 'tencent_meeting_background')]
final class BackgroundCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Background::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('会议背景')
            ->setEntityLabelInPlural('会议背景管理')
            ->setSearchFields(['backgroundId', 'name', 'description', 'imageUrl'])
            ->setDefaultSort(['orderWeight' => 'ASC', 'id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setHelp('index', '管理腾讯会议的虚拟背景，支持图片、颜色、渐变等多种类型')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield TextField::new('backgroundId', '背景ID')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('唯一的背景标识符')
        ;

        yield TextField::new('name', '背景名称')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('显示给用户的背景名称')
        ;

        yield TextareaField::new('description', '背景描述')
            ->setColumns(12)
            ->setNumOfRows(3)
            ->setHelp('对背景的详细描述')
            ->hideOnIndex()
        ;

        yield ChoiceField::new('backgroundType', '背景类型')
            ->setChoices([
                '图片' => 'image',
                '纯色' => 'color',
                '渐变' => 'gradient',
                '模糊' => 'blur',
                '自定义' => 'custom',
            ])
            ->setRequired(true)
            ->setColumns(4)
            ->setHelp('选择背景的类型')
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

        yield UrlField::new('imageUrl', '背景图片URL')
            ->setRequired(true)
            ->setColumns(8)
            ->setHelp('背景图片的完整URL地址')
            ->hideOnIndex()
        ;

        yield UrlField::new('thumbnailUrl', '缩略图URL')
            ->setColumns(4)
            ->setHelp('背景的缩略图URL')
            ->hideOnIndex()
        ;

        yield BooleanField::new('isDefault', '默认背景')
            ->setColumns(3)
            ->setHelp('是否设为默认背景')
        ;

        yield BooleanField::new('isBuiltIn', '内置背景')
            ->setColumns(3)
            ->setHelp('是否为系统内置背景')
        ;

        yield TextField::new('applicableScope', '适用范围')
            ->setColumns(6)
            ->setHelp('背景的适用范围或场景')
            ->hideOnIndex()
        ;

        yield IntegerField::new('fileSize', '文件大小')
            ->setColumns(3)
            ->setHelp('文件大小（字节）')
            ->hideOnIndex()
        ;

        yield TextField::new('imageFormat', '图片格式')
            ->setColumns(3)
            ->setHelp('图片的格式（如jpg、png等）')
            ->hideOnIndex()
        ;

        yield IntegerField::new('imageWidth', '图片宽度')
            ->setColumns(3)
            ->setHelp('图片宽度（像素）')
            ->hideOnIndex()
        ;

        yield IntegerField::new('imageHeight', '图片高度')
            ->setColumns(3)
            ->setHelp('图片高度（像素）')
            ->hideOnIndex()
        ;

        yield ColorField::new('primaryColor', '主色调')
            ->setColumns(6)
            ->setHelp('背景的主要颜色（十六进制）')
            ->hideOnIndex()
        ;

        yield DateTimeField::new('expirationTime', '过期时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns(6)
            ->setHelp('背景的过期时间')
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
            ->add(TextFilter::new('backgroundId', '背景ID'))
            ->add(TextFilter::new('name', '背景名称'))
            ->add(
                ChoiceFilter::new('backgroundType', '背景类型')
                    ->setChoices([
                        '图片' => 'image',
                        '纯色' => 'color',
                        '渐变' => 'gradient',
                        '模糊' => 'blur',
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
            ->add(BooleanFilter::new('isDefault', '默认背景'))
            ->add(BooleanFilter::new('isBuiltIn', '内置背景'))
            ->add(EntityFilter::new('config', '关联配置'))
            ->add(DateTimeFilter::new('expirationTime', '过期时间'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}

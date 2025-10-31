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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TencentMeetingBundle\Controller\RoomCrudController;
use Tourze\TencentMeetingBundle\Entity\Device;

/**
 * @extends AbstractCrudController<Device>
 */
#[AdminCrud(routePath: '/tencent-meeting/device', routeName: 'tencent_meeting_device')]
final class DeviceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Device::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('设备')
            ->setEntityLabelInPlural('设备管理')
            ->setPageTitle('index', '设备管理')
            ->setPageTitle('new', '新建设备')
            ->setPageTitle('edit', '编辑设备')
            ->setPageTitle('detail', '设备详情')
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

        yield TextField::new('deviceId', '设备ID')
            ->setHelp('设备的唯一标识符')
            ->setRequired(true)
        ;

        yield TextField::new('name', '设备名称')
            ->setHelp('设备的显示名称')
            ->setRequired(true)
        ;

        yield ChoiceField::new('deviceType', '设备类型')
            ->setChoices([
                '摄像头' => 'camera',
                '麦克风' => 'microphone',
                '扬声器' => 'speaker',
                '显示器' => 'display',
                '触摸屏' => 'touch_screen',
                '白板' => 'whiteboard',
                '其他' => 'other',
            ])
            ->setHelp('设备的类型分类')
            ->renderAsBadges([
                'camera' => 'primary',
                'microphone' => 'success',
                'speaker' => 'info',
                'display' => 'warning',
                'touch_screen' => 'secondary',
                'whiteboard' => 'dark',
                'other' => 'light',
            ])
        ;

        yield TextField::new('brand', '设备品牌')
            ->setHelp('设备制造商品牌')
            ->hideOnIndex()
        ;

        yield TextField::new('model', '设备型号')
            ->setHelp('设备的具体型号')
            ->hideOnIndex()
        ;

        yield TextField::new('serialNumber', '序列号')
            ->setHelp('设备的序列号')
            ->hideOnIndex()
        ;

        yield ChoiceField::new('status', '设备状态')
            ->setChoices([
                '在线' => 'online',
                '离线' => 'offline',
                '维护中' => 'maintenance',
                '故障' => 'error',
            ])
            ->setHelp('设备当前状态')
            ->renderAsBadges([
                'online' => 'success',
                'offline' => 'secondary',
                'maintenance' => 'warning',
                'error' => 'danger',
            ])
        ;

        yield TextField::new('activationCode', '激活码')
            ->setHelp('设备激活使用的代码')
            ->hideOnIndex()
        ;

        yield DateTimeField::new('activationTime', '激活时间')
            ->setHelp('设备激活的时间')
            ->hideOnIndex()
        ;

        yield DateTimeField::new('expirationTime', '过期时间')
            ->setHelp('设备授权的过期时间')
            ->hideOnIndex()
        ;

        yield DateTimeField::new('lastOnlineTime', '最后在线时间')
            ->setHelp('设备最后一次在线的时间')
            ->hideOnIndex()
        ;

        yield TextField::new('ipAddress', 'IP地址')
            ->setHelp('设备的网络IP地址')
            ->hideOnIndex()
        ;

        yield TextField::new('macAddress', 'MAC地址')
            ->setHelp('设备的MAC地址')
            ->hideOnIndex()
        ;

        yield TextField::new('firmwareVersion', '固件版本')
            ->setHelp('设备当前固件版本')
            ->hideOnIndex()
        ;

        yield TextField::new('softwareVersion', '软件版本')
            ->setHelp('设备当前软件版本')
            ->hideOnIndex()
        ;

        yield TextareaField::new('remark', '设备备注')
            ->setHelp('设备的其他备注信息')
            ->hideOnIndex()
        ;

        yield AssociationField::new('room', '关联会议室')
            ->setHelp('设备所属的会议室')
            ->setCrudController(RoomCrudController::class)
        ;

        yield ArrayField::new('deviceConfig', '设备配置')
            ->setHelp('设备的详细配置信息')
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
            ->add(TextFilter::new('deviceId', '设备ID'))
            ->add(TextFilter::new('name', '设备名称'))
            ->add(
                ChoiceFilter::new('deviceType', '设备类型')
                    ->setChoices([
                        '摄像头' => 'camera',
                        '麦克风' => 'microphone',
                        '扬声器' => 'speaker',
                        '显示器' => 'display',
                        '触摸屏' => 'touch_screen',
                        '白板' => 'whiteboard',
                        '其他' => 'other',
                    ])
            )
            ->add(
                ChoiceFilter::new('status', '设备状态')
                    ->setChoices([
                        '在线' => 'online',
                        '离线' => 'offline',
                        '维护中' => 'maintenance',
                        '故障' => 'error',
                    ])
            )
            ->add(TextFilter::new('brand', '设备品牌'))
            ->add(TextFilter::new('model', '设备型号'))
            ->add(TextFilter::new('ipAddress', 'IP地址'))
            ->add(EntityFilter::new('room', '关联会议室'))
        ;
    }
}

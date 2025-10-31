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
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TencentMeetingBundle\Entity\MeetingRoom;

/**
 * @extends AbstractCrudController<MeetingRoom>
 */
#[AdminCrud(routePath: '/tencent-meeting/meeting-room', routeName: 'tencent_meeting_meeting_room')]
final class MeetingRoomCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MeetingRoom::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('会议室')
            ->setEntityLabelInPlural('会议室管理')
            ->setPageTitle('index', '会议室列表')
            ->setPageTitle('new', '创建会议室')
            ->setPageTitle('edit', '编辑会议室')
            ->setPageTitle('detail', '会议室详情')
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

        $roomId = TextField::new('roomId', '会议室ID')
            ->setHelp('会议室的唯一标识符')
            ->setColumns(6)
        ;

        $name = TextField::new('name', '会议室名称')
            ->setHelp('会议室的显示名称')
            ->setColumns(6)
        ;

        $description = TextareaField::new('description', '会议室描述')
            ->setHelp('会议室的详细描述')
            ->setNumOfRows(3)
            ->setColumns(12)
            ->setRequired(false)
            ->hideOnIndex()
        ;

        $capacity = IntegerField::new('capacity', '容量')
            ->setHelp('会议室的最大容纳人数')
            ->setColumns(6)
        ;

        $roomType = ChoiceField::new('roomType', '会议室类型')
            ->setChoices([
                '小型会议室' => 'huddle_room',
                '会议室' => 'conference_room',
                '培训室' => 'training_room',
                '礼堂' => 'auditorium',
            ])
            ->setHelp('会议室的类型分类')
            ->setColumns(6)
        ;

        $location = TextField::new('location', '位置')
            ->setHelp('会议室的物理位置')
            ->setRequired(false)
            ->setColumns(6)
        ;

        $deviceStatus = ChoiceField::new('deviceStatus', '设备状态')
            ->setChoices([
                '在线' => 'online',
                '离线' => 'offline',
                '维护中' => 'maintenance',
            ])
            ->setHelp('会议室设备的当前状态')
            ->setColumns(6)
        ;

        $status = ChoiceField::new('status', '会议室状态')
            ->setChoices([
                '可用' => 'available',
                '占用中' => 'occupied',
                '维护中' => 'maintenance',
                '已禁用' => 'disabled',
            ])
            ->setHelp('会议室的使用状态')
            ->setColumns(6)
        ;

        $equipmentList = TextField::new('equipmentList', '设备列表')
            ->setHelp('会议室内的设备清单')
            ->setRequired(false)
            ->hideOnIndex()
        ;

        $supportRecording = BooleanField::new('supportRecording', '支持录制')
            ->setHelp('会议室是否支持会议录制')
            ->renderAsSwitch(false)
        ;

        $supportLive = BooleanField::new('supportLive', '支持直播')
            ->setHelp('会议室是否支持会议直播')
            ->renderAsSwitch(false)
        ;

        $supportScreenShare = BooleanField::new('supportScreenShare', '支持屏幕共享')
            ->setHelp('会议室是否支持屏幕共享')
            ->renderAsSwitch(false)
        ;

        $config = AssociationField::new('config', '配置')
            ->setHelp('关联的腾讯会议配置')
            ->hideOnIndex()
        ;

        $devicesCount = IntegerField::new('devices', '设备数量')
            ->onlyOnIndex()
            ->formatValue(function ($value, MeetingRoom $entity): int {
                return $entity->getDevices()->count();
            })
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
                $name,
                $roomType,
                $capacity,
                $location,
                $deviceStatus,
                $status,
                $devicesCount,
                $supportRecording,
                $supportLive,
                $supportScreenShare,
            ];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [
                $id,
                $roomId,
                $name,
                $description,
                $capacity,
                $roomType,
                $location,
                $deviceStatus,
                $status,
                $equipmentList,
                $supportRecording,
                $supportLive,
                $supportScreenShare,
                $config,
                $createdAt,
                $updatedAt,
            ];
        }

        return [
            $roomId,
            $name,
            $description,
            $capacity,
            $roomType,
            $location,
            $deviceStatus,
            $status,
            $equipmentList,
            $supportRecording,
            $supportLive,
            $supportScreenShare,
            $config,
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', '会议室名称'))
            ->add(TextFilter::new('roomId', '会议室ID'))
            ->add(ChoiceFilter::new('roomType', '会议室类型')->setChoices([
                '小型会议室' => 'huddle_room',
                '会议室' => 'conference_room',
                '培训室' => 'training_room',
                '礼堂' => 'auditorium',
            ]))
            ->add(ChoiceFilter::new('deviceStatus', '设备状态')->setChoices([
                '在线' => 'online',
                '离线' => 'offline',
                '维护中' => 'maintenance',
            ]))
            ->add(ChoiceFilter::new('status', '会议室状态')->setChoices([
                '可用' => 'available',
                '占用中' => 'occupied',
                '维护中' => 'maintenance',
                '已禁用' => 'disabled',
            ]))
            ->add(NumericFilter::new('capacity', '容量'))
            ->add(TextFilter::new('location', '位置'))
            ->add(BooleanFilter::new('supportRecording', '支持录制'))
            ->add(BooleanFilter::new('supportLive', '支持直播'))
            ->add(BooleanFilter::new('supportScreenShare', '支持屏幕共享'))
            ->add(EntityFilter::new('config', '配置'))
        ;
    }
}

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
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TencentMeetingBundle\Entity\MeetingUser;

/**
 * @extends AbstractCrudController<MeetingUser>
 */
#[AdminCrud(routePath: '/tencent-meeting/meeting-user', routeName: 'tencent_meeting_meeting_user')]
final class MeetingUserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MeetingUser::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('会议参会用户')
            ->setEntityLabelInPlural('会议参会用户管理')
            ->setPageTitle('index', '会议参会用户列表')
            ->setPageTitle('new', '添加参会用户')
            ->setPageTitle('edit', '编辑参会用户')
            ->setPageTitle('detail', '参会用户详情')
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
            ->setHelp('用户参加的会议')
            ->setColumns(6)
        ;

        $user = AssociationField::new('user', '用户')
            ->setHelp('参会的用户')
            ->setColumns(6)
        ;

        $role = ChoiceField::new('role', '用户角色')
            ->setChoices([
                '主持人' => 'host',
                '联席主持人' => 'cohost',
                '参会者' => 'attendee',
                '旁听者' => 'audience',
            ])
            ->setHelp('用户在会议中的角色')
            ->setColumns(6)
        ;

        $attendeeStatus = ChoiceField::new('attendeeStatus', '参会状态')
            ->setChoices([
                '已邀请' => 'invited',
                '已入会' => 'joined',
                '已离会' => 'left',
                '缺席' => 'absent',
            ])
            ->setHelp('用户的参会状态')
            ->setColumns(6)
        ;

        $joinTime = DateTimeField::new('joinTime', '入会时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setHelp('用户加入会议的时间')
            ->setRequired(false)
            ->setColumns(6)
        ;

        $leaveTime = DateTimeField::new('leaveTime', '离会时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setHelp('用户离开会议的时间')
            ->setRequired(false)
            ->setColumns(6)
        ;

        $attendDuration = NumberField::new('attendDuration', '参会时长')
            ->setHelp('用户参会的总时长，单位为秒')
            ->formatValue(function (int $value): string {
                if ($value >= 3600) {
                    $hours = floor($value / 3600);
                    $minutes = floor(($value % 3600) / 60);
                    $seconds = $value % 60;

                    return sprintf('%d小时%d分钟%d秒', $hours, $minutes, $seconds);
                }
                if ($value >= 60) {
                    $minutes = floor($value / 60);
                    $seconds = $value % 60;

                    return sprintf('%d分钟%d秒', $minutes, $seconds);
                }

                return $value . '秒';
            })
            ->setColumns(6)
        ;

        $deviceInfo = TextField::new('deviceInfo', '设备信息')
            ->setHelp('用户使用的设备信息')
            ->setRequired(false)
            ->hideOnIndex()
        ;

        $networkType = TextField::new('networkType', '网络类型')
            ->setHelp('用户的网络连接类型')
            ->setRequired(false)
            ->hideOnIndex()
        ;

        $cameraOn = BooleanField::new('cameraOn', '摄像头状态')
            ->setHelp('用户是否开启摄像头')
            ->renderAsSwitch(false)
        ;

        $micOn = BooleanField::new('micOn', '麦克风状态')
            ->setHelp('用户是否开启麦克风')
            ->renderAsSwitch(false)
        ;

        $screenShared = BooleanField::new('screenShared', '屏幕共享状态')
            ->setHelp('用户是否正在共享屏幕')
            ->renderAsSwitch(false)
        ;

        $remark = TextareaField::new('remark', '参会备注')
            ->setHelp('关于用户参会的备注信息')
            ->setNumOfRows(3)
            ->setColumns(12)
            ->setRequired(false)
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
                $user,
                $role,
                $attendeeStatus,
                $joinTime,
                $attendDuration,
                $cameraOn,
                $micOn,
                $screenShared,
            ];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [
                $id,
                $meeting,
                $user,
                $role,
                $attendeeStatus,
                $joinTime,
                $leaveTime,
                $attendDuration,
                $deviceInfo,
                $networkType,
                $cameraOn,
                $micOn,
                $screenShared,
                $remark,
                $createdAt,
                $updatedAt,
            ];
        }

        return [
            $meeting,
            $user,
            $role,
            $attendeeStatus,
            $joinTime,
            $leaveTime,
            $attendDuration,
            $deviceInfo,
            $networkType,
            $cameraOn,
            $micOn,
            $screenShared,
            $remark,
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('meeting', '所属会议'))
            ->add(EntityFilter::new('user', '用户'))
            ->add(ChoiceFilter::new('role', '用户角色')->setChoices([
                '主持人' => 'host',
                '联席主持人' => 'cohost',
                '参会者' => 'attendee',
                '旁听者' => 'audience',
            ]))
            ->add(ChoiceFilter::new('attendeeStatus', '参会状态')->setChoices([
                '已邀请' => 'invited',
                '已入会' => 'joined',
                '已离会' => 'left',
                '缺席' => 'absent',
            ]))
            ->add(DateTimeFilter::new('joinTime', '入会时间'))
            ->add(BooleanFilter::new('cameraOn', '摄像头状态'))
            ->add(BooleanFilter::new('micOn', '麦克风状态'))
            ->add(BooleanFilter::new('screenShared', '屏幕共享状态'))
        ;
    }
}

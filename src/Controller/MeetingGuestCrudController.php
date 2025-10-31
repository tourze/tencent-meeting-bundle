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
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TencentMeetingBundle\Entity\MeetingGuest;

/**
 * @extends AbstractCrudController<MeetingGuest>
 */
#[AdminCrud(routePath: '/tencent-meeting/meeting-guest', routeName: 'tencent_meeting_meeting_guest')]
final class MeetingGuestCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MeetingGuest::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('会议嘉宾')
            ->setEntityLabelInPlural('会议嘉宾管理')
            ->setPageTitle('index', '会议嘉宾列表')
            ->setPageTitle('new', '添加会议嘉宾')
            ->setPageTitle('edit', '编辑会议嘉宾')
            ->setPageTitle('detail', '会议嘉宾详情')
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
            ->setHelp('嘉宾参加的会议')
            ->setColumns(6)
        ;

        $guestName = TextField::new('guestName', '嘉宾姓名')
            ->setHelp('嘉宾的全名')
            ->setColumns(6)
        ;

        $email = EmailField::new('email', '邮箱')
            ->setHelp('嘉宾的联系邮箱')
            ->setRequired(false)
            ->setColumns(6)
        ;

        $phone = TelephoneField::new('phone', '手机号')
            ->setHelp('嘉宾的联系电话')
            ->setRequired(false)
            ->setColumns(6)
        ;

        $company = TextField::new('company', '公司')
            ->setHelp('嘉宾所在的公司或组织')
            ->setRequired(false)
            ->setColumns(6)
        ;

        $position = TextField::new('position', '职位')
            ->setHelp('嘉宾的职位或头衔')
            ->setRequired(false)
            ->setColumns(6)
        ;

        $guestType = ChoiceField::new('guestType', '嘉宾类型')
            ->setChoices([
                '内部人员' => 'internal',
                '外部人员' => 'external',
                '重要嘉宾' => 'vip',
            ])
            ->setHelp('嘉宾的类型分类')
            ->setColumns(6)
        ;

        $inviteStatus = ChoiceField::new('inviteStatus', '邀请状态')
            ->setChoices([
                '已邀请' => 'invited',
                '已接受' => 'accepted',
                '已拒绝' => 'declined',
                '待定' => 'tentative',
            ])
            ->setHelp('嘉宾对邀请的回复状态')
            ->setColumns(6)
        ;

        $invitationTime = DateTimeField::new('invitationTime', '邀请发送时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setHelp('邀请发送的时间')
            ->setRequired(false)
            ->hideOnIndex()
        ;

        $responseTime = DateTimeField::new('responseTime', '回复时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setHelp('嘉宾回复邀请的时间')
            ->setRequired(false)
            ->hideOnIndex()
        ;

        $attendanceStatus = ChoiceField::new('attendanceStatus', '参会状态')
            ->setChoices([
                '预期参会' => 'expected',
                '已入会' => 'joined',
                '已离会' => 'left',
                '未出席' => 'no_show',
            ])
            ->setHelp('嘉宾的实际参会状态')
            ->setColumns(6)
        ;

        $joinTime = DateTimeField::new('joinTime', '入会时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setHelp('嘉宾加入会议的时间')
            ->setRequired(false)
            ->setColumns(6)
        ;

        $leaveTime = DateTimeField::new('leaveTime', '离会时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setHelp('嘉宾离开会议的时间')
            ->setRequired(false)
            ->setColumns(6)
        ;

        $attendDuration = NumberField::new('attendDuration', '参会时长')
            ->setHelp('嘉宾参会的总时长，单位为秒')
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

        $needReminder = BooleanField::new('needReminder', '需要提醒')
            ->setHelp('是否需要发送会议提醒给嘉宾')
            ->renderAsSwitch(false)
        ;

        $remark = TextareaField::new('remark', '嘉宾备注')
            ->setHelp('关于嘉宾的备注信息')
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
                $guestName,
                $email,
                $company,
                $position,
                $guestType,
                $inviteStatus,
                $attendanceStatus,
                $needReminder,
            ];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [
                $id,
                $meeting,
                $guestName,
                $email,
                $phone,
                $company,
                $position,
                $guestType,
                $inviteStatus,
                $invitationTime,
                $responseTime,
                $attendanceStatus,
                $joinTime,
                $leaveTime,
                $attendDuration,
                $needReminder,
                $remark,
                $config,
                $createdAt,
                $updatedAt,
            ];
        }

        return [
            $meeting,
            $guestName,
            $email,
            $phone,
            $company,
            $position,
            $guestType,
            $inviteStatus,
            $invitationTime,
            $responseTime,
            $attendanceStatus,
            $joinTime,
            $leaveTime,
            $attendDuration,
            $needReminder,
            $remark,
            $config,
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('meeting', '所属会议'))
            ->add(TextFilter::new('guestName', '嘉宾姓名'))
            ->add(TextFilter::new('email', '邮箱'))
            ->add(TextFilter::new('company', '公司'))
            ->add(ChoiceFilter::new('guestType', '嘉宾类型')->setChoices([
                '内部人员' => 'internal',
                '外部人员' => 'external',
                '重要嘉宾' => 'vip',
            ]))
            ->add(ChoiceFilter::new('inviteStatus', '邀请状态')->setChoices([
                '已邀请' => 'invited',
                '已接受' => 'accepted',
                '已拒绝' => 'declined',
                '待定' => 'tentative',
            ]))
            ->add(ChoiceFilter::new('attendanceStatus', '参会状态')->setChoices([
                '预期参会' => 'expected',
                '已入会' => 'joined',
                '已离会' => 'left',
                '未出席' => 'no_show',
            ]))
            ->add(DateTimeFilter::new('joinTime', '入会时间'))
            ->add(BooleanFilter::new('needReminder', '需要提醒'))
            ->add(EntityFilter::new('config', '配置'))
        ;
    }
}

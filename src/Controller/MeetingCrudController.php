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
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Enum\MeetingStatus;

/**
 * @extends AbstractCrudController<Meeting>
 */
#[AdminCrud(routePath: '/tencent-meeting/meeting', routeName: 'tencent_meeting_meeting')]
final class MeetingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Meeting::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('会议')
            ->setEntityLabelInPlural('会议管理')
            ->setPageTitle('index', '会议列表')
            ->setPageTitle('new', '创建会议')
            ->setPageTitle('edit', '编辑会议')
            ->setPageTitle('detail', '会议详情')
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

        $meetingId = TextField::new('meetingId', '会议ID')
            ->setHelp('腾讯会议系统的唯一标识')
        ;

        $meetingCode = TextField::new('meetingCode', '会议号')
            ->setHelp('用户入会的会议号码')
            ->setColumns(6)
        ;

        $subject = TextField::new('subject', '会议主题')
            ->setHelp('会议的主要议题或标题')
            ->setColumns(12)
        ;

        $startTime = DateTimeField::new('startTime', '开始时间')
            ->setFormat('yyyy-MM-dd HH:mm')
            ->setHelp('会议的计划开始时间')
            ->setColumns(6)
        ;

        $endTime = DateTimeField::new('endTime', '结束时间')
            ->setFormat('yyyy-MM-dd HH:mm')
            ->setHelp('会议的计划结束时间，可为空')
            ->setRequired(false)
            ->setColumns(6)
        ;

        $status = ChoiceField::new('status', '会议状态')
            ->setChoices([
                '已安排' => MeetingStatus::SCHEDULED,
                '进行中' => MeetingStatus::IN_PROGRESS,
                '已结束' => MeetingStatus::ENDED,
                '已取消' => MeetingStatus::CANCELLED,
            ])
            ->setHelp('会议当前的状态')
            ->renderExpanded(false)
            ->setColumns(6)
        ;

        $duration = IntegerField::new('duration', '时长（分钟）')
            ->setHelp('会议预计时长，单位为分钟')
            ->setColumns(6)
        ;

        $userId = TextField::new('userId', '主持人用户ID')
            ->setHelp('会议主持人的用户标识')
            ->setColumns(6)
        ;

        $timezone = TextField::new('timezone', '时区')
            ->setHelp('会议所在的时区设置')
            ->setColumns(6)
        ;

        $meetingUrl = UrlField::new('meetingUrl', '会议链接')
            ->setHelp('用户可以通过此链接直接加入会议')
            ->hideOnIndex()
            ->setRequired(false)
        ;

        $password = TextField::new('password', '会议密码')
            ->setHelp('入会时需要的密码，为空表示无密码')
            ->hideOnIndex()
            ->setRequired(false)
        ;

        $reminderSent = BooleanField::new('reminderSent', '已发送提醒')
            ->setHelp('是否已向参会者发送会议提醒')
            ->renderAsSwitch(false)
        ;

        $config = AssociationField::new('config', '配置')
            ->setHelp('关联的腾讯会议配置')
            ->hideOnIndex()
        ;

        $attendeesCount = IntegerField::new('attendees', '参会人数')
            ->onlyOnIndex()
            ->formatValue(function ($value, Meeting $entity): int {
                return $entity->getAttendees()->count();
            })
        ;

        $documentsCount = IntegerField::new('documents', '文档数量')
            ->onlyOnIndex()
            ->formatValue(function ($value, Meeting $entity): int {
                return $entity->getDocuments()->count();
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
                $meetingId,
                $subject,
                $meetingCode,
                $startTime,
                $status,
                $attendeesCount,
                $documentsCount,
                $reminderSent,
            ];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [
                $id,
                $meetingId,
                $meetingCode,
                $subject,
                $startTime,
                $endTime,
                $status,
                $duration,
                $userId,
                $timezone,
                $meetingUrl,
                $password,
                $reminderSent,
                $config,
                $createdAt,
                $updatedAt,
            ];
        }

        return [
            $meetingId,
            $meetingCode,
            $subject,
            $startTime,
            $endTime,
            $status,
            $duration,
            $userId,
            $timezone,
            $meetingUrl,
            $password,
            $reminderSent,
            $config,
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('subject', '会议主题'))
            ->add(TextFilter::new('meetingCode', '会议号'))
            ->add(ChoiceFilter::new('status', '会议状态')->setChoices([
                '已安排' => MeetingStatus::SCHEDULED->value,
                '进行中' => MeetingStatus::IN_PROGRESS->value,
                '已结束' => MeetingStatus::ENDED->value,
                '已取消' => MeetingStatus::CANCELLED->value,
            ]))
            ->add(DateTimeFilter::new('startTime', '开始时间'))
            ->add(TextFilter::new('userId', '主持人用户ID'))
            ->add(EntityFilter::new('config', '配置'))
        ;
    }
}

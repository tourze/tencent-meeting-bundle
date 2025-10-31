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
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TencentMeetingBundle\Entity\MeetingVote;

/**
 * @extends AbstractCrudController<MeetingVote>
 */
#[AdminCrud(routePath: '/tencent-meeting/meeting-vote', routeName: 'tencent_meeting_meeting_vote')]
final class MeetingVoteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MeetingVote::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('会议投票')
            ->setEntityLabelInPlural('会议投票管理')
            ->setPageTitle('index', '会议投票列表')
            ->setPageTitle('new', '创建会议投票')
            ->setPageTitle('edit', '编辑会议投票')
            ->setPageTitle('detail', '会议投票详情')
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
            ->setHelp('投票关联的会议')
            ->setColumns(6)
        ;

        $subject = TextField::new('subject', '投票主题')
            ->setHelp('投票的标题或主要问题')
            ->setColumns(6)
        ;

        $description = TextareaField::new('description', '投票描述')
            ->setHelp('投票的详细描述和说明')
            ->setNumOfRows(3)
            ->setColumns(12)
            ->setRequired(false)
            ->hideOnIndex()
        ;

        $voteType = ChoiceField::new('voteType', '投票类型')
            ->setChoices([
                '单选投票' => 'single_choice',
                '多选投票' => 'multiple_choice',
                '是否投票' => 'yes_no',
            ])
            ->setHelp('投票的形式类型')
            ->setColumns(6)
        ;

        $status = ChoiceField::new('status', '投票状态')
            ->setChoices([
                '草稿' => 'draft',
                '进行中' => 'active',
                '已结束' => 'closed',
                '已取消' => 'cancelled',
            ])
            ->setHelp('投票的当前状态')
            ->setColumns(6)
        ;

        $anonymous = BooleanField::new('anonymous', '匿名投票')
            ->setHelp('是否为匿名投票，不显示投票人信息')
            ->renderAsSwitch(false)
        ;

        $showResult = BooleanField::new('showResult', '显示结果')
            ->setHelp('是否向参与者显示投票结果')
            ->renderAsSwitch(false)
        ;

        $startTime = DateTimeField::new('startTime', '开始时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setHelp('投票开始的时间')
            ->setRequired(false)
            ->setColumns(6)
        ;

        $endTime = DateTimeField::new('endTime', '结束时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setHelp('投票结束的时间')
            ->setRequired(false)
            ->setColumns(6)
        ;

        $totalVotes = IntegerField::new('totalVotes', '总投票数')
            ->setHelp('已收到的投票总数')
            ->setColumns(6)
        ;

        $options = CodeEditorField::new('options', '投票选项')
            ->setLanguage('javascript')
            ->setHelp('投票的选项列表，JSON格式')
            ->setRequired(false)
            ->hideOnIndex()
            ->formatValue(function ($value) {
                if (is_array($value)) {
                    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                }

                return $value;
            })
        ;

        $results = CodeEditorField::new('results', '投票结果')
            ->setLanguage('javascript')
            ->setHelp('投票的统计结果，JSON格式')
            ->setRequired(false)
            ->hideOnIndex()
            ->formatValue(function ($value) {
                if (is_array($value)) {
                    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                }

                return $value;
            })
        ;

        $creatorUserId = TextField::new('creatorUserId', '创建者用户ID')
            ->setHelp('创建此投票的用户标识')
            ->setRequired(false)
            ->setColumns(6)
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
                $subject,
                $voteType,
                $status,
                $totalVotes,
                $anonymous,
                $showResult,
                $startTime,
                $endTime,
            ];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [
                $id,
                $meeting,
                $subject,
                $description,
                $voteType,
                $status,
                $anonymous,
                $showResult,
                $startTime,
                $endTime,
                $totalVotes,
                $options,
                $results,
                $creatorUserId,
                $config,
                $createdAt,
                $updatedAt,
            ];
        }

        return [
            $meeting,
            $subject,
            $description,
            $voteType,
            $status,
            $anonymous,
            $showResult,
            $startTime,
            $endTime,
            $totalVotes,
            $options,
            $results,
            $creatorUserId,
            $config,
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('meeting', '所属会议'))
            ->add(TextFilter::new('subject', '投票主题'))
            ->add(ChoiceFilter::new('voteType', '投票类型')->setChoices([
                '单选投票' => 'single_choice',
                '多选投票' => 'multiple_choice',
                '是否投票' => 'yes_no',
            ]))
            ->add(ChoiceFilter::new('status', '投票状态')->setChoices([
                '草稿' => 'draft',
                '进行中' => 'active',
                '已结束' => 'closed',
                '已取消' => 'cancelled',
            ]))
            ->add(BooleanFilter::new('anonymous', '匿名投票'))
            ->add(BooleanFilter::new('showResult', '显示结果'))
            ->add(DateTimeFilter::new('startTime', '开始时间'))
            ->add(DateTimeFilter::new('endTime', '结束时间'))
            ->add(NumericFilter::new('totalVotes', '总投票数'))
            ->add(TextFilter::new('creatorUserId', '创建者用户ID'))
            ->add(EntityFilter::new('config', '配置'))
        ;
    }
}

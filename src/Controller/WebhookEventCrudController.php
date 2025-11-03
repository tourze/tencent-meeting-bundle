<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
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
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\HttpFoundation\Response;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Entity\WebhookEvent;

/**
 * @extends AbstractCrudController<WebhookEvent>
 */
#[AdminCrud(routePath: '/tencent-meeting/webhook-event', routeName: 'tencent_meeting_webhook_event')]
final class WebhookEventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return WebhookEvent::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Webhook事件')
            ->setEntityLabelInPlural('Webhook事件管理')
            ->setSearchFields(['eventId', 'eventType', 'meetingId', 'userId', 'sourceIp'])
            ->setDefaultSort(['eventTime' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setHelp('index', '管理腾讯会议的Webhook事件，包括事件处理状态和重试机制')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $retryAction = Action::new('retry', '重试处理')
            ->linkToCrudAction('retryProcessing')
            ->setIcon('fa fa-refresh')
            ->setCssClass('btn btn-info')
            ->displayIf(function (WebhookEvent $event): bool {
                return in_array($event->getProcessStatus(), ['failed', 'pending'], true);
            })
        ;

        $viewPayloadAction = Action::new('viewPayload', '查看载荷')
            ->linkToCrudAction('viewPayload')
            ->setIcon('fa fa-code')
            ->setCssClass('btn btn-secondary')
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $retryAction)
            ->add(Crud::PAGE_INDEX, $viewPayloadAction)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield TextField::new('eventId', '事件ID')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('唯一的事件标识符')
        ;

        yield TextField::new('eventType', '事件类型')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('Webhook事件的类型')
        ;

        yield ChoiceField::new('processStatus', '处理状态')
            ->setChoices([
                '待处理' => 'pending',
                '处理中' => 'processing',
                '成功' => 'success',
                '失败' => 'failed',
            ])
            ->setRequired(true)
            ->setColumns(4)
            ->setHelp('事件的处理状态')
        ;

        yield IntegerField::new('retryCount', '重试次数')
            ->setColumns(4)
            ->setHelp('事件处理的重试次数')
        ;

        yield BooleanField::new('signatureVerified', '签名验证')
            ->setColumns(4)
            ->setHelp('Webhook签名是否已验证')
        ;

        yield TextField::new('meetingId', '会议ID')
            ->setColumns(6)
            ->setHelp('关联的会议ID')
            ->hideOnIndex()
        ;

        yield TextField::new('userId', '用户ID')
            ->setColumns(6)
            ->setHelp('触发事件的用户ID')
            ->hideOnIndex()
        ;

        yield DateTimeField::new('eventTime', '事件时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns(6)
            ->setRequired(true)
            ->setHelp('事件发生的时间')
        ;

        yield DateTimeField::new('processingTime', '处理时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns(6)
            ->setHelp('事件处理的时间')
            ->hideOnIndex()
        ;

        yield DateTimeField::new('nextRetryTime', '下次重试时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns(12)
            ->setHelp('下次重试处理的时间')
            ->hideOnIndex()
        ;

        yield TextField::new('sourceIp', '来源IP')
            ->setColumns(6)
            ->setHelp('发送Webhook的源IP地址')
            ->hideOnIndex()
        ;

        yield TextField::new('userAgent', '用户代理')
            ->setColumns(6)
            ->setHelp('发送请求的用户代理字符串')
            ->hideOnIndex()
        ;

        yield TextField::new('errorMessage', '错误信息')
            ->setColumns(12)
            ->setHelp('处理失败时的错误信息')
            ->hideOnIndex()
        ;

        if (Crud::PAGE_DETAIL === $pageName) {
            yield TextareaField::new('payload', '原始载荷')
                ->setTemplateName('crud/field/textarea')
                ->setHelp('Webhook的原始JSON载荷')
                ->addCssClass('font-monospace')
            ;

            yield TextareaField::new('processResult', '处理结果')
                ->setTemplateName('crud/field/textarea')
                ->setHelp('事件处理的详细结果')
                ->addCssClass('font-monospace')
            ;
        } elseif (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield TextareaField::new('payload', '原始载荷')
                ->setColumns(12)
                ->setNumOfRows(10)
                ->setHelp('Webhook的原始JSON载荷')
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => '{"event": "meeting.started", "data": {...}}',
                        'class' => 'font-monospace',
                    ],
                ])
            ;

            yield TextareaField::new('processResult', '处理结果')
                ->setColumns(12)
                ->setNumOfRows(5)
                ->setHelp('事件处理的详细结果（JSON格式）')
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => '{"status": "success", "message": "处理完成"}',
                        'class' => 'font-monospace',
                    ],
                ])
            ;
        }

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
            ->add(TextFilter::new('eventId', '事件ID'))
            ->add(TextFilter::new('eventType', '事件类型'))
            ->add(
                ChoiceFilter::new('processStatus', '处理状态')
                    ->setChoices([
                        '待处理' => 'pending',
                        '处理中' => 'processing',
                        '成功' => 'success',
                        '失败' => 'failed',
                    ])
            )
            ->add(BooleanFilter::new('signatureVerified', '签名验证'))
            ->add(TextFilter::new('meetingId', '会议ID'))
            ->add(TextFilter::new('userId', '用户ID'))
            ->add(TextFilter::new('sourceIp', '来源IP'))
            ->add(NumericFilter::new('retryCount', '重试次数'))
            ->add(DateTimeFilter::new('eventTime', '事件时间'))
            ->add(DateTimeFilter::new('processingTime', '处理时间'))
            ->add(DateTimeFilter::new('nextRetryTime', '下次重试时间'))
            ->add(EntityFilter::new('config', '关联配置'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }

    /**
     * 重试处理事件的自定义操作
     */
    #[AdminAction(routeName: 'tencent_meeting_webhook_event_retry', routePath: '/tencent-meeting/webhook-event/{entityId}/retry')]
    public function retryProcessing(): Response
    {
        $context = $this->getContext();
        if (null === $context) {
            $this->addFlash('danger', '无效的上下文');

            return $this->redirectToRoute('admin');
        }
        $entity = $context->getEntity()->getInstance();

        if (!$entity instanceof WebhookEvent) {
            $this->addFlash('danger', '无效的Webhook事件');

            return $this->redirectToRoute('admin');
        }

        if (!in_array($entity->getProcessStatus(), ['failed', 'pending'], true)) {
            $this->addFlash('warning', '只有失败或待处理的事件才能重试');

            return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->generateUrl('admin'));
        }

        try {
            // 重置处理状态
            $entity->setProcessStatus('pending');
            $entity->setNextRetryTime(new \DateTimeImmutable('+5 minutes'));
            $entity->setRetryCount($entity->getRetryCount() + 1);
            $entity->setErrorMessage(null);

            // 保存更改
            $doctrine = $this->container->get('doctrine');
            assert($doctrine instanceof ManagerRegistry);
            $entityManager = $doctrine->getManager();
            assert($entityManager instanceof EntityManagerInterface);
            $entityManager->flush();

            $this->addFlash('success', sprintf(
                '事件 %s 已重新加入处理队列，这是第 %d 次重试',
                $entity->getEventId(),
                $entity->getRetryCount()
            ));
        } catch (\Exception $e) {
            $this->addFlash('danger', '重试操作失败：' . $e->getMessage());
        }

        return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->generateUrl('admin'));
    }

    /**
     * 查看载荷的自定义操作
     */
    #[AdminAction(routeName: 'tencent_meeting_webhook_event_view_payload', routePath: '/tencent-meeting/webhook-event/{entityId}/view-payload')]
    public function viewPayload(): Response
    {
        $context = $this->getContext();
        if (null === $context) {
            $this->addFlash('danger', '无效的上下文');

            return $this->redirectToRoute('admin');
        }
        $entity = $context->getEntity()->getInstance();

        if (!$entity instanceof WebhookEvent) {
            $this->addFlash('danger', '无效的Webhook事件');

            return $this->redirectToRoute('admin');
        }

        // 返回JSON响应
        $data = [
            'event' => [
                'id' => $entity->getId(),
                'eventId' => $entity->getEventId(),
                'eventType' => $entity->getEventType(),
                'processStatus' => $entity->getProcessStatus(),
            ],
            'payload' => json_decode($entity->getPayload(), true),
            'processResult' => null !== $entity->getProcessResult() ? json_decode($entity->getProcessResult(), true) : null,
        ];

        return new Response(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200, [
            'Content-Type' => 'application/json; charset=utf-8',
        ]);
    }

    /**
     * 创建实体时的默认值设置
     */
    public function createEntity(string $entityFqcn): WebhookEvent
    {
        $event = new $entityFqcn();
        $event->setEventTime(new \DateTimeImmutable());
        $event->setProcessStatus('pending');
        $event->setRetryCount(0);
        $event->setSignatureVerified(false);

        return $event;
    }
}

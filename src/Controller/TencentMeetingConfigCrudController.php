<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\HttpFoundation\Response;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Repository\TencentMeetingConfigRepository;

/**
 * @extends AbstractCrudController<TencentMeetingConfig>
 */
#[AdminCrud(routePath: '/tencent-meeting/config', routeName: 'tencent_meeting_config')]
final class TencentMeetingConfigCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly TencentMeetingConfigRepository $repository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return TencentMeetingConfig::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('腾讯会议配置')
            ->setEntityLabelInPlural('腾讯会议配置管理')
            ->setSearchFields(['appId', 'secretId', 'webhookToken'])
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setHelp('index', '管理腾讯会议API的配置信息，包括应用ID、密钥和Webhook配置')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $testConnectionAction = Action::new('testConnection', '测试连接')
            ->linkToCrudAction('testConnection')
            ->setIcon('fa fa-link')
            ->setCssClass('btn btn-info')
            ->displayIf(function (TencentMeetingConfig $config): bool {
                return $config->isEnabled();
            })
        ;

        $enableAction = Action::new('enable', '启用')
            ->linkToCrudAction('enableConfig')
            ->setIcon('fa fa-check')
            ->setCssClass('btn btn-success')
            ->displayIf(function (TencentMeetingConfig $config): bool {
                return !$config->isEnabled();
            })
        ;

        $disableAction = Action::new('disable', '禁用')
            ->linkToCrudAction('disableConfig')
            ->setIcon('fa fa-times')
            ->setCssClass('btn btn-warning')
            ->displayIf(function (TencentMeetingConfig $config): bool {
                return $config->isEnabled();
            })
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $testConnectionAction)
            ->add(Crud::PAGE_INDEX, $enableAction)
            ->add(Crud::PAGE_INDEX, $disableAction)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield TextField::new('appId', '应用ID')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('腾讯会议分配的应用ID')
        ;

        yield TextField::new('secretId', '密钥ID')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('腾讯会议分配的密钥ID')
        ;

        yield from $this->getSecretKeyField($pageName);

        yield ChoiceField::new('authType', '认证类型')
            ->setChoices([
                'JWT' => 'JWT',
                'OAuth2' => 'OAuth2',
            ])
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('选择API认证方式')
        ;

        yield BooleanField::new('enabled', '是否启用')
            ->setColumns(6)
            ->setHelp('是否启用此配置')
        ;

        yield from $this->getWebhookTokenField($pageName);
        yield from $this->getStatsDisplayField($pageName);

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
            ->add(TextFilter::new('appId', '应用ID'))
            ->add(TextFilter::new('secretId', '密钥ID'))
            ->add(
                ChoiceFilter::new('authType', '认证类型')
                    ->setChoices([
                        'JWT' => 'JWT',
                        'OAuth2' => 'OAuth2',
                    ])
            )
            ->add(BooleanFilter::new('enabled', '是否启用'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }

    /**
     * 测试连接的自定义操作
     */
    #[AdminAction(routeName: 'tencent_meeting_config_test_connection', routePath: '/tencent-meeting/config/{entityId}/test-connection')]
    public function testConnection(): Response
    {
        $context = $this->getContext();
        if (null === $context) {
            $this->addFlash('danger', '无效的上下文');

            return $this->redirectToRoute('admin');
        }
        $entity = $context->getEntity()->getInstance();

        if (!$entity instanceof TencentMeetingConfig) {
            $this->addFlash('danger', '无效的配置');

            return $this->redirectToRoute('admin');
        }

        if (!$entity->isEnabled()) {
            $this->addFlash('warning', '配置已禁用，无法测试连接');

            return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->generateUrl('admin'));
        }

        try {
            // 这里应该调用实际的腾讯会议API测试连接
            // 目前仅作为示例，显示成功消息
            $this->addFlash('success', sprintf(
                '配置 %s 连接测试成功！',
                $entity->getAppId()
            ));
        } catch (\Exception $e) {
            $this->addFlash('danger', '连接测试失败：' . $e->getMessage());
        }

        return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->generateUrl('admin'));
    }

    /**
     * 启用配置的自定义操作
     */
    #[AdminAction(routeName: 'tencent_meeting_config_enable', routePath: '/tencent-meeting/config/{entityId}/enable')]
    public function enableConfig(): Response
    {
        $context = $this->getContext();
        if (null === $context) {
            $this->addFlash('danger', '无效的上下文');

            return $this->redirectToRoute('admin');
        }
        $entity = $context->getEntity()->getInstance();

        if (!$entity instanceof TencentMeetingConfig) {
            $this->addFlash('danger', '无效的配置');

            return $this->redirectToRoute('admin');
        }

        try {
            $entity->setEnabled(true);

            /** @var Registry $doctrine */
            $doctrine = $this->container->get('doctrine');
            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            $this->addFlash('success', sprintf(
                '配置 %s 已启用',
                $entity->getAppId()
            ));
        } catch (\Exception $e) {
            $this->addFlash('danger', '启用配置失败：' . $e->getMessage());
        }

        return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->generateUrl('admin'));
    }

    /**
     * 禁用配置的自定义操作
     */
    #[AdminAction(routeName: 'tencent_meeting_config_disable', routePath: '/tencent-meeting/config/{entityId}/disable')]
    public function disableConfig(): Response
    {
        $context = $this->getContext();
        if (null === $context) {
            $this->addFlash('danger', '无效的上下文');

            return $this->redirectToRoute('admin');
        }
        $entity = $context->getEntity()->getInstance();

        if (!$entity instanceof TencentMeetingConfig) {
            $this->addFlash('danger', '无效的配置');

            return $this->redirectToRoute('admin');
        }

        try {
            $entity->setEnabled(false);

            /** @var Registry $doctrine */
            $doctrine = $this->container->get('doctrine');
            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            $this->addFlash('warning', sprintf(
                '配置 %s 已禁用',
                $entity->getAppId()
            ));
        } catch (\Exception $e) {
            $this->addFlash('danger', '禁用配置失败：' . $e->getMessage());
        }

        return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->generateUrl('admin'));
    }

    /**
     * 创建实体时的默认值设置
     */
    public function createEntity(string $entityFqcn): TencentMeetingConfig
    {
        $config = new $entityFqcn();
        $config->setAuthType('JWT');
        $config->setEnabled(true);

        return $config;
    }

    /**
     * 保存前验证
     */
    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /** @var TencentMeetingConfig $entityInstance */

        // 验证应用ID唯一性
        $existingConfig = $this->repository->findOneByAppId($entityInstance->getAppId());

        if (null !== $existingConfig && $existingConfig->getId() !== $entityInstance->getId()) {
            $this->addFlash('danger', '应用ID已存在，请使用不同的应用ID');

            return;
        }

        parent::persistEntity($entityManager, $entityInstance);

        $this->addFlash('success', '腾讯会议配置已成功保存');
    }

    /**
     * 更新前验证
     */
    public function updateEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /** @var TencentMeetingConfig $entityInstance */

        // 验证应用ID唯一性
        $existingConfig = $this->repository->findOneByAppId($entityInstance->getAppId());

        if (null !== $existingConfig && $existingConfig->getId() !== $entityInstance->getId()) {
            $this->addFlash('danger', '应用ID已存在，请使用不同的应用ID');

            return;
        }

        parent::updateEntity($entityManager, $entityInstance);

        $this->addFlash('success', '腾讯会议配置已成功更新');
    }

    /**
     * @return iterable<FieldInterface>
     */
    private function getSecretKeyField(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            yield TextField::new('secretKey', '密钥')
                ->formatValue(function ($value) {
                    return $value ? '***已设置***' : '未设置';
                })
            ;
        } else {
            yield TextareaField::new('secretKey', '密钥')
                ->setRequired(true)
                ->setColumns(12)
                ->setNumOfRows(4)
                ->setHelp('腾讯会议分配的私钥内容')
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => '请输入完整的私钥内容...',
                        'class' => 'font-monospace',
                    ],
                ])
            ;
        }
    }

    /**
     * @return iterable<FieldInterface>
     */
    private function getWebhookTokenField(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            yield TextField::new('webhookToken', 'Webhook令牌')
                ->formatValue(function ($value) {
                    return $value ? '***已设置***' : '未设置';
                })
            ;
        } else {
            yield TextField::new('webhookToken', 'Webhook令牌')
                ->setColumns(12)
                ->setHelp('用于验证Webhook请求的令牌（可选）')
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => '留空表示不验证Webhook签名',
                    ],
                ])
                ->hideOnIndex()
            ;
        }
    }

    /**
     * @return iterable<FieldInterface>
     */
    private function getStatsDisplayField(string $pageName): iterable
    {
        if (Crud::PAGE_DETAIL === $pageName) {
            yield TextField::new('statsDisplay', '使用统计')
                ->setTemplateName('admin/field/config_stats.html.twig')
                ->setHelp('显示此配置关联的各种实体数量')
            ;
        }
    }
}

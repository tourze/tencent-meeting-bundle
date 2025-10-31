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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\HttpFoundation\Response;
use Tourze\TencentMeetingBundle\Entity\Role;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;
use Tourze\TencentMeetingBundle\Entity\User;
use Tourze\TencentMeetingBundle\Entity\UserRole;

/**
 * @extends AbstractCrudController<UserRole>
 */
#[AdminCrud(routePath: '/tencent-meeting/user-role', routeName: 'tencent_meeting_user_role')]
final class UserRoleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserRole::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('用户角色')
            ->setEntityLabelInPlural('用户角色管理')
            ->setSearchFields(['assignedBy', 'remark'])
            ->setDefaultSort(['assignmentTime' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setHelp('index', '管理用户在腾讯会议系统中的角色分配和权限')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $revokeAction = Action::new('revoke', '撤销角色')
            ->linkToCrudAction('revoke')
            ->setIcon('fa fa-ban')
            ->setCssClass('btn btn-warning')
            ->displayIf(function (UserRole $userRole): bool {
                return 'active' === $userRole->getStatus();
            })
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $revokeAction)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield AssociationField::new('user', '用户')
            ->setFormTypeOptions([
                'choice_label' => function (?User $user) {
                    if (null === $user) {
                        return '无';
                    }

                    return sprintf(
                        '[%s] %s (%s)',
                        $user->getUserid(),
                        $user->getUsername(),
                        $user->getEmail() ?? 'N/A'
                    );
                },
                'placeholder' => '请选择用户',
            ])
            ->setColumns(6)
            ->setHelp('选择要分配角色的用户')
            ->setRequired(false)
        ;

        yield AssociationField::new('role', '角色')
            ->setFormTypeOptions([
                'choice_label' => function (?Role $role) {
                    if (null === $role) {
                        return '无';
                    }

                    return sprintf('[%s] %s', $role->getRoleId(), $role->getName());
                },
                'placeholder' => '请选择角色',
            ])
            ->setColumns(6)
            ->setHelp('选择要分配的角色')
            ->setRequired(false)
        ;

        yield ChoiceField::new('status', '状态')
            ->setChoices([
                '有效' => 'active',
                '已过期' => 'expired',
                '已撤销' => 'revoked',
            ])
            ->setRequired(true)
            ->setColumns(4)
            ->setHelp('角色分配的当前状态')
        ;

        yield TextField::new('assignedBy', '分配者')
            ->setColumns(8)
            ->setHelp('分配此角色的管理员或系统')
            ->hideOnIndex()
        ;

        yield DateTimeField::new('assignmentTime', '分配时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns(6)
            ->setHelp('角色分配的时间')
        ;

        yield DateTimeField::new('expirationTime', '过期时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns(6)
            ->setHelp('角色的过期时间，留空表示永不过期')
        ;

        yield TextareaField::new('remark', '备注')
            ->setColumns(12)
            ->setNumOfRows(3)
            ->setHelp('关于此角色分配的备注信息')
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
            ->add(EntityFilter::new('user', '用户'))
            ->add(EntityFilter::new('role', '角色'))
            ->add(
                ChoiceFilter::new('status', '状态')
                    ->setChoices([
                        '有效' => 'active',
                        '已过期' => 'expired',
                        '已撤销' => 'revoked',
                    ])
            )
            ->add(TextFilter::new('assignedBy', '分配者'))
            ->add(DateTimeFilter::new('assignmentTime', '分配时间'))
            ->add(DateTimeFilter::new('expirationTime', '过期时间'))
            ->add(EntityFilter::new('config', '关联配置'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }

    /**
     * 撤销角色的自定义操作
     */
    #[AdminAction(routeName: 'tencent_meeting_user_role_revoke', routePath: '/tencent-meeting/user-role/{entityId}/revoke')]
    public function revoke(): Response
    {
        $context = $this->getContext();
        if (null === $context) {
            $this->addFlash('danger', '无效的上下文');

            return $this->redirectToRoute('admin');
        }
        $entity = $context->getEntity()->getInstance();

        if (!$entity instanceof UserRole) {
            $this->addFlash('danger', '无效的用户角色');

            return $this->redirectToRoute('admin');
        }

        if ('active' !== $entity->getStatus()) {
            $this->addFlash('warning', '该角色已经不是激活状态');

            return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->generateUrl('admin'));
        }

        try {
            // 更新状态为撤销
            $entity->setStatus('revoked');

            // 保存更改
            $doctrine = $this->container->get('doctrine');
            assert($doctrine instanceof ManagerRegistry);
            $entityManager = $doctrine->getManager();
            assert($entityManager instanceof EntityManagerInterface);
            $entityManager->flush();

            $this->addFlash('success', sprintf(
                '已成功撤销用户 %s 的角色 %s',
                $entity->getUser()?->getUsername() ?? 'N/A',
                $entity->getRole()?->getName() ?? 'N/A'
            ));
        } catch (\Exception $e) {
            $this->addFlash('danger', '撤销角色失败：' . $e->getMessage());
        }

        return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->generateUrl('admin'));
    }

    /**
     * 创建实体时的默认值设置
     */
    public function createEntity(string $entityFqcn): UserRole
    {
        $userRole = new $entityFqcn();
        $userRole->setAssignmentTime(new \DateTimeImmutable());
        $userRole->setStatus('active');

        return $userRole;
    }
}

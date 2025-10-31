<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

/**
 * TencentMeetingConfigRepository
 *
 * 提供腾讯会议配置实体的数据访问操作
 *
 * @extends ServiceEntityRepository<TencentMeetingConfig>
 */
#[AsRepository(entityClass: TencentMeetingConfig::class)]
class TencentMeetingConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TencentMeetingConfig::class);
    }

    public function save(TencentMeetingConfig $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TencentMeetingConfig $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 根据应用ID查找配置
     *
     * @param string $appId 应用ID
     * @return TencentMeetingConfig|null
     */
    public function findOneByAppId(string $appId): ?TencentMeetingConfig
    {
        return $this->findOneBy(['appId' => $appId]);
    }

    /**
     * 查找所有启用的配置
     *
     * @return TencentMeetingConfig[]
     */
    public function findEnabledConfigs(): array
    {
        return $this->findBy(['enabled' => true]);
    }
}

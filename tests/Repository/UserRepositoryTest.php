<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TencentMeetingBundle\Entity\User;
use Tourze\TencentMeetingBundle\Repository\UserRepository;

/**
 * @internal
 */
#[CoversClass(UserRepository::class)]
#[RunTestsInSeparateProcesses]
final class UserRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // Repository 测试基类不需要特殊的初始化
    }

    protected function createNewEntity(): object
    {
        $user = new User();
        $user->setUserid('test-user-' . uniqid());
        $user->setUsername('test-username-' . uniqid());
        $user->setEmail('test-' . uniqid() . '@example.com');
        $user->setPhone('1380000' . mt_rand(1000, 9999));
        $user->setUserType('normal');
        $user->setStatus('active');
        $user->setCreateTime(new \DateTimeImmutable());

        return $user;
    }

    /**
     * @return ServiceEntityRepository<User>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(UserRepository::class);
    }
}

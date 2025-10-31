<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TencentMeetingBundle\Entity\Room;
use Tourze\TencentMeetingBundle\Repository\RoomRepository;

/**
 * @internal
 */
#[CoversClass(RoomRepository::class)]
#[RunTestsInSeparateProcesses]
final class RoomRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // Repository 测试基类不需要特殊的初始化
    }

    protected function createNewEntity(): object
    {
        $room = new Room();
        $room->setRoomId('test-room-' . uniqid());
        $room->setName('Test Room ' . uniqid());
        $room->setCapacity(50);
        $room->setLocation('Test Location');
        $room->setDescription('Test Room Description');
        $room->setStatus('active');
        $room->setCreateTime(new \DateTimeImmutable());

        return $room;
    }

    /**
     * @return ServiceEntityRepository<Room>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(RoomRepository::class);
    }
}

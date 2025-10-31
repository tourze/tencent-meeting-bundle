<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CreateMissingTablesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 这个类仅用于确保数据库表结构存在
        // 不需要插入任何数据，仅用于触发表创建

        $manager->flush();
    }
}

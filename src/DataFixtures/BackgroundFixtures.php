<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\Background;

class BackgroundFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 创建测试用的背景数据
        for ($i = 1; $i <= 3; ++$i) {
            $background = new Background();
            $background->setBackgroundId('test-bg-' . $i);
            $background->setName('Test Background ' . $i);
            $background->setImageUrl('https://test-media.local/bg' . $i . '.jpg');
            $background->setBackgroundType('image');
            $background->setStatus('active');
            $background->setDefault(1 === $i);
            $background->setApplicableScope('all');
            $background->setOrderWeight($i * 10);
            $background->setBuiltIn(true);

            $manager->persist($background);
        }

        $manager->flush();
    }
}

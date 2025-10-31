<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\Layout;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

class LayoutFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 使用已存在的配置实体
        /** @var TencentMeetingConfig $config */
        $config = $this->getReference(TencentMeetingConfigFixtures::CONFIG_REFERENCE_1, TencentMeetingConfig::class);

        // 创建测试用的布局数据
        $layoutTypes = ['gallery', 'speaker', 'active_speaker', 'grid', 'focus'];

        for ($i = 1; $i <= 3; ++$i) {
            $layout = new Layout();

            $layout->setLayoutId('layout_' . $i);
            $layout->setName('测试布局 ' . $i);
            $layout->setDescription('这是第 ' . $i . ' 个测试布局');
            $layout->setLayoutType($layoutTypes[($i - 1) % count($layoutTypes)]);
            $layout->setStatus('active');
            $layout->setDefault(1 === $i); // 第一个设为默认
            $layout->setMaxParticipants(25 + ($i * 5));
            $layout->setLayoutConfig([
                'theme' => 'default',
                'show_participant_names' => true,
                'auto_switch_speaker' => $i > 1,
            ]);
            $layout->setThumbnailUrl('https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=300&fit=crop');
            $layout->setOrderWeight($i);
            $layout->setBuiltIn($i <= 2);
            $layout->setApplicableScope('all');
            $layout->setConfigEntity($config);

            $manager->persist($layout);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TencentMeetingConfigFixtures::class,
        ];
    }
}

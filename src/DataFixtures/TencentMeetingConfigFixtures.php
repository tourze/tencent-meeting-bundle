<?php

namespace Tourze\TencentMeetingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

class TencentMeetingConfigFixtures extends Fixture
{
    public const CONFIG_REFERENCE_1 = 'tencent-meeting-config-1';
    public const CONFIG_REFERENCE_2 = 'tencent-meeting-config-2';
    public const CONFIG_REFERENCE_3 = 'tencent-meeting-config-3';

    public function load(ObjectManager $manager): void
    {
        $configs = [
            [
                'appId' => 'test_app_id_001',
                'secretId' => 'test_secret_id_001',
                'secretKey' => 'test_secret_key_001',
                'authType' => 'JWT',
                'webhookToken' => 'webhook_token_001',
                'enabled' => true,
            ],
            [
                'appId' => 'test_app_id_002',
                'secretId' => 'test_secret_id_002',
                'secretKey' => 'test_secret_key_002',
                'authType' => 'OAuth2',
                'webhookToken' => 'webhook_token_002',
                'enabled' => false,
            ],
            [
                'appId' => 'test_app_id_003',
                'secretId' => 'test_secret_id_003',
                'secretKey' => 'test_secret_key_003',
                'authType' => 'JWT',
                'webhookToken' => 'webhook_token_003',
                'enabled' => true,
            ],
        ];

        foreach ($configs as $index => $configData) {
            $config = new TencentMeetingConfig();
            $config->setAppId($configData['appId']);
            $config->setSecretId($configData['secretId']);
            $config->setSecretKey($configData['secretKey']);
            $config->setAuthType($configData['authType']);
            $config->setWebhookToken($configData['webhookToken']);
            $config->setEnabled($configData['enabled']);

            // 添加引用以供其他fixtures使用
            $this->addReference(match ($index + 1) {
                1 => self::CONFIG_REFERENCE_1,
                2 => self::CONFIG_REFERENCE_2,
                default => self::CONFIG_REFERENCE_3,
            }, $config);
            $manager->persist($config);
        }

        $manager->flush();
    }
}

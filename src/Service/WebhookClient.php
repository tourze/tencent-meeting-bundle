<?php

namespace Tourze\TencentMeetingBundle\Service;

use Monolog\Attribute\WithMonologChannel;
use Tourze\TencentMeetingBundle\Exception\ApiException;
use Tourze\TencentMeetingBundle\Exception\AuthenticationException;
use Tourze\TencentMeetingBundle\Trait\WebhookCrudOperationsTrait;
use Tourze\TencentMeetingBundle\Trait\WebhookHelperTrait;
use Tourze\TencentMeetingBundle\Trait\WebhookManagementTrait;
use Tourze\TencentMeetingBundle\Trait\WebhookQueryTrait;
use Tourze\TencentMeetingBundle\Trait\WebhookValidatorTrait;

/**
 * Webhook客户端
 *
 * 提供Webhook管理的完整API封装，包括创建、查询、更新、删除等操作
 */
#[WithMonologChannel(channel: 'tencent_meeting')]
final class WebhookClient extends BaseClient
{
    use WebhookValidatorTrait;
    use WebhookCrudOperationsTrait;
    use WebhookManagementTrait;
    use WebhookQueryTrait;
    use WebhookHelperTrait;
}

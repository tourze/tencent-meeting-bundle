<?php

namespace Tourze\TencentMeetingBundle\Service;

use Monolog\Attribute\WithMonologChannel;
use Tourze\TencentMeetingBundle\Exception\ApiException;
use Tourze\TencentMeetingBundle\Exception\AuthenticationException;
use Tourze\TencentMeetingBundle\Trait\UserCrudOperationsTrait;
use Tourze\TencentMeetingBundle\Trait\UserHelperTrait;
use Tourze\TencentMeetingBundle\Trait\UserManagementTrait;
use Tourze\TencentMeetingBundle\Trait\UserRelatedDataTrait;
use Tourze\TencentMeetingBundle\Trait\UserValidatorTrait;

/**
 * 用户客户端
 *
 * 提供用户管理的完整API封装，包括创建、查询、更新、删除等操作
 */
#[WithMonologChannel(channel: 'tencent_meeting')]
final class UserClient extends BaseClient
{
    use UserValidatorTrait;
    use UserCrudOperationsTrait;
    use UserRelatedDataTrait;
    use UserManagementTrait;
    use UserHelperTrait;
}

<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Service;

use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouteCollection;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;
use Tourze\TencentMeetingBundle\Controller\BackgroundCrudController;
use Tourze\TencentMeetingBundle\Controller\DepartmentCrudController;
use Tourze\TencentMeetingBundle\Controller\DeviceCrudController;
use Tourze\TencentMeetingBundle\Controller\LayoutCrudController;
use Tourze\TencentMeetingBundle\Controller\MeetingBackgroundCrudController;
use Tourze\TencentMeetingBundle\Controller\MeetingCrudController;
use Tourze\TencentMeetingBundle\Controller\MeetingDocumentCrudController;
use Tourze\TencentMeetingBundle\Controller\MeetingGuestCrudController;
use Tourze\TencentMeetingBundle\Controller\MeetingLayoutCrudController;
use Tourze\TencentMeetingBundle\Controller\MeetingRoleCrudController;
use Tourze\TencentMeetingBundle\Controller\MeetingRoomCrudController;
use Tourze\TencentMeetingBundle\Controller\MeetingUserCrudController;
use Tourze\TencentMeetingBundle\Controller\MeetingVoteCrudController;
use Tourze\TencentMeetingBundle\Controller\PermissionCrudController;
use Tourze\TencentMeetingBundle\Controller\RecordingCrudController;
use Tourze\TencentMeetingBundle\Controller\RoleCrudController;
use Tourze\TencentMeetingBundle\Controller\RoomCrudController;
use Tourze\TencentMeetingBundle\Controller\TencentMeetingConfigCrudController;
use Tourze\TencentMeetingBundle\Controller\UserCrudController;
use Tourze\TencentMeetingBundle\Controller\UserRoleCrudController;
use Tourze\TencentMeetingBundle\Controller\WebhookEventCrudController;

// Temporarily disabled to avoid route conflicts with EasyAdmin auto-discovery
// #[AutoconfigureTag(name: 'routing.loader')]
final class AttributeControllerLoader extends Loader implements RoutingAutoLoaderInterface
{
    private AttributeRouteControllerLoader $controllerLoader;

    public function __construct()
    {
        parent::__construct();
        $this->controllerLoader = new AttributeRouteControllerLoader();
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        return $this->autoload();
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return false;
    }

    public function autoload(): RouteCollection
    {
        $collection = new RouteCollection();
        $collection->addCollection($this->controllerLoader->load(BackgroundCrudController::class));
        $collection->addCollection($this->controllerLoader->load(DepartmentCrudController::class));
        $collection->addCollection($this->controllerLoader->load(DeviceCrudController::class));
        $collection->addCollection($this->controllerLoader->load(LayoutCrudController::class));
        $collection->addCollection($this->controllerLoader->load(MeetingBackgroundCrudController::class));
        $collection->addCollection($this->controllerLoader->load(MeetingCrudController::class));
        $collection->addCollection($this->controllerLoader->load(MeetingDocumentCrudController::class));
        $collection->addCollection($this->controllerLoader->load(MeetingGuestCrudController::class));
        $collection->addCollection($this->controllerLoader->load(MeetingLayoutCrudController::class));
        $collection->addCollection($this->controllerLoader->load(MeetingRoleCrudController::class));
        $collection->addCollection($this->controllerLoader->load(MeetingRoomCrudController::class));
        $collection->addCollection($this->controllerLoader->load(MeetingUserCrudController::class));
        $collection->addCollection($this->controllerLoader->load(MeetingVoteCrudController::class));
        $collection->addCollection($this->controllerLoader->load(PermissionCrudController::class));
        $collection->addCollection($this->controllerLoader->load(RecordingCrudController::class));
        $collection->addCollection($this->controllerLoader->load(RoleCrudController::class));
        $collection->addCollection($this->controllerLoader->load(RoomCrudController::class));
        $collection->addCollection($this->controllerLoader->load(TencentMeetingConfigCrudController::class));
        $collection->addCollection($this->controllerLoader->load(UserCrudController::class));
        $collection->addCollection($this->controllerLoader->load(UserRoleCrudController::class));
        $collection->addCollection($this->controllerLoader->load(WebhookEventCrudController::class));

        return $collection;
    }
}

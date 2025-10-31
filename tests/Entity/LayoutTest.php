<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Layout;
use Tourze\TencentMeetingBundle\Entity\MeetingLayout;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

/**
 * @internal
 */
#[CoversClass(Layout::class)]
final class LayoutTest extends AbstractEntityTestCase
{
    protected function createEntity(): Layout
    {
        return new Layout();
    }

    public function testLayoutCreation(): void
    {
        $layout = new Layout();

        $this->assertInstanceOf(Layout::class, $layout);
        $this->assertSame(0, $layout->getId()); // New entities have ID = 0 before persistence
        $this->assertEquals('gallery', $layout->getLayoutType());
        $this->assertEquals('active', $layout->getStatus());
        $this->assertFalse($layout->isDefault());
        $this->assertEquals(25, $layout->getMaxParticipants());
        $this->assertNull($layout->getLayoutConfig());
        $this->assertNull($layout->getThumbnailUrl());
        $this->assertEquals(0, $layout->getOrderWeight());
        $this->assertFalse($layout->isBuiltIn());
        $this->assertNull($layout->getApplicableScope());
        $this->assertNull($layout->getDescription());
        // Initially, times should be null as they are set automatically by the system
        $this->assertNull($layout->getCreateTime());
        $this->assertNull($layout->getUpdateTime());

        // 测试集合初始化
        $this->assertInstanceOf(Collection::class, $layout->getMeetingLayouts());
        $this->assertCount(0, $layout->getMeetingLayouts());
    }

    public function testLayoutSettersAndGetters(): void
    {
        $layout = new Layout();
        $config = new TencentMeetingConfig();

        $createTime = new \DateTimeImmutable('2024-05-01 09:00:00');
        $updateTime = new \DateTimeImmutable('2024-05-01 09:30:00');
        $layoutConfig = ['grid_size' => 4, 'show_names' => true, 'video_quality' => 'hd'];

        $layout->setLayoutId('layout_123');
        $layout->setName('测试布局');
        $layout->setDescription('这是一个测试布局');
        $layout->setLayoutType('grid');
        $layout->setStatus('inactive');
        $layout->setDefault(true);
        $layout->setMaxParticipants(50);
        $layout->setLayoutConfig($layoutConfig);
        $layout->setThumbnailUrl('https://example.com/thumbnail.jpg');
        $layout->setOrderWeight(10);
        $layout->setBuiltIn(true);
        $layout->setApplicableScope('large_meetings');
        $layout->setCreateTime($createTime);
        $layout->setUpdateTime($updateTime);
        $layout->setConfigEntity($config);

        $this->assertEquals('layout_123', $layout->getLayoutId());
        $this->assertEquals('测试布局', $layout->getName());
        $this->assertEquals('这是一个测试布局', $layout->getDescription());
        $this->assertEquals('grid', $layout->getLayoutType());
        $this->assertEquals('inactive', $layout->getStatus());
        $this->assertTrue($layout->isDefault());
        $this->assertEquals(50, $layout->getMaxParticipants());
        $this->assertEquals($layoutConfig, $layout->getLayoutConfig());
        $this->assertEquals('https://example.com/thumbnail.jpg', $layout->getThumbnailUrl());
        $this->assertEquals(10, $layout->getOrderWeight());
        $this->assertTrue($layout->isBuiltIn());
        $this->assertEquals('large_meetings', $layout->getApplicableScope());
        $this->assertEquals($createTime, $layout->getCreateTime());
        $this->assertEquals($updateTime, $layout->getUpdateTime());
        $this->assertSame($config, $layout->getConfigEntity());
    }

    public function testLayoutToString(): void
    {
        $layout = new Layout();
        $layout->setName('测试布局');

        // 使用反射设置ID，因为它通常由ORM设置
        $reflection = new \ReflectionClass($layout);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($layout, 123);

        $this->assertEquals('Layout[id=123, name=测试布局]', (string) $layout);
    }

    public function testLayoutImplementsStringable(): void
    {
        $layout = new Layout();
        $layout->setLayoutId('layout_123');
        $layout->setName('Test Layout');

        $this->assertInstanceOf(\Stringable::class, $layout);
        // 强制转换总是返回string，无需断言
        $stringValue = (string) $layout;
        $this->assertEquals('Layout[id=new, name=Test Layout]', $stringValue);
    }

    public function testLayoutTypeChoices(): void
    {
        $layout = new Layout();

        $validTypes = ['gallery', 'speaker', 'active_speaker', 'grid', 'focus', 'custom'];

        foreach ($validTypes as $type) {
            $layout->setLayoutType($type);
            $this->assertEquals($type, $layout->getLayoutType());
        }
    }

    public function testLayoutStatusChoices(): void
    {
        $layout = new Layout();

        $validStatuses = ['active', 'inactive', 'deleted'];

        foreach ($validStatuses as $status) {
            $layout->setStatus($status);
            $this->assertEquals($status, $layout->getStatus());
        }
    }

    public function testLayoutMeetingLayoutMethods(): void
    {
        $layout = new Layout();
        $meetingLayout = new MeetingLayout();

        $layout->addMeetingLayout($meetingLayout);
        $this->assertCount(1, $layout->getMeetingLayouts());
        $this->assertTrue($layout->getMeetingLayouts()->contains($meetingLayout));
        $this->assertSame($layout, $meetingLayout->getLayout());

        $layout->removeMeetingLayout($meetingLayout);
        $this->assertCount(0, $layout->getMeetingLayouts());
        $this->assertFalse($layout->getMeetingLayouts()->contains($meetingLayout));

        // 测试添加重复项不会增加集合大小
        $layout->addMeetingLayout($meetingLayout);
        $layout->addMeetingLayout($meetingLayout);
        $this->assertCount(1, $layout->getMeetingLayouts());
    }

    public function testLayoutConfigHandling(): void
    {
        $layout = new Layout();

        // 测试空配置
        $layout->setLayoutConfig(null);
        $this->assertNull($layout->getLayoutConfig());

        // 测试复杂配置
        $complexConfig = [
            'grid_size' => 9,
            'show_names' => true,
            'video_quality' => 'hd',
            'audio_settings' => [
                'noise_suppression' => true,
                'echo_cancellation' => true,
            ],
            'ui_settings' => [
                'toolbar_position' => 'bottom',
                'participant_list_visible' => false,
            ],
        ];

        $layout->setLayoutConfig($complexConfig);
        $this->assertEquals($complexConfig, $layout->getLayoutConfig());
    }

    public function testLayoutMaxParticipants(): void
    {
        $layout = new Layout();

        $participantCounts = [1, 5, 25, 50, 100, 500];

        foreach ($participantCounts as $count) {
            $layout->setMaxParticipants($count);
            $this->assertEquals($count, $layout->getMaxParticipants());
        }
    }

    public function testLayoutApplicableScopes(): void
    {
        $layout = new Layout();

        $scopes = ['all_meetings', 'small_meetings', 'large_meetings', 'webinars', 'training_sessions'];

        foreach ($scopes as $scope) {
            $layout->setApplicableScope($scope);
            $this->assertEquals($scope, $layout->getApplicableScope());
        }

        // 测试设置为null
        $layout->setApplicableScope(null);
        $this->assertNull($layout->getApplicableScope());
    }

    public function testLayoutOrderWeight(): void
    {
        $layout = new Layout();

        $weights = [0, 5, 10, -5, 100];

        foreach ($weights as $weight) {
            $layout->setOrderWeight($weight);
            $this->assertEquals($weight, $layout->getOrderWeight());
        }
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'layoutId' => ['layoutId', 'layout_123'],
            'name' => ['name', 'Test Layout'],
            'description' => ['description', 'Test layout description'],
            'layoutType' => ['layoutType', 'gallery'],
            'status' => ['status', 'active'],
            'default' => ['default', false],
            'maxParticipants' => ['maxParticipants', 25],
            'layoutConfig' => ['layoutConfig', ['grid_size' => 4, 'show_names' => true]],
            'thumbnailUrl' => ['thumbnailUrl', 'https://example.com/thumbnail.jpg'],
            'orderWeight' => ['orderWeight', 0],
            'builtIn' => ['builtIn', false],
            'applicableScope' => ['applicableScope', 'all_meetings'],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
            'updateTime' => ['updateTime', new \DateTimeImmutable('2024-01-01 09:30:00')],
        ];
    }
}

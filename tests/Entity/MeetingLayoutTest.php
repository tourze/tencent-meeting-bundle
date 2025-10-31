<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Layout;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\MeetingLayout;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

/**
 * @internal
 */
#[CoversClass(MeetingLayout::class)]
final class MeetingLayoutTest extends AbstractEntityTestCase
{
    protected function createEntity(): MeetingLayout
    {
        return new MeetingLayout();
    }

    public function testMeetingLayoutCreation(): void
    {
        $meetingLayout = new MeetingLayout();
        $this->assertInstanceOf(MeetingLayout::class, $meetingLayout);
    }

    public function testMeetingLayoutSettersAndGetters(): void
    {
        $meetingLayout = new MeetingLayout();

        // Test status
        $meetingLayout->setStatus('inactive');
        $this->assertEquals('inactive', $meetingLayout->getStatus());

        // Test appliedBy
        $meetingLayout->setAppliedBy('test_user');
        $this->assertEquals('test_user', $meetingLayout->getAppliedBy());

        // Test customConfig
        $config = ['theme' => 'dark', 'layout' => 'grid'];
        $meetingLayout->setCustomConfig($config);
        $this->assertEquals($config, $meetingLayout->getCustomConfig());

        // Test remark
        $meetingLayout->setRemark('Test remark');
        $this->assertEquals('Test remark', $meetingLayout->getRemark());
    }

    public function testMeetingLayoutRelations(): void
    {
        $meetingLayout = new MeetingLayout();

        // Create mock objects for relations
        $meeting = $this->createMock(Meeting::class);
        $layout = $this->createMock(Layout::class);
        $config = $this->createMock(TencentMeetingConfig::class);

        // Test meeting relation
        $meetingLayout->setMeeting($meeting);
        $this->assertSame($meeting, $meetingLayout->getMeeting());

        // Test layout relation
        $meetingLayout->setLayout($layout);
        $this->assertSame($layout, $meetingLayout->getLayout());

        // Test config relation
        $meetingLayout->setConfig($config);
        $this->assertSame($config, $meetingLayout->getConfig());
    }

    public function testMeetingLayoutTimeMethods(): void
    {
        $meetingLayout = new MeetingLayout();

        // Test createTime is set on creation
        // Initially, times should be null as they are set automatically by the system
        $this->assertNull($meetingLayout->getCreateTime());

        // Test updateTime
        $updateTime = new \DateTimeImmutable();
        $meetingLayout->setUpdateTime($updateTime);
        $this->assertEquals($updateTime, $meetingLayout->getUpdateTime());

        // Test applicationTime
        $applicationTime = new \DateTimeImmutable();
        $meetingLayout->setApplicationTime($applicationTime);
        $this->assertEquals($applicationTime, $meetingLayout->getApplicationTime());
    }

    public function testMeetingLayoutToString(): void
    {
        $meetingLayout = new MeetingLayout();

        // Test toString when layout is not set
        $string = (string) $meetingLayout;
        $this->assertStringContainsString('MeetingLayout', $string);
        $this->assertStringContainsString('unknown', $string);
    }

    public function testMeetingLayoutStatusChoices(): void
    {
        $meetingLayout = new MeetingLayout();

        // Test valid status choices
        $meetingLayout->setStatus('active');
        $this->assertEquals('active', $meetingLayout->getStatus());

        $meetingLayout->setStatus('inactive');
        $this->assertEquals('inactive', $meetingLayout->getStatus());
    }

    public function testMeetingLayoutDefaultStatus(): void
    {
        $meetingLayout = new MeetingLayout();
        $this->assertEquals('active', $meetingLayout->getStatus());
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'status' => ['status', 'active'],
            'appliedBy' => ['appliedBy', 'user_123'],
            'customConfig' => ['customConfig', ['theme' => 'dark', 'layout' => 'grid']],
            'remark' => ['remark', 'Test meeting layout remark'],
            'applicationTime' => ['applicationTime', new \DateTimeImmutable('2024-01-01 10:00:00')],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
            'updateTime' => ['updateTime', new \DateTimeImmutable('2024-01-01 09:30:00')],
        ];
    }
}

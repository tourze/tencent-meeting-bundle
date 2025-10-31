<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TencentMeetingBundle\Entity\Background;
use Tourze\TencentMeetingBundle\Entity\Meeting;
use Tourze\TencentMeetingBundle\Entity\MeetingBackground;
use Tourze\TencentMeetingBundle\Entity\TencentMeetingConfig;

/**
 * @internal
 */
#[CoversClass(MeetingBackground::class)]
final class MeetingBackgroundTest extends AbstractEntityTestCase
{
    protected function createEntity(): MeetingBackground
    {
        return new MeetingBackground();
    }

    public function testMeetingBackgroundCreation(): void
    {
        $meetingBackground = new MeetingBackground();

        $this->assertInstanceOf(MeetingBackground::class, $meetingBackground);
        $this->assertSame(0, $meetingBackground->getId()); // New entities have ID = 0 before persistence
        $this->assertEquals('active', $meetingBackground->getStatus());
        $this->assertNull($meetingBackground->getApplicationTime());
        $this->assertNull($meetingBackground->getAppliedBy());
        $this->assertNull($meetingBackground->getCustomConfig());
        $this->assertNull($meetingBackground->getRemark());
        // Initially, times should be null as they are set automatically by the system
        $this->assertNull($meetingBackground->getCreateTime());
        $this->assertNull($meetingBackground->getUpdateTime());
    }

    public function testMeetingBackgroundSettersAndGetters(): void
    {
        $meetingBackground = new MeetingBackground();
        $meeting = new Meeting();
        $background = new Background();
        $config = new TencentMeetingConfig();

        $applicationTime = new \DateTimeImmutable('2024-06-01 10:00:00');
        $createTime = new \DateTimeImmutable('2024-05-01 09:00:00');
        $updateTime = new \DateTimeImmutable('2024-05-01 09:30:00');
        $customConfig = ['opacity' => 0.8, 'blur' => true, 'brightness' => 1.2];

        $meetingBackground->setMeeting($meeting);
        $meetingBackground->setBackground($background);
        $meetingBackground->setApplicationTime($applicationTime);
        $meetingBackground->setStatus('inactive');
        $meetingBackground->setAppliedBy('user_123');
        $meetingBackground->setCustomConfig($customConfig);
        $meetingBackground->setRemark('测试背景应用');
        $meetingBackground->setCreateTime($createTime);
        $meetingBackground->setUpdateTime($updateTime);
        $meetingBackground->setConfig($config);

        $this->assertSame($meeting, $meetingBackground->getMeeting());
        $this->assertSame($background, $meetingBackground->getBackground());
        $this->assertEquals($applicationTime, $meetingBackground->getApplicationTime());
        $this->assertEquals('inactive', $meetingBackground->getStatus());
        $this->assertEquals('user_123', $meetingBackground->getAppliedBy());
        $this->assertEquals($customConfig, $meetingBackground->getCustomConfig());
        $this->assertEquals('测试背景应用', $meetingBackground->getRemark());
        $this->assertEquals($createTime, $meetingBackground->getCreateTime());
        $this->assertEquals($updateTime, $meetingBackground->getUpdateTime());
        $this->assertSame($config, $meetingBackground->getConfig());
    }

    public function testMeetingBackgroundToString(): void
    {
        $meetingBackground = new MeetingBackground();
        $background = new Background();
        $background->setName('测试背景');
        $meetingBackground->setBackground($background);

        // 使用反射设置ID，因为它通常由ORM设置
        $reflection = new \ReflectionClass($meetingBackground);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($meetingBackground, 123);

        $this->assertEquals('MeetingBackground[id=123, background=测试背景]', (string) $meetingBackground);
    }

    public function testMeetingBackgroundImplementsStringable(): void
    {
        $meetingBackground = new MeetingBackground();
        $background = new Background();
        $background->setName('Test Background');
        $meetingBackground->setBackground($background);

        $this->assertInstanceOf(\Stringable::class, $meetingBackground);
        // 强制转换总是返回string，验证具体内容更有意义
        $stringValue = (string) $meetingBackground;
        $this->assertNotEmpty($stringValue);
    }

    public function testMeetingBackgroundStatusChoices(): void
    {
        $meetingBackground = new MeetingBackground();

        $validStatuses = ['active', 'inactive'];

        foreach ($validStatuses as $status) {
            $meetingBackground->setStatus($status);
            $this->assertEquals($status, $meetingBackground->getStatus());
        }
    }

    public function testMeetingBackgroundCustomConfig(): void
    {
        $meetingBackground = new MeetingBackground();

        // 测试空配置
        $meetingBackground->setCustomConfig(null);
        $this->assertNull($meetingBackground->getCustomConfig());

        // 测试简单配置
        $simpleConfig = ['opacity' => 0.5];
        $meetingBackground->setCustomConfig($simpleConfig);
        $this->assertEquals($simpleConfig, $meetingBackground->getCustomConfig());

        // 测试复杂配置
        $complexConfig = [
            'opacity' => 0.8,
            'blur' => true,
            'brightness' => 1.2,
            'contrast' => 1.1,
            'filters' => [
                'sepia' => 0.2,
                'grayscale' => 0,
            ],
            'position' => [
                'x' => 0,
                'y' => 0,
                'scale' => 1.0,
            ],
        ];

        $meetingBackground->setCustomConfig($complexConfig);
        $this->assertEquals($complexConfig, $meetingBackground->getCustomConfig());
    }

    public function testMeetingBackgroundApplicationTime(): void
    {
        $meetingBackground = new MeetingBackground();

        // 测试初始值为null
        $this->assertNull($meetingBackground->getApplicationTime());

        // 测试设置和获取应用时间
        $applicationTime = new \DateTimeImmutable('2024-06-01 14:30:00');
        $meetingBackground->setApplicationTime($applicationTime);
        $this->assertEquals($applicationTime, $meetingBackground->getApplicationTime());

        // 测试设置为null
        $meetingBackground->setApplicationTime(null);
        $this->assertNull($meetingBackground->getApplicationTime());
    }

    public function testMeetingBackgroundAppliedBy(): void
    {
        $meetingBackground = new MeetingBackground();

        // 测试初始值为null
        $this->assertNull($meetingBackground->getAppliedBy());

        // 测试设置不同类型的应用者
        $appliedByValues = ['admin_user', 'host_123', 'system', 'user_456'];

        foreach ($appliedByValues as $appliedBy) {
            $meetingBackground->setAppliedBy($appliedBy);
            $this->assertEquals($appliedBy, $meetingBackground->getAppliedBy());
        }

        // 测试设置为null
        $meetingBackground->setAppliedBy(null);
        $this->assertNull($meetingBackground->getAppliedBy());
    }

    public function testMeetingBackgroundRemark(): void
    {
        $meetingBackground = new MeetingBackground();

        // 测试初始值为null
        $this->assertNull($meetingBackground->getRemark());

        // 测试设置简单备注
        $meetingBackground->setRemark('简单备注');
        $this->assertEquals('简单备注', $meetingBackground->getRemark());

        // 测试设置长备注
        $longRemark = '这是一个很长的备注信息，用于测试MeetingBackground实体的备注字段功能，包含中文字符和标点符号。';
        $meetingBackground->setRemark($longRemark);
        $this->assertEquals($longRemark, $meetingBackground->getRemark());

        // 测试设置为null
        $meetingBackground->setRemark(null);
        $this->assertNull($meetingBackground->getRemark());
    }

    public function testMeetingBackgroundRelations(): void
    {
        $meetingBackground = new MeetingBackground();
        $meeting = new Meeting();
        $background = new Background();
        $config = new TencentMeetingConfig();

        // 测试设置和获取会议关联
        $meetingBackground->setMeeting($meeting);
        $this->assertSame($meeting, $meetingBackground->getMeeting());

        // 测试设置和获取背景关联
        $meetingBackground->setBackground($background);
        $this->assertSame($background, $meetingBackground->getBackground());

        // 测试设置和获取配置关联
        $meetingBackground->setConfig($config);
        $this->assertSame($config, $meetingBackground->getConfig());
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'applicationTime' => ['applicationTime', new \DateTimeImmutable('2024-01-01 10:00:00')],
            'status' => ['status', 'active'],
            'appliedBy' => ['appliedBy', 'user_123'],
            'customConfig' => ['customConfig', ['opacity' => 0.8, 'blur' => true]],
            'remark' => ['remark', 'Test background remark'],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
            'updateTime' => ['updateTime', new \DateTimeImmutable('2024-01-01 09:30:00')],
        ];
    }
}

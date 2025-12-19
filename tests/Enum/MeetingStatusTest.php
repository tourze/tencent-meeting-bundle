<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use Tourze\TencentMeetingBundle\Enum\MeetingStatus;

/**
 * @internal
 */
#[CoversClass(MeetingStatus::class)]
final class MeetingStatusTest extends AbstractEnumTestCase
{
    #[TestWith([MeetingStatus::SCHEDULED, 'scheduled', '已安排'])]
    #[TestWith([MeetingStatus::IN_PROGRESS, 'in_progress', '进行中'])]
    #[TestWith([MeetingStatus::ENDED, 'ended', '已结束'])]
    #[TestWith([MeetingStatus::CANCELLED, 'cancelled', '已取消'])]
    public function testValueAndLabel(MeetingStatus $case, string $expectedValue, string $expectedLabel): void
    {
        $this->assertSame($expectedValue, $case->value);
        $this->assertSame($expectedLabel, $case->label());
        $this->assertSame($expectedLabel, $case->getLabel());
    }

    public function testCases(): void
    {
        $cases = MeetingStatus::cases();

        $this->assertCount(4, $cases);
        // Verify all case names are present
        $caseNames = array_map(fn ($case) => $case->name, $cases);
        $this->assertContains('SCHEDULED', $caseNames);
        $this->assertContains('IN_PROGRESS', $caseNames);
        $this->assertContains('ENDED', $caseNames);
        $this->assertContains('CANCELLED', $caseNames);
    }

    #[TestWith(['scheduled', MeetingStatus::SCHEDULED])]
    #[TestWith(['in_progress', MeetingStatus::IN_PROGRESS])]
    #[TestWith(['ended', MeetingStatus::ENDED])]
    #[TestWith(['cancelled', MeetingStatus::CANCELLED])]
    public function testFromValue(string $value, MeetingStatus $expected): void
    {
        $this->assertSame($expected, MeetingStatus::from($value));
    }

    public function testFromValueThrowsExceptionForInvalidValue(): void
    {
        $this->expectException(\ValueError::class);
        MeetingStatus::from('INVALID');
    }

    #[TestWith(['scheduled', MeetingStatus::SCHEDULED])]
    #[TestWith(['in_progress', MeetingStatus::IN_PROGRESS])]
    #[TestWith(['ended', MeetingStatus::ENDED])]
    #[TestWith(['cancelled', MeetingStatus::CANCELLED])]
    public function testTryFromValueValid(string $value, MeetingStatus $expected): void
    {
        $this->assertSame($expected, MeetingStatus::tryFrom($value));
    }

    #[TestWith(['INVALID'])]
    #[TestWith(['invalid'])]
    #[TestWith([''])]
    public function testTryFromValueInvalid(string $value): void
    {
        $this->assertNull(MeetingStatus::tryFrom($value));
    }

    public function testValueUniqueness(): void
    {
        $values = array_map(fn (MeetingStatus $case) => $case->value, MeetingStatus::cases());
        $this->assertSame($values, array_unique($values));
    }

    public function testLabelUniqueness(): void
    {
        $labels = array_map(fn (MeetingStatus $case) => $case->label(), MeetingStatus::cases());
        $this->assertSame($labels, array_unique($labels));
    }

    #[TestWith([MeetingStatus::SCHEDULED, 'scheduled', '已安排'])]
    #[TestWith([MeetingStatus::IN_PROGRESS, 'in_progress', '进行中'])]
    #[TestWith([MeetingStatus::ENDED, 'ended', '已结束'])]
    #[TestWith([MeetingStatus::CANCELLED, 'cancelled', '已取消'])]
    public function testToArray(MeetingStatus $case, string $expectedValue, string $expectedLabel): void
    {
        $array = $case->toArray();
        $this->assertArrayHasKey('value', $array);
        $this->assertArrayHasKey('label', $array);
        $this->assertSame($expectedValue, $array['value']);
        $this->assertSame($expectedLabel, $array['label']);
    }
}

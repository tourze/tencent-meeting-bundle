<?php

declare(strict_types=1);

namespace Tourze\TencentMeetingBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum MeetingStatus: string implements Itemable, Labelable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case SCHEDULED = 'scheduled';
    case IN_PROGRESS = 'in_progress';
    case ENDED = 'ended';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::SCHEDULED => '已安排',
            self::IN_PROGRESS => '进行中',
            self::ENDED => '已结束',
            self::CANCELLED => '已取消',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }
}

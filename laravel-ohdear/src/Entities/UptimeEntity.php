<?php

namespace abenevaut\Ohdear\Entities;

use abenevaut\Ohdear\Contracts\UptimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\DataTransferObject\DataTransferObject;

class UptimeEntity extends DataTransferObject implements UptimeInterface
{
    use HasFactory;

    public string $datetime;

    public string $uptime_percentage;

    public function getUptimePercentage(): int
    {
        return $this->uptime_percentage;
    }

    public function getDatetime(): string
    {
        return $this->datetime;
    }

    protected static function newFactory(): UptimeEntityFactory
    {
        return UptimeEntityFactory::new();
    }
}

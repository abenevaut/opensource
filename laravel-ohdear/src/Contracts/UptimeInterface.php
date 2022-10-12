<?php

namespace abenevaut\Ohdear\Contracts;

interface UptimeInterface
{
    public function getDatetime(): string;
    public function getUptimePercentage(): int;
}

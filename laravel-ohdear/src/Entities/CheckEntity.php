<?php

namespace abenevaut\Ohdear\Entities;

use Spatie\DataTransferObject\DataTransferObject;

class CheckEntity extends DataTransferObject
{
    public int $id;
    public string $type;
    public string $label;
    public bool $enabled;
    public ?string $latest_run_ended_at;
    public ?string $latest_run_result;
    public ?string $summary;

    public function getId(): int
    {
        return $this->id;
    }
}

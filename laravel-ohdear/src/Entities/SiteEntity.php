<?php

namespace abenevaut\Ohdear\Entities;

use abenevaut\Ohdear\Contracts\SiteInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Casters\ArrayCaster;
use Spatie\DataTransferObject\DataTransferObject;

class SiteEntity extends DataTransferObject implements SiteInterface
{
    use HasFactory;

    public string|int $id;
    public string $url;
    public string $sort_url;
    public string $label;
    public string $team_id;
    public ?string $group_name;
    public string $latest_run_date;
    public string $summarized_check_result;
    public string $created_at;
    public string $updated_at;
    #[CastWith(ArrayCaster::class, itemType: CheckEntity::class)]
    public array $checks;

    public function getId(): int
    {
        return $this->id;
    }

    protected static function newFactory(): SiteEntityFactory
    {
        return SiteEntityFactory::new();
    }
}

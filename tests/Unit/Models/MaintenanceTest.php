<?php

namespace Tests\Unit\Models;

use App\Models\Maintenance;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

class MaintenanceTest extends TestCase
{
    public function test_datetime_and_date_casts_are_defined(): void
    {
        $maintenance = new Maintenance;

        $this->assertSame('datetime', $maintenance->getCasts()['scheduled_at']);
        $this->assertSame('datetime', $maintenance->getCasts()['started_at']);
        $this->assertSame('datetime', $maintenance->getCasts()['completed_at']);
        $this->assertSame('date', $maintenance->getCasts()['next_maintenance_at']);
    }

    public function test_relations_are_defined(): void
    {
        $maintenance = new Maintenance;

        $this->assertInstanceOf(BelongsTo::class, $maintenance->product());
        $this->assertInstanceOf(BelongsTo::class, $maintenance->performer());
    }
}

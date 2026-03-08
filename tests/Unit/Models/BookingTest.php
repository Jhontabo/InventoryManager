<?php

namespace Tests\Unit\Models;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tests\TestCase;

class BookingTest extends TestCase
{
    public function test_pending_scope_adds_pending_status_constraint(): void
    {
        $query = (new Booking)->newQuery()->pending()->getQuery();

        $this->assertNotEmpty($query->wheres);
        $this->assertSame('status', $query->wheres[0]['column']);
        $this->assertSame(Booking::STATUS_PENDING, $query->wheres[0]['value']);
    }

    public function test_status_helpers_return_expected_values(): void
    {
        $pending = new Booking(['status' => Booking::STATUS_PENDING]);
        $approved = new Booking(['status' => Booking::STATUS_APPROVED]);
        $reserved = new Booking(['status' => Booking::STATUS_RESERVED]);
        $rejected = new Booking(['status' => Booking::STATUS_REJECTED]);

        $this->assertTrue($pending->isPending());
        $this->assertTrue($approved->isApproved());
        $this->assertTrue($reserved->isReserved());
        $this->assertTrue($rejected->isRejected());
    }

    public function test_relations_are_defined(): void
    {
        $booking = new Booking;

        $this->assertInstanceOf(BelongsTo::class, $booking->schedule());
        $this->assertInstanceOf(BelongsTo::class, $booking->laboratory());
        $this->assertInstanceOf(BelongsTo::class, $booking->user());
        $this->assertInstanceOf(BelongsToMany::class, $booking->productLinks());
    }
}

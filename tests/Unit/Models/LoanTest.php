<?php

namespace Tests\Unit\Models;

use App\Models\Loan;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

class LoanTest extends TestCase
{
    public function test_datetime_casts_are_defined(): void
    {
        $loan = new Loan;

        $this->assertSame('datetime', $loan->getCasts()['requested_at']);
        $this->assertSame('datetime', $loan->getCasts()['approved_at']);
        $this->assertSame('datetime', $loan->getCasts()['estimated_return_at']);
        $this->assertSame('datetime', $loan->getCasts()['actual_return_at']);
    }

    public function test_relations_are_defined(): void
    {
        $loan = new Loan;

        $this->assertInstanceOf(BelongsTo::class, $loan->product());
        $this->assertInstanceOf(BelongsTo::class, $loan->user());
    }
}

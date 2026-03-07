<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_key_relations_are_defined(): void
    {
        $product = new Product;

        $this->assertInstanceOf(BelongsToMany::class, $product->bookings());
        $this->assertInstanceOf(BelongsToMany::class, $product->schedules());
        $this->assertInstanceOf(BelongsTo::class, $product->laboratory());
        $this->assertInstanceOf(HasMany::class, $product->loans());
    }
}

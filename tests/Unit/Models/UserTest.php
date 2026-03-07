<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mockery;
use Tests\TestCase;

class UserTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_access_panel_only_when_user_is_active(): void
    {
        $panel = Mockery::mock(Panel::class);

        $activeUser = new User(['status' => 'active']);
        $inactiveUser = new User(['status' => 'inactive']);

        $this->assertTrue($activeUser->canAccessPanel($panel));
        $this->assertFalse($inactiveUser->canAccessPanel($panel));
    }

    public function test_bookings_relation_is_defined(): void
    {
        $user = new User;

        $this->assertInstanceOf(HasMany::class, $user->bookings());
    }
}

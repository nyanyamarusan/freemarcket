<?php

namespace Tests\Browser;

use App\Models\Item;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SubtotalUpdateTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_selected_payment_method(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $item = Item::factory()->create();
        PaymentMethod::create([
            'id' => 1,
            'name' => 'コンビニ払い',
        ]);
        PaymentMethod::create([
            'id' => 2,
            'name' => 'カード支払い',
        ]);

        $this->browse(function (Browser $browser) use ($user, $item) {
            $browser->loginAs($user)
                ->visit('/purchase/' . $item->id)
                ->waitFor('.custom-select__trigger', 5)
                ->click('.custom-select__trigger')
                ->click('.custom-option[data-value="2"]')
                ->pause(1000)
                ->assertSeeIn('#selectedPaymentMethod', 'カード支払い');
        });
    }
}

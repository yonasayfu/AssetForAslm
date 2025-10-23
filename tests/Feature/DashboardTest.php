<?php

use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('renders dashboard data for authenticated users', function () {
     = User::factory()->create();
    Staff::factory()->count(2)->create();

    actingAs()
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn () => 
            ->component('Dashboard')
            ->has('metrics')
            ->has('staffTrend.labels')
            ->has('staffTrend.series')
            ->has('maintenance')
            ->has('recentExports')
            ->has('recentActivity')
        );
});

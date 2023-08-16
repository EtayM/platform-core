<?php

namespace Enjin\Platform\Tests\Feature\Commands;

use Enjin\Platform\Models\PendingEvent;
use Enjin\Platform\Tests\Feature\GraphQL\TestCaseGraphQL;

class PruneTest extends TestCaseGraphQL
{
    public function test_it_can_prune_expired_events(): void
    {
        PendingEvent::truncate();
        PendingEvent::insert(
            PendingEvent::factory(1)->make([
                'sent' => now()->subDays(config('enjin-platform.prune_expired_events') + 1)->toDateTimeString(),
            ])->toArray()
        );
        $this->artisan('model:prune', ['--model' => PendingEvent::resolveClassFqn()]);
        $this->assertDatabaseCount('pending_events', 0);
    }

    public function test_it_cannot_prune_expired_events(): void
    {
        config(['enjin-platform.prune_expired_events' => null]);
        PendingEvent::insert(
            PendingEvent::factory(1)->make([
                'sent' => now()->subDays(config('enjin-platform.prune_expired_events') + 1)->toDateTimeString(),
            ])->toArray()
        );
        $this->artisan('model:prune', ['--model' => PendingEvent::resolveClassFqn()]);
        $this->assertNotEmpty(PendingEvent::count());

        config(['enjin-platform.prune_expired_events' => 0]);
        $this->artisan('model:prune', ['--model' => PendingEvent::resolveClassFqn()]);
        $this->assertNotEmpty(PendingEvent::count());
    }
}

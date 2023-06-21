<?php

namespace Enjin\Platform\Events;

use Enjin\Platform\Events\Substrate\Commands\PlatformEventCached;
use Enjin\Platform\Models\PendingEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class PlatformBroadcastEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Model $model;

    /**
     * The broadcast data.
     */
    public array $broadcastData = [];

    /**
     * The broadcast channels.
     */
    protected array $broadcastChannels = [];

    /**
     * The event class name.
     */
    protected string $className;

    /**
     * The event UUID.
     */
    protected string $uuid;

    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        $this->className = (new \ReflectionClass(static::class))->getShortName();
        $this->uuid = Str::uuid()->toString();
    }

    /**
     * Get the name the event should be broadcast on.
     */
    public function broadcastAs()
    {
        $className = Str::kebab($this->className);

        return "platform:{$className}";
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return $this->broadcastChannels;
    }

    /**
     * Get the data that should be sent with the broadcast event.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        if (config('enjin-platform.cache_events')) {
            $this->cacheEvent();
        }

        $this->broadcastData['uuid'] = $this->uuid;

        return $this->broadcastData;
    }

    /**
     * Broadcast the event and catch any errors.
     */
    public static function safeBroadcast()
    {
        try {
            static::broadcast(...func_get_args());
        } catch (\Throwable $e) {
            $class = (new \ReflectionClass(static::class))->getShortName();
            Log::info("{$class} : Event cached but no websocket open to broadcast on. {$e->getMessage()}");
        }
    }

    /**
     * Store the event in the database.
     */
    protected function cacheEvent()
    {
        $pendingEvent = PendingEvent::create([
            'uuid' => $this->uuid,
            'name' => $this->broadcastAs(),
            'sent' => now()->toIso8601String(),
            'channels' => collect($this->broadcastChannels)->pluck('name')->toJson(),
            'data' => json_encode($this->broadcastData),
        ]);

        PlatformEventCached::dispatch($pendingEvent);
    }
}

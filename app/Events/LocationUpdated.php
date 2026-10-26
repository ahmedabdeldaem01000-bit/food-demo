<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $userId;
    public $latitude;
    // latitude
    // longitude
    public $longitude;
    public $room_id;

    /**
     * Create a new event instance.
     */
    public function __construct($userId,$latitude,$longitude,$room_id)
    {
        $this->userId = $userId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->room_id = $room_id;
    }
    

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('location-tracking.' . $this->room_id),
        ];
    }
    public function broadcastAs(): string
    {
        return 'location.updated';
    }
    public function broadcastWith(): array
    {
        return [
            'userId' => $this->userId,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'room_id' => $this->room_id,
        ];
    }
}

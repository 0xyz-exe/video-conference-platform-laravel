<?php

namespace Database\Factories;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MeetingFactory extends Factory
{
    protected $model = Meeting::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'room_code' => Str::lower(Str::random(3) . '-' . Str::random(4) . '-' . Str::random(3)),
            'description' => fake()->paragraph(),
            'passcode' => null,
            'is_locked' => false,
            'max_participants' => 50,
            'status' => 'scheduled',
            'scheduled_at' => now()->addDay(),
        ];
    }
}

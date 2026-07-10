<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'notable_type' => null,
            'notable_id' => null,
            'filename' => 'test.txt',
            'original_name' => 'test.txt',
            'mime_type' => 'text/plain',
            'size' => 100,
        ];
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ChatRoom;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

class ChatRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users
        $admin = User::where('email', 'admin@chatweb.com')->first();
        $user = User::where('email', 'user@chatweb.com')->first();

        // Create additional test users
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $users[] = User::firstOrCreate(
                ['email' => "user{$i}@chatweb.com"],
                [
                    'name' => "User {$i}",
                    'password' => bcrypt('password123'),
                    'role' => 'user',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        // Add admin and main user to users array
        $allUsers = collect([$admin, $user])->merge($users)->filter();

        // Create chat rooms
        $chatRooms = [
            [
                'name' => 'General Discussion',
                'description' => 'General discussion room for all users',
                'type' => 'group',
                'created_by' => $admin->id,
                'is_active' => true,
            ],
            [
                'name' => 'Technical Support',
                'description' => 'Technical support and help',
                'type' => 'group',
                'created_by' => $admin->id,
                'is_active' => true,
            ],
            [
                'name' => 'Random Chat',
                'description' => 'Random discussions and fun',
                'type' => 'group',
                'created_by' => $user->id,
                'is_active' => true,
            ],
            [
                'name' => 'Admin Only',
                'description' => 'Admin only discussion',
                'type' => 'group',
                'created_by' => $admin->id,
                'is_active' => true,
            ],
            [
                'name' => 'Private Chat',
                'description' => 'Private chat between two users',
                'type' => 'private',
                'created_by' => $user->id,
                'is_active' => true,
            ],
        ];

        foreach ($chatRooms as $roomData) {
            $chatRoom = ChatRoom::firstOrCreate(
                ['name' => $roomData['name']],
                $roomData
            );

            // Add participants to chat room
            if ($chatRoom->type === 'group') {
                // Add all users to group chat
                foreach ($allUsers as $participant) {
                    $chatRoom->participants()->syncWithoutDetaching([
                        $participant->id => [
                            'role' => $participant->role === 'admin' ? 'admin' : 'member',
                            'joined_at' => now(),
                            'is_active' => true,
                        ]
                    ]);
                }
            } else {
                // Add only creator and one other user to private chat
                $chatRoom->participants()->syncWithoutDetaching([
                    $chatRoom->created_by => [
                        'role' => 'admin',
                        'joined_at' => now(),
                        'is_active' => true,
                    ],
                    $admin->id => [
                        'role' => 'member',
                        'joined_at' => now(),
                        'is_active' => true,
                    ]
                ]);
            }

            // Create sample messages
            $messageCount = rand(5, 20);
            for ($i = 0; $i < $messageCount; $i++) {
                $randomUser = $allUsers->random();
                $messageTypes = ['text', 'text', 'text', 'text', 'image']; // More text messages
                $messageType = $messageTypes[array_rand($messageTypes)];
                
                $contents = [
                    'Hello everyone!',
                    'How are you doing today?',
                    'This is a test message.',
                    'Great to see everyone here.',
                    'Let\'s have a good discussion.',
                    'Anyone online?',
                    'Good morning/afternoon!',
                    'Thanks for the help.',
                    'See you later!',
                    'Have a great day!',
                    'What\'s new?',
                    'Nice weather today.',
                    'How\'s the project going?',
                    'Let me know if you need help.',
                    'Looking forward to hearing from you.',
                ];

                Message::create([
                    'chat_room_id' => $chatRoom->id,
                    'user_id' => $randomUser->id,
                    'content' => $contents[array_rand($contents)],
                    'type' => $messageType,
                    'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                    'updated_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                ]);
            }
        }

        $this->command->info('Chat rooms and messages created successfully!');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ChatRoom;
use App\Models\Message;

class ChatSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users
        $admin = User::where('email', 'admin@chatweb.com')->first();
        $user = User::where('email', 'user@chatweb.com')->first();

        if (!$admin || !$user) {
            $this->command->error('Admin or test user not found. Please run AdminUserSeeder first.');
            return;
        }

        // Create additional test users
        $user2 = User::firstOrCreate(
            ['email' => 'user2@chatweb.com'],
            [
                'name' => 'Test User 2',
                'password' => bcrypt('password123'),
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $user3 = User::firstOrCreate(
            ['email' => 'user3@chatweb.com'],
            [
                'name' => 'Test User 3',
                'password' => bcrypt('password123'),
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create sample chat rooms
        $privateChat = ChatRoom::firstOrCreate(
            ['name' => 'Private Chat - Admin & User'],
            [
                'description' => 'Private conversation between admin and user',
                'type' => 'private',
                'created_by' => $admin->id,
                'is_active' => true,
            ]
        );

        $groupChat = ChatRoom::firstOrCreate(
            ['name' => 'General Discussion'],
            [
                'description' => 'General discussion group for all users',
                'type' => 'group',
                'created_by' => $admin->id,
                'is_active' => true,
                'settings' => [
                    'max_participants' => 50,
                    'allow_file_upload' => true,
                ]
            ]
        );

        $techChat = ChatRoom::firstOrCreate(
            ['name' => 'Tech Talk'],
            [
                'description' => 'Technical discussions and programming topics',
                'type' => 'group',
                'created_by' => $user->id,
                'is_active' => true,
                'settings' => [
                    'max_participants' => 20,
                    'allow_file_upload' => true,
                ]
            ]
        );

        // Add participants to chat rooms
        // Private chat participants
        $privateChat->addParticipant($admin, 'admin');
        $privateChat->addParticipant($user, 'member');

        // Group chat participants
        $groupChat->addParticipant($admin, 'admin');
        $groupChat->addParticipant($user, 'member');
        $groupChat->addParticipant($user2, 'member');
        $groupChat->addParticipant($user3, 'moderator');

        // Tech chat participants
        $techChat->addParticipant($user, 'admin');
        $techChat->addParticipant($user2, 'member');
        $techChat->addParticipant($user3, 'member');

        // Create sample messages
        $this->createSampleMessages($privateChat, [$admin, $user]);
        $this->createSampleMessages($groupChat, [$admin, $user, $user2, $user3]);
        $this->createSampleMessages($techChat, [$user, $user2, $user3]);

        $this->command->info('Sample chat data created successfully!');
    }

    private function createSampleMessages(ChatRoom $chatRoom, array $users): void
    {
        $sampleMessages = [
            'Hello everyone! 👋',
            'How are you doing today?',
            'This is a great chat application!',
            'I love the real-time features.',
            'The file upload functionality is awesome.',
            'Thanks for creating this platform.',
            'Looking forward to more features.',
            'Have a great day!',
            'See you later! 👋',
            'This message was sent for testing purposes.',
        ];

        foreach ($sampleMessages as $index => $content) {
            $user = $users[array_rand($users)];

            $message = Message::create([
                'chat_room_id' => $chatRoom->id,
                'user_id' => $user->id,
                'content' => $content,
                'type' => 'text',
                'created_at' => now()->subMinutes(count($sampleMessages) - $index),
            ]);

            // Mark some messages as read by random users
            foreach ($users as $reader) {
                if ($reader->id !== $user->id && rand(0, 1)) {
                    $message->markAsRead($reader);
                }
            }
        }

        // Create one reply message
        $originalMessage = $chatRoom->messages()->first();
        if ($originalMessage) {
            $replyUser = $users[array_rand($users)];
            Message::create([
                'chat_room_id' => $chatRoom->id,
                'user_id' => $replyUser->id,
                'content' => 'Thanks for the message!',
                'type' => 'text',
                'reply_to_message_id' => $originalMessage->id,
                'created_at' => now()->subMinutes(2),
            ]);
        }
    }
}

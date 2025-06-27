<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class IpWhitelistTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user without IP restrictions
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@chatweb.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'allowed_ips' => null, // No IP restrictions
            'email_verified_at' => now(),
        ]);

        // Create user with office IP restrictions
        User::create([
            'name' => 'Office Worker',
            'email' => 'office@chatweb.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_active' => true,
            'allowed_ips' => [
                '192.168.1.100',    // Office network
                '192.168.1.101',    // Backup office connection
                '203.0.113.5'       // Office public IP
            ],
            'email_verified_at' => now(),
        ]);

        // Create user with home IP restriction
        User::create([
            'name' => 'Remote Worker',
            'email' => 'remote@chatweb.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_active' => true,
            'allowed_ips' => [
                '198.51.100.10',    // Home internet
                '198.51.100.11'     // Backup home connection
            ],
            'email_verified_at' => now(),
        ]);

        // Create user with single IP restriction
        User::create([
            'name' => 'Secure User',
            'email' => 'secure@chatweb.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_active' => true,
            'allowed_ips' => [
                '10.0.0.100'        // Single secure location
            ],
            'email_verified_at' => now(),
        ]);

        // Create user with no IP restrictions
        User::create([
            'name' => 'Mobile User',
            'email' => 'mobile@chatweb.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_active' => true,
            'allowed_ips' => null, // Can access from anywhere
            'email_verified_at' => now(),
        ]);

        // Create admin with IP restrictions (for testing admin security)
        User::create([
            'name' => 'Restricted Admin',
            'email' => 'restricted-admin@chatweb.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'allowed_ips' => [
                '192.168.1.200',    // Admin workstation
                '10.0.0.200'        // Secure admin network
            ],
            'email_verified_at' => now(),
        ]);

        // Create multiple test users for bulk operations
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => "Test User {$i}",
                'email' => "test{$i}@chatweb.com",
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_active' => true,
                'allowed_ips' => $i % 3 === 0 ? null : [
                    "192.168.{$i}.100",
                    "10.0.{$i}.100"
                ],
                'email_verified_at' => now(),
            ]);
        }

        $this->command->info('IP Whitelist test users created successfully!');
        $this->command->info('');
        $this->command->info('Test Credentials:');
        $this->command->info('- Admin (no restrictions): admin@chatweb.com / password');
        $this->command->info('- Office worker: office@chatweb.com / password');
        $this->command->info('- Remote worker: remote@chatweb.com / password');
        $this->command->info('- Secure user: secure@chatweb.com / password');
        $this->command->info('- Mobile user: mobile@chatweb.com / password');
        $this->command->info('- Restricted admin: restricted-admin@chatweb.com / password');
        $this->command->info('- Test users: test1@chatweb.com to test10@chatweb.com / password');
    }
}

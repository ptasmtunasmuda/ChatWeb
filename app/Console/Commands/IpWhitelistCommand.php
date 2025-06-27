<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class IpWhitelistCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ip-whitelist {action} {--user=} {--ip=} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage IP whitelist for users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'list':
                $this->listUsers();
                break;
            case 'show':
                $this->showUser();
                break;
            case 'add':
                $this->addIp();
                break;
            case 'remove':
                $this->removeIp();
                break;
            case 'clear':
                $this->clearIps();
                break;
            case 'emergency-clear':
                $this->emergencyClear();
                break;
            default:
                $this->error("Unknown action: {$action}");
                $this->showHelp();
        }
    }

    /**
     * List all users with IP restrictions
     */
    private function listUsers()
    {
        $users = User::whereNotNull('allowed_ips')->get();

        if ($users->isEmpty()) {
            $this->info('No users with IP restrictions found.');
            return;
        }

        $this->info('Users with IP restrictions:');
        $this->line('');

        foreach ($users as $user) {
            $ips = is_array($user->allowed_ips) ? implode(', ', $user->allowed_ips) : 'None';
            $this->line("ID: {$user->id} | {$user->name} ({$user->email}) | Role: {$user->role}");
            $this->line("  IPs: {$ips}");
            $this->line('');
        }
    }

    /**
     * Show specific user's IP whitelist
     */
    private function showUser()
    {
        $userIdentifier = $this->option('user');
        
        if (!$userIdentifier) {
            $this->error('Please specify --user=email or --user=id');
            return;
        }

        $user = is_numeric($userIdentifier) 
            ? User::find($userIdentifier)
            : User::where('email', $userIdentifier)->first();

        if (!$user) {
            $this->error("User not found: {$userIdentifier}");
            return;
        }

        $this->info("User: {$user->name} ({$user->email})");
        $this->info("Role: {$user->role}");
        $this->info("Status: " . ($user->is_active ? 'Active' : 'Inactive'));
        
        if (empty($user->allowed_ips)) {
            $this->info("IP Restrictions: None (can access from any IP)");
        } else {
            $this->info("IP Restrictions:");
            foreach ($user->allowed_ips as $ip) {
                $this->line("  - {$ip}");
            }
        }
    }

    /**
     * Add IP to user's whitelist
     */
    private function addIp()
    {
        $userIdentifier = $this->option('user');
        $ip = $this->option('ip');

        if (!$userIdentifier || !$ip) {
            $this->error('Please specify --user=email and --ip=address');
            return;
        }

        $user = is_numeric($userIdentifier) 
            ? User::find($userIdentifier)
            : User::where('email', $userIdentifier)->first();

        if (!$user) {
            $this->error("User not found: {$userIdentifier}");
            return;
        }

        // Validate IP format
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->error("Invalid IP address format: {$ip}");
            return;
        }

        $allowedIps = $user->allowed_ips ?? [];

        if (in_array($ip, $allowedIps)) {
            $this->warn("IP {$ip} is already in whitelist for user {$user->email}");
            return;
        }

        $allowedIps[] = $ip;
        $user->update(['allowed_ips' => $allowedIps]);

        $this->info("IP {$ip} added to whitelist for user {$user->email}");
    }

    /**
     * Remove IP from user's whitelist
     */
    private function removeIp()
    {
        $userIdentifier = $this->option('user');
        $ip = $this->option('ip');

        if (!$userIdentifier || !$ip) {
            $this->error('Please specify --user=email and --ip=address');
            return;
        }

        $user = is_numeric($userIdentifier) 
            ? User::find($userIdentifier)
            : User::where('email', $userIdentifier)->first();

        if (!$user) {
            $this->error("User not found: {$userIdentifier}");
            return;
        }

        $allowedIps = $user->allowed_ips ?? [];

        if (!in_array($ip, $allowedIps)) {
            $this->warn("IP {$ip} is not in whitelist for user {$user->email}");
            return;
        }

        $allowedIps = array_values(array_filter($allowedIps, function($allowedIp) use ($ip) {
            return $allowedIp !== $ip;
        }));

        $user->update(['allowed_ips' => empty($allowedIps) ? null : $allowedIps]);

        $this->info("IP {$ip} removed from whitelist for user {$user->email}");
    }

    /**
     * Clear all IP restrictions for a user
     */
    private function clearIps()
    {
        $userIdentifier = $this->option('user');

        if (!$userIdentifier) {
            $this->error('Please specify --user=email or --user=id');
            return;
        }

        $user = is_numeric($userIdentifier) 
            ? User::find($userIdentifier)
            : User::where('email', $userIdentifier)->first();

        if (!$user) {
            $this->error("User not found: {$userIdentifier}");
            return;
        }

        if (empty($user->allowed_ips)) {
            $this->info("User {$user->email} already has no IP restrictions");
            return;
        }

        if (!$this->confirm("Are you sure you want to clear all IP restrictions for {$user->email}?")) {
            $this->info('Operation cancelled');
            return;
        }

        $user->update(['allowed_ips' => null]);
        $this->info("All IP restrictions cleared for user {$user->email}");
    }

    /**
     * Emergency clear all IP restrictions (for lockout recovery)
     */
    private function emergencyClear()
    {
        if (!$this->option('all')) {
            $this->error('This is a dangerous operation. Use --all flag to confirm.');
            $this->warn('This will remove ALL IP restrictions from ALL users!');
            return;
        }

        if (!$this->confirm('Are you absolutely sure you want to clear ALL IP restrictions for ALL users? This cannot be undone!')) {
            $this->info('Operation cancelled');
            return;
        }

        $affectedUsers = User::whereNotNull('allowed_ips')->count();
        
        User::whereNotNull('allowed_ips')->update(['allowed_ips' => null]);

        $this->info("Emergency clear completed. Removed IP restrictions from {$affectedUsers} users.");
        $this->warn('All users can now access from any IP address.');
    }

    /**
     * Show command help
     */
    private function showHelp()
    {
        $this->line('');
        $this->info('IP Whitelist Management Commands:');
        $this->line('');
        $this->line('List users with IP restrictions:');
        $this->line('  php artisan ip-whitelist list');
        $this->line('');
        $this->line('Show user IP whitelist:');
        $this->line('  php artisan ip-whitelist show --user=admin@chatweb.com');
        $this->line('  php artisan ip-whitelist show --user=1');
        $this->line('');
        $this->line('Add IP to user whitelist:');
        $this->line('  php artisan ip-whitelist add --user=admin@chatweb.com --ip=192.168.1.100');
        $this->line('');
        $this->line('Remove IP from user whitelist:');
        $this->line('  php artisan ip-whitelist remove --user=admin@chatweb.com --ip=192.168.1.100');
        $this->line('');
        $this->line('Clear all IP restrictions for user:');
        $this->line('  php artisan ip-whitelist clear --user=admin@chatweb.com');
        $this->line('');
        $this->line('Emergency clear ALL restrictions (use with caution):');
        $this->line('  php artisan ip-whitelist emergency-clear --all');
        $this->line('');
    }
}

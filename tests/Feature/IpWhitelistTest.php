<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class IpWhitelistTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'allowed_ips' => null // No IP restrictions for admin
        ]);
        
        $this->user = User::factory()->create([
            'role' => 'user',
            'allowed_ips' => ['192.168.1.100', '203.0.113.5']
        ]);
    }

    /** @test */
    public function admin_can_view_current_ip()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/current-ip');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'current_ip',
                    'user_agent',
                    'timestamp'
                ]);
    }

    /** @test */
    public function admin_can_update_user_ip_whitelist()
    {
        Sanctum::actingAs($this->admin);

        $newIps = ['10.0.0.1', '10.0.0.2'];

        $response = $this->putJson("/api/admin/users/{$this->user->id}/ip-whitelist", [
            'allowed_ips' => $newIps
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'IP whitelist updated successfully',
                    'allowed_ips' => $newIps
                ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'allowed_ips' => json_encode($newIps)
        ]);
    }

    /** @test */
    public function admin_can_add_ip_to_whitelist()
    {
        Sanctum::actingAs($this->admin);

        $newIp = '10.0.0.1';

        $response = $this->postJson("/api/admin/users/{$this->user->id}/ip-whitelist/add", [
            'ip' => $newIp
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'IP added to whitelist successfully'
                ]);

        $user = $this->user->fresh();
        $this->assertContains($newIp, $user->allowed_ips);
    }

    /** @test */
    public function admin_can_remove_ip_from_whitelist()
    {
        Sanctum::actingAs($this->admin);

        $ipToRemove = '192.168.1.100';

        $response = $this->deleteJson("/api/admin/users/{$this->user->id}/ip-whitelist/remove", [
            'ip' => $ipToRemove
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'IP removed from whitelist successfully'
                ]);

        $user = $this->user->fresh();
        $this->assertNotContains($ipToRemove, $user->allowed_ips ?? []);
    }

    /** @test */
    public function invalid_ip_format_is_rejected()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/admin/users/{$this->user->id}/ip-whitelist/add", [
            'ip' => 'invalid-ip'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['ip']);
    }

    /** @test */
    public function user_without_ip_restrictions_can_access_from_any_ip()
    {
        $user = User::factory()->create([
            'allowed_ips' => null
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/auth/user');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_with_ip_restrictions_is_blocked_from_non_whitelisted_ip()
    {
        // This test would require mocking the IP address
        // In real testing, you'd need to mock the request IP
        
        Sanctum::actingAs($this->user);

        // Mock request IP that's not in whitelist
        $this->app['request']->server->set('REMOTE_ADDR', '99.99.99.99');

        $response = $this->getJson('/api/auth/user');

        $response->assertStatus(403)
                ->assertJson([
                    'message' => 'Access denied from this IP address'
                ]);
    }

    /** @test */
    public function user_model_correctly_validates_allowed_ips()
    {
        // Test with allowed IP
        $this->assertTrue($this->user->isAllowedFromIp('192.168.1.100'));
        $this->assertTrue($this->user->isAllowedFromIp('203.0.113.5'));

        // Test with non-allowed IP
        $this->assertFalse($this->user->isAllowedFromIp('99.99.99.99'));

        // Test user without restrictions
        $userWithoutRestrictions = User::factory()->create(['allowed_ips' => null]);
        $this->assertTrue($userWithoutRestrictions->isAllowedFromIp('99.99.99.99'));
    }

    /** @test */
    public function empty_ip_array_removes_all_restrictions()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/admin/users/{$this->user->id}/ip-whitelist", [
            'allowed_ips' => []
        ]);

        $response->assertStatus(200);

        $user = $this->user->fresh();
        $this->assertNull($user->allowed_ips);
    }

    /** @test */
    public function duplicate_ips_are_not_added()
    {
        Sanctum::actingAs($this->admin);

        $existingIp = '192.168.1.100'; // Already in user's whitelist

        $response = $this->postJson("/api/admin/users/{$this->user->id}/ip-whitelist/add", [
            'ip' => $existingIp
        ]);

        $response->assertStatus(200);

        $user = $this->user->fresh();
        
        // Count occurrences of the IP
        $ipCount = array_count_values($user->allowed_ips)[$existingIp] ?? 0;
        $this->assertEquals(1, $ipCount);
    }

    /** @test */
    public function non_admin_cannot_manage_ip_whitelist()
    {
        $regularUser = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($regularUser);

        $response = $this->getJson('/api/admin/current-ip');
        $response->assertStatus(403);

        $response = $this->putJson("/api/admin/users/{$this->user->id}/ip-whitelist", [
            'allowed_ips' => ['10.0.0.1']
        ]);
        $response->assertStatus(403);
    }

    /** @test */
    public function ip_whitelist_is_properly_cast_as_array()
    {
        $user = User::factory()->create([
            'allowed_ips' => ['192.168.1.1', '192.168.1.2']
        ]);

        $this->assertIsArray($user->allowed_ips);
        $this->assertCount(2, $user->allowed_ips);
        $this->assertContains('192.168.1.1', $user->allowed_ips);
    }

    /** @test */
    public function activity_log_is_created_when_ip_is_blocked()
    {
        // This would test the activity logging functionality
        // Implementation depends on your UserActivityLog model
        
        Sanctum::actingAs($this->user);
        
        // Mock a blocked request
        $this->app['request']->server->set('REMOTE_ADDR', '99.99.99.99');
        
        $response = $this->getJson('/api/auth/user');
        
        $response->assertStatus(403);
        
        // Assert activity log was created
        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'access_blocked',
            'description' => 'Access blocked due to IP restriction'
        ]);
    }
}

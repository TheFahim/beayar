<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_login_page()
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.auth.login');
    }

    public function test_admin_can_login_with_correct_credentials()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@beayar.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => 'admin@beayar.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_admin_cannot_login_with_incorrect_credentials()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@beayar.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => 'admin@beayar.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_authenticated_admin_can_access_dashboard()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@beayar.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($admin, 'admin')
                         ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');

        // Check sidebar content
        $response->assertSee('Tenants');
        $response->assertDontSee('Image Library');
        // We can't easily assertDontSee('Customers') because "Customers" might appear in other contexts,
        // but given the specific sidebar issue, checking for "Image Library" is a strong indicator
        // that the tenant sidebar is gone.
    }

    public function test_unauthenticated_admin_is_redirected_to_admin_login()
    {
        // This tests the middleware redirect behavior
        $response = $this->get(route('admin.dashboard'));

        // If auth:admin fails, it typically redirects to 'login' (user login) unless configured.
        // We want it to redirect to 'admin.login'.
        $response->assertRedirect(route('admin.login'));
    }

    public function test_regular_user_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create();

        // Acting as regular user (web guard)
        $response = $this->actingAs($user, 'web')
                         ->get(route('admin.dashboard'));

        // Should still redirect to admin login because user is not authenticated as admin
        $response->assertRedirect(route('admin.login'));
    }
}

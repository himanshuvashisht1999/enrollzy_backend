<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can login.
     */
    public function test_admin_can_login()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('123456'),
            'is_admin' => true,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@admin.com',
            'password' => '123456',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    /**
     * Test non-admin cannot login via admin route.
     */
    public function test_non_admin_cannot_login_as_admin()
    {
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@user.com',
            'password' => Hash::make('123456'),
            'is_admin' => false,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'user@user.com',
            'password' => '123456',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}

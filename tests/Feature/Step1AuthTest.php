<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Step1AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_sees_login_page(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Sign In');
    }

    public function test_admin_redirected_to_admin_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_manager_redirected_to_manager_dashboard(): void
    {
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@test.com',
            'password' => bcrypt('password123'),
            'role' => 'manager',
        ]);

        $response = $this->post('/login', [
            'email' => 'manager@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('manager.dashboard'));
    }

    public function test_employee_redirected_to_employee_dashboard(): void
    {
        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@test.com',
            'password' => bcrypt('password123'),
            'role' => 'employee',
        ]);

        $response = $this->post('/login', [
            'email' => 'employee@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('employee.dashboard'));
    }

    public function test_employee_cannot_access_admin_dashboard(): void
    {
        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@test.com',
            'password' => bcrypt('password123'),
            'role' => 'employee',
        ]);

        $response = $this->actingAs($employee)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_admin_can_register_new_employee(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post('/admin/register-employee', [
            'name' => 'New Staff',
            'email' => 'newstaff@company.com',
            'password' => 'secret123',
            'role' => 'employee',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'newstaff@company.com',
            'role' => 'employee',
        ]);
    }
}

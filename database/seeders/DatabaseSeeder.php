<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users
        $admin = User::create([
            'name'       => 'System Admin',
            'email'      => 'admin@company.com',
            'password'   => Hash::make('password123'),
            'role'       => 'admin',
            'department' => 'IT Administration',
            'is_active'  => true,
        ]);

        $manager = User::create([
            'name'       => 'Project Manager',
            'email'      => 'manager@company.com',
            'password'   => Hash::make('password123'),
            'role'       => 'manager',
            'department' => 'Project Management',
            'is_active'  => true,
        ]);

        $john = User::create([
            'name'       => 'John Doe',
            'email'      => 'john@company.com',
            'password'   => Hash::make('password123'),
            'role'       => 'employee',
            'department' => 'Software Engineering',
            'is_active'  => true,
        ]);

        $jane = User::create([
            'name'       => 'Jane Smith',
            'email'      => 'jane@company.com',
            'password'   => Hash::make('password123'),
            'role'       => 'employee',
            'department' => 'UI/UX Design',
            'is_active'  => true,
        ]);

        $alex = User::create([
            'name'       => 'Alex Taylor',
            'email'      => 'alex@company.com',
            'password'   => Hash::make('password123'),
            'role'       => 'employee',
            'department' => 'Quality Assurance',
            'is_active'  => true,
        ]);

        $sarah = User::create([
            'name'       => 'Sarah Jenkins',
            'email'      => 'sarah@company.com',
            'password'   => Hash::make('password123'),
            'role'       => 'employee',
            'department' => 'UI/UX Design',
            'is_active'  => true,
        ]);

        $james = User::create([
            'name'       => 'James Wilson',
            'email'      => 'james@company.com',
            'password'   => Hash::make('password123'),
            'role'       => 'employee',
            'department' => 'Backend Development',
            'is_active'  => true,
        ]);

        $peter = User::create([
            'name'       => 'Peter Parker',
            'email'      => 'peter@company.com',
            'password'   => Hash::make('password123'),
            'role'       => 'employee',
            'department' => 'Frontend Development',
            'is_active'  => true,
        ]);

        // 2. Projects
        $websiteRedesign = Project::create([
            'title'       => 'Website Redesign',
            'description' => 'Complete modern redesign of customer portal, authentication, and marketing pages.',
            'start_date'  => '2026-11-10',
            'deadline'    => '2026-12-10',
            'manager_id'  => $manager->id,
            'priority'    => 'high',
            'status'      => 'active',
        ]);
        $websiteRedesign->members()->attach([$john->id, $jane->id, $alex->id]);

        $proj1 = Project::create([
            'title'       => 'E-Commerce Mobile App',
            'description' => 'Build iOS and Android client applications with shopping cart and Stripe checkout.',
            'start_date'  => now()->subDays(5)->toDateString(),
            'deadline'    => now()->addDays(14)->toDateString(),
            'manager_id'  => $manager->id,
            'priority'    => 'high',
            'status'      => 'active',
        ]);
        $proj1->members()->attach([$john->id, $jane->id]);

        $proj2 = Project::create([
            'title'       => 'Cloud CRM Integration',
            'description' => 'Sync customer databases with third-party CRM webhook listeners.',
            'start_date'  => now()->subDays(2)->toDateString(),
            'deadline'    => now()->addDays(5)->toDateString(),
            'manager_id'  => $manager->id,
            'priority'    => 'medium',
            'status'      => 'active',
        ]);
        $proj2->members()->attach([$jane->id, $alex->id]);

        // 3. Website Redesign Tasks (Step 5 example)
        $task1 = Task::create([
            'project_id'  => $websiteRedesign->id,
            'assigned_to' => $jane->id,
            'title'       => 'Design homepage',
            'description' => 'Create Figma wireframes and responsive desktop/mobile designs for the new homepage.',
            'priority'    => 'high',
            'start_date'  => '2026-11-10',
            'due_date'    => '2026-11-18',
            'status'      => 'in_progress',
        ]);
        $jane->notify(new \App\Notifications\TaskNotification(
            'New Task Assigned',
            'You were assigned to task "Design homepage" in Website Redesign.',
            route('tasks.show', $task1),
            '📋',
            'task_assigned'
        ));

        $task2 = Task::create([
            'project_id'  => $websiteRedesign->id,
            'assigned_to' => $john->id,
            'title'       => 'Develop login page',
            'description' => 'Build frontend login interface with responsive inputs and error validation.',
            'priority'    => 'medium',
            'start_date'  => '2026-11-12',
            'due_date'    => '2026-11-20',
            'status'      => 'pending',
        ]);
        $john->notify(new \App\Notifications\TaskNotification(
            'New Task Assigned',
            'You were assigned to task "Develop login page" in Website Redesign.',
            route('tasks.show', $task2),
            '📋',
            'task_assigned'
        ));

        $task3 = Task::create([
            'project_id'  => $websiteRedesign->id,
            'assigned_to' => $john->id,
            'title'       => 'Create database',
            'description' => 'Design MySQL/SQLite relational schema with migrations and seeders.',
            'priority'    => 'high',
            'start_date'  => '2026-11-10',
            'due_date'    => '2026-11-15',
            'status'      => 'completed',
        ]);
        // Seed task approved notification
        $john->notify(new \App\Notifications\TaskNotification(
            'Task Approved! 🎉',
            'Your work on task "Create database" was reviewed and approved by manager.',
            route('tasks.show', $task3),
            '✅',
            'task_approved'
        ));

        $task4 = Task::create([
            'project_id'  => $websiteRedesign->id,
            'assigned_to' => $john->id,
            'title'       => 'Develop backend API',
            'description' => 'Implement REST endpoints, middleware role protection, and token validation.',
            'priority'    => 'high',
            'start_date'  => '2026-11-15',
            'due_date'    => '2026-11-25',
            'status'      => 'pending',
        ]);

        $task5 = Task::create([
            'project_id'  => $websiteRedesign->id,
            'assigned_to' => $alex->id,
            'title'       => 'Test the website',
            'description' => 'Perform end-to-end user journey tests, QA security audits, and cross-browser testing.',
            'priority'    => 'medium',
            'start_date'  => '2026-11-25',
            'due_date'    => '2026-12-05',
            'status'      => 'pending',
        ]);
        $alex->notify(new \App\Notifications\TaskNotification(
            'New Task Assigned',
            'You were assigned to task "Test the website" in Website Redesign.',
            route('tasks.show', $task5),
            '📋',
            'task_assigned'
        ));

        // 4. Sample Communication / Comments Thread (Step 7 example)
        $task1->comments()->create([
            'user_id' => $manager->id,
            'content' => 'Please finish the homepage design by Friday.',
        ]);
        $jane->notify(new \App\Notifications\TaskNotification(
            'New Comment on Task',
            'Project Manager commented on "Design homepage".',
            route('tasks.show', $task1),
            '💬',
            'task_commented'
        ));

        $task1->comments()->create([
            'user_id' => $jane->id,
            'content' => 'The design is ready for review.',
        ]);
        $manager->notify(new \App\Notifications\TaskNotification(
            'New Comment on Task',
            'Jane Smith commented on "Design homepage".',
            route('tasks.show', $task1),
            '💬',
            'task_commented'
        ));

        // 5. Team Workload Seeding (Sarah, James, Peter - Step 9 example)
        $websiteRedesign->members()->attach([$sarah->id, $james->id, $peter->id]);

        // Sarah: 12 completed, 3 pending (15 assigned)
        for ($i = 1; $i <= 12; $i++) {
            Task::create([
                'project_id'  => $websiteRedesign->id,
                'assigned_to' => $sarah->id,
                'title'       => "UI Component Spec #{$i}",
                'due_date'    => now()->subDays($i)->toDateString(),
                'status'      => 'completed',
                'priority'    => 'medium',
            ]);
        }
        for ($i = 1; $i <= 3; $i++) {
            Task::create([
                'project_id'  => $websiteRedesign->id,
                'assigned_to' => $sarah->id,
                'title'       => "Design Review #{$i}",
                'due_date'    => now()->addDays($i * 2)->toDateString(),
                'status'      => 'in_progress',
                'priority'    => 'high',
            ]);
        }

        // James: 8 completed, 2 pending (10 assigned)
        for ($i = 1; $i <= 8; $i++) {
            Task::create([
                'project_id'  => $websiteRedesign->id,
                'assigned_to' => $james->id,
                'title'       => "API Endpoint Build #{$i}",
                'due_date'    => now()->subDays($i)->toDateString(),
                'status'      => 'completed',
                'priority'    => 'medium',
            ]);
        }
        for ($i = 1; $i <= 2; $i++) {
            Task::create([
                'project_id'  => $websiteRedesign->id,
                'assigned_to' => $james->id,
                'title'       => "Database Optimization #{$i}",
                'due_date'    => now()->addDays($i * 3)->toDateString(),
                'status'      => 'pending',
                'priority'    => 'high',
            ]);
        }

        // Peter: 9 completed, 3 pending (12 assigned)
        for ($i = 1; $i <= 9; $i++) {
            Task::create([
                'project_id'  => $websiteRedesign->id,
                'assigned_to' => $peter->id,
                'title'       => "Frontend Widget #{$i}",
                'due_date'    => now()->subDays($i)->toDateString(),
                'status'      => 'completed',
                'priority'    => 'low',
            ]);
        }
        for ($i = 1; $i <= 3; $i++) {
            Task::create([
                'project_id'  => $websiteRedesign->id,
                'assigned_to' => $peter->id,
                'title'       => "Client Integration #{$i}",
                'due_date'    => now()->addDays($i)->toDateString(),
                'status'      => 'in_progress',
                'priority'    => 'medium',
            ]);
        }
    }
}

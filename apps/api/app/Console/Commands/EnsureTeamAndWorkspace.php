<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Ensures Novixa team users exist with valid credentials and optionally
 * seeds the workspace with baseline projects, tasks, and notes if empty.
 */
class EnsureTeamAndWorkspace extends Command
{
    protected $signature = 'workos:ensure-team
        {--password=password123 : Default password for seeded team accounts}
        {--seed-if-empty : Seed baseline Novixa projects/tasks if no projects exist}';

    protected $description = 'Ensure core Novixa team accounts exist with known passwords and optionally populate empty workspace.';

    public function handle(): int
    {
        $password = (string) $this->option('password');
        $hashed = Hash::make($password);

        $team = [
            [
                'name' => 'رضوان (Radwan - System Owner)',
                'email' => 'ak01redwan@novixa.dev',
                'role' => 'owner',
                'job_title' => 'System Owner & Principal Architect / مالك المنظومة',
                'locale' => 'ar',
                'timezone' => 'Asia/Riyadh',
                'theme' => 'dark',
            ],
            [
                'name' => 'أحمد الحضرمي (Ahmed Al-Hadrami)',
                'email' => 'ahmed@novixa.dev',
                'role' => 'owner',
                'job_title' => 'Founder & CEO / المؤسس والمدير التنفيذي',
                'locale' => 'ar',
                'timezone' => 'Asia/Riyadh',
                'theme' => 'dark',
            ],
            [
                'name' => 'سارة سالم (Sara Salem)',
                'email' => 'sara@novixa.dev',
                'role' => 'admin',
                'job_title' => 'Operations Lead / مديرة العمليات',
                'locale' => 'ar',
                'timezone' => 'Asia/Riyadh',
                'theme' => 'dark',
            ],
            [
                'name' => 'طارق باوزير (Tariq Ba-Wazir)',
                'email' => 'tariq@novixa.dev',
                'role' => 'manager',
                'job_title' => 'Technical Lead / قائد تقني ومشاريع',
                'locale' => 'ar',
                'timezone' => 'Asia/Riyadh',
                'theme' => 'dark',
            ],
            [
                'name' => 'عمر بن محفوظ (Omar Bin-Mahfouz)',
                'email' => 'omar@novixa.dev',
                'role' => 'member',
                'job_title' => 'Senior Fullstack Engineer / مهندس برمجيات أول',
                'locale' => 'ar',
                'timezone' => 'Asia/Riyadh',
                'theme' => 'dark',
            ],
            [
                'name' => 'فاطمة العطاس (Fatima Al-Attas)',
                'email' => 'fatima@novixa.dev',
                'role' => 'member',
                'job_title' => 'UI/UX & Product Designer / مصممة واجهات',
                'locale' => 'ar',
                'timezone' => 'Asia/Riyadh',
                'theme' => 'dark',
            ],
            [
                'name' => 'سعيد الكندي (Saeed Al-Kindi)',
                'email' => 'saeed@darhadramout.com',
                'role' => 'guest',
                'job_title' => 'Representative / ممثل دار حضرموت العقارية',
                'locale' => 'ar',
                'timezone' => 'Asia/Riyadh',
                'theme' => 'dark',
            ],
        ];

        foreach ($team as $member) {
            $user = User::updateOrCreate(
                ['email' => strtolower($member['email'])],
                [
                    'name' => $member['name'],
                    'password' => $hashed,
                    'role' => $member['role'],
                    'job_title' => $member['job_title'],
                    'locale' => $member['locale'],
                    'timezone' => $member['timezone'],
                    'theme' => $member['theme'],
                    'is_active' => true,
                ]
            );

            $this->info("✔ User {$user->email} synced (Role: {$user->role}).");
        }

        if ($this->option('seed-if-empty') && Project::count() === 0) {
            $this->info('Workspace is empty (0 projects). Seeding baseline products, projects, tasks, and notes...');
            $seeder = new DatabaseSeeder;
            $seeder->run();
            $this->info('✔ Workspace seeded successfully.');
        }

        return self::SUCCESS;
    }
}

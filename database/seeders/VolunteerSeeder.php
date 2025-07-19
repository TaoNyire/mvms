<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\VolunteerProfile;

class VolunteerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $volunteerRole = Role::where('name', 'volunteer')->first();

        $volunteers = [
            [
                'name' => 'Chikondi Mvula',
                'email' => 'chikondi.mvula@mvms.mw',
                'password' => 'Chikondi2024!',
                'skills' => ['Teaching', 'Healthcare'],
                'district' => 'Lilongwe',
                'region' => 'Central',
            ],
            [
                'name' => 'Thandiwe Phiri',
                'email' => 'thandiwe.phiri@mvms.mw',
                'password' => 'Thandiwe2024!',
                'skills' => ['Technology', 'Education'],
                'district' => 'Blantyre',
                'region' => 'Southern',
            ],
            [
                'name' => 'Blessings Banda',
                'email' => 'blessings.banda@mvms.mw',
                'password' => 'Blessings2024!',
                'skills' => ['Environment', 'Agriculture'],
                'district' => 'Zomba',
                'region' => 'Southern',
            ],
            [
                'name' => 'Joyce Kumwenda',
                'email' => 'joyce.kumwenda@mvms.mw',
                'password' => 'Joyce2024!',
                'skills' => ['Administration', 'Social Work'],
                'district' => 'Kasungu',
                'region' => 'Central',
            ],
            [
                'name' => 'Patrick Chirwa',
                'email' => 'patrick.chirwa@mvms.mw',
                'password' => 'Patrick2024!',
                'skills' => ['Emergency Response', 'Logistics'],
                'district' => 'Nsanje',
                'region' => 'Southern',
            ],
        ];

        foreach ($volunteers as $vol) {
            $user = User::firstOrCreate(
                ['email' => $vol['email']],
                [
                    'name' => $vol['name'],
                    'password' => Hash::make($vol['password']),
                    'email_verified_at' => now(),
                ]
            );

            if (!$user->hasRole('volunteer')) {
                $user->roles()->attach($volunteerRole);
            }

            if (!$user->volunteerProfile) {
                VolunteerProfile::create([
                    'user_id' => $user->id,
                    'skills' => $vol['skills'],
                    'district' => $vol['district'],
                    'region' => $vol['region'],
                    'is_complete' => true,
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('✅ Successfully created volunteers!');
        $this->command->info('👤 VOLUNTEER LOGIN CREDENTIALS:');
        foreach ($volunteers as $vol) {
            $this->command->info("   📧 {$vol['email']} | 🔑 {$vol['password']} ({$vol['name']})");
        }
        $this->command->info('🌟 All volunteer accounts are email verified and ready to use!');
    }
}

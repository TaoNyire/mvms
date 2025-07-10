<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Message;
use App\Models\User;
use App\Models\Application;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users for testing
        $volunteer = User::whereHas('roles', function($query) {
            $query->where('name', 'volunteer');
        })->first();

        $organization = User::whereHas('roles', function($query) {
            $query->where('name', 'organization');
        })->first();

        if ($volunteer && $organization) {
            // Create some test messages
            Message::create([
                'sender_id' => $organization->id,
                'receiver_id' => $volunteer->id,
                'subject' => 'Welcome to our organization!',
                'message' => 'Thank you for applying to our volunteer opportunity. We are excited to work with you!',
                'message_type' => 'general',
                'is_read' => false,
            ]);

            Message::create([
                'sender_id' => $volunteer->id,
                'receiver_id' => $organization->id,
                'subject' => 'Thank you for the opportunity',
                'message' => 'I am very excited to contribute to your organization and make a positive impact in the community.',
                'message_type' => 'general',
                'is_read' => false,
            ]);

            Message::create([
                'sender_id' => $organization->id,
                'receiver_id' => $volunteer->id,
                'subject' => 'Task Assignment',
                'message' => 'We have assigned you to help with our community outreach program. Please let us know your availability.',
                'message_type' => 'task_update',
                'is_read' => false,
            ]);

            // Get an application if exists
            $application = Application::where('volunteer_id', $volunteer->id)->first();
            
            if ($application) {
                Message::create([
                    'sender_id' => $organization->id,
                    'receiver_id' => $volunteer->id,
                    'application_id' => $application->id,
                    'subject' => 'Application Update',
                    'message' => 'Your application has been reviewed. We would like to schedule an interview with you.',
                    'message_type' => 'application_related',
                    'is_read' => false,
                ]);
            }
        }
    }
}

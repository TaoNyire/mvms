<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration by sending a test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? config('mail.from.address');
        
        $this->info("Testing email configuration...");
        $this->info("SMTP Host: " . config('mail.mailers.smtp.host'));
        $this->info("SMTP Port: " . config('mail.mailers.smtp.port'));
        $this->info("SMTP Encryption: " . config('mail.mailers.smtp.encryption'));
        $this->info("From Address: " . config('mail.from.address'));
        $this->info("To Address: " . $email);
        
        try {
            Mail::raw('This is a test email from MVMS. If you receive this, your email configuration is working correctly!', function ($message) use ($email) {
                $message->to($email)
                        ->subject('MVMS Email Configuration Test');
            });

            $this->info("✅ Test email sent successfully to: " . $email);
            $this->info("Check your inbox (and spam folder) for the test email.");
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to send test email:");
            $this->error($e->getMessage());
            
            // Additional debugging information
            $this->warn("\nDebugging Information:");
            $this->warn("Error Type: " . get_class($e));
            $this->warn("Error Code: " . $e->getCode());
            
            if (str_contains($e->getMessage(), 'Connection could not be established')) {
                $this->warn("\n🔧 Possible Solutions:");
                $this->warn("1. Check your internet connection");
                $this->warn("2. Verify SMTP host and port are correct");
                $this->warn("3. Ensure firewall allows outbound connections on port 587");
                $this->warn("4. Check if your Gmail account has 2-factor authentication enabled");
                $this->warn("5. Verify the app password is correct (not your regular Gmail password)");
            }
            
            if (str_contains($e->getMessage(), 'Authentication failed')) {
                $this->warn("\n🔧 Authentication Issues:");
                $this->warn("1. Make sure you're using an App Password, not your regular Gmail password");
                $this->warn("2. Enable 2-factor authentication on your Gmail account");
                $this->warn("3. Generate a new App Password in Gmail settings");
                $this->warn("4. Verify the username matches the Gmail address exactly");
            }
            
            return 1;
        }
        
        return 0;
    }
}

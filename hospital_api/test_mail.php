<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

ob_start();
echo "Current Mailer: " . config('mail.default') . "\n";
print_r(config('mail.mailers.smtp'));

try {
    echo "Attempting to send test email to nahar.sabikunlima@gmail.com...\n";
    Mail::raw('Test email from Telehealth System', function ($message) {
        $message->to('nahar.sabikunlima@gmail.com')
            ->subject('Test Connectivity');
    });
    echo "Mail sent successfully (according to Laravel).\n";
} catch (\Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
$output = ob_get_clean();
file_put_contents('test_mail_output.txt', $output);
echo "Check test_mail_output.txt\n";

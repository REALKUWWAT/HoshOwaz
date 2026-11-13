<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestSmtp extends Command
{
    protected $signature = 'test:smtp {email=kuvvat874@gmail.com}';
    protected $description = 'Gmail SMTP bağlantysyny synap görmek';

    public function handle(): int
    {
        $email = $this->argument('email');
        $this->info("📧 SMTP synag başlaýar: {$email}");

        try {
            Mail::raw(
                '🎵 Hoşowaz SMTP synag haty - bu hat işleýändigini tassyklaýar!',
                function ($m) use ($email) {
                    $m->to($email)->subject('🎵 Hoşowaz - SMTP Synag / Test');
                }
            );
            $this->info('✅ EMAIL ÜSTÜNLIKLI UGRADYLDY! Gmail-y barlaň.');
            return 0;
        } catch (\Throwable $e) {
            $this->error('❌ SMTP HATA: ' . $e->getMessage());
            return 1;
        }
    }
}


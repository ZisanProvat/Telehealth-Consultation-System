<?php

namespace App\Console\Commands;

use App\Models\Doctor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class HashDoctorPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'doctor:hash-passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hash all unhashed doctor passwords';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $doctors = Doctor::all();
        $updated = 0;

        foreach ($doctors as $doctor) {
            // Check if password is not already hashed (doesn't start with $2y$)
            if (!str_starts_with($doctor->password, '$2y$')) {
                $doctor->password = Hash::make($doctor->password);
                $doctor->save();
                $updated++;
                $this->info("Updated doctor: {$doctor->full_name}");
            }
        }

        $this->info("Total passwords updated: $updated");
    }
}

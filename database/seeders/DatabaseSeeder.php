<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\Client;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Artisan::call('passport:client', [
            '--password' => true,
            '--name' => 'Password Grant Client',
            '--provider' => 'users_plain_api',
        ]);

        $output = Artisan::output();
        // Parsear líneas y extraer "Client ID" y "Client secret" (texto plano que el comando imprime)
        $this->command->info($output);


        // Call the Artisan command to create a client credentials token
        Artisan::call('passport:client', [
            '--client' => true,
            '--name' => 'Client Credentials Client',
        ]);

        // Parse the output to extract the client ID and client secret
        $output = Artisan::output();
        // Parsear líneas y extraer "Client ID" y "Client secret" (texto plano que el comando imprime)
        $this->command->info($output);
    }
}

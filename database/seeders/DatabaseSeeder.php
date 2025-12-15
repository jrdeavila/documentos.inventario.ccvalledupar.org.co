<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Laravel\Passport\Passport;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Registrar Client Credentials Passport Client
        Passport::client()->forceCreate([
            'name' => 'Laravel Personal Access Client',
            "id" => "5f4c-9a5e-4d10-87f7-6f4963833460",
            'secret' => '3fab4f4c-9a5e-4d10-87f7-6f4963833460',
            'redirect_uris' => ['https://ccvalledupar.org.co'],
            'grant_types' => ['personal_access', 'refresh_token', 'client_credentials'],
            'revoked' => false,
        ]);
    }
}

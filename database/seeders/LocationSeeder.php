<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $json = file_get_contents(database_path('data/locations.json'));
        $locations = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            dd('JSON Error: '.json_last_error_msg());
        }

        foreach ($locations as $location) {
            DB::table('locations')->insert([
                'id' => $location['Id'],
                'parent_country_division_id' => $location['ParentCountryDivisionId'] ?? null,
                'name' => $location['Name'],
                'code' => $location['Code'],
                'division_type' => $location['DivisionType'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

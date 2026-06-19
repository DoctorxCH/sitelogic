<?php

namespace Database\Seeders;

use App\Models\JobFieldSetting;
use Illuminate\Database\Seeder;

class JobFieldSettingSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [
            ['key' => 'pid', 'label' => 'PID', 'type' => 'text', 'is_required' => true],
            ['key' => 'site_name', 'label' => 'Site Name', 'type' => 'text', 'is_required' => true],
            ['key' => 'address', 'label' => 'Address (Street, ZIP, City)', 'type' => 'text', 'is_required' => true],
            ['key' => 'business_area', 'label' => 'Geschäftsbereich', 'type' => 'text', 'is_required' => false],
            ['key' => 'region', 'label' => 'Region', 'type' => 'text', 'is_required' => false],
            ['key' => 'project_type_code', 'label' => 'Project Type', 'type' => 'text', 'is_required' => false],
            ['key' => 'technology', 'label' => 'Technologien', 'type' => 'text', 'is_required' => false],
            ['key' => 'an_code', 'label' => 'AN', 'type' => 'text', 'is_required' => false],
            ['key' => 'site_identificator', 'label' => 'Site Identificator', 'type' => 'text', 'is_required' => false],
            ['key' => 'drop_cable_labels', 'label' => 'Drop Cable Labels', 'type' => 'text', 'is_required' => false],
            ['key' => 'bep_type', 'label' => 'BEP Type', 'type' => 'text', 'is_required' => false],
            ['key' => 'target_latitude', 'label' => 'Target Latitude (Soll-Breitengrad)', 'type' => 'text', 'is_required' => false],
            ['key' => 'target_longitude', 'label' => 'Target Longitude (Soll-Längengrad)', 'type' => 'text', 'is_required' => false],
        ];

        foreach ($fields as $field) {
            JobFieldSetting::updateOrCreate(['key' => $field['key']], $field);
        }
    }
}

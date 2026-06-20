<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Language;
use App\Models\Translation;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            ['code' => 'en', 'name' => 'English', 'flag_code' => '🇬🇧', 'is_default' => true],
            ['code' => 'de', 'name' => 'Deutsch', 'flag_code' => '🇩🇪', 'is_default' => false],
            ['code' => 'sk', 'name' => 'Slovenčina', 'flag_code' => '🇸🇰', 'is_default' => false],
        ];

        foreach ($languages as $lang) {
            Language::updateOrCreate(['code' => $lang['code']], $lang);
        }

        $translations = [
            'en' => [
                'available_jobs' => 'Available Jobs',
                'pending_quality_reviews' => 'Pending Quality Reviews',
                'start_job' => 'Start Job',
                'complete_job' => 'Complete Job',
                'open' => 'Open',
            ],
            'de' => [
                'available_jobs' => 'Verfügbare Jobs',
                'pending_quality_reviews' => 'Ausstehende Qualitätsprüfungen',
                'start_job' => 'Job starten',
                'complete_job' => 'Job abschließen',
                'open' => 'Offen',
            ],
            'sk' => [
                'available_jobs' => 'Dostupné úlohy',
                'pending_quality_reviews' => 'Čakajúce kontroly kvality',
                'start_job' => 'Začať úlohu',
                'complete_job' => 'Dokončiť úlohu',
                'open' => 'Otvorené',
            ]
        ];

        foreach ($translations as $langCode => $phrases) {
            foreach ($phrases as $key => $value) {
                Translation::updateOrCreate(
                    [
                        'group' => 'main',
                        'key' => $key,
                        'language_code' => $langCode,
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }
        }
    }
}

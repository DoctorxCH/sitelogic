<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\File;

class Translation extends Model
{
    protected $fillable = [
        'group',
        'key',
        'language_code',
        'value',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_code', 'code');
    }

    public static function generateJsonFile($languageCode)
    {
        $translations = static::where('language_code', $languageCode)->get();

        $jsonArray = [];
        foreach ($translations as $translation) {
            // Flatten to "group.key"
            $jsonArray["{$translation->group}.{$translation->key}"] = $translation->value;
        }

        $langPath = lang_path();
        if (!File::exists($langPath)) {
            File::makeDirectory($langPath, 0755, true);
        }

        $filePath = lang_path("{$languageCode}.json");
        File::put($filePath, json_encode($jsonArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

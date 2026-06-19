<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Job;
use App\Models\ProjectType;
use App\Models\ChecklistTemplate;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Define roles
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'bauleiter']);
        Role::firstOrCreate(['name' => 'monteur']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Super Admin', 'password' => bcrypt('password')]
        );
        $admin->assignRole('super_admin');

        $bauleiter = User::firstOrCreate(
            ['email' => 'bauleiter@example.com'],
            ['name' => 'Test Bauleiter', 'password' => bcrypt('password')]
        );
        $bauleiter->assignRole('bauleiter');

        // Create templates
        $inhouseTemplate = ChecklistTemplate::firstOrCreate(['name' => 'Inhouse']);
        if ($inhouseTemplate->items()->count() === 0) {
            $inhouseTemplate->items()->createMany([
                ['question' => 'OTO-Dose korrekt beschriftet und montiert?'],
                ['question' => 'BEP (Building Entry Point) geerdet und gespleisst?'],
                ['question' => 'Steigzone/Rohranlage auf Durchgängigkeit geprüft?'],
                ['question' => 'Optische Dämpfungsmessung (OTDR) Protokoll hochgeladen?'],
                ['question' => 'Installationsanzeige (IA) visiert und abgelegt?'],
            ]);
        }

        $manholeTemplate = ChecklistTemplate::firstOrCreate(['name' => 'Manhole']);
        if ($manholeTemplate->items()->count() === 0) {
            $manholeTemplate->items()->createMany([
                ['question' => 'Muffe wasserdicht verschlossen und fixiert?'],
                ['question' => 'Kabelreserve ordnungsgemäss im Schacht abgelegt?'],
                ['question' => 'Rohrabdichtung (Gas-/Wasserdicht) installiert?'],
                ['question' => 'Schachtreinigung durchgeführt und Protokoll erstellt?'],
                ['question' => 'Beschriftung der Transmissionskabel im Schacht angebracht?'],
            ]);
        }

        // Create Project Type
        $ftthProject = ProjectType::firstOrCreate(['name' => 'FTTH']);

        // Assign templates to FTTH
        $ftthProject->checklistTemplates()->syncWithoutDetaching([
            $inhouseTemplate->id,
            $manholeTemplate->id
        ]);

        // Truncate jobs, checklists, checklist_items so we start fresh for jobs
        \DB::statement('PRAGMA foreign_keys = OFF;');
        Job::truncate();
        \App\Models\Checklist::truncate();
        \App\Models\ChecklistItem::truncate();
        \DB::statement('PRAGMA foreign_keys = ON;');

        // Create a Job. The JobObserver will automatically generate Checklists and Items.
        Job::create([
            'pid' => 'PID-FTTH-001',
            'adresse' => 'Musterstraße 1',
            'projekt_typ' => 'FTTH',
            'bauleiter' => 'Test Bauleiter',
            'technologie' => 'Tech A',
        ]);
    }
}

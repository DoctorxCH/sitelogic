<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ProjectType;
use App\Models\ChecklistTemplate;
use App\Models\ChecklistTemplateItem;
use App\Models\Job;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Rollen erstellen (Englisch)
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // 2. Benutzer anlegen und Rollen zuweisen
        $admin = User::updateOrCreate(
            ['email' => 'martin@sitelogic.sk'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Ckeesjb6&M'),
            ]
        );
        $admin->assignRole($superAdminRole);

        $user = User::updateOrCreate(
            ['email' => 'martin@365jobs.sk'],
            [
                'name' => 'Martin Kurka',
                'password' => Hash::make('Ckeesjb6&M'),
            ]
        );
        $user->assignRole($userRole);

        // 3. Projekttyp anlegen
        $projectType = ProjectType::updateOrCreate(['name' => 'FTTH']);

        // 4. Templates anlegen
        $inhouseTemplate = ChecklistTemplate::updateOrCreate(['name' => 'Inhouse']);
        $manholeTemplate = ChecklistTemplate::updateOrCreate(['name' => 'Manhole']);

        // 5. Template Items für Inhouse
        $inhouseItems = [
            'OTO-Dose korrekt beschriftet und montiert?',
            'BEP (Building Entry Point) geerdet und gespleisst?',
            'Steigzone/Rohranlage auf Durchgängigkeit geprüft?',
            'Optische Dämpfungsmessung (OTDR) Protokoll hochgeladen?',
            'Installationsanzeige (IA) visiert und abgelegt?'
        ];

        foreach ($inhouseItems as $item) {
            ChecklistTemplateItem::updateOrCreate([
                'checklist_template_id' => $inhouseTemplate->id,
                'question' => $item
            ]);
        }

        // 6. Template Items für Manhole
        $manholeItems = [
            'Muffe wasserdicht verschlossen und fixiert?',
            'Kabelreserve ordnungsgemäss im Schacht abgelegt?',
            'Rohrabdichtung (Gas-/Wasserdicht) installiert?',
            'Schachtreinigung durchgeführt und Protokoll erstellt?',
            'Beschriftung der Transmissionskabel im Schacht angebracht?'
        ];

        foreach ($manholeItems as $item) {
            ChecklistTemplateItem::updateOrCreate([
                'checklist_template_id' => $manholeTemplate->id,
                'question' => $item
            ]);
        }

        // 7. Test-Job generieren (triggert den JobObserver)
        Job::create([
            'pid' => '0193930209',
            'adresse' => 'Dorfstrasse 37',
            'projekt_typ' => 'FTTH',
            'bauleiter' => 'Martin Kurka',
            'technologie' => 'FTTH'
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Job;
use App\Models\Checklist;
use App\Models\ChecklistItem;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $role = Role::create(['name' => 'bauleiter']);
        Role::create(['name' => 'monteur']);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('bauleiter');

        $job = Job::create([
            'pid' => 'PID-1',
            'adresse' => 'Musterstraße 1',
            'projekt_typ' => 'Typ A',
            'bauleiter' => 'Test User',
            'technologie' => 'Tech A',
        ]);

        $checklist = Checklist::create([
            'auftragskartei_id' => $job->id,
            'status' => 'open',
            'hauptschalter' => true,
        ]);

        ChecklistItem::create([
            'checklist_id' => $checklist->id,
            'kriterien_ausgeschaltet' => false,
        ]);
    }
}

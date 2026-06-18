<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class JobApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_job_with_assets_via_api(): void
    {
        $payload = [
            'pid' => 'PID-12345',
            'adresse' => 'Musterstraße 1, Berlin',
            'projekt_typ' => 'Tiefbau',
            'bauleiter' => 'Max Mustermann',
            'technologie' => 'FTTH',
            'asset_ids' => ['A-100', 'A-200'],
            'flat_ids' => ['F-001'],
            'kabel_bep_muffentypen' => ['Kabel Typ A', 'Muffe Typ B'],
            'asset_metadaten' => ['color' => 'red', 'length' => 150],
        ];

        $response = $this->postJson('/api/jobs', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'job' => [
                         'id',
                         'pid',
                         'adresse',
                         'projekt_typ',
                         'bauleiter',
                         'technologie',
                         'job_assets',
                     ]
                 ]);

        $this->assertDatabaseHas('jobs', [
            'pid' => 'PID-12345',
            'adresse' => 'Musterstraße 1, Berlin',
        ]);

        $this->assertDatabaseCount('job_assets', 1);
    }
}

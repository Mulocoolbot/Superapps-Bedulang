<?php

namespace Tests\Feature;

use Tests\TestCase;

class InformationServiceTest extends TestCase
{
    public function test_service_pages_are_available_for_all_frontend_layanan_slugs(): void
    {
        $slugs = [
            'pemerintahan',
            'pendidikan',
            'kesehatan',
            'perindagkop-ukm',
            'hukum',
            'sosial',
            'kependudukan',
            'kepegawaian',
            'lingkungan',
            'geografis',
            'pariwisata',
            'data-sektoral',
        ];

        foreach ($slugs as $slug) {
            $this->get('/layanan/' . $slug)
                ->assertOk();
        }
    }
}

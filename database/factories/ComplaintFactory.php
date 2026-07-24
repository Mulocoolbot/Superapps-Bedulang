<?php

namespace Database\Factories;

use App\Models\Complaint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    protected $model = Complaint::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $services = [
            'pemerintahan', 'pendidikan', 'kesehatan', 'perindagkop-ukm',
            'hukum', 'sosial', 'kependudukan', 'kepegawaian', 'pertanian',
            'informasi-publik', 'pariwisata', 'data-sektoral', 'makanan-bergizi-gratis',
        ];
        $statuses = ['pending', 'processing', 'resolved', 'rejected', 'cancelled'];
        
        return [
            'service_slug' => $this->faker->randomElement($services),
            'title' => $this->faker->sentence(mt_rand(4, 10)),
            'description' => $this->faker->paragraph(mt_rand(2, 5)),
            'category' => $this->faker->randomElement(['umum', 'layanan', 'lainnya']),
            'contact_person_name' => $this->faker->name(),
            'contact_email' => $this->faker->safeEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
            'status' => $this->faker->randomElement($statuses),
            'whatsapp_phone' => $this->faker->phoneNumber(),
            'created_at' => clone $this->faker->dateTimeBetween('-3 months', 'now'),
            'updated_at' => function (array $attributes) {
                return $attributes['created_at'];
            },
        ];
    }
}

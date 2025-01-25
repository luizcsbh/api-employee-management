<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'name' => $this->faker->name(),
            'cpf' => $this->faker->numerify('###########'),
            'email' => $this->faker->unique()->safeEmail(),
            'position' => $this->faker->jobTitle(),
            'hired_at' => $this->faker->date(),
        ];
    }
}
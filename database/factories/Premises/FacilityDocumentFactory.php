<?php

declare(strict_types=1);

namespace Database\Factories\Premises;

use App\Models\Premises\FacilityDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacilityDocumentFactory extends Factory
{
    protected $model = FacilityDocument::class;

    public function definition(): array
    {
        return [
            'company_id'       => 1,
            'doc_type'         => 'site_document',
            'title'            => $this->faker->sentence(4),
            'file_path'        => 'documents/' . $this->faker->uuid() . '.pdf',
            'file_name'        => $this->faker->word() . '.pdf',
            'mime_type'        => 'application/pdf',
            'file_size'        => $this->faker->numberBetween(1024, 512000),
            'status'           => 'valid',
            'document_category' => null,
            'is_mandatory'     => false,
        ];
    }
}

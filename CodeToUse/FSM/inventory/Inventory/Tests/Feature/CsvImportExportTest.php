<?php

namespace Modules\Inventory\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CsvImportExportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function items_export_and_import_cycle_works()
    {
        // Export should return a CSV
        $res = $this->get('/inventory/items/export');
        $res->assertStatus(200);
        $this->assertStringContainsString('name,sku,qty,category,unit_price', $res->getContent());

        // Import a tiny CSV
        Storage::fake('local');
        $file = UploadedFile::fake()->createWithContent('items.csv', "id,name,sku,qty,category,unit_price\n,Demo,,5,,1.00\n");
        $res = $this->post('/inventory/items/import', ['csv' => $file]);
        $res->assertStatus(302);
    }
}

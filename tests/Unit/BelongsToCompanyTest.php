<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class BelongsToCompanyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('samples', static function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->timestamps();
        });

        Schema::table('users', static function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable();
            }
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('samples');

        parent::tearDown();
    }

    public function test_sets_company_on_create_from_authenticated_user(): void
    {
        $this->actingAsCompany(7);

        $record = SampleModel::create(['name' => 'example']);

        $this->assertSame(7, $record->company_id);
    }

    public function test_global_scope_filters_by_authenticated_company(): void
    {
        DB::table('samples')->insert([
            ['name' => 'tenant-a', 'company_id' => 5],
            ['name' => 'tenant-b', 'company_id' => 9],
        ]);

        $this->actingAsCompany(5);

        $visible = SampleModel::pluck('company_id')->all();

        $this->assertSame([5], $visible);
    }

    public function test_scope_is_inactive_when_no_authenticated_user(): void
    {
        DB::table('samples')->insert([
            ['name' => 'tenant-a', 'company_id' => 5],
            ['name' => 'tenant-b', 'company_id' => 9],
        ]);

        $all = SampleModel::pluck('company_id')->all();

        $this->assertSame([5, 9], $all);
    }

    public function test_scope_for_company_overrides_default_scope(): void
    {
        DB::table('samples')->insert([
            ['name' => 'tenant-a', 'company_id' => 5],
            ['name' => 'tenant-b', 'company_id' => 9],
        ]);

        $this->actingAsCompany(5);

        $companyNine = SampleModel::forCompany(9)->pluck('company_id')->all();

        $this->assertSame([9], $companyNine);
    }

    protected function actingAsCompany(int $companyId): void
    {
        $user = User::factory()->create([
            'email' => sprintf('company%s-%s@example.com', $companyId, Str::uuid()),
        ]);

        $user->forceFill(['company_id' => $companyId])->saveQuietly();

        Auth::shouldUse('web');
        Auth::setUser($user);
    }
}

class SampleModel extends Model
{
    use BelongsToCompany;

    protected $table = 'samples';

    protected $guarded = [];
}

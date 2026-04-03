<?php

declare(strict_types=1);

use App\Models\Team\AvailabilityOverride;
use App\Models\Team\AvailabilityWindow;
use App\Models\Team\Certification;
use App\Models\Team\SkillDefinition;
use App\Models\Team\SkillRequirement;
use App\Models\Team\TechnicianSkill;
use App\Models\User;
use App\Models\Work\JobType;
use App\Services\Team\CapabilityRegistryService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->service = new CapabilityRegistryService();
});

// ── getSkillProfile ──────────────────────────────────────────────────────────

it('getSkillProfile returns expected keys', function () {
    $user = Mockery::mock(User::class)->makePartial();

    $skillQuery = Mockery::mock();
    $skillQuery->shouldReceive('with')->with('skillDefinition')->andReturnSelf();
    $skillQuery->shouldReceive('get')->andReturn(collect([]));

    $certQuery = Mockery::mock();
    $certQuery->shouldReceive('get')->andReturn(collect([]));

    $availQuery = Mockery::mock();
    $availQuery->shouldReceive('active')->andReturnSelf();
    $availQuery->shouldReceive('get')->andReturn(collect([]));

    $user->shouldReceive('technicianSkills')->andReturn($skillQuery);
    $user->shouldReceive('certifications')->andReturn($certQuery);
    $user->shouldReceive('availabilityWindows')->andReturn($availQuery);

    $profile = $this->service->getSkillProfile($user);

    expect($profile)->toHaveKeys(['skills', 'certifications', 'availability']);
    expect($profile['skills'])->toBeInstanceOf(Collection::class);
});

// ── hasSkill ─────────────────────────────────────────────────────────────────

it('hasSkill returns false when no skill record exists', function () {
    $user = Mockery::mock(User::class)->makePartial();

    $query = Mockery::mock();
    $query->shouldReceive('where')->with('skill_definition_id', 1)->andReturnSelf();
    $query->shouldReceive('active')->andReturnSelf();
    $query->shouldReceive('first')->andReturn(null);
    $user->shouldReceive('technicianSkills')->andReturn($query);

    expect($this->service->hasSkill($user, 1))->toBeFalse();
});

it('hasSkill returns false when skill is expired', function () {
    $user  = Mockery::mock(User::class)->makePartial();
    $skill = Mockery::mock(TechnicianSkill::class)->makePartial();
    $skill->shouldReceive('meetsLevel')->with('competent')->andReturn(false);

    $query = Mockery::mock();
    $query->shouldReceive('where')->with('skill_definition_id', 1)->andReturnSelf();
    $query->shouldReceive('active')->andReturnSelf();
    $query->shouldReceive('first')->andReturn($skill);
    $user->shouldReceive('technicianSkills')->andReturn($query);

    expect($this->service->hasSkill($user, 1))->toBeFalse();
});

it('hasSkill returns true when skill level meets minimum', function () {
    $user  = Mockery::mock(User::class)->makePartial();
    $skill = Mockery::mock(TechnicianSkill::class)->makePartial();
    $skill->shouldReceive('meetsLevel')->with('competent')->andReturn(true);

    $query = Mockery::mock();
    $query->shouldReceive('where')->with('skill_definition_id', 7)->andReturnSelf();
    $query->shouldReceive('active')->andReturnSelf();
    $query->shouldReceive('first')->andReturn($skill);
    $user->shouldReceive('technicianSkills')->andReturn($query);

    expect($this->service->hasSkill($user, 7, 'competent'))->toBeTrue();
});

// ── getCertificationStatus ────────────────────────────────────────────────────

it('getCertificationStatus returns none when no cert exists', function () {
    $user  = Mockery::mock(User::class)->makePartial();
    $query = Mockery::mock();
    $query->shouldReceive('where')->with('certification_name', 'ISO9001')->andReturnSelf();
    $query->shouldReceive('orderByDesc')->with('issued_at')->andReturnSelf();
    $query->shouldReceive('first')->andReturn(null);
    $user->shouldReceive('certifications')->andReturn($query);

    expect($this->service->getCertificationStatus($user, 'ISO9001'))->toBe('none');
});

it('getCertificationStatus returns expired when expiry has passed', function () {
    $cert = Mockery::mock(Certification::class)->makePartial();
    $cert->status     = 'active';
    $cert->expires_at = Carbon::now()->subDay();

    $user  = Mockery::mock(User::class)->makePartial();
    $query = Mockery::mock();
    $query->shouldReceive('where')->with('certification_name', 'FGas')->andReturnSelf();
    $query->shouldReceive('orderByDesc')->with('issued_at')->andReturnSelf();
    $query->shouldReceive('first')->andReturn($cert);
    $user->shouldReceive('certifications')->andReturn($query);

    expect($this->service->getCertificationStatus($user, 'FGas'))->toBe('expired');
});

it('getCertificationStatus returns active for valid cert', function () {
    $cert = Mockery::mock(Certification::class)->makePartial();
    $cert->status     = 'active';
    $cert->expires_at = Carbon::now()->addYear();

    $user  = Mockery::mock(User::class)->makePartial();
    $query = Mockery::mock();
    $query->shouldReceive('where')->with('certification_name', 'FGas')->andReturnSelf();
    $query->shouldReceive('orderByDesc')->with('issued_at')->andReturnSelf();
    $query->shouldReceive('first')->andReturn($cert);
    $user->shouldReceive('certifications')->andReturn($query);

    expect($this->service->getCertificationStatus($user, 'FGas'))->toBe('active');
});

// ── hasCertification ──────────────────────────────────────────────────────────

it('hasCertification returns false for expired cert', function () {
    $cert = Mockery::mock(Certification::class)->makePartial();
    $cert->status     = 'active';
    $cert->expires_at = Carbon::now()->subDay(); // already past

    $user  = Mockery::mock(User::class)->makePartial();
    $query = Mockery::mock();
    $query->shouldReceive('where')->with('certification_name', 'COSHH')->andReturnSelf();
    $query->shouldReceive('orderByDesc')->with('issued_at')->andReturnSelf();
    $query->shouldReceive('first')->andReturn($cert);
    $user->shouldReceive('certifications')->andReturn($query);

    expect($this->service->hasCertification($user, 'COSHH'))->toBeFalse();
});

// ── isAvailable — override takes precedence ────────────────────────────────

it('isAvailable returns override value when date override exists', function () {
    $datetime = Carbon::parse('2026-04-07 10:00:00');

    $override            = Mockery::mock(AvailabilityOverride::class)->makePartial();
    $override->available = false;

    $overrideQuery = Mockery::mock();
    $overrideQuery->shouldReceive('where')->with('date', '2026-04-07')->andReturnSelf();
    $overrideQuery->shouldReceive('first')->andReturn($override);

    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('availabilityOverrides')->andReturn($overrideQuery);

    expect($this->service->isAvailable($user, $datetime))->toBeFalse();
});

it('isAvailable falls back to window when no override exists', function () {
    $datetime = Carbon::parse('2026-04-07 10:00:00'); // Tuesday = day 2

    $overrideQuery = Mockery::mock();
    $overrideQuery->shouldReceive('where')->with('date', '2026-04-07')->andReturnSelf();
    $overrideQuery->shouldReceive('first')->andReturn(null);

    $windowQuery = Mockery::mock();
    $windowQuery->shouldReceive('active')->andReturnSelf();
    $windowQuery->shouldReceive('forDay')->with(2)->andReturnSelf();
    $windowQuery->shouldReceive('where')->with('start_time', '<=', '10:00:00')->andReturnSelf();
    $windowQuery->shouldReceive('where')->with('end_time', '>=', '10:00:00')->andReturnSelf();
    $windowQuery->shouldReceive('exists')->andReturn(true);

    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('availabilityOverrides')->andReturn($overrideQuery);
    $user->shouldReceive('availabilityWindows')->andReturn($windowQuery);

    expect($this->service->isAvailable($user, $datetime))->toBeTrue();
});

// ── TechnicianSkill ordinal helpers ──────────────────────────────────────────

it('TechnicianSkill meetsLevel respects ordinal hierarchy', function () {
    $skill        = new TechnicianSkill();
    $skill->level = 'proficient';

    expect($skill->meetsLevel('trainee'))->toBeTrue();
    expect($skill->meetsLevel('competent'))->toBeTrue();
    expect($skill->meetsLevel('proficient'))->toBeTrue();
    expect($skill->meetsLevel('expert'))->toBeFalse();
});

it('TechnicianSkill isExpired returns true when expires_at is past', function () {
    $skill            = new TechnicianSkill();
    $skill->expires_at = Carbon::now()->subDay();

    expect($skill->isExpired())->toBeTrue();
});

it('TechnicianSkill isExpired returns false when expires_at is future', function () {
    $skill            = new TechnicianSkill();
    $skill->expires_at = Carbon::now()->addYear();

    expect($skill->isExpired())->toBeFalse();
});

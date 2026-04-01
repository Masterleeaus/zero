<?php

declare(strict_types=1);

use App\Extensions\BusinessSuite\System\Enums\PlatformEnum;
use App\Extensions\BusinessSuite\System\Enums\StatusEnum;
use App\Extensions\BusinessSuite\System\Models\BusinessSuitePost;
use App\Helpers\Classes\MarketplaceHelper;

beforeEach(function () {
    if (! MarketplaceHelper::isRegistered('business-suite')) {
        $this->markTestSkipped('BusinessSuite extension is not registered.');
    }
});

it('has images in fillable array', function () {
    $post = new BusinessSuitePost;

    expect($post->getFillable())->toContain('images');
});

it('casts images to json', function () {
    $post = new BusinessSuitePost;
    $casts = $post->getCasts();

    expect($casts)->toHaveKey('images')
        ->and($casts['images'])->toBe('json');
});

it('can set images as array', function () {
    $post = new BusinessSuitePost;
    $post->images = ['/uploads/img1.jpg', '/uploads/img2.jpg'];

    expect($post->images)->toBeArray()
        ->and($post->images)->toHaveCount(2)
        ->and($post->images[0])->toBe('/uploads/img1.jpg');
});

it('returns false for hasMultipleImages with single image', function () {
    $post = new BusinessSuitePost;
    $post->images = ['/uploads/img1.jpg'];

    expect($post->hasMultipleImages())->toBeFalse();
});

it('returns true for hasMultipleImages with multiple images', function () {
    $post = new BusinessSuitePost;
    $post->images = ['/uploads/img1.jpg', '/uploads/img2.jpg', '/uploads/img3.jpg'];

    expect($post->hasMultipleImages())->toBeTrue();
});

it('returns false for hasMultipleImages with null images', function () {
    $post = new BusinessSuitePost;
    $post->images = null;

    expect($post->hasMultipleImages())->toBeFalse();
});

it('returns false for hasMultipleImages with empty array', function () {
    $post = new BusinessSuitePost;
    $post->images = [];

    expect($post->hasMultipleImages())->toBeFalse();
});

it('includes images when filling attributes', function () {
    $post = new BusinessSuitePost;
    $post->fill([
        'user_id'               => 1,
        'business_suite_platform' => PlatformEnum::facebook->value,
        'content'               => 'Test carousel content',
        'tone'                  => 'default',
        'status'                => StatusEnum::scheduled->value,
        'image'                 => '/uploads/img1.jpg',
        'images'                => ['/uploads/img1.jpg', '/uploads/img2.jpg'],
    ]);

    expect($post->getAttributes())->toHaveKey('images')
        ->and($post->getAttributes()['image'])->toBe('/uploads/img1.jpg');
});

it('keeps image and images in sync via fill', function () {
    $images = ['/uploads/img1.jpg', '/uploads/img2.jpg', '/uploads/img3.jpg'];
    $post = new BusinessSuitePost;
    $post->fill([
        'user_id'               => 1,
        'business_suite_platform' => PlatformEnum::instagram->value,
        'content'               => 'Carousel post',
        'tone'                  => 'default',
        'status'                => StatusEnum::scheduled->value,
        'image'                 => $images[0],
        'images'                => $images,
    ]);

    expect($post->getAttributes()['image'])->toBe($images[0]);
});

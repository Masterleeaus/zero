<?php

declare(strict_types=1);

use App\Extensions\BusinessSuite\System\Enums\PostTypeEnum;
use App\Extensions\BusinessSuiteAgent\System\Models\BusinessSuiteAgentPost;

it('has publishing_type in fillable array', function () {
    $post = new BusinessSuiteAgentPost;

    expect($post->getFillable())->toContain('publishing_type');
});

it('casts publishing_type to PostTypeEnum', function () {
    $post = new BusinessSuiteAgentPost;
    $casts = $post->getCasts();

    expect($casts)->toHaveKey('publishing_type')
        ->and($casts['publishing_type'])->toBe(PostTypeEnum::class);
});

it('can set publishing_type to post', function () {
    $post = new BusinessSuiteAgentPost;
    $post->publishing_type = PostTypeEnum::Post;

    expect($post->publishing_type)->toBe(PostTypeEnum::Post);
});

it('can set publishing_type to story', function () {
    $post = new BusinessSuiteAgentPost;
    $post->publishing_type = PostTypeEnum::Story;

    expect($post->publishing_type)->toBe(PostTypeEnum::Story)
        ->and($post->publishing_type->label())->toBe('Story');
});

it('includes publishing_type when filling attributes', function () {
    $post = new BusinessSuiteAgentPost;
    $post->fill([
        'agent_id'        => 1,
        'platform_id'     => 1,
        'content'         => 'Test content',
        'post_type'       => 'single_image',
        'publishing_type' => 'story',
        'status'          => BusinessSuiteAgentPost::STATUS_DRAFT,
    ]);

    expect($post->getAttributes())->toHaveKey('publishing_type')
        ->and($post->getAttributes()['publishing_type'])->toBe('story');
});

it('keeps publishing_type separate from post_type', function () {
    $post = new BusinessSuiteAgentPost;
    $post->fill([
        'agent_id'        => 1,
        'platform_id'     => 1,
        'content'         => 'Test content',
        'post_type'       => 'carousel',
        'publishing_type' => 'story',
        'status'          => BusinessSuiteAgentPost::STATUS_DRAFT,
    ]);

    expect($post->getAttributes()['post_type'])->toBe('carousel')
        ->and($post->getAttributes()['publishing_type'])->toBe('story');
});

it('defaults publishing_type to post', function () {
    $post = new BusinessSuiteAgentPost;
    $post->fill([
        'agent_id'    => 1,
        'platform_id' => 1,
        'content'     => 'Test content',
        'post_type'   => 'single_image',
        'status'      => BusinessSuiteAgentPost::STATUS_DRAFT,
    ]);

    expect($post->publishing_type)->toBeNull();
});

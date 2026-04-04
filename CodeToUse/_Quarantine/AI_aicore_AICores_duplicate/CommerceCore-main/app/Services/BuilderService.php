<?php

namespace App\Services;

use App\Models\BuilderPage;
use App\Models\BuilderSection;
use App\Models\BuilderContent;
use App\Models\Store;
use Illuminate\Support\Str;

class BuilderService
{
    public function createPage(Store $store, array $data): BuilderPage
    {
        $data['store_id'] = $store->id;
        $data['slug'] = $data['slug'] ?? Str::slug($data['page_name']);

        // If setting as homepage, unset other homepage
        if (!empty($data['is_homepage'])) {
            BuilderPage::where('store_id', $store->id)->update(['is_homepage' => false]);
            \Illuminate\Support\Facades\Cache::forget("storefront:{$store->id}:homepage");
        }

        return BuilderPage::create($data);
    }

    public function updatePage(BuilderPage $page, array $data): BuilderPage
    {
        if (!empty($data['is_homepage'])) {
            BuilderPage::where('store_id', $page->store_id)
                ->where('id', '!=', $page->id)
                ->update(['is_homepage' => false]);
        }

        $page->update($data);
        
        if ($page->is_homepage) {
            \Illuminate\Support\Facades\Cache::forget("storefront:{$page->store_id}:homepage");
        }

        return $page->fresh();
    }

    public function addSection(BuilderPage $page, string $type): BuilderSection
    {
        $maxPosition = $page->sections()->max('position') ?? 0;

        $section = BuilderSection::create([
            'page_id' => $page->id,
            'type' => $type,
            'position' => $maxPosition + 1,
            'is_active' => true,
        ]);

        // Add default content
        $defaults = BuilderSection::defaultContent($type);
        foreach ($defaults as $key => $value) {
            BuilderContent::create([
                'section_id' => $section->id,
                'key' => $key,
                'value' => $value,
            ]);
        }

        if ($page->is_homepage) {
            \Illuminate\Support\Facades\Cache::forget("storefront:{$page->store_id}:homepage");
        }

        return $section->load('contents');
    }

    public function updateSectionContent(BuilderSection $section, array $contents): BuilderSection
    {
        foreach ($contents as $key => $value) {
            if (is_numeric($key)) {
                BuilderContent::where('id', $key)
                    ->where('section_id', $section->id)
                    ->update(['value' => $value]);
            } else {
                BuilderContent::updateOrCreate(
                    ['section_id' => $section->id, 'key' => $key],
                    ['value' => $value]
                );
            }
        }

        if ($section->page->is_homepage) {
            \Illuminate\Support\Facades\Cache::forget("storefront:{$section->page->store_id}:homepage");
        }

        return $section->load('contents');
    }

    public function reorderSections(BuilderPage $page, array $order): void
    {
        foreach ($order as $position => $sectionId) {
            BuilderSection::where('id', $sectionId)
                ->where('page_id', $page->id)
                ->update(['position' => $position]);
        }

        if ($page->is_homepage) {
            \Illuminate\Support\Facades\Cache::forget("storefront:{$page->store_id}:homepage");
        }
    }

    public function toggleSection(BuilderSection $section): BuilderSection
    {
        $section->update(['is_active' => !$section->is_active]);

        if ($section->page->is_homepage) {
            \Illuminate\Support\Facades\Cache::forget("storefront:{$section->page->store_id}:homepage");
        }

        return $section->fresh();
    }

    public function deleteSection(BuilderSection $section): bool
    {
        $page = $section->page;
        $deleted = $section->delete();

        if ($deleted && $page->is_homepage) {
            \Illuminate\Support\Facades\Cache::forget("storefront:{$page->store_id}:homepage");
        }

        return $deleted;
    }

    public function getHomepage(Store $store): ?BuilderPage
    {
        return BuilderPage::where('store_id', $store->id)
            ->where('is_homepage', true)
            ->with('activeSections.contents')
            ->first();
    }
}

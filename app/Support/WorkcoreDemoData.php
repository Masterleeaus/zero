<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Collection;

class WorkcoreDemoData
{
    public static function customerContacts(): Collection
    {
        return collect([
            ['name' => 'Alex Murray', 'role' => 'Facilities Lead', 'email' => 'alex@acme.example', 'phone' => '+1 202 555 0145'],
            ['name' => 'Priya Patel', 'role' => 'Office Manager', 'email' => 'priya@acme.example', 'phone' => '+1 202 555 0192'],
        ]);
    }

    public static function customerNotes(): Collection
    {
        return collect([
            ['title' => 'Onboarding summary', 'body' => 'Walkthrough completed. Prefers early morning visits.', 'pinned' => true, 'author' => 'Maria', 'created_at' => '2026-03-20'],
            ['title' => 'Access instructions', 'body' => 'Use loading dock entrance. Keycard in lockbox #42.', 'pinned' => false, 'author' => 'Brian', 'created_at' => '2026-03-18'],
        ]);
    }

    public static function customerDocuments(): Collection
    {
        return collect([
            ['name' => 'Service Agreement.pdf', 'size' => '1.2 MB', 'uploaded_by' => 'Maria', 'uploaded_at' => '2026-03-01'],
            ['name' => 'Insurance Certificate.pdf', 'size' => '640 KB', 'uploaded_by' => 'Finance Bot', 'uploaded_at' => '2026-02-24'],
        ]);
    }

    public static function deals(): Collection
    {
        return collect([
            ['id' => 'DL-2401', 'title' => 'HQ Nightly Clean', 'value' => 4200, 'stage' => 'prospecting', 'owner' => 'Nora', 'customer' => 'Acme Corp', 'updated_at' => '2026-03-27'],
            ['id' => 'DL-2402', 'title' => 'Warehouse Deep Clean', 'value' => 7800, 'stage' => 'proposal', 'owner' => 'Devon', 'customer' => 'Blue Logistics', 'updated_at' => '2026-03-25'],
            ['id' => 'DL-2403', 'title' => 'Quarterly Floor Care', 'value' => 6100, 'stage' => 'negotiation', 'owner' => 'Nora', 'customer' => 'Acme Corp', 'updated_at' => '2026-03-24'],
            ['id' => 'DL-2404', 'title' => 'Campus Day Porter', 'value' => 12500, 'stage' => 'won', 'owner' => 'Sam', 'customer' => 'North University', 'updated_at' => '2026-03-22'],
            ['id' => 'DL-2405', 'title' => 'Event Clean-up', 'value' => 2100, 'stage' => 'lost', 'owner' => 'Mina', 'customer' => 'City Events', 'updated_at' => '2026-03-20'],
        ]);
    }

    public static function creditNotes(): Collection
    {
        return collect([
            ['number' => 'CN-1201', 'status' => 'draft', 'customer' => 'Acme Corp', 'total' => 320.00, 'issued_at' => '2026-03-10'],
            ['number' => 'CN-1202', 'status' => 'issued', 'customer' => 'Blue Logistics', 'total' => 540.50, 'issued_at' => '2026-03-12'],
            ['number' => 'CN-1203', 'status' => 'applied', 'customer' => 'North University', 'total' => 180.75, 'issued_at' => '2026-03-14'],
        ]);
    }

    public static function quoteTemplates(): Collection
    {
        return collect([
            ['name' => 'Office Cleaning Bundle', 'category' => 'Commercial', 'items' => 6, 'updated_at' => '2026-03-21'],
            ['name' => 'Event Clean', 'category' => 'Events', 'items' => 4, 'updated_at' => '2026-03-18'],
        ]);
    }

    public static function bankAccounts(): Collection
    {
        return collect([
            ['name' => 'Operating Account', 'bank' => 'First National', 'last4' => '4821', 'currency' => 'USD', 'default' => true],
            ['name' => 'Savings Reserve', 'bank' => 'First National', 'last4' => '9134', 'currency' => 'USD', 'default' => false],
        ]);
    }

    public static function taxes(): Collection
    {
        return collect([
            ['name' => 'Standard VAT', 'rate' => 12.5, 'default' => true],
            ['name' => 'Zero rated', 'rate' => 0, 'default' => false],
            ['name' => 'Reduced Rate', 'rate' => 5, 'default' => false],
        ]);
    }

    public static function zones(): Collection
    {
        return collect([
            ['name' => 'Downtown', 'code' => 'DT', 'sites' => 18],
            ['name' => 'Waterfront', 'code' => 'WF', 'sites' => 9],
            ['name' => 'Airport', 'code' => 'AP', 'sites' => 6],
        ]);
    }

    public static function cleanerProfile(): Collection
    {
        return collect([
            'name' => 'Jamie Lee',
            'role' => 'Senior Cleaner',
            'phone' => '+1 202 555 0199',
            'email' => 'jamie.lee@example.test',
            'zones' => ['Downtown', 'Airport'],
            'certifications' => ['OSHA', 'Covid-19 Sanitisation', 'Floor Care'],
            'bio' => 'Lead cleaner focused on healthcare and high-traffic facilities.',
        ]);
    }

    public static function timesheets(): Collection
    {
        return collect([
            ['number' => 'TS-301', 'period' => 'Mar 17 - Mar 23', 'status' => 'pending', 'hours' => 38.5, 'total' => 1155.00],
            ['number' => 'TS-302', 'period' => 'Mar 24 - Mar 30', 'status' => 'approved', 'hours' => 40.0, 'total' => 1200.00],
            ['number' => 'TS-303', 'period' => 'Mar 31 - Apr 6', 'status' => 'submitted', 'hours' => 12.5, 'total' => 375.00],
        ]);
    }

    public static function lineItemsSeed(): Collection
    {
        return collect([
            ['description' => __('Standard clean'), 'quantity' => 1, 'unit_price' => 150, 'tax_rate' => 12, 'sort_order' => 0],
            ['description' => __('Supplies'), 'quantity' => 1, 'unit_price' => 35, 'tax_rate' => 0, 'sort_order' => 1],
        ]);
    }
}

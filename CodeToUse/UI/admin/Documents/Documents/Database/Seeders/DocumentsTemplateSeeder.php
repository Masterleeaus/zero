<?php

namespace Modules\Documents\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Documents\Entities\DocumentTemplate;

class DocumentsTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'SWMS – Working at Heights',
                'slug' => 'swms-working-at-heights',
                'category' => 'SWMS',
                'description' => 'Working at heights SWMS (generic).',
                'body_markdown' => '# SWMS – Working at Heights\n\n**Job Ref:** {{job.ref}}\n**Site:** {{site.address}}\n\n## Hazards\n- Fall from height\n- Falling objects\n\n## Controls\n- Identify fall risks: {{heights.risk_assess}}\n- Use suitable access equipment: {{heights.access_equipment}}\n- Edge protection / fall arrest: {{heights.fall_protection}}\n- Exclusion zone below: {{site.exclusion_zone}}\n\n**PPE:** {{ppe.list}}\n\n**Sign-off:** {{sign.off.block}}\n',
                'placeholders' => ['job.ref','site.address','heights.risk_assess','heights.access_equipment','heights.fall_protection','site.exclusion_zone','ppe.list','sign.off.block'],
                'tags' => ['swms','heights'],
                'trade' => null,
                'role_key' => null,
                'is_active' => true,
            ],
            [
                'name' => 'SWMS – Manual Handling',
                'slug' => 'swms-manual-handling',
                'category' => 'SWMS',
                'description' => 'Manual handling SWMS (generic).',
                'body_markdown' => '# SWMS – Manual Handling\n\n**Job Ref:** {{job.ref}}\n\n## Hazards\n- Musculoskeletal strain\n- Crush / pinch injuries\n\n## Controls\n- Assess load: {{manual.assess_load}}\n- Use aids: {{manual.aids}}\n- Team lift: {{manual.team_lift}}\n- Clear path: {{site.housekeeping}}\n\n**PPE:** {{ppe.list}}\n\n**Sign-off:** {{sign.off.block}}\n',
                'placeholders' => ['job.ref','manual.assess_load','manual.aids','manual.team_lift','site.housekeeping','ppe.list','sign.off.block'],
                'tags' => ['swms','manual-handling'],
                'trade' => null,
                'role_key' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Electrician – SWMS Electrical Isolation & Testing',
                'slug' => 'swms-electrician-isolation-testing',
                'category' => 'SWMS',
                'description' => 'SWMS focused on isolation, LOTO and testing.',
                'body_markdown' => '# SWMS – Electrical Isolation & Testing\n\n**Job Ref:** {{job.ref}}\n\n## Hazards\n- Electric shock / arc flash\n- Inadvertent re-energisation\n\n## Controls\n- Isolate supply: {{electrical.isolate}}\n- Lock-out / tag-out: {{electrical.loto}}\n- Test before touch: {{electrical.test}}\n- Insulated tools: {{tools.insulated}}\n- RCD protection: {{electrical.rcd}}\n\n**PPE:** {{ppe.list}}\n\n## Emergency\n- CPR / first aid: {{emergency.cpr}}\n- Escalation plan: {{emergency.plan}}\n\n**Sign-off:** {{sign.off.block}}\n',
                'placeholders' => ['job.ref','electrical.isolate','electrical.loto','electrical.test','tools.insulated','electrical.rcd','ppe.list','emergency.cpr','emergency.plan','sign.off.block'],
                'tags' => ['swms','electrical','isolation'],
                'trade' => 'electrician',
                'role_key' => 'tradie',
                'is_active' => true,
            ],
            [
                'name' => 'Plumber – SWMS Confined Space (if applicable)',
                'slug' => 'swms-plumber-confined-space',
                'category' => 'SWMS',
                'description' => 'Confined space SWMS scaffold (only where applicable).',
                'body_markdown' => '# SWMS – Confined Space (Plumbing)\n\n**Job Ref:** {{job.ref}}\n\n## Hazards\n- Atmospheric hazards\n- Restricted movement\n- Engulfment\n\n## Controls\n- Confirm permit/authorisation: {{confined.permit}}\n- Atmospheric testing: {{confined.gas_test}}\n- Ventilation: {{confined.ventilation}}\n- Rescue plan in place: {{confined.rescue_plan}}\n- Spotter / communications: {{confined.spotter}}\n\n**PPE:** {{ppe.list}}\n\n**Sign-off:** {{sign.off.block}}\n',
                'placeholders' => ['job.ref','confined.permit','confined.gas_test','confined.ventilation','confined.rescue_plan','confined.spotter','ppe.list','sign.off.block'],
                'tags' => ['swms','confined-space','plumbing'],
                'trade' => 'plumber',
                'role_key' => 'tradie',
                'is_active' => true,
            ],
            [
                'name' => 'Quote Follow-up (No Response)',
                'slug' => 'quote-followup-no-response',
                'category' => null,
                'description' => 'Customer-friendly follow up message when no response.',
                'body_markdown' => '# Following up your quote\n\nHi {{customer.name}},\n\nJust checking in on the quote for {{job.summary}}.\n\n- Proposed start: {{job.proposed_start}}\n- Scope summary: {{scope.summary}}\n\nIf you’d like to proceed or adjust anything, reply here and we’ll update it.\n\nThanks,\n{{company.name}}\n',
                'placeholders' => ['customer.name','job.summary','job.proposed_start','scope.summary','company.name'],
                'tags' => ['quote','followup'],
                'trade' => null,
                'role_key' => 'office',
                'is_active' => true,
            ],
            [
                'name' => 'Plumber – Job Scope & Exclusions (Residential)',
                'slug' => 'plumber-scope-exclusions',
                'category' => null,
                'description' => 'Structured scope and exclusions for plumbing jobs.',
                'body_markdown' => '# Plumbing Job Scope\n\n## Scope\n- {{scope.items}}\n\n## Assumptions\n- {{assumptions.items}}\n\n## Exclusions\n- {{exclusions.items}}\n\n## Access Notes\n- {{site.access_notes}}\n\n## Next steps\n- {{next.steps}}\n',
                'placeholders' => ['scope.items','assumptions.items','exclusions.items','site.access_notes','next.steps'],
                'tags' => ['scope','exclusions','quote'],
                'trade' => 'plumber',
                'role_key' => 'tradie',
                'is_active' => true,
            ],
            [
                'name' => 'Builder – Site Induction & Safety Brief',
                'slug' => 'builder-site-induction-brief',
                'category' => null,
                'description' => 'Site induction checklist and brief for crews/subbies.',
                'body_markdown' => '# Site Induction & Safety Brief\n\n**Site:** {{site.address}}\n\n## Site Rules\n- Access/egress: {{site.access}}\n- Amenities: {{site.amenities}}\n- PPE minimum: {{ppe.minimum}}\n\n## Hazards on site\n- {{hazards.list}}\n\n## Emergency\n- Assembly point: {{emergency.assembly}}\n- Contacts: {{emergency.contacts}}\n\n## Sign-in\n- {{crew.signin_table}}\n',
                'placeholders' => ['site.address','site.access','site.amenities','ppe.minimum','hazards.list','emergency.assembly','emergency.contacts','crew.signin_table'],
                'tags' => ['induction','safety','site'],
                'trade' => 'builder',
                'role_key' => 'office',
                'is_active' => true,
            ],
            [
                'name' => 'Cleaner – Property Entry Checklist',
                'slug' => 'cleaner-property-entry-checklist',
                'category' => null,
                'description' => 'Checklist for access, hazards and before/after photos.',
                'body_markdown' => '# Property Entry Checklist\n\n**Property:** {{site.address}}\n\n## Access\n- Keys/lockbox: {{access.keys}}\n- Alarm: {{access.alarm}}\n\n## Hazards\n- Pets: {{hazards.pets}}\n- Chemicals/storage: {{hazards.chemicals}}\n- Slip/trip risks: {{hazards.slip_trip}}\n\n## Photos\n- Before: {{photos.before}}\n- After: {{photos.after}}\n\n## Notes\n- {{notes.general}}\n',
                'placeholders' => ['site.address','access.keys','access.alarm','hazards.pets','hazards.chemicals','hazards.slip_trip','photos.before','photos.after','notes.general'],
                'tags' => ['checklist','property'],
                'trade' => 'cleaner',
                'role_key' => 'tradie',
                'is_active' => true,
            ],
            [
                'name' => 'Property Manager – Routine Inspection Report',
                'slug' => 'pm-routine-inspection-report',
                'category' => null,
                'description' => 'Routine inspection report with defects/actions.',
                'body_markdown' => '# Routine Inspection Report\n\n**Property:** {{site.address}}\n\n## Summary\n- Overall condition: {{inspection.condition}}\n\n## Items Checked\n- Smoke alarms: {{inspection.smoke_alarms}}\n- Water leaks: {{inspection.leaks}}\n- Electrical: {{inspection.electrical}}\n- Cleanliness: {{inspection.cleanliness}}\n\n## Defects / Actions\n- {{defects.list}}\n\n## Photos\n- {{photos.list}}\n',
                'placeholders' => ['site.address','inspection.condition','inspection.smoke_alarms','inspection.leaks','inspection.electrical','inspection.cleanliness','defects.list','photos.list'],
                'tags' => ['inspection','report'],
                'trade' => 'property_manager',
                'role_key' => 'office',
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            // idempotent: update-or-create by slug
            DocumentTemplate::query()->updateOrCreate(
                ['slug' => $item['slug']],
                $item
            );
        }
    }
}

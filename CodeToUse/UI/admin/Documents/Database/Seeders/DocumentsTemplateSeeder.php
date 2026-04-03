<?php

namespace Modules\Documents\Database\Seeders;
use Illuminate\Database\Seeder;
use Modules\Documents\Entities\DocumentTemplate;

class DocumentsTemplateSeeder extends Seeder
{
    public function run()
    {
        $items = [
            ['name'=>'SWMS – Working at Heights','slug'=>'swms-working-at-heights','category'=>'SWMS','description'=>'Heights work','body_markdown'=>'# SWMS – Working at Heights','placeholders'=>[]],
            ['name'=>'Quote Follow-up (No Response)','slug'=>'quote-follow-up-no-response','category'=>'Sales','description'=>'Follow-up','body_markdown'=>'# Following up your quote','placeholders'=>[]],
        
            [
                'name' => 'SWMS – Manual Handling',
                'slug' => 'swms-manual-handling',
                'category' => 'SWMS',
                'subcategory' => 'General Construction',
                'description' => 'Lifting, carrying, pushing/pulling; team lifts and aids.',
                'body_markdown' => "# SWMS – Manual Handling
**Job/Ref:** {{job.ref}}  |  **Site:** {{site.address}}

## Tasks
{{tasks.list}}

## Hazards
- Musculoskeletal strain
- Pinch/crush injuries
- Trip hazards

## Controls
- Assess load: {{manual.assess_load}}
- Use aids: {{manual.aids}}
- Team lift: {{manual.team_lift}}
- Keep path clear: {{site.housekeeping}}

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'job.ref','site.address','tasks.list','manual.assess_load','manual.aids','manual.team_lift','site.housekeeping','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Traffic Management (Work Near Roads)',
                'slug' => 'swms-traffic-management',
                'category' => 'SWMS',
                'subcategory' => 'General Construction',
                'description' => 'TMP setup, spotters, signage, and separations.',
                'body_markdown' => "# SWMS – Traffic Management
**Road Class:** {{traffic.road_class}} | **TMP #:** {{traffic.tmp_no}} | **Spotter:** {{people.spotter}}

## Controls
- Signage per MUTCD/Austroads: {{traffic.sign_plan}}
- Delineation/barriers: {{traffic.barriers}}
- Speed management: {{traffic.speed}}
- Night work lighting: {{traffic.lighting}}

**Communications:** {{comms.method}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'traffic.road_class','traffic.tmp_no','people.spotter','traffic.sign_plan','traffic.barriers','traffic.speed','traffic.lighting','comms.method','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Hot Works (Welding/Cutting)',
                'slug' => 'swms-hot-works',
                'category' => 'SWMS',
                'subcategory' => 'Plant & Equipment',
                'description' => 'Permits, fire watch, gas cylinders, heat sources.',
                'body_markdown' => "# SWMS – Hot Works
**Permit #:** {{permits.hotwork}} | **Method:** {{hotworks.method}}

## Hazards
- Fire/Explosion
- Fumes and UV radiation
- Cylinder handling/falls

## Controls
- Remove combustibles / fire blankets: {{hotworks.blankets}}
- Fire watch & extinguisher: {{hotworks.fire_watch}}
- Gas storage upright/secured: {{hotworks.cylinders}}
- Ventilation: {{hotworks.ventilation}}

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'permits.hotwork','hotworks.method','hotworks.blankets','hotworks.fire_watch','hotworks.cylinders','hotworks.ventilation','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Elevated Work Platform (EWP)',
                'slug' => 'swms-ewp',
                'category' => 'SWMS',
                'subcategory' => 'Heights',
                'description' => 'Prestart, harnesses, ground conditions, exclusion zones.',
                'body_markdown' => "# SWMS – EWP
**Model/ID:** {{ewp.model}} | **SWL:** {{ewp.swl}} | **Operator:** {{people.operator}}

## Controls
- Prestart inspection: {{ewp.prestart}}
- Harness & lanyard anchored: {{ewp.harness}}
- Ground conditions & slope: {{ewp.ground}}
- Exclusion/barricades: {{ewp.exclusion}}
- Weather limits: {{ewp.weather}}

**Rescue Plan:** {{rescue.plan}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'ewp.model','ewp.swl','people.operator','ewp.prestart','ewp.harness','ewp.ground','ewp.exclusion','ewp.weather','rescue.plan','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Ladder Use',
                'slug' => 'swms-ladder-use',
                'category' => 'SWMS',
                'subcategory' => 'Heights',
                'description' => 'Selection, setup, 3-points-of-contact, securing.',
                'body_markdown' => "# SWMS – Ladder Use
**Type:** {{ladder.type}} | **Height:** {{ladder.height}}

## Controls
- Inspect before use: {{ladder.inspect}}
- Correct angle & secure: {{ladder.secure}}
- 3-point contact; no overreach
- Keep base clear; no top rung use

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'ladder.type','ladder.height','ladder.inspect','ladder.secure','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Minor Demolition (Non-Structural)',
                'slug' => 'swms-minor-demolition',
                'category' => 'SWMS',
                'subcategory' => 'General Construction',
                'description' => 'Dust, noise, services isolation, debris handling.',
                'body_markdown' => "# SWMS – Minor Demolition (Non-Structural)
**Area:** {{demo.area}} | **Services isolated:** {{demo.services_isolated}}

## Hazards
- Hidden services
- Falling debris
- Silica/dust and noise

## Controls
- Isolation & permits: {{demo.isolation}}
- Dust suppression: {{demo.dust}}
- Debris chute/containment: {{demo.debris}}
- Waste segregation: {{demo.waste}}

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'demo.area','demo.services_isolated','demo.isolation','demo.dust','demo.debris','demo.waste','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Concrete Cutting/Drilling',
                'slug' => 'swms-concrete-cutting',
                'category' => 'SWMS',
                'subcategory' => 'Plant & Equipment',
                'description' => 'Silica control, noise, water management, anchoring.',
                'body_markdown' => "# SWMS – Concrete Cutting/Drilling
**Equipment:** {{concrete.equipment}} | **Anchor method:** {{concrete.anchor}}

## Controls
- Silica dust control (wet/vac): {{concrete.silica}}
- Noise management: {{concrete.noise}}
- Slurry/water capture: {{concrete.slurry}}
- Verify services scan: {{concrete.scan}}

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'concrete.equipment','concrete.anchor','concrete.silica','concrete.noise','concrete.slurry','concrete.scan','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Roof Work (Fragile Roofs)',
                'slug' => 'swms-roof-fragile',
                'category' => 'SWMS',
                'subcategory' => 'Heights',
                'description' => 'Covers skylights, purlins checks, temporary walkways.',
                'body_markdown' => "# SWMS – Roof Work (Fragile)
**Roof Type:** {{roof.type}} | **Anchor/Restraint:** {{roof.anchor}}

## Controls
- Temporary walkway/mesh: {{roof.walkway}}
- Edge protection: {{roof.edge_protection}}
- Cover/openings & skylights: {{roof.skylights}}
- Weather monitoring: {{roof.weather}}

**Rescue:** {{rescue.plan}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'roof.type','roof.anchor','roof.walkway','roof.edge_protection','roof.skylights','roof.weather','rescue.plan','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Scaffolding (Erection/Use)',
                'slug' => 'swms-scaffolding',
                'category' => 'SWMS',
                'subcategory' => 'Heights',
                'description' => 'Tagging, ties/bracing, access, exclusion.',
                'body_markdown' => "# SWMS – Scaffolding
**System:** {{scaf.system}} | **Height:** {{scaf.height}} | **Permit:** {{permits.scaf}}

## Controls
- Competent installer; tag green/yellow/red
- Base, ties & bracing: {{scaf.bracing}}
- Access/egress & decking: {{scaf.access}}
- Exclusion/overhead protection: {{scaf.exclusion}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'scaf.system','scaf.height','permits.scaf','scaf.bracing','scaf.access','scaf.exclusion','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Hazardous Substances (Chemicals)',
                'slug' => 'swms-chemicals',
                'category' => 'SWMS',
                'subcategory' => 'Hazardous Substances',
                'description' => 'SDS, decanting, ventilation, spill & first-aid.',
                'body_markdown' => "# SWMS – Hazardous Substances
**Substance:** {{chem.name}} | **SDS:** {{chem.sds_ref}}

## Controls
- Risk assessment: {{chem.assessment}}
- Storage/segregation: {{chem.storage}}
- Decanting & ventilation: {{chem.decanting}}
- Spill kit & first aid: {{chem.spill}} / {{chem.first_aid}}

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'chem.name','chem.sds_ref','chem.assessment','chem.storage','chem.decanting','chem.spill','chem.first_aid','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Mobile Plant (Excavator/Forklift)',
                'slug' => 'swms-mobile-plant',
                'category' => 'SWMS',
                'subcategory' => 'Plant & Equipment',
                'description' => 'Prestart, operator competency, exclusion, spotters.',
                'body_markdown' => "# SWMS – Mobile Plant
**Plant:** {{plant.type}} | **Operator:** {{people.operator}} | **Prestart:** {{plant.prestart}}

## Controls
- Exclusion zone/spotter: {{plant.exclusion}}
- Ground conditions: {{plant.ground}}
- Load limits & attachments: {{plant.attachments}}
- Traffic interface: {{plant.traffic}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'plant.type','people.operator','plant.prestart','plant.exclusion','plant.ground','plant.attachments','plant.traffic','sign.off.block'
                ],
            ],

            [
                'name' => 'SWMS – Crane Lift & Rigging',
                'slug' => 'swms-crane-lift',
                'category' => 'SWMS',
                'subcategory' => 'Plant & Equipment',
                'description' => 'Lift plan, load charts, exclusion zones, dogging.',
                'body_markdown' => "# SWMS – Crane Lift & Rigging
**Crane Type:** {{crane.type}} | **Lift Plan ID:** {{crane.plan}} | **Dogman/Rigger:** {{people.rigger}}

## Controls
- Lift study/load chart verified: {{crane.load_chart}}
- Sling/rigging inspected & tagged: {{rigging.inspection}}
- Exclusion zone & spotters: {{exclusion.zone}}
- Wind limits & communication: {{crane.wind_comms}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'crane.type','crane.plan','people.rigger','crane.load_chart','rigging.inspection','exclusion.zone','crane.wind_comms','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Angle Grinder Use',
                'slug' => 'swms-angle-grinder',
                'category' => 'SWMS',
                'subcategory' => 'Plant & Equipment',
                'description' => 'Discs, guards, sparks, noise, eye/face protection.',
                'body_markdown' => "# SWMS – Angle Grinder
**Model:** {{grinder.model}} | **Disc Type:** {{grinder.disc}}

## Controls
- Guard in place & correct disc speed
- Two-hand control; secure workpiece
- Sparks control & exclusion: {{grinder.sparks_control}}
- Hearing/eye/face protection: {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'grinder.model','grinder.disc','grinder.sparks_control','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Chainsaw / Vegetation Clearing',
                'slug' => 'swms-chainsaw',
                'category' => 'SWMS',
                'subcategory' => 'Plant & Equipment',
                'description' => 'Kickback control, exclusion, felling plan, refuelling.',
                'body_markdown' => "# SWMS – Chainsaw/Vegetation Clearing
**Area/Tree ID:** {{veg.id}} | **Spotter:** {{people.spotter}}

## Controls
- Felling/trim plan & escape path: {{veg.plan}}
- Chain brake & sharp chain; two-hand grip
- Exclusion zone; no over-head cutting
- Refuel in ventilated area, cool engine

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'veg.id','people.spotter','veg.plan','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Pressure Washing / Water Jetting',
                'slug' => 'swms-pressure-washing',
                'category' => 'SWMS',
                'subcategory' => 'Plant & Equipment',
                'description' => 'High-pressure injection, slips, overspray, runoff.',
                'body_markdown' => "# SWMS – Pressure Washing
**Pressure/Nozzle:** {{wash.pressure}} | **Detergent:** {{wash.detergent}}

## Controls
- Inspect hose/lance; trigger lock
- Exclusion & slip control: {{wash.slip_control}}
- Manage runoff; protect drains: {{wash.runoff}}
- Electrical separation (RCD): {{wash.rcd}}

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'wash.pressure','wash.detergent','wash.slip_control','wash.runoff','wash.rcd','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Generator & Temporary Power',
                'slug' => 'swms-generator-temp-power',
                'category' => 'SWMS',
                'subcategory' => 'Electrical',
                'description' => 'Earthing/bonding, RCDs, cords, exhaust, refuelling.',
                'body_markdown' => "# SWMS – Generator & Temporary Power
**Generator ID:** {{gen.id}} | **RCD Test:** {{gen.rcd_test}}

## Controls
- Earth/neutral bonding as required: {{gen.earthing}}
- Lead management & tagging: {{gen.leads}}
- Exhaust away from people; ventilation
- Refuel only when cool; spill kit

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'gen.id','gen.rcd_test','gen.earthing','gen.leads','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Working Near Overhead Powerlines',
                'slug' => 'swms-overhead-powerlines',
                'category' => 'SWMS',
                'subcategory' => 'Electrical',
                'description' => 'No-go/exclusion zones, spotter, permits, de-energise.',
                'body_markdown' => "# SWMS – Working Near Overhead Powerlines
**Line Voltage:** {{elec.voltage}} | **Permit:** {{permits.electrical}}

## Controls
- Identify & map powerlines; signage
- Observe no-go/exclusion distances: {{elec.exclusion}}
- Spotter & observers; EWP/plant limits
- Request isolation/de-energisation where possible

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'elec.voltage','permits.electrical','elec.exclusion','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Formwork & Falsework (Erect/Strip)',
                'slug' => 'swms-formwork-falsework',
                'category' => 'SWMS',
                'subcategory' => 'General Construction',
                'description' => 'Stability, propping, working platforms, stripping sequence.',
                'body_markdown' => "# SWMS – Formwork & Falsework
**System:** {{form.system}} | **Engineer Drawing:** {{form.drawing}}

## Controls
- Erection per drawings; propping: {{form.propping}}
- Working platforms, edge protection
- Strip sequence & fall-of-material control
- Load limits; inspections

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'form.system','form.drawing','form.propping','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Brick/Block Laying (Silica)',
                'slug' => 'swms-brick-block',
                'category' => 'SWMS',
                'subcategory' => 'General Construction',
                'description' => 'Cutting control, mixing mortar, manual handling, scaff access.',
                'body_markdown' => "# SWMS – Brick/Block Laying
**Mix:** {{brick.mix}} | **Cutting method:** {{brick.cutting}}

## Controls
- Wet cutting / dust extraction: {{brick.silica_control}}
- Mixing station containment & washout
- Access/scaffolding; fall prevention
- Manual handling aids & rotation

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'brick.mix','brick.cutting','brick.silica_control','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Tiling & Waterproofing (Membranes)',
                'slug' => 'swms-tiling-waterproofing',
                'category' => 'SWMS',
                'subcategory' => 'General Construction',
                'description' => 'Primers/adhesives, ventilation, slips, curing, SDS.',
                'body_markdown' => "# SWMS – Tiling & Waterproofing
**Membrane:** {{tile.membrane}} | **SDS:** {{chem.sds_ref}}

## Controls
- Ventilation & solvent controls: {{tile.ventilation}}
- Slips/trips during laying & cure
- Waste/cleanup; environment
- SDS available on site

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'tile.membrane','chem.sds_ref','tile.ventilation','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Painting & Coatings (Solvents)',
                'slug' => 'swms-painting-solvents',
                'category' => 'SWMS',
                'subcategory' => 'Hazardous Substances',
                'description' => 'Ventilation, flammables, ladders/height, cleanup & waste.',
                'body_markdown' => "# SWMS – Painting & Coatings
**Product:** {{paint.product}} | **SDS:** {{chem.sds_ref}}

## Controls
- Ventilation; no ignition sources
- Ladder/height control where applicable
- Storage of flammables: {{paint.storage}}
- Cleanup/waste per SDS

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'paint.product','chem.sds_ref','paint.storage','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Pressure Testing Pipework',
                'slug' => 'swms-pressure-testing',
                'category' => 'SWMS',
                'subcategory' => 'General Construction',
                'description' => 'Stored energy, isolation, barriers, venting/bleeding.',
                'body_markdown' => "# SWMS – Pressure Testing
**Medium:** {{pressure.medium}} | **Test Pressure:** {{pressure.kpa}} kPa

## Controls
- Isolate & tag; verified gauges
- Barriers/exclusion; no personnel in line-of-fire
- Vent/bleed procedures: {{pressure.vent}}
- Record results & sign-off

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'pressure.medium','pressure.kpa','pressure.vent','sign.off.block'
                ],
            ],
[
    'name' => 'SWMS – Electrical Installations (General)',
    'slug' => 'swms-elec-installations-general',
    'category' => 'SWMS',
    'subcategory' => 'Electrical • Installations',
    'description' => 'General electrical install: routing, fixing, terminations, verification.',
    'body_markdown' => "# SWMS – Electrical Installations (General)\n**Job/Ref:** {{job.ref}} | **Site:** {{site.address}} | **Supervisor:** {{people.supervisor}}\n\n## Hazards\n- Electric shock, arc fault, live parts\n- Cuts/abrasions, manual handling\n- Slips/trips in work area\n\n## Controls\n- Isolate and verify de-energised: {{isolation.method}}\n- Lockout/Tagout: {{loto.ids}}\n- Route/fix cables per drawings: {{cabling.route}}\n- Maintain IP ratings; glands/bushings: {{enclosures.glanding}}\n- Housekeeping and access: {{site.housekeeping}}\n\n**PPE:** {{ppe.list}}\n\n**Testing/Verification:** {{electrical.tests}}\n**Sign-off:** {{sign.off.block}}",
    'placeholders' => ['job.ref','site.address','people.supervisor','isolation.method','loto.ids','cabling.route','enclosures.glanding','site.housekeeping','ppe.list','electrical.tests','sign.off.block'],
],
[
    'name' => 'SWMS – Isolation & LOTO (Electrical)',
    'slug' => 'swms-elec-isolation-loto',
    'category' => 'SWMS',
    'subcategory' => 'Electrical • Service & Maintenance',
    'description' => 'Lockout/Tagout, test-before-touch, proving de-energised.',
    'body_markdown' => "# SWMS – Isolation & LOTO (Electrical)\n**Supply/Board:** {{electrical.board}} | **Permit:** {{permits.electrical}}\n\n## Controls\n- Identify isolation points: {{isolation.points}}\n- Lock & tag devices: {{loto.ids}}\n- Verify de-energised with an approved tester: {{test.instrument}}\n- Prove tester on known source before/after\n- Clearly mark work zone and keep covers closed\n\n**PPE:** {{ppe.list}}\n**Sign-off:** {{sign.off.block}}",
    'placeholders' => ['electrical.board','permits.electrical','isolation.points','loto.ids','test.instrument','ppe.list','sign.off.block'],
],
            [
                'name' => 'SWMS – HVAC Split System Installation',
                'slug' => 'swms-hvac-split-install',
                'category' => 'SWMS',
                'subcategory' => 'HVAC • Installations',
                'description' => 'Wall bracket, line set, condensate, electrical, commissioning.',
                'body_markdown' => "# SWMS – HVAC Split System Install
**Indoor/Outdoor:** {{hvac.models}} | **Refrigerant:** {{hvac.ref}} | **Circuit:** {{electrical.circuit}}

## Controls
- Wall/roof brackets secure; fixings per engineer
- Line set sized and insulated; protect from UV
- Condensate fall and discharge point: {{hvac.condensate}}
- Electrical isolation and compliance per AS/NZS 3000
- Commission; record pressures/temps: {{hvac.commission}}

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'hvac.models','hvac.ref','electrical.circuit','hvac.condensate','hvac.commission','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Rooftop Package Unit Installation',
                'slug' => 'swms-hvac-packaged-rooftop',
                'category' => 'SWMS',
                'subcategory' => 'HVAC • Installations',
                'description' => 'Crane lift, curb, duct connections, gas/electric, controls.',
                'body_markdown' => "# SWMS – Rooftop Package Unit Install
**Unit:** {{hvac.unit}} | **Lift Plan:** {{crane.plan}} | **Spotter:** {{people.spotter}}

## Controls
- Lift study/load chart verified: {{crane.load_chart}}
- Roof curb and fixings sealed; weatherproof
- Duct connections sealed; vibration isolation
- Electrical/gas connections per drawings; isolate and test

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'hvac.unit','crane.plan','people.spotter','crane.load_chart','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Refrigerant Handling & Recovery',
                'slug' => 'swms-hvac-refrigerant-recovery',
                'category' => 'SWMS',
                'subcategory' => 'HVAC • Refrigerants',
                'description' => 'F-Gas handling, recovery, cylinders, leak testing.',
                'body_markdown' => "# SWMS – Refrigerant Handling & Recovery
**Type:** {{ref.type}} | **Cylinder:** {{ref.cylinder}}

## Controls
- Confirm competency/licence; SDS available
- Leak test with approved method; no naked flames
- Recovery using rated equipment; weigh out
- Cylinders upright/secured; transport caps on

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'ref.type','ref.cylinder','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Brazing / Silver Soldering (Hot Works)',
                'slug' => 'swms-hvac-brazing-hotworks',
                'category' => 'SWMS',
                'subcategory' => 'HVAC • Refrigerants',
                'description' => 'Permits, fire watch, ventilation, cylinder control.',
                'body_markdown' => "# SWMS – Brazing / Hot Works
**Permit #:** {{permits.hotwork}} | **Method:** {{brazing.method}}

## Controls
- Remove combustibles/fire blankets; fire extinguisher
- Ventilation and fume control
- Purge with nitrogen where required
- Cylinder handling upright/secured

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'permits.hotwork','brazing.method','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – AHU/FCU Service & Maintenance',
                'slug' => 'swms-hvac-ahu-fcu-service',
                'category' => 'SWMS',
                'subcategory' => 'HVAC • Service & Maintenance',
                'description' => 'Filters, belts, coils, drains, isolation.',
                'body_markdown' => "# SWMS – AHU/FCU Service
**Unit ID:** {{unit.id}} | **Access:** {{unit.access}}

## Controls
- Isolate power; lock and tag
- Filters changed; coils cleaned; drains cleared
- Belt guard on; tensions per spec
- Restart and check rotation/amps

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'unit.id','unit.access','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Chiller Plant Maintenance',
                'slug' => 'swms-hvac-chiller-maint',
                'category' => 'SWMS',
                'subcategory' => 'HVAC • Service & Maintenance',
                'description' => 'LOTO, chemical treatment, pumps, strainers, logs.',
                'body_markdown' => "# SWMS – Chiller Plant Maintenance
**Plant:** {{plant.id}} | **Chemicals:** {{chem.treatment}}

## Controls
- Electrical/mechanical isolation; verify zero energy
- Chemical handling per SDS; eye wash available
- Pump/strainer maintenance; leak checks
- Record logs and restart checks

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'plant.id','chem.treatment','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Duct Installation & Sheetmetal Work',
                'slug' => 'swms-hvac-duct-install',
                'category' => 'SWMS',
                'subcategory' => 'HVAC • Ducting',
                'description' => 'Lifting sections, hanging, sealing, insulation.',
                'body_markdown' => "# SWMS – Duct Installation
**Ductwork:** {{duct.type}} | **Insulation:** {{duct.insulation}}

## Controls
- Lift with aids/team; avoid overreach
- Hangers & supports per drawings
- Joints sealed; insulation installed neatly
- Scissor lift/EWP usage per prestart

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'duct.type','duct.insulation','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Cooling Tower Service',
                'slug' => 'swms-hvac-cooling-tower-service',
                'category' => 'SWMS',
                'subcategory' => 'HVAC • Service & Maintenance',
                'description' => 'Legionella control, dosing, drift eliminators, access.',
                'body_markdown' => "# SWMS – Cooling Tower Service
**Tower ID:** {{tower.id}} | **Dosing:** {{tower.dosing}}

## Controls
- Follow Legionella management plan
- Chemical dosing per SDS; PPE and spill kit
- Access control and fall prevention
- Clean drift eliminators; record checks

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'tower.id','tower.dosing','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – EWP Access for HVAC Roof Work',
                'slug' => 'swms-hvac-ewp-roof',
                'category' => 'SWMS',
                'subcategory' => 'HVAC • Heights',
                'description' => 'Prestart, harness, ground conditions, exclusion zones.',
                'body_markdown' => "# SWMS – EWP Roof Access
**Model/ID:** {{ewp.model}} | **SWL:** {{ewp.swl}} | **Operator:** {{people.operator}}

## Controls
- Prestart inspection; logbook check
- Harness and lanyard anchored
- Ground conditions and slope assessed
- Exclusion/barricades; weather limits

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'ewp.model','ewp.swl','people.operator','ppe.list','sign.off.block'
                ],
            ],
            [
                'name' => 'SWMS – Commissioning HVAC Systems',
                'slug' => 'swms-hvac-commissioning',
                'category' => 'SWMS',
                'subcategory' => 'HVAC • Installations',
                'description' => 'Balancing, sensors, controls, verification, documentation.',
                'body_markdown' => "# SWMS – HVAC Commissioning
**System:** {{hvac.system}} | **Docs:** {{hvac.docs}}

## Controls
- Verify isolation removed; guards in place
- Air/water balance per plan; record setpoints
- Controls checked; sensors calibrated
- Handover docs and maintenance schedules

**PPE:** {{ppe.list}}

**Sign-off:** {{sign.off.block}}",
                'placeholders' => [
                    'hvac.system','hvac.docs','ppe.list','sign.off.block'
                ],
            ],
];
        foreach ($items as $it) { DocumentTemplate::firstOrCreate(['slug'=>$it['slug']], $it); }
    }
}

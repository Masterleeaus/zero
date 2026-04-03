<?php

namespace Modules\Recruitment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\LandingPage\Entities\MarketplacePageSetting;

class MarketPlaceSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'Recruitment';
        $data['product_main_description'] = '<p>Speed up your hiring process. Use built-in hiring features to create and manage new job openings and fill your open positions faster.Depending on Hrm Module.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = 'Recruit New Candidates and Grow Your Team';
        $data['dedicated_theme_description'] = '<p>Collect and manage applications from start to finish. Easily compare candidates and pick the best one for the job.</p>';
        $data['dedicated_theme_sections'] = '[{"dedicated_theme_section_image":"",
            "dedicated_theme_section_heading":"Recruitment Management",
            "dedicated_theme_section_description":"<p>Job Post, Job application, Interview Scheduling, Custom Interview Questions, Job Onboarding, Unique link for applying for a job<\/p>",
            "dedicated_theme_section_cards":{"1":{"title":"Speed up your hiring process","description":"Speed up your hiring process. Use built-in hiring features to create and manage new job openings and fill your open positions faster."},
            "2":{"title":"Candidate pipeline","description":"Collect and manage applications from start to finish. Easily compare candidates and pick the best one for the job.Create a candidate pipeline. Get a clear view of all the potential candidates and the recruitment stagesthey’re in."},
            "3":{"title":"Schedule interviews","description":"Schedule interviews, create interview questions and assign interviewers in just a few clicks."}}},

            {"dedicated_theme_section_image":"",
                "dedicated_theme_section_heading":"Job Openings Editor",
                "dedicated_theme_section_description":"<p>Easily add new job openings. Add it to a job board, start tracking applications, and fill the position. Schedule interviews, assign interviewers, and get full control over the recruitment process. Create a pipeline to track every candidate and have a clear view of who progresses to the next stage.<\/p>",
                "dedicated_theme_section_cards":{"1":{"title":null,"description":null}}}]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"Recruitment"},{"screenshots":"","screenshots_heading":"Recruitment"},{"screenshots":"","screenshots_heading":"Recruitment"},{"screenshots":"","screenshots_heading":"Recruitment"},{"screenshots":"","screenshots_heading":"Recruitment"}]';
        $data['addon_heading'] = 'Why choose dedicated modulesfor Your Business?';
        $data['addon_description'] = '<p>With Dash, you can conveniently manage all your business functions from a single location.</p>';
        $data['addon_section_status'] = 'on';
        $data['whychoose_heading'] = 'Why choose dedicated modulesfor Your Business?';
        $data['whychoose_description'] = '<p>With Dash, you can conveniently manage all your business functions from a single location.</p>';
        $data['pricing_plan_heading'] = 'Empower Your Workforce with DASH';
        $data['pricing_plan_description'] = '<p>Access over Premium Add-ons for Accounting, HR, Payments, Leads, Communication, Management, and more, all in one place!</p>';
        $data['pricing_plan_demo_link'] = '#';
        $data['pricing_plan_demo_button_text'] = 'View Live Demo';
        $data['pricing_plan_text'] = '{"1":{"title":"Pay-as-you-go"},"2":{"title":"Unlimited installation"},"3":{"title":"Secure cloud storage"}}';
        $data['whychoose_sections_status'] = 'on';
        $data['dedicated_theme_section_status'] = 'on';

        foreach($data as $key => $value){
            if(!MarketplacePageSetting::where('name', '=', $key)->where('module', '=', 'Recruitment')->exists()){
                MarketplacePageSetting::updateOrCreate(
                [
                    'name' => $key,
                    'module' => 'Recruitment'

                ],
                [
                    'value' => $value
                ]);
            }
        }
    }
}

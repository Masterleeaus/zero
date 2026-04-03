<?php

namespace Modules\FormBuilder\Database\Seeders;

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
        $module = 'FormBuilder';

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'Form Builder';
        $data['product_main_description'] = '<p>An inline frame (iframe) of a form builder performs as an external form of the product which can be attached to any of the other websites without redirecting the internal form and getting the responses to the main product. This mainly works as a third party to get more leads from various websites.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = "Add Form builder module, using this provides create lead option";
        $data['dedicated_theme_description'] = "Create and manage various required forms with diverse form fields, like, Text, Email, Number, Date, and Description as per the business needs for leads.";
        $data['dedicated_theme_sections'] = '[{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"Form Builder Introduction","dedicated_theme_section_description":"<p>Form Tools was originally designed to provide an access and storage script for your own web forms. While the script still allows this functionality through external forms, the Form Builder expands on this functionality by allowing you to construct forms right within the Form Tools interface and publish them on your site through the click of a button.<\/p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null}}},{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"Publishing your First Form","dedicated_theme_section_description":"<p>Publishing your forms with the Form Builder is extremely easy. This page explains how to create and publish a form from scratch. We don`t go into any great detail, but hopefully it`s enough to get you started and see how the whole process works.<\/p>"}]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"Form Builder"},{"screenshots":"","screenshots_heading":"Form Builder"},{"screenshots":"","screenshots_heading":"Form Builder"},{"screenshots":"","screenshots_heading":"Form Builder"},{"screenshots":"","screenshots_heading":"Form Builder"}]';
        $data['addon_heading'] = '<h2>Why choose dedicated modules<b> for Your Business?</b></h2>';
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
            if(!MarketplacePageSetting::where('name', '=', $key)->where('module', '=', $module)->exists()){
                MarketplacePageSetting::updateOrCreate(
                [
                    'name' => $key,
                    'module' => $module

                ],
                [
                    'value' => $value
                ]);
            }
        }
    }
}

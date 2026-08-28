<?php

namespace Workdo\Api\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Workdo\LandingPage\Entities\MarketplacePageSetting;


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
        $module = 'Api';

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'Api';
        $data['product_main_description'] = '<p>The API Module of BookingGo SaaS offers seamless integration capabilities, allowing your business to connect with external systems effortlessly. Whether it\'s a third-party app, custom software, or other services, this module provides the tools to automate and synchronize your operations with ease.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = '<h2>Effortless Connectivity</h2>';
        $data['dedicated_theme_description'] = '<p>With the API Module, you can link BookingGo to your preferred tools and platforms without complications. The module ensures smooth data transfer, enabling a unified ecosystem for managing your business operations.</p>';
        $data['dedicated_theme_sections'] = '[{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"Customizable Endpoints","dedicated_theme_section_description":"<p>The API Module provides customizable endpoints to meet your specific needs. Access a range of features such as fetching appointment details, updating schedules, or syncing customer data. This flexibility ensures that the integration aligns perfectly with your workflow and business requirements.<\/p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null}}},{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"Enhanced Automation","dedicated_theme_section_description":"<p>Save time and eliminate manual work with automated data sharing across systems. The API Module allows real-time updates, ensuring that every change made in BookingGo is reflected across connected platforms instantly. This reduces errors and enhances productivity.<\/p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null}}}]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"Api"},{"screenshots":"","screenshots_heading":"Api"},{"screenshots":"","screenshots_heading":"Api"}]';
        $data['addon_heading'] = '<h2>Why choose dedicated modules<b> for Your Business?</b></h2>';
        $data['addon_description'] = '<p>With BookingGo, you can conveniently manage all your business functions from a single location.</p>';
        $data['addon_section_status'] = 'on';
        $data['whychoose_heading'] = 'Why choose dedicated modulesfor Your Business?';
        $data['whychoose_description'] = '<p>With BookingGo, you can conveniently manage all your business functions from a single location.</p>';
        $data['pricing_plan_heading'] = 'Empower Your Workforce with BookingGo';
        $data['pricing_plan_description'] = '<p>Access over Premium Add-ons for Stripe , Paypal , Google Recaptcha, and more, all in one place!</p>';
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

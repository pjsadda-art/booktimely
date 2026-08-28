<?php

namespace Workdo\SMS\Database\Seeders;

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
        $module = 'SMS';

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'SMS';
        $data['product_main_description'] = '<p>Introducing SMS Integration within BookingGo SaaS, a groundbreaking feature designed to simplify your communication strategy. With this new addition, notifying individuals via SMS has never been easier. Whether you\'re sending updates, alerts, or reminders, BookingGo SaaS provides a seamless experience, allowing you to select a category and send messages effortlessly through a range of reliable SMS gateways. Our platform integrates with industry-leading providers such as AWS, Twilio, Clockwork, Melipayamak, Kavenegar, and SMS Gateway, ensuring your messages are delivered promptly and efficiently.
        </p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = '<h2>Effortless<b> SMS Communication</b> with BookingGo SaaS</h2>';
        $data['dedicated_theme_description'] = '<p>Explore the simplicity of reaching your audience via SMS with BookingGo SaaS. Organize, send, and track messages seamlessly through trusted gateways, ensuring reliable delivery and enhanced communication efficiency.</p>';
        $data['dedicated_theme_sections'] = '[
            {
                "dedicated_theme_section_image": "",
                "dedicated_theme_section_heading": "SMS Notification With Workdo BookingGo",
                "dedicated_theme_section_description": "<p>Receive immediate notifications of vital updates, notifications, and alerts directly to your mobile device via SMS. Stay informed about crucial events wherever you are, ensuring you never miss an important message. Getting started is simple - just input your SMS gateway information in the settings page, then toggle notifications on or off according to your preferences. Stay connected and informed with Workdo BookingGo/s SMS Module.<\/p>",
                "dedicated_theme_section_cards": {
                    "1": {
                        "title": null,
                        "description": null
                    }
                }
            },
            {
                "dedicated_theme_section_image": "",
                "dedicated_theme_section_heading": "Trusted Providers, Seamless Integration: Elevating Communication with BookingGo SaaS",
                "dedicated_theme_section_description": "<p>Our platform seamlessly integrates with leading SMS gateways, providing you with unparalleled reliability and delivery assurance. Whether you prefer AWS, Twilio, Clockwork, Melipayamak, Kavenegar, or another SMS gateway provider, BookingGo SaaS has you covered. You can leverage the infrastructure of trusted providers to ensure that your messages reach their intended recipients without fail.<\/p>",
                "dedicated_theme_section_cards": {
                    "1": {
                        "title": null,
                        "description": null
                    }
                }
            }
        ]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"SMS"},{"screenshots":"","screenshots_heading":"SMS"},{"screenshots":"","screenshots_heading":"SMS"},{"screenshots":"","screenshots_heading":"SMS"},{"screenshots":"","screenshots_heading":"SMS"}]';
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

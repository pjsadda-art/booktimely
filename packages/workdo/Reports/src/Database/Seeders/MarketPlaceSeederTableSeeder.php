<?php

namespace Workdo\Reports\Database\Seeders;

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
        $module = 'Reports';

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'Reports';
        $data['product_main_description'] = '<p>The Reports module in BookingGo SaaS is designed to provide comprehensive insights into your business operations, specifically focusing on key metrics and performance indicators. This module allows you to generate detailed reports that help you monitor and analyze various aspects of your service appointments and customer interactions. With its intuitive interface and powerful reporting capabilities, the Reports module empowers you to make data-driven decisions and optimize your business effectively.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = '<h2><b>Reports </b> Module</h2>';
        $data['dedicated_theme_description'] = '<p>The Reports Module of BookingGo SaaS provides detailed analytics and customizable reports tailored for transportation and travel businesses. Gain insights into booking trends, revenue performance, customer demographics, and operational metrics with intuitive visualizations and exportable data formats. Empower your decision-making and optimize business strategies efficiently with the Reports Module.</p>';
        $data['dedicated_theme_sections'] = '[
            {
                "dedicated_theme_section_image": "",
                "dedicated_theme_section_heading": "Appointment Status Report",
                "dedicated_theme_section_description": "<p>The Appointment Status Report is a critical feature of the Reports module, offering a clear overview of the status of all appointments. This report helps you track which appointments are confirmed, pending, completed, or canceled. By having a real-time view of appointment statuses, you can efficiently manage your schedule, reduce no-shows, and improve overall appointment management. This report is essential for maintaining a smooth operational flow and ensuring that your services are delivered as planned.</p>",
                "dedicated_theme_section_cards": {
                    "1": {
                        "title": null,
                        "description": null
                    }
                }
            },
            {
                "dedicated_theme_section_image": "",
                "dedicated_theme_section_heading": "Customer vs. Guest Report",
                "dedicated_theme_section_description": "<p>Understanding the behavior and preferences of your customers and guests is crucial for tailoring your services. The Customer vs. Guest Report provides insights into the interactions and engagement levels of registered customers compared to one-time guests. This report helps you identify patterns and trends, enabling you to develop targeted marketing strategies and personalized service offerings. By leveraging this information, you can enhance customer loyalty and attract new clients more effectively.</p>",
                "dedicated_theme_section_cards": {
                    "1": {
                        "title": null,
                        "description": null
                    }
                }
            },
            {
                "dedicated_theme_section_image": "",
                "dedicated_theme_section_heading": "Service Appointment Report",
                "dedicated_theme_section_description": "<p>The Service Appointment Report delves into the specifics of the services provided, offering detailed analysis on the types and frequency of appointments. This report allows you to see which services are most popular, peak booking times, and the overall demand for each service. With this data, you can optimize your service offerings, allocate resources more efficiently, and identify opportunities for expanding your service portfolio to meet customer needs better.</p>",
                "dedicated_theme_section_cards": {
                    "1": {
                        "title": null,
                        "description": null
                    }
                }
            },
            {
                "dedicated_theme_section_image": "",
                "dedicated_theme_section_heading": "Revenue Report",
                "dedicated_theme_section_description": "<p>The Revenue Report is a vital component of the Reports module, providing a comprehensive overview of your business\'s financial performance. This report details the income generated from appointments, broken down by service type, period, and customer segments. It helps you track your earnings, identify revenue trends, and forecast future financial performance. By analyzing this report, you can make informed decisions about pricing strategies, budget allocations, and growth initiatives, ensuring the financial health and sustainability of your business.</p>",
                "dedicated_theme_section_cards": {
                    "1": {
                        "title": null,
                        "description": null
                    }
                }
            }
        ]';        

        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"Reports"},{"screenshots":"","screenshots_heading":"Reports"},{"screenshots":"","screenshots_heading":"Reports"}]';
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

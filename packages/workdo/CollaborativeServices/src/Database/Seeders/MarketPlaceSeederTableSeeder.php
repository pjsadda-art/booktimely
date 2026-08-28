<?php

namespace Workdo\CollaborativeServices\Database\Seeders;

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
        $module = 'CollaborativeServices';

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'Collaborative Services';
        $data['product_main_description'] = '<p>The Collaborative Service Module in BookingGo is designed to enhance your booking process by allowing the integration of multiple services into a single appointment. This feature-rich module simplifies the scheduling process, ensuring a seamless experience for both service providers and customers
        \ Whether you are managing a spa, salon, or any service-based business, this module will help you efficiently coordinate and offer a variety of services.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = "<h2>Collaborative Service Module:<b>Revolutionizing Multi-Service</b> Bookings</h2>";
        $data['dedicated_theme_description'] = '<p>The Collaborative Service Module in BookingGo integrates multiple services into a single appointment, streamlining the scheduling process for both service providers and customers. Ideal for spas, salons, and other service-based businesses, this module enhances efficiency and provides a comprehensive customer experience.</p>';
        $data['dedicated_theme_sections'] = '[
                                                {
                                                    "dedicated_theme_section_image": "",
                                                    "dedicated_theme_section_heading": "Adding Multiple Services",
                                                    "dedicated_theme_section_description": "With the Collaborative Service Module, you can easily add multiple services to a single booking. This flexibility allows you to cater to customers who wish to book several services in one visit, such as a haircut followed by a massage. By integrating various services into one appointment, you can maximize the efficiency of your scheduling and provide a more comprehensive customer experience.",
                                                    "dedicated_theme_section_cards": {
                                                    "1": {
                                                        "title": "",
                                                        "description": ""
                                                    },
                                                    "2": {
                                                    "title": "",
                                                        "description": ""
                                                    },
                                                    "3": {
                                                    "title": "",
                                                        "description": ""
                                                    }
                                                    }
                                                },
                                                {
                                                    "dedicated_theme_section_image": "",
                                                    "dedicated_theme_section_heading": "Streamlined Booking Process",
                                                    "dedicated_theme_section_description": "The module simplifies the booking process for both staff and customers. Service providers can effortlessly select and combine services during the appointment setup, while customers can enjoy the convenience of booking all desired services at once. This streamlined process reduces the need for multiple bookings and minimizes scheduling conflicts, leading to a more efficient and user-friendly experience.",
                                                    "dedicated_theme_section_cards": {
                                                    "1": {
                                                        "title": "",
                                                        "description": ""
                                                    },
                                                    "2": {
                                                    "title": "",
                                                        "description": ""
                                                    },
                                                    "3": {
                                                    "title": "",
                                                        "description": ""
                                                    }
                                                    }
                                                },
                                                {
                                                    "dedicated_theme_section_image": "",
                                                    "dedicated_theme_section_heading": "Enhanced Customer Satisfaction",
                                                    "dedicated_theme_section_description": "By offering the ability to book multiple services in one appointment, the Collaborative Service Module significantly enhances customer satisfaction. Customers appreciate the convenience and time savings of coordinating multiple services in one visit. This feature also allows businesses to upsell and cross-sell services, increasing revenue while meeting customer needs more effectively.",
                                                    "dedicated_theme_section_cards": {
                                                    "1": {
                                                        "title": "",
                                                        "description": ""
                                                    },
                                                    "2": {
                                                    "title": "",
                                                        "description": ""
                                                    },
                                                    "3": {
                                                    "title": "",
                                                        "description": ""
                                                    }
                                                    }
                                                },
                                                {
                                                    "dedicated_theme_section_image": "",
                                                    "dedicated_theme_section_heading": "Optimized Resource Management",
                                                    "dedicated_theme_section_description": "The Collaborative Service Module also aids in better resource management. By consolidating multiple services into single appointments, businesses can optimize the use of their resources, such as staff time and service areas. This optimization leads to increased productivity and improved utilization of available resources, ensuring that your business runs smoothly and efficiently.",
                                                    "dedicated_theme_section_cards": {
                                                    "1": {
                                                        "title": "",
                                                        "description": ""
                                                    },
                                                    "2": {
                                                    "title": "",
                                                        "description": ""
                                                    },
                                                    "3": {
                                                    "title": "",
                                                        "description": ""
                                                    }
                                                    }
                                                }
                                            ]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"CollaborativeServices"},{"screenshots":"","screenshots_heading":"CollaborativeServices"},{"screenshots":"","screenshots_heading":"CollaborativeServices"},{"screenshots":"","screenshots_heading":"CollaborativeServices"},{"screenshots":"","screenshots_heading":"CollaborativeServices"}]';
        $data['addon_heading'] = '<h2>Why choose dedicated modules<b> for Your Business?</b></h2>';
        $data['addon_description'] = '<p>with BookingGo, you can conveniently manage all your business functions from a single location.</p>';
        $data['addon_section_status'] = 'on';
        $data['whychoose_heading'] = 'Why choose dedicated modules for Your Business?';
        $data['whychoose_description'] = '<p>with BookingGo, you can conveniently manage all your business functions from a single location.</p>';
        $data['pricing_plan_heading'] = 'Empower Your Workforce with BookingGo';
        $data['pricing_plan_description'] = '<p>Access over Premium Add-ons for Stripe, Paypal, Google Recaptcha Communication, and more, all in one place!</p>';
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

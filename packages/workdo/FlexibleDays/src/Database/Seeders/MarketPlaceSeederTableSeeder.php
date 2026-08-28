<?php

namespace Workdo\FlexibleDays\Database\Seeders;

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
        $module = 'FlexibleDays';

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'Flexible Days';
        $data['product_main_description'] = "<p>The Flexible Days Module by BookingGo SaaS offers staff members unprecedented flexibility in managing their schedules. With this module, staff can easily mark their availability based on their personal commitments, whether it's a medical appointment, a family event, or a day off. By providing this level of control, staff members can maintain a healthy work-life balance, leading to increased job satisfaction and productivity.</p>";
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = 'Unlock Flexibility: The Flexible Days Module in BookingGo SaaS';
        $data['dedicated_theme_description'] = '<p>Empower staff to manage their availability effortlessly, while providing customers with convenient booking options. Streamline scheduling and enhance customer satisfaction with BookingGo SaaS</p>';
        $data['dedicated_theme_sections'] = '[
            {
                "dedicated_theme_section_image": "",
                "dedicated_theme_section_heading": "Convenient Booking Experience",
                "dedicated_theme_section_description": "<p>From the customers perspective, the Flexible Days Module provides a convenient and hassle-free booking experience. Customers can view the real-time availability of staff members directly from the frontend interface, allowing them to select appointments that best fit their schedules. This transparency eliminates confusion and frustration, leading to a smoother booking process and higher customer satisfaction rates.<\/p>",
                "dedicated_theme_section_cards": {
                    "1": {
                        "title": null,
                        "description": null
                    }
                }
            },
            {
                "dedicated_theme_section_image": "",
                "dedicated_theme_section_heading": "Streamlined Appointment Management",
                "dedicated_theme_section_description": "<p>With the Flexible Days Module, appointment management becomes streamlined and efficient. Staff members can easily update their availability within the BookingGo SaaS platform, and these changes are instantly reflected across all aspects of the system. This integration ensures that appointments are always accurate and up-to-date, reducing the risk of double bookings or scheduling conflicts.<\/p>",
                "dedicated_theme_section_cards": {
                    "1": {
                        "title": null,
                        "description": null
                    }
                }
            },
            {
                "dedicated_theme_section_image":"",
                "dedicated_theme_section_heading":" Increased Customer Satisfaction",
                "dedicated_theme_section_description":"<p>By offering flexible booking options, businesses can significantly enhance customer satisfaction. The ability to choose appointments based on staff availability increases convenience for customers, making it more likely that they will return for future services. Additionally, the transparent nature of the booking process builds trust and confidence in the business, further solidifying customer relationships.<\/p>",
                "dedicated_theme_section_cards":{
                    "1":{
                        "title":null,
                        "description":null
                    }
                }
            }
        ]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"FlexibleDays"},{"screenshots":"","screenshots_heading":"FlexibleDays"},{"screenshots":"","screenshots_heading":"FlexibleDays"},{"screenshots":"","screenshots_heading":"FlexibleDays"}]';
        $data['addon_heading'] = '<h2>Why choose dedicated modules<b> for Your Business?</b></h2>';
        $data['addon_description'] = '<p>With BookingGo, you can conveniently manage all your business functions from a single location.</p>';
        $data['addon_section_status'] = 'on';
        $data['whychoose_heading'] = 'Why choose dedicated modulesfor Your Business?';
        $data['whychoose_description'] = '<p>With BookingGo, you can conveniently manage all your business functions from a single location.</p>';
        $data['pricing_plan_heading'] = 'Empower Your Workforce with BookingGo';
        $data['pricing_plan_description'] = '<p>Access over Premium Add-ons for Stripe, Paypal, Google Recaptcha, and more, all in one place!</p>';
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

<?php

namespace Workdo\CarService\Database\Seeders;

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
        $module = 'CarService';

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'CarService';
        $data['product_main_description'] = '<p>This theme prioritizes your clients experience. Imagine them effortlessly booking appointments directly through your BookingGo page, selecting their date, time, and the specific service needed (oil change, tire rotation, diagnostics, etc.) with just a few clicks. The platform also facilitates clear communication. You can answer client questions, confirm details, and even send automated appointment reminders – all within a centralized and organized platform. This eliminates confusion and ensures a smooth experience for your customers, leaving them feeling valued and informed.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = 'BookingGo Car Services ThemeEmpowers Your Business';
        $data['dedicated_theme_description'] = '<p>Running a car service business should not feel like navigating a stormy sea. BookingGo Car Services Theme is your trusty captain, offering a user-friendly online platform that streamlines your workflow, builds trust with clients, and frees you up to focus on smooth sailing – delivering exceptional service</p>';
        $data['dedicated_theme_sections'] = '[
            { 
                "dedicated_theme_section_image":"",
                "dedicated_theme_section_heading":"Transparency Builds Trustworthy Relationships",
                "dedicated_theme_section_description":"<p>BookingGo  Car Services Theme is your compass, guiding you towards building trust and establishing your expertise.  Customize your page with your company logo, brand colors, and high-quality photos showcasing your clean, well-equipped workshop and your friendly team. This personalized touch fosters trust with potential clients, demonstrating professionalism and a commitment to quality service.  Additionally, dedicate a section to clearly explain your service offerings. Outline service packages with detailed descriptions and transparent pricing structures. This allows clients to make informed decisions and choose the service that best suits their needs and budget, eliminating any surprises or hidden fees.<\/p>",
                "dedicated_theme_section_cards":{"1":{"title":null,"description":null}}
            },
            {"dedicated_theme_section_image":"",
                "dedicated_theme_section_heading":"Focus on Your Expertise, Not Marketing Maneuvers",
                "dedicated_theme_section_description":"<p>With BookingGo, you can spend less time on marketing gymnastics and more time focusing on what you do best – providing top-notch car service. The theme allows for effortless promotion through seamless integration with social media platforms. You can easily share special promotions and showcase your expertise through service-specific posts. Furthermore, integrate a testimonial section where satisfied clients can sing your praises. These positive reviews act as powerful social proof, attracting new customers and reaffirming your reputation for excellent service.<\/p>",
                "dedicated_theme_section_cards":{"1":{"title":null,"description":null}}
            },
            {"dedicated_theme_section_image":"",
                "dedicated_theme_section_heading":"Streamlined Operations for a Well-Oiled Machine",
                "dedicated_theme_section_description":"<p>BookingGo  Car Services Theme goes beyond just scheduling. It seamlessly integrates with existing appointment management and invoicing software, eliminating the need for manual data entry. This reduces the risk of errors and saves you valuable time, allowing you to focus on serving your clients. Additionally, the platform enables online payment options, offering your clients a secure and convenient way to settle their bills. This streamlines the entire process, freeing you and your staff to focus on what matters most – providing exceptional car service.<\/p>",
                "dedicated_theme_section_cards":{"1":{"title":null,"description":null}}
            }
        ]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"CarService"},{"screenshots":"","screenshots_heading":"CarService"},{"screenshots":"","screenshots_heading":"CarService"},{"screenshots":"","screenshots_heading":"CarService"},{"screenshots":"","screenshots_heading":"CarService"}]';
        $data['addon_heading'] = 'Why choose dedicated modules for Your Business?';
        $data['addon_description'] = '<p>With BookingGo, you can conveniently manage all your business functions from a single location.</p>';
        $data['addon_section_status'] = 'on';
        $data['whychoose_heading'] = 'Why choose dedicated modules for Your Business?';
        $data['whychoose_description'] = '<p>With BookingGO, you can conveniently manage all your business functions from a single location.</p>';
        $data['pricing_plan_heading'] = 'Empower Your Workforce with BookingGo';
        $data['pricing_plan_description'] = '<p>Access over Premium Add-ons for Stripe, Paypal, Google Recaptcha and more, all in one place!</p>';
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

<?php

namespace Workdo\Photography\Database\Seeders;

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
        $module = 'Photography';

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'Photography';
        $data['product_main_description'] = '<p>Go beyond static galleries. This theme allows you to curate your photographic journey. Organize high-resolution images by themes, projects, or even chronological order to tell a visual story. Do not just display photos; showcase them in stunning slideshows with captivating captions that delve into your creative process. This allows potential clients to not only see your technical skills but also understand the emotions and experiences you strive to capture in your work.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = 'The BookingGo Photography Theme A Deep Dive';
        $data['dedicated_theme_description'] = '<p>The BookingGo Photography Theme is more than just a pretty face for your online presence. It is a comprehensive toolbox designed to empower you, the photographer, to not only showcase your work in a stunning and captivating way but also to streamline your entire workflow and connect with clients seamlessly.</p>';
        $data['dedicated_theme_sections'] = '[
            { 
                "dedicated_theme_section_image":"",
                "dedicated_theme_section_heading":"Transforming Browsers into Engaged Clients",
                "dedicated_theme_section_description":"<p>This theme goes beyond aesthetics. It fosters engagement and interaction with potential clients.  Strategically placed calls to action, like Book Now buttons and contact forms, make it easy for visitors to connect with you for inquiries and schedule sessions directly through BookingGo intuitive scheduling system.  Furthermore, you can showcase special packages and promotions directly within your galleries, sparking interest and driving potential clients to take the next step.<\/p>",
                "dedicated_theme_section_cards":{"1":{"title":null,"description":null}}
            },
            {"dedicated_theme_section_image":"",
                "dedicated_theme_section_heading":"Effortless Booking Management & Client Communication",
                "dedicated_theme_section_description":"<p>BookingGo streamlines your booking process, freeing you up to focus on what you do best capturing stunning photographs.  Clients can book sessions directly through your BookingGo powered website, eliminating the need for back-and-forth emails and phone calls. The platform also facilitates two way communication, allowing you to answer client questions, discuss details, and prepare for the shoot  all within a centralized and organized space. This not only saves you time but also ensures a smooth and efficient experience for both you and your clients.<\/p>",
                "dedicated_theme_section_cards":{"1":{"title":null,"description":null}}
            },
            {"dedicated_theme_section_image":"",
                "dedicated_theme_section_heading":"Building Trust & Credibility",
                "dedicated_theme_section_description":"<p>Positive word of mouth is powerful, and this theme allows you to leverage it. Integrate a dedicated testimonial section where you can display glowing reviews from satisfied clients.  These testimonials serve as social proof,  building trust and credibility with potential clients who may be unfamiliar with your work.  Knowing that others have had positive experiences with your services puts them at ease and makes them more likely to choose you for their photography needs.<\/p>",
                "dedicated_theme_section_cards":{"1":{"title":null,"description":null}}
            }
        ]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"Photography"},{"screenshots":"","screenshots_heading":"Photography"},{"screenshots":"","screenshots_heading":"Photography"},{"screenshots":"","screenshots_heading":"Photography"},{"screenshots":"","screenshots_heading":"Photography"}]';
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

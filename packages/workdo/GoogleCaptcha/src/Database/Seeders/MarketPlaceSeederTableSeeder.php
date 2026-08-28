<?php

namespace Workdo\GoogleCaptcha\Database\Seeders;

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
        $module = 'GoogleCaptcha';

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'Google Captcha';
        $data['product_main_description'] = '<p>To start using reCAPTCHA, you need to sign up for an API key pair for your site. The key pair consists of a site key and secret key. The site key is used to invoke reCAPTCHA service on your site or mobile application. The secret key authorizes communication between your application backend and the reCAPTCHA server to verify the user`s response. The secret key needs to be kept safe for security purposes.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = 'Google Captcha';
        $data['dedicated_theme_description'] = '<p>Google Captcha, or reCAPTCHA, is a security tool that prevents automated bots from spamming websites. It presents challenges to users, ensuring they are human and not malicious bots. This helps protect online forms and enhance overall website security.</p>';
        $data['dedicated_theme_sections'] = '[{"dedicated_theme_section_image": "","dedicated_theme_section_heading": "What Is ReCAPTCHA?",
              "dedicated_theme_section_description": "CAPTCHA stands for Completely Automated Public Turing Test to tell Computers and Humans Apart. CAPTCHA is essentially a simple test that verifies that human website users are not malicious bots intent on enacting fraud and abuse on a site. Google has its own version of CAPTCHA, which is called reCAPTCHA. reCAPTCHA is a free service that uses an advanced risk analysis engine and adaptive CAPTCHAs to protect websites from abusive automated software.","dedicated_theme_section_cards": {"1": {"title": "","description": ""}}},{"dedicated_theme_section_image": "",
              "dedicated_theme_section_heading": "Why Your Website Needs ReCAPTCHA",
              "dedicated_theme_section_description": "Hackers, bad bots, and malicious software can wreak havoc on websites that don\'t have the proper protection — like reCAPTCHA — in place. To outsmart bad bots, you need the right tools working for your website. That\'s where reCAPTCHA comes in. Let\'s dive into how to set it up on your Morweb-powered site.",
              "dedicated_theme_section_cards": {"1": {"title": "","description": ""}}}]';
        $data['dedicated_theme_sections_heading'] = '';

        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"GoogleCaptcha"},{"screenshots":"","screenshots_heading":"GoogleCaptcha"},{"screenshots":"","screenshots_heading":"GoogleCaptcha"}]';
        $data['addon_heading'] = 'Why choose dedicated modulesfor Your Business?';
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

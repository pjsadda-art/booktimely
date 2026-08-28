<?php

namespace Workdo\PromoCodes\Database\Seeders;

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
        $module = 'PromoCodes';

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'Promo Codes';
        $data['product_main_description'] = '<p>Bookinggo SaaS offers a robust promo code module designed to incentivize purchases and boost sales for businesses. With this feature, customers can apply promo codes during checkout, unlocking discounts, and special offers. Not only does this enhance the customer shopping experience, but it also provides an opportunity for businesses to attract new customers and retain existing ones through targeted promotions.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = '<h2>Promo Code <b> Module in</b> Bookinggo SaaS</h2>';
        $data['dedicated_theme_description'] = '<p>Learn how Bookinggo SaaS is promo code module empowers businesses to boost sales, attract new customers, and foster loyalty through targeted promotions and affiliate partnerships.</p>';
        $data['dedicated_theme_sections'] = '[
                                                {
                                                    "dedicated_theme_section_image": "",
                                                    "dedicated_theme_section_heading": "How Promo Codes Work",
                                                    "dedicated_theme_section_description": "When a customer enters a valid promo code at checkout, the discount associated with that code is applied to their total order amount. This encourages customers to complete their purchases by providing them with a valuable incentive. For businesses, promo codes serve as a powerful marketing tool, allowing them to run targeted campaigns, reward loyal customers, and drive sales for specific products or services.",
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
                                                    "dedicated_theme_section_heading": "Generating Promo Codes",
                                                    "dedicated_theme_section_description": "Businesses can easily generate promo codes within the Bookinggo SaaS platform. They have the flexibility to create unique codes with customizable parameters such as discount percentage, flat rate, validity period, and usage limits. This empowers businesses to tailor their promotions to align with their marketing objectives and target audience preferences.",
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
                                                    "dedicated_theme_section_heading": "Commission Structure",
                                                    "dedicated_theme_section_description": "One of the key benefits of the promo code module is its commission structure. When a customer uses a promo code provided by a partner or affiliate, the partner earns a commission on the sale. This incentivizes partners to promote the business and drive traffic to the platform, resulting in mutually beneficial partnerships that contribute to revenue growth.",
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
                                                    "dedicated_theme_section_heading": "Maximizing Revenue Opportunities:",
                                                    "dedicated_theme_section_description": "By leveraging the promo code module effectively, businesses can maximize revenue opportunities and expand their customer base. Whether it is through affiliate partnerships, targeted marketing campaigns, or customer loyalty programs, promo codes offer a versatile and impactful way to drive sales, increase brand awareness, and foster long-term customer relationships. With Bookinggo SaaS, businesses have the tools they need to unlock the full potential of promo code marketing and achieve their growth objectives.",
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
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"PromoCodes"},{"screenshots":"","screenshots_heading":"PromoCodes"},{"screenshots":"","screenshots_heading":"PromoCodes"},{"screenshots":"","screenshots_heading":"PromoCodes"},{"screenshots":"","screenshots_heading":"PromoCodes"}]';
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

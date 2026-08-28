<?php

namespace Workdo\CompoundService\Database\Seeders;

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
        $module = 'CompoundService';

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'CompoundService';
        $data['product_main_description'] = '<p>BookingGo\'s Compound Service Module is a revolutionary tool designed to transform the way you handle complex bookings that involve multiple services.  For businesses struggling with cumbersome booking processes, scattered appointments, and frustrated customers, this module offers a powerful solution to streamline operations, boost sales, and gain valuable customer insights.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = "<h2>Compound Service Module:<b>Streamline Bookings, Boost Sales,</b> Gain Insights</h2>";
        $data['dedicated_theme_description'] = '<p>Effortlessly manage complex bookings, create enticing service packages, and gain valuable customer insights - all with BookingGo\'s innovative Compound Service Module.</p>';
        $data['dedicated_theme_sections'] = '[
                                                {
                                                    "dedicated_theme_section_image": "",
                                                    "dedicated_theme_section_heading": "Effortless Booking for Multi-Service Packages: A Seamless Customer Experience",
                                                    "dedicated_theme_section_description": "Imagine offering a luxurious weekend getaway package that includes a stay at your charming bed and breakfast, a couples massage, a gourmet dinner reservation, and complimentary bicycles for exploring the picturesque countryside. Traditionally, this would require customers to book each element separately, leading to a fragmented and time-consuming process.  BookingGo\'s Compound Service Module allows you to create a single, cohesive package listing that showcases all the included services. Customers can effortlessly book the entire package in one go, with clear information about each service, pricing, and availability. This not only simplifies the booking process for your customers but also enhances their overall experience, leaving them feeling valued and ready for a relaxing getaway.",
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
                                                    "title": "Lorem Ipsum",
                                                        "description": ""
                                                    }
                                                    }
                                                },
                                                {
                                                    "dedicated_theme_section_image": "",
                                                    "dedicated_theme_section_heading": "Manage Complexities with Ease: Streamlining Operations and Staff Scheduling",
                                                    "dedicated_theme_section_description": "The Compound Service Module goes beyond simply improving the customer experience.  It also streamlines your internal operations by providing a centralized platform to manage complex bookings. You can define durations for each service within the package, ensuring appointments flow smoothly and eliminating scheduling conflicts.  Staff assignments can be pre-determined for each service, guaranteeing the right personnel are available for each appointment. This level of organization reduces confusion for both your staff and your customers, leading to a more efficient and stress-free work environment.",
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
                                                    "title": "Lorem Ipsum",
                                                        "description": ""
                                                    }
                                                    }
                                                },
                                                {
                                                    "dedicated_theme_section_image": "",
                                                    "dedicated_theme_section_heading": "Boost Sales and Revenue: The Power of Bundled Service Packages",
                                                    "dedicated_theme_section_description": "Offering bundled service packages through the Compound Service Module presents a unique opportunity to incentivize customers and increase your revenue.  By combining complementary services into an attractive package deal, you entice customers to book more services at once, maximizing the value they receive. This can lead to a significant increase in sales compared to individual service bookings. Additionally, creating enticing packages can attract new customers who are looking for a comprehensive and convenient solution. By showcasing the combined value of your services, you can effectively position yourself as a one-stop shop for their needs.",
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
                                                    "title": "Lorem Ipsum",
                                                        "description": ""
                                                    }
                                                    }
                                                },
                                                {
                                                    "dedicated_theme_section_image": "",
                                                    "dedicated_theme_section_heading": "Gain Valuable Insights: Understanding Customer Preferences and Optimizing Your Offerings",
                                                    "dedicated_theme_section_description": "BookingGo\'s Compound Service Module goes beyond streamlining bookings and boosting sales. It also provides valuable insights into your customer preferences. By tracking which packages are most popular, you can gain a deeper understanding of your customer base and their service preferences. This data can be used to tailor your offerings to better meet customer demands. You can identify popular service combinations and develop new packages that cater to specific customer segments.  Furthermore, by analyzing booking trends, you can identify underutilized services and potentially bundle them with more popular offerings to increase their visibility and bookings.  This data-driven approach empowers you to optimize your service packages, maximize customer satisfaction, and achieve long-term business growth.",
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
                                                    "title": "Lorem Ipsum",
                                                        "description": ""
                                                    }
                                                    }
                                                }
                                            ]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"CompoundService"},{"screenshots":"","screenshots_heading":"CompoundService"},{"screenshots":"","screenshots_heading":"CompoundService"},{"screenshots":"","screenshots_heading":"CompoundService"},{"screenshots":"","screenshots_heading":"CompoundService"}]';
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

        foreach ($data as $key => $value) {
            if (!MarketplacePageSetting::where('name', '=', $key)->where('module', '=', $module)->exists()) {
                MarketplacePageSetting::updateOrCreate(
                    [
                        'name' => $key,
                        'module' => $module

                    ],
                    [
                        'value' => $value
                    ]
                );
            }
        }
    }
}

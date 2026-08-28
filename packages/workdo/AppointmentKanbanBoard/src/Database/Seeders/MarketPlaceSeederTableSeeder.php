<?php

namespace Workdo\AppointmentKanbanBoard\Database\Seeders;

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
        $module = 'AppointmentKanbanBoard';

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'AppointmentKanbanBoard';
        $data['product_main_description'] = '<p>The Appointment Kanban Board displayed in BookingGo provides a clear and intuitive visual workflow for managing appointments. Each custom status, such as "Pending," "Confirm," "In Progress," and "Done," is represented as a separate column. These columns allow users to see the progress of each appointment at a glance, ensuring a smooth and organized scheduling process. Appointments are displayed as detailed cards, including key information like client name, email, service, staff, payment method, and date, making it easier to manage and track tasks efficiently. As users move cards between columns, the appointment status is automatically updated in real-time within the system.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = '<h2>Custom Status Columns <b>for </b> Tailored Organization</h2>';
        $data['dedicated_theme_description'] = '<p>The board\'s flexible design supports businesses with unique workflows by letting them define custom statuses. For example, in the screenshot, the statuses "Pending," "Confirm," "In Progress," and "Done" help categorize appointments as they move through their lifecycle. This feature ensures that the kanban board aligns with each business\'s operational needs, offering full control over how appointments are sorted and tracked. Moving a card from one column to another not only updates the visual workflow but also triggers a corresponding status change in the system.</p>';
        $data['dedicated_theme_sections'] = '[{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"Seamless Appointment Tracking","dedicated_theme_section_description":"<p>Each card on the kanban board includes essential details, such as the client\'s name and contact information, appointment date and time, assigned staff, service type, and payment status. This comprehensive view allows businesses to track every aspect of their appointments in real time. Drag-and-drop functionality enables users to easily move cards between columns as appointments progress, with each movement automatically reflecting the updated status, ensuring seamless updates and reducing manual effort.<\/p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null}}},{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"Enhanced Visibility for Teams","dedicated_theme_section_description":"<p>The Appointment Kanban Board fosters collaboration by providing a shared platform for teams to view and manage appointments. The clear separation of statuses allows team members to quickly identify what needs attention, such as confirming pending appointments or completing ongoing tasks. This enhanced visibility ensures that everyone stays informed, and status updates triggered by card movements improve coordination and productivity.<\/p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null}}}]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"AppointmentKanbanBoard"},{"screenshots":"","screenshots_heading":"AppointmentKanbanBoard"},{"screenshots":"","screenshots_heading":"AppointmentKanbanBoard"}]';
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

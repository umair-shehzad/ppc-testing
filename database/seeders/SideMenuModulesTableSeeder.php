<?php

namespace Database\Seeders;

use App\Models\SideMenuModule;
use Illuminate\Database\Seeder;

class SideMenuModulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $side_menu_modules = [
            [
                'name' => 'Main Menu',
                'component' => 'mainMenu',
                'icon_path' => env('APP_URL') . '/storage/menu_icons/main_menu.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CRM',
                'component' => 'crm',
                'icon_path' => env('APP_URL') . '/storage/menu_icons/crm.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SMS Messenger',
                'component' => 'sms',
                'icon_path' => env('APP_URL') . '/storage/menu_icons/sms_messenger.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fixed Price Mode',
                'component' => 'fixedPriceMode',
                'icon_path' => env('APP_URL') . '/storage/menu_icons/fixed_price_mode.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Premium Mode',
                'component' => 'premiumMode',
                'icon_path' => env('APP_URL') . '/storage/menu_icons/premium_mode.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Packages',
                'component' => 'packages',
                'icon_path' => env('APP_URL') . '/storage/menu_icons/packages.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Profile',
                'component' => 'profile',
                'icon_path' => env('APP_URL') . '/storage/menu_icons/profile.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Integrations',
                'component' => 'integrations',
                'icon_path' => env('APP_URL') . '/storage/menu_icons/integrations.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'FAQ',
                'component' => 'faq',
                'icon_path' => env('APP_URL') . '/storage/menu_icons/faq.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Support Ticket',
                'component' => 'supportTicket',
                'icon_path' => env('APP_URL') . '/storage/menu_icons/support_ticket.png',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];
        SideMenuModule::insert($side_menu_modules);
    }
}

<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Artisan::call('passport:install');
        $this->call([
            StatesTableSeeder::class,
            CountySeeder::class,
            CitySeeder::class,
            BusinessTypesTableSeeder::class,
            FaqsTableSeeder::class,
            UserReviewsTableSeeder::class,
            SideMenuModulesTableSeeder::class,
            RolesAndPermissionsSeeder::class,
            UsersTableSeeder::class,
            SellersTableSeeder::class,
            SellerEmailsTableSeeder::class,
            SellerPhonesTableSeeder::class,
            LeadsTableSeeder::class,
            OrdersTableSeeder::class
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\User;
use App\Utils\BusinessUtil;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class NewBusinessAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $businessUtil = app(BusinessUtil::class);

        // Define owner details
        $owner_details = [
            'surname' => 'Mr',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'username' => 'admin',
            'email' => 'admin@techseba.com',
            'password' => '123456',
            'language' => 'en',
        ];

        // Create owner user
        $user = User::create_user($owner_details);

        // Find default currency (BDT or USD or default to 1)
        $currency = DB::table('currencies')->where('code', 'BDT')->first();
        if (!$currency) {
            $currency = DB::table('currencies')->where('code', 'USD')->first();
        }
        $currency_id = $currency ? $currency->id : 1;

        // Define business details
        $business_details = [
            'name' => 'TechSeba POS Business',
            'start_date' => Carbon::now()->format('Y-m-d'),
            'currency_id' => $currency_id,
            'time_zone' => 'Asia/Dhaka',
            'fy_start_month' => 1,
            'accounting_method' => 'fifo',
            'enabled_modules' => ['purchases', 'add_sale', 'pos_sale', 'stock_transfers', 'stock_adjustment', 'expenses'],
            'owner_id' => $user->id,
        ];

        // Create the business
        $business = $businessUtil->createNewBusiness($business_details);

        // Update user with business id
        $user->business_id = $business->id;
        $user->save();

        // Initialize business default resources (default roles, default accounts, etc.)
        $businessUtil->newBusinessDefaultResources($business->id, $user->id);

        // Define default location
        $business_location = [
            'name' => 'Main Branch',
            'country' => 'Bangladesh',
            'state' => 'Dhaka',
            'city' => 'Dhaka',
            'zip_code' => '1200',
            'landmark' => 'Dhaka',
        ];

        // Add the location
        $new_location = $businessUtil->addLocation($business->id, $business_location);

        // Create permission for this location if it doesn't exist
        Permission::findOrCreate('location.'.$new_location->id);
    }
}

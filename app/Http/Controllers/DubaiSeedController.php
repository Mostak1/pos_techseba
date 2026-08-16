<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Database\Seeders\DubaiBusinessDataSeeder;
use Illuminate\Support\Facades\DB;

class DubaiSeedController extends Controller
{
    /**
     * Run Dubai Dummy Data Seeder via Web URL.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function seed(Request $request)
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', 600);

        try {
            $seeder = new DubaiBusinessDataSeeder();
            $seeder->run();

            // Fetch summary stats
            $productCount  = DB::table('products')->where('business_id', 2)->count();
            $supplierCount = DB::table('contacts')->where('business_id', 2)->where('type', 'supplier')->count();
            $customerCount = DB::table('contacts')->where('business_id', 2)->where('type', 'customer')->count();
            $purchaseCount = DB::table('transactions')->where('business_id', 2)->where('type', 'purchase')->count();
            $sellCount     = DB::table('transactions')->where('business_id', 2)->where('type', 'sell')->where('created_by', 2)->count();

            $html = "
            <!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Dubai Business Data Seeding Successful</title>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0f172a; color: #f8fafc; padding: 40px; display: flex; justify-content: center; align-items: center; min-height: 80vh; margin: 0; }
                    .card { background: #1e293b; border-radius: 16px; padding: 40px; max-width: 600px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5); border: 1px solid #334155; }
                    h1 { color: #38bdf8; margin-top: 0; font-size: 24px; display: flex; align-items: center; gap: 10px; }
                    .badge { background: #0284c7; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; font-weight: normal; }
                    ul { list-style: none; padding: 0; margin: 25px 0; }
                    li { background: #0f172a; margin-bottom: 12px; padding: 14px 20px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; border-left: 4px solid #38bdf8; }
                    .count { font-size: 18px; font-weight: bold; color: #4ade80; }
                    .label { font-size: 15px; color: #cbd5e1; }
                    .footer { text-align: center; margin-top: 30px; font-size: 13px; color: #94a3b8; }
                </style>
            </head>
            <body>
                <div class='card'>
                    <h1>Dubai Business Data Seeded! <span class='badge'>Business ID: 2</span></h1>
                    <p style='color: #94a3b8;'>Data seeded successfully for Location ID = 2 and Seller User ID = 2.</p>
                    <ul>
                        <li><span class='label'>Products (with generated images)</span><span class='count'>{$productCount} items</span></li>
                        <li><span class='label'>Suppliers (Dubai TRN & Contact)</span><span class='count'>{$supplierCount} items</span></li>
                        <li><span class='label'>Customers (Dubai Mobile & Name)</span><span class='count'>{$customerCount} items</span></li>
                        <li><span class='label'>Purchases (Stock Received)</span><span class='count'>{$purchaseCount} items</span></li>
                        <li><span class='label'>Sales / Sells (Seller User ID = 2)</span><span class='count'>{$sellCount} items</span></li>
                    </ul>
                    <div class='footer'>
                        Generated product images are located in <code>/public/uploads/img/</code>
                    </div>
                </div>
            </body>
            </html>
            ";

            return response($html, 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }
}

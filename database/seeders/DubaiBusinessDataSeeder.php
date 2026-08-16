<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DubaiBusinessDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Set higher limits for execution
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', 600);

        DB::beginTransaction();

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');

            $now = Carbon::now()->format('Y-m-d H:i:s');

            // ----------------------------------------------------
            // 1. Ensure User ID = 2 exists (Seller User ID = 2)
            // ----------------------------------------------------
            $user2 = DB::table('users')->where('id', 2)->first();
            if (!$user2) {
                DB::table('users')->insert([
                    'id' => 2,
                    'surname' => 'Mr',
                    'first_name' => 'Dubai',
                    'last_name' => 'Seller',
                    'username' => 'dubai_seller',
                    'email' => 'seller2@dubaitrading.ae',
                    'password' => Hash::make('123456'),
                    'language' => 'en',
                    'business_id' => 2,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('users')->where('id', 2)->update(['business_id' => 2]);
            }

            // ----------------------------------------------------
            // 2. Ensure Business ID = 2 and Location ID = 2 exist
            // ----------------------------------------------------
            $business = DB::table('business')->where('id', 2)->first();
            if (!$business) {
                $shortcuts = '{"pos":{"express_checkout":"shift+e","pay_n_ckeckout":"shift+p","draft":"shift+d","cancel":"shift+c","edit_discount":"shift+i","edit_order_tax":"shift+t","add_payment_row":"shift+r","finalize_payment":"shift+f","recent_product_quantity":"f2","add_new_product":"f4"}}';
                $prefixes = '{"purchase":"PO","stock_transfer":"ST","stock_adjustment":"SA","sell_return":"CN","expense":"EP","contacts":"CO","purchase_payment":"PP","sell_payment":"SP","business_location":"BL"}';

                DB::table('business')->insert([
                    'id' => 2,
                    'name' => 'Dubai Tech & General Trading',
                    'currency_id' => 124, // AED or default currency
                    'start_date' => '2024-01-01',
                    'tax_number_1' => '100298374100003',
                    'tax_label_1' => 'VAT TRN',
                    'default_profit_percent' => 25.00,
                    'owner_id' => 2,
                    'time_zone' => 'Asia/Dubai',
                    'fy_start_month' => 1,
                    'accounting_method' => 'fifo',
                    'sell_price_tax' => 'includes',
                    'sku_prefix' => 'DUB',
                    'keyboard_shortcuts' => $shortcuts,
                    'ref_no_prefixes' => $prefixes,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Ensure Business Location 2 exists
            $location = DB::table('business_locations')->where('id', 2)->first();
            if (!$location) {
                DB::table('business_locations')->insert([
                    'id' => 2,
                    'business_id' => 2,
                    'name' => 'Dubai Main Outlet',
                    'landmark' => 'Sheikh Zayed Road',
                    'country' => 'United Arab Emirates',
                    'state' => 'Dubai',
                    'city' => 'Dubai',
                    'zip_code' => '00000',
                    'invoice_scheme_id' => 1,
                    'invoice_layout_id' => 1,
                    'print_receipt_on_invoice' => 1,
                    'receipt_printer_type' => 'browser',
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else if ($location->business_id != 2) {
                DB::table('business_locations')->where('id', 2)->update([
                    'business_id' => 2,
                    'name' => 'Dubai Main Outlet',
                    'country' => 'United Arab Emirates',
                    'city' => 'Dubai'
                ]);
            }


            // ----------------------------------------------------
            // 2. Ensure Units, Categories & Brands for Business 2
            // ----------------------------------------------------
            $unitId = DB::table('units')->where('business_id', 2)->value('id');
            if (!$unitId) {
                $unitId = DB::table('units')->insertGetId([
                    'business_id' => 2,
                    'actual_name' => 'Pieces',
                    'short_name' => 'Pc(s)',
                    'allow_decimal' => 0,
                    'created_by' => 2,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $catCosmetics = $this->getOrCreateCategory(2, 'Cosmetics', 'COSM');
            $catMedicine  = $this->getOrCreateCategory(2, 'Medicine', 'MED');
            $catMobile    = $this->getOrCreateCategory(2, 'Mobile', 'MOB');
            $catLaptop    = $this->getOrCreateCategory(2, 'Laptop', 'LAP');

            $brandsMap = [
                'L\'Oreal' => $this->getOrCreateBrand(2, 'L\'Oreal'),
                'Nivea' => $this->getOrCreateBrand(2, 'Nivea'),
                'Maybelline' => $this->getOrCreateBrand(2, 'Maybelline'),
                'Pfizer' => $this->getOrCreateBrand(2, 'Pfizer'),
                'GSK' => $this->getOrCreateBrand(2, 'GSK'),
                'Apple' => $this->getOrCreateBrand(2, 'Apple'),
                'Samsung' => $this->getOrCreateBrand(2, 'Samsung'),
                'Xiaomi' => $this->getOrCreateBrand(2, 'Xiaomi'),
                'Dell' => $this->getOrCreateBrand(2, 'Dell'),
                'HP' => $this->getOrCreateBrand(2, 'HP'),
                'Lenovo' => $this->getOrCreateBrand(2, 'Lenovo'),
                'Asus' => $this->getOrCreateBrand(2, 'Asus'),
            ];

            // ----------------------------------------------------
            // 3. Generate Dummy Product Images on Disk
            // ----------------------------------------------------
            $imgDirectory = public_path('uploads/img');
            if (!file_exists($imgDirectory)) {
                @mkdir($imgDirectory, 0777, true);
            }

            // ----------------------------------------------------
            // 4. Create 100+ Products with Images
            // ----------------------------------------------------
            $productTemplates = $this->getDubaiProductTemplates();
            $seededProducts = []; // Array of {product_id, variation_id, purchase_price, sell_price}

            foreach ($productTemplates as $idx => $p) {
                $sku = 'DUB-' . strtoupper(substr($p['cat_code'], 0, 3)) . '-' . sprintf('%04d', $idx + 1);
                $imgFilename = 'dubai_prod_' . ($idx + 1) . '.png';
                $imgPath = $imgDirectory . '/' . $imgFilename;

                // Create placeholder image using GD if file doesn't exist
                $this->generateProductImage($imgPath, $p['name'], $p['category_name'], $p['bg_color']);

                $productId = DB::table('products')->insertGetId([
                    'name' => $p['name'],
                    'business_id' => 2,
                    'type' => 'single',
                    'unit_id' => $unitId,
                    'brand_id' => $brandsMap[$p['brand']] ?? null,
                    'category_id' => $p['category_id'],
                    'tax_type' => 'exclusive',
                    'enable_stock' => 1,
                    'alert_quantity' => 10,
                    'sku' => $sku,
                    'barcode_type' => 'C128',
                    'image' => $imgFilename,
                    'created_by' => 2,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $productVarId = DB::table('product_variations')->insertGetId([
                    'name' => 'DUMMY',
                    'product_id' => $productId,
                    'is_dummy' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $purchasePrice = $p['cost'];
                $sellPrice = $p['price'];

                $variationId = DB::table('variations')->insertGetId([
                    'name' => 'DUMMY',
                    'product_id' => $productId,
                    'sub_sku' => $sku,
                    'product_variation_id' => $productVarId,
                    'default_purchase_price' => $purchasePrice,
                    'dpp_inc_tax' => $purchasePrice,
                    'profit_percent' => 25.00,
                    'default_sell_price' => $sellPrice,
                    'sell_price_inc_tax' => $sellPrice,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Create variation location stock detail for location 2
                DB::table('variation_location_details')->insert([
                    'product_id' => $productId,
                    'product_variation_id' => $productVarId,
                    'variation_id' => $variationId,
                    'location_id' => 2,
                    'qty_available' => 500, // base opening stock
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $seededProducts[] = [
                    'product_id' => $productId,
                    'product_variation_id' => $productVarId,
                    'variation_id' => $variationId,
                    'purchase_price' => $purchasePrice,
                    'sell_price' => $sellPrice,
                ];
            }

            // ----------------------------------------------------
            // 5. Seed 100+ Dubai Suppliers
            // ----------------------------------------------------
            $supplierIds = [];
            $dubaiSupplierCompanies = [
                'Al Maktoum Logistics & Wholesale LLC', 'Gulf Healthcare & Pharma Distributors',
                'Emirates Tech & Electronics Dubai', 'Dubai Cosmetics & Care Ltd.',
                'Burj Commercial Suppliers UAE', 'Deira Trading Establishment',
                'Jebel Ali Global Importers', 'Al Barsha Medical Supplies',
                'Business Bay Digital Hub', 'Marina Retail Wholesalers'
            ];

            for ($i = 1; $i <= 105; $i++) {
                $compName = $dubaiSupplierCompanies[($i - 1) % count($dubaiSupplierCompanies)] . ' #' . $i;
                $contactId = DB::table('contacts')->insertGetId([
                    'business_id' => 2,
                    'type' => 'supplier',
                    'supplier_business_name' => $compName,
                    'name' => 'Manager ' . $this->getDubaiName($i),
                    'tax_number' => '100' . sprintf('%012d', $i),
                    'city' => 'Dubai',
                    'state' => 'Dubai',
                    'country' => 'United Arab Emirates',
                    'land_mark' => 'Deira Commercial Zone',
                    'mobile' => '+971 50 ' . rand(1000000, 9999999),
                    'contact_id' => 'SUP-DUB-' . sprintf('%04d', $i),
                    'created_by' => 2,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $supplierIds[] = $contactId;
            }

            // ----------------------------------------------------
            // 6. Seed 100+ Dubai Customers
            // ----------------------------------------------------
            $customerIds = [];
            for ($i = 1; $i <= 105; $i++) {
                $custName = $this->getDubaiName($i + 50);
                $contactId = DB::table('contacts')->insertGetId([
                    'business_id' => 2,
                    'type' => 'customer',
                    'name' => $custName,
                    'tax_number' => null,
                    'city' => 'Dubai',
                    'state' => 'Dubai',
                    'country' => 'United Arab Emirates',
                    'land_mark' => 'Downtown Dubai',
                    'mobile' => '+971 55 ' . rand(1000000, 9999999),
                    'contact_id' => 'CUS-DUB-' . sprintf('%04d', $i),
                    'created_by' => 2,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $customerIds[] = $contactId;
            }

            // ----------------------------------------------------
            // 7. Seed 100+ Purchases (type = 'purchase', location_id = 2)
            // ----------------------------------------------------
            for ($i = 1; $i <= 105; $i++) {
                $supplierId = $supplierIds[array_rand($supplierIds)];
                $transDate = Carbon::now()->subDays(rand(1, 180))->format('Y-m-d H:i:s');
                $refNo = 'PO-DUB-' . sprintf('%04d', $i);

                $txId = DB::table('transactions')->insertGetId([
                    'business_id' => 2,
                    'location_id' => 2,
                    'type' => 'purchase',
                    'status' => 'received',
                    'payment_status' => 'paid',
                    'contact_id' => $supplierId,
                    'ref_no' => $refNo,
                    'transaction_date' => $transDate,
                    'total_before_tax' => 0,
                    'tax_amount' => 0,
                    'final_total' => 0,
                    'created_by' => 2,
                    'created_at' => $transDate,
                    'updated_at' => $transDate,
                ]);

                // Attach 1-3 purchase lines
                $lineCount = rand(1, 3);
                $totalPurchaseVal = 0;

                for ($l = 0; $l < $lineCount; $l++) {
                    $prodItem = $seededProducts[array_rand($seededProducts)];
                    $qty = rand(10, 50);
                    $lineTotal = $qty * $prodItem['purchase_price'];
                    $totalPurchaseVal += $lineTotal;

                    DB::table('purchase_lines')->insert([
                        'transaction_id' => $txId,
                        'product_id' => $prodItem['product_id'],
                        'variation_id' => $prodItem['variation_id'],
                        'quantity' => $qty,
                        'purchase_price' => $prodItem['purchase_price'],
                        'purchase_price_inc_tax' => $prodItem['purchase_price'],
                        'item_tax' => 0,
                        'created_at' => $transDate,
                        'updated_at' => $transDate,
                    ]);

                    // Increase stock in location 2
                    DB::table('variation_location_details')
                        ->where('variation_id', $prodItem['variation_id'])
                        ->where('location_id', 2)
                        ->increment('qty_available', $qty);
                }

                DB::table('transactions')->where('id', $txId)->update([
                    'total_before_tax' => $totalPurchaseVal,
                    'final_total' => $totalPurchaseVal,
                ]);
            }

            // ----------------------------------------------------
            // 8. Seed 100+ Sales / Sells (type = 'sell', location_id = 2, created_by = 2)
            // ----------------------------------------------------
            for ($i = 1; $i <= 105; $i++) {
                $customerId = $customerIds[array_rand($customerIds)];
                $transDate = Carbon::now()->subDays(rand(1, 150))->format('Y-m-d H:i:s');
                $invoiceNo = 'INV-DUB-' . sprintf('%04d', $i);

                $txId = DB::table('transactions')->insertGetId([
                    'business_id' => 2,
                    'location_id' => 2,
                    'type' => 'sell',
                    'status' => 'final',
                    'payment_status' => 'paid',
                    'contact_id' => $customerId,
                    'invoice_no' => $invoiceNo,
                    'transaction_date' => $transDate,
                    'total_before_tax' => 0,
                    'tax_amount' => 0,
                    'discount_type' => 'fixed',
                    'discount_amount' => 0,
                    'final_total' => 0,
                    'created_by' => 2, // seller user id = 2
                    'created_at' => $transDate,
                    'updated_at' => $transDate,
                ]);

                // Attach 1-4 sell lines
                $lineCount = rand(1, 4);
                $totalSellVal = 0;

                for ($l = 0; $l < $lineCount; $l++) {
                    $prodItem = $seededProducts[array_rand($seededProducts)];
                    $qty = rand(1, 5);
                    $lineTotal = $qty * $prodItem['sell_price'];
                    $totalSellVal += $lineTotal;

                    DB::table('transaction_sell_lines')->insert([
                        'transaction_id' => $txId,
                        'product_id' => $prodItem['product_id'],
                        'variation_id' => $prodItem['variation_id'],
                        'quantity' => $qty,
                        'unit_price' => $prodItem['sell_price'],
                        'unit_price_inc_tax' => $prodItem['sell_price'],
                        'item_tax' => 0,
                        'created_at' => $transDate,
                        'updated_at' => $transDate,
                    ]);

                    // Deduct stock in location 2
                    DB::table('variation_location_details')
                        ->where('variation_id', $prodItem['variation_id'])
                        ->where('location_id', 2)
                        ->decrement('qty_available', $qty);
                }

                DB::table('transactions')->where('id', $txId)->update([
                    'total_before_tax' => $totalSellVal,
                    'final_total' => $totalSellVal,
                ]);

                // Add payment record
                DB::table('transaction_payments')->insert([
                    'transaction_id' => $txId,
                    'amount' => $totalSellVal,
                    'method' => 'cash',
                    'paid_on' => $transDate,
                    'created_by' => 2,
                    'created_at' => $transDate,
                    'updated_at' => $transDate,
                ]);
            }

            DB::commit();
            echo "Dubai business data (Business 2, Location 2, Seller User 2) seeded successfully!\n";
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function getOrCreateCategory($businessId, $name, $code)
    {
        $cat = DB::table('categories')->where('business_id', $businessId)->where('name', $name)->first();
        if ($cat) {
            return $cat->id;
        }
        return DB::table('categories')->insertGetId([
            'name' => $name,
            'business_id' => $businessId,
            'short_code' => $code,
            'parent_id' => 0,
            'created_by' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    private function getOrCreateBrand($businessId, $name)
    {
        $brand = DB::table('brands')->where('business_id', $businessId)->where('name', $name)->first();
        if ($brand) {
            return $brand->id;
        }
        return DB::table('brands')->insertGetId([
            'business_id' => $businessId,
            'name' => $name,
            'created_by' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    private function generateProductImage($filePath, $title, $category, $bgColorHex)
    {
        if (file_exists($filePath)) {
            return;
        }

        $width = 400;
        $height = 400;
        $im = @imagecreatetruecolor($width, $height);
        if (!$im) return;

        // Parse RGB hex
        $bgColorHex = ltrim($bgColorHex, '#');
        $r = hexdec(substr($bgColorHex, 0, 2));
        $g = hexdec(substr($bgColorHex, 2, 2));
        $b = hexdec(substr($bgColorHex, 4, 2));

        $bgColor = imagecolorallocate($im, $r, $g, $b);
        $textColor = imagecolorallocate($im, 255, 255, 255);
        $subTextColor = imagecolorallocate($im, 240, 240, 240);
        $badgeBg = imagecolorallocate($im, 0, 0, 0);

        imagefill($im, 0, 0, $bgColor);

        // Draw header badge rectangle
        imagefilledrectangle($im, 20, 20, 380, 70, $badgeBg);
        imagestring($im, 5, 35, 35, strtoupper($category) . " - DUBAI STORE", $textColor);

        // Word wrap title
        $words = explode(' ', $title);
        $lines = [];
        $currentLine = '';
        foreach ($words as $w) {
            if (strlen($currentLine . ' ' . $w) > 22) {
                $lines[] = trim($currentLine);
                $currentLine = $w;
            } else {
                $currentLine .= ' ' . $w;
            }
        }
        if (!empty($currentLine)) {
            $lines[] = trim($currentLine);
        }

        $startY = 140;
        foreach ($lines as $line) {
            imagestring($im, 5, 30, $startY, $line, $textColor);
            $startY += 30;
        }

        // Draw footer badge
        imagefilledrectangle($im, 20, 330, 380, 380, $badgeBg);
        imagestring($im, 4, 40, 345, "ORIGINAL PRODUCT - AED CURRENCY", $subTextColor);

        imagepng($im, $filePath);
        imagedestroy($im);
    }

    private function getDubaiName($idx)
    {
        $names = [
            'Rashid Al Maktoum', 'Hamdan Al Nahyan', 'Zayed Al Qasimi', 'Sultan Al Nuaimi',
            'Fatima Al Mansoori', 'Ayesha Al Shamsi', 'Tariq Mahmood', 'Khalid Al Hashmi',
            'Mohammed Al Falasi', 'Mariam Al Suwaidi', 'Omar Al Harbi', 'Saeed Al Kaabi',
            'Noura Al Blooshi', 'Ahmed Al Haddad', 'Reem Al Zaabi', 'Youssef Al Hosani'
        ];
        return $names[($idx - 1) % count($names)] . ' (' . $idx . ')';
    }

    private function getDubaiProductTemplates()
    {
        // 25 Cosmetics + 25 Medicines + 25 Mobiles + 25 Laptops = 100 items
        $items = [];

        // Cosmetics (25)
        $cosmeticsNames = [
            'L\'Oreal Paris Revitalift Anti-Aging Cream 50ml', 'Nivea Soft Refreshingly Soft Cream 300ml',
            'Maybelline SuperStay Matte Ink Liquid Lipstick', 'L\'Oreal Color Riche Intense Volume Matte',
            'Nivea Body Lotion Express Hydration 400ml', 'Maybelline Fit Me Matte Poreless Foundation',
            'L\'Oreal Elvive Extra Ordinary Hair Oil 100ml', 'Nivea Men Deep Impact Face Wash 100g',
            'Maybelline Lash Sensational Waterproof Mascara', 'L\'Oreal Hydra Genius Water Moisturizer',
            'Nivea Sun Protect & Moisture SPF 50 200ml', 'Maybelline Instant Age Rewind Concealer',
            'L\'Oreal Paris Pure Clay Mask Charcoal 50ml', 'Nivea Pearl & Beauty Deodorant Roll-on',
            'Maybelline Hyper Precise Liquid Eyeliner Black', 'L\'Oreal Infallible 24H Fresh Wear Powder',
            'Nivea Crème Classic Blue Tin 250ml', 'Maybelline Baby Lips Moisturizing Lip Balm',
            'L\'Oreal Elvive Total Repair 5 Shampoo 400ml', 'Nivea Micellar Air Water Makeup Remover',
            'Maybelline Tattoo Liner Gel Pencil Liquid Waterproof', 'L\'Oreal Sugar Scrubs Nourishing Lip Scrub',
            'Nivea Men Sensitive Shaving Foam 200ml', 'Maybelline Cheek Heat Gel-Cream Blush',
            'L\'Oreal Paris True Match Super-Blendable Fluid'
        ];
        foreach ($cosmeticsNames as $i => $name) {
            $brand = ($i % 3 == 0) ? 'L\'Oreal' : (($i % 3 == 1) ? 'Nivea' : 'Maybelline');
            $items[] = [
                'name' => $name,
                'category_name' => 'Cosmetics',
                'category_id' => 1,
                'cat_code' => 'COSM',
                'brand' => $brand,
                'cost' => rand(25, 120),
                'price' => rand(35, 180),
                'bg_color' => '#881144'
            ];
        }

        // Medicines (25)
        $medicineNames = [
            'Panadol Extra 500mg Paracetamol (24 Tablets)', 'Augmentin 1g Amoxicillin (14 Film-Coated)',
            'Strepsils Honey & Lemon Lozenges (24 Pack)', 'Voltaren Emulgel 1% Pain Relief Gel 100g',
            'Advil Extra Strength 400mg Liqui-Gels (30s)', 'Otrivin 0.1% Adult Nasal Spray Drops 10ml',
            'Gaviscon Double Action Liquid Mint 300ml', 'Brufen 400mg Ibuprofen Pain Reliever (30s)',
            'Panadol Cold & Flu Day Non-Drowsy (24s)', 'Claritin 10mg Allergy Relief Tablets (10s)',
            'Nexium 20mg Acid Reflux Relief (14 Tablets)', 'Zyrtec 10mg Antihistamine Allergy Relief',
            'Panadol Joint 665mg Extended Release (18s)', 'Augmentin 625mg Antibiotic Tablets (20s)',
            'Strepsils Extra Triple Action Blackcurrant', 'Voltaren 50mg Anti-inflammatory Tablets',
            'Advil PM Pain Reliever & Nighttime Sleep Aid', 'Otrivin Pediatric Nasal Drops 10ml',
            'Gaviscon Peppermint Chewable Tablets (16s)', 'Brufen Retard 800mg Prolonged Release',
            'Panadol Night Pain Reliever & Sleep Aid', 'Claritin Syrup Peach Flavor for Children 100ml',
            'Nexium 40mg Prescription Strength Control', 'Strepsils Orange with Vitamin C (24 Lozenges)',
            'Voltaren 100mg Suppositories (5 Pack)'
        ];
        foreach ($medicineNames as $i => $name) {
            $brand = ($i % 2 == 0) ? 'Pfizer' : 'GSK';
            $items[] = [
                'name' => $name,
                'category_name' => 'Medicine',
                'category_id' => 2,
                'cat_code' => 'MED',
                'brand' => $brand,
                'cost' => rand(15, 80),
                'price' => rand(25, 130),
                'bg_color' => '#006655'
            ];
        }

        // Mobiles (25)
        $mobileNames = [
            'Apple iPhone 15 Pro Max 256GB Natural Titanium', 'Samsung Galaxy S24 Ultra 512GB Titanium Black',
            'Xiaomi Redmi Note 13 Pro+ 5G 256GB Black', 'Apple iPhone 15 128GB Blue 5G Dual SIM',
            'Samsung Galaxy Z Fold 5 5G 512GB Phantom Black', 'Xiaomi 14 Ultra 512GB Leica Camera Black',
            'Apple iPhone 14 Pro 128GB Deep Purple', 'Samsung Galaxy A55 5G 256GB Awesome Navy',
            'Xiaomi Poco X6 Pro 5G 512GB Yellow', 'Apple iPhone 13 128GB Midnight Aluminum',
            'Samsung Galaxy S23 FE 5G 128GB Mint', 'Xiaomi Redmi 13C 256GB Midnight Black',
            'Apple iPhone 15 Plus 256GB Pink Dual SIM', 'Samsung Galaxy Z Flip 5 256GB Mint Green',
            'Xiaomi Note 12 Pro 5G 128GB Sky Blue', 'Apple iPhone SE 3rd Gen 64GB Product Red',
            'Samsung Galaxy A35 5G 128GB Awesome Iceblue', 'Xiaomi Poco M6 Pro 256GB Purple',
            'Apple iPhone 14 256GB Starlight Dual SIM', 'Samsung Galaxy S24+ 5G 256GB Cobalt Violet',
            'Xiaomi 13T Pro 512GB Meadow Green', 'Apple iPhone 12 128GB Black 5G OLED',
            'Samsung Galaxy A15 4G 128GB Light Blue', 'Xiaomi Redmi Note 13 4G 256GB Mint Green',
            'Apple iPhone 15 Pro 512GB Blue Titanium'
        ];
        foreach ($mobileNames as $i => $name) {
            $brand = ($i % 3 == 0) ? 'Apple' : (($i % 3 == 1) ? 'Samsung' : 'Xiaomi');
            $items[] = [
                'name' => $name,
                'category_name' => 'Mobile',
                'category_id' => 3,
                'cat_code' => 'MOB',
                'brand' => $brand,
                'cost' => rand(800, 3800),
                'price' => rand(1100, 4800),
                'bg_color' => '#112255'
            ];
        }

        // Laptops (25)
        $laptopNames = [
            'Apple MacBook Air 13-inch M2 8GB RAM 256GB SSD', 'Dell XPS 15 9530 Intel Core i7 16GB 512GB RTX',
            'HP Spectre x360 Convertible Core i7 16GB 1TB', 'Lenovo ThinkPad X1 Carbon Gen 11 Core i7 16GB',
            'Asus ROG Zephyrus G14 Gaming Ryzen 9 16GB RTX', 'Apple MacBook Pro 14-inch M3 Pro 18GB 512GB',
            'Dell Inspiron 16 Laptop Core i5 16GB 512GB SSD', 'HP Pavilion 15 Laptop AMD Ryzen 7 16GB 512GB',
            'Lenovo IdeaPad Slim 5 Core i7 16GB 512GB SSD', 'Asus ZenBook 14 OLED Touch Core i7 16GB 1TB',
            'Apple MacBook Air 15-inch M2 16GB 512GB Midnight', 'Dell Alienware m16 Gaming Core i9 32GB 1TB RTX',
            'HP Omen 16 Gaming Core i7 16GB 1TB RTX 4060', 'Lenovo Legion Slim 5 Ryzen 7 16GB 512GB RTX',
            'Asus TUF Gaming A15 Laptop Ryzen 7 16GB 512GB', 'Apple MacBook Pro 16-inch M3 Max 36GB 1TB',
            'Dell Latitude 5440 Core i7 16GB 512GB Business', 'HP Envy x360 2-in-1 Laptop Core i7 16GB 1TB',
            'Lenovo Yoga Slim 7 Pro OLED Core i7 16GB 1TB', 'Asus VivoBook Pro 15 OLED Ryzen 7 16GB 512GB',
            'Apple MacBook Air 13-inch M3 8GB 256GB Space Grey', 'Dell Vostro 3520 Laptop Core i5 8GB 512GB SSD',
            'HP ProBook 450 G10 Core i7 16GB 512GB SSD', 'Lenovo ThinkBook 15 Gen 4 Core i5 8GB 512GB',
            'Asus ROG Strix G16 Core i7 16GB 1TB RTX 4070'
        ];
        foreach ($laptopNames as $i => $name) {
            $brand = ($i % 5 == 0) ? 'Apple' : (($i % 5 == 1) ? 'Dell' : (($i % 5 == 2) ? 'HP' : (($i % 5 == 3) ? 'Lenovo' : 'Asus')));
            $items[] = [
                'name' => $name,
                'category_name' => 'Laptop',
                'category_id' => 4,
                'cat_code' => 'LAP',
                'brand' => $brand,
                'cost' => rand(1500, 7500),
                'price' => rand(2000, 9500),
                'bg_color' => '#332244'
            ];
        }

        return $items;
    }
}

<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Page;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    private string $uploads;

    public function run(): void
    {
        $this->uploads = public_path('uploads');
        foreach (['products', 'categories', 'brands', 'sliders', 'testimonials', 'settings'] as $dir) {
            File::ensureDirectoryExists("{$this->uploads}/{$dir}");
        }
        File::ensureDirectoryExists(public_path('images'));
        File::put(public_path('images/placeholder.svg'), ProductArt::placeholder());
        File::put(public_path('images/favicon.svg'), ProductArt::favicon());

        $this->settings();

        User::updateOrCreate(['email' => 'admin@grihosthali.com'], [
            'name' => 'Grihosthali Admin',
            'phone' => '01700000000',
            'password' => 'admin123',
            'is_admin' => true,
        ]);

        $brands = $this->brands();
        $categories = $this->categories();
        $this->products($categories, $brands);
        $this->sliders();
        $this->testimonials();
        $this->pages();

        Coupon::updateOrCreate(['code' => 'WELCOME10'], ['type' => 'percent', 'value' => 10, 'min_order' => 1000, 'is_active' => true]);
        Coupon::updateOrCreate(['code' => 'GHEE100'], ['type' => 'fixed', 'value' => 100, 'min_order' => 1500, 'is_active' => true]);
    }

    private function settings(): void
    {
        Setting::put([
            'site_name' => 'Grihosthali',
            'site_tagline' => 'Pure • Natural • Traditional',
            'top_notice' => 'সারা বাংলাদেশে ক্যাশ অন ডেলিভারি — খাঁটি স্বাদ, সবার রান্নায়',
            'phone' => '+8801700000000',
            'hotline' => '+8801700000000',
            'email' => 'info@grihosthali.com',
            'whatsapp' => '8801700000000',
            'address' => 'Dhaka, Bangladesh',
            'footer_about' => 'গৃহস্থালী — Grihosthali brings pure, natural and traditional food from the villages of Bangladesh straight to your kitchen. No adulteration, no compromise.',
            'facebook' => 'https://facebook.com/',
            'instagram' => 'https://instagram.com/',
            'youtube' => 'https://youtube.com/',
            'shipping_inside' => '70',
            'shipping_outside' => '130',
            'free_shipping_min' => '3000',
            'primary_color' => '#1f4d36',
            'secondary_color' => '#c9a24a',
            'meta_description' => 'Grihosthali — premium desi ghee, pure honey, cold-pressed mustard oil and natural foods delivered across Bangladesh with cash on delivery.',
            'meta_keywords' => 'grihosthali, desi ghee, gawa ghee, honey, mustard oil, organic food bangladesh',
        ]);
    }

    private function brands(): array
    {
        $list = [
            'Grihosthali' => 'PURE • NATURAL',
            'Sundarban Gold' => 'WILD HONEY',
            'Village Fresh' => 'FARM TO HOME',
            'Pure Harvest' => 'ORGANIC',
            "Nature's Gift" => 'TRADITIONAL',
            'Desh Kitchen' => 'SPICES & MORE',
        ];
        $brands = [];
        foreach ($list as $name => $tag) {
            $slug = Str::slug($name);
            File::put("{$this->uploads}/brands/{$slug}.svg", ProductArt::brand($name, $tag));
            $brands[$name] = Brand::updateOrCreate(['slug' => $slug], ['name' => $name, 'logo' => "brands/{$slug}.svg", 'is_active' => true]);
        }

        return $brands;
    }

    private function categories(): array
    {
        // name => [type, colour, label, show on home]
        $list = [
            'Ghee & Butter' => ['jar', '#f2b51c', 'Desi Ghee', true],
            'Honey' => ['jar', '#d98c0b', 'Pure Honey', true],
            'Oil' => ['bottle', '#d9a514', 'Mustard Oil', true],
            'Dates' => ['box', '#5a2d16', 'Dates', true],
            'Nuts & Seeds' => ['pouch', '#d9b98a', 'Nuts & Seeds', true],
            'Spices' => ['pouch', '#b8481b', 'Spices', true],
            'Rice & Pulses' => ['pouch', '#efe6cf', 'Rice & Dal', false],
            'Tea' => ['box', '#4a6b2a', 'Tea', false],
            'Pickles' => ['jar', '#c8741c', 'Achar', false],
            'Combo Pack' => ['box', '#c9a24a', 'Combo', false],
        ];
        $categories = [];
        $order = 1;
        foreach ($list as $name => [$type, $color, $label, $home]) {
            $slug = Str::slug($name);
            File::put("{$this->uploads}/categories/{$slug}.svg", ProductArt::product($type, $label, $color));
            $categories[$name] = Category::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'image' => "categories/{$slug}.svg",
                'sort_order' => $order++,
                'show_in_menu' => true,
                'show_on_home' => $home,
                'is_active' => true,
            ]);
        }

        return $categories;
    }

    private function products(array $categories, array $brands): void
    {
        // [name, category, brand, type, colour, label, sub, price, discount, flag, top selling, unit, stock]
        $rows = [
            ['Premium Desi Ghee 500ml', 'Ghee & Butter', 'Grihosthali', 'photo', '', '', '', 950, 850, 'Best Selling', true, '500 ml', 120],
            ['Premium Desi Ghee 1kg', 'Ghee & Butter', 'Grihosthali', 'jar', '#f2b51c', 'Premium Desi Ghee', '1 KG', 1850, 1690, 'Best Selling', true, '1 kg', 80],
            ['Gawa Ghee 250gm', 'Ghee & Butter', 'Village Fresh', 'jar', '#f0c03a', 'Gawa Ghee', '250 GM', 520, null, 'New Arrival', false, '250 gm', 60],
            ['Sundarban Cow Ghee 1kg', 'Ghee & Butter', 'Village Fresh', 'jar', '#e9a60f', 'Cow Ghee', '1 KG', 1990, 1890, null, false, '1 kg', 40],
            ['Butter Oil 500gm', 'Ghee & Butter', 'Pure Harvest', 'jar', '#f6d36b', 'Butter Oil', '500 GM', 690, null, null, false, '500 gm', 35],

            ['Sundarban Honey 1kg', 'Honey', 'Sundarban Gold', 'jar', '#c97a06', 'Sundarban Honey', '1 KG', 2500, 2125, 'Best Selling', true, '1 kg', 70],
            ['Black Seed Honey 500gm', 'Honey', 'Sundarban Gold', 'jar', '#7a3d06', 'Black Seed Honey', '500 GM', 850, 790, null, true, '500 gm', 55],
            ['Lychee Flower Honey 1kg', 'Honey', 'Sundarban Gold', 'jar', '#e3a020', 'Lychee Honey', '1 KG', 1350, 1150, 'Hot Deal', false, '1 kg', 45],
            ['Mustard Flower Honey 1kg', 'Honey', "Nature's Gift", 'jar', '#eebb3a', 'Mustard Honey', '1 KG', 950, null, null, false, '1 kg', 50],
            ['Sidr Honey 500gm', 'Honey', "Nature's Gift", 'jar', '#a55b07', 'Sidr Honey', '500 GM', 1450, 1250, 'Limited Stock', false, '500 gm', 12],

            ['Cold Pressed Mustard Oil 1 Litre', 'Oil', 'Grihosthali', 'bottle', '#d9a514', 'Mustard Oil', '1 LITRE', 380, 350, 'Best Selling', true, '1 litre', 150],
            ['Cold Pressed Mustard Oil 5 Litre', 'Oil', 'Grihosthali', 'bottle', '#cf9a10', 'Mustard Oil', '5 LITRE', 1750, 1600, null, false, '5 litre', 40],
            ['Virgin Coconut Oil 500ml', 'Oil', 'Pure Harvest', 'bottle', '#f4ecd0', 'Coconut Oil', '500 ML', 650, 590, 'Organic', false, '500 ml', 45],
            ['Black Seed Oil 250ml', 'Oil', "Nature's Gift", 'bottle', '#3a2a12', 'Black Seed Oil', '250 ML', 550, null, null, false, '250 ml', 30],
            ['Sesame Oil 500ml', 'Oil', 'Village Fresh', 'bottle', '#c08a3e', 'Sesame Oil', '500 ML', 520, null, null, false, '500 ml', 30],

            ['Ajwa Dates 1kg', 'Dates', "Nature's Gift", 'box', '#3d1f10', 'Ajwa Dates', '1 KG', 2400, 2150, 'Best Selling', true, '1 kg', 40],
            ['Medjool Dates 1kg', 'Dates', "Nature's Gift", 'box', '#6b3518', 'Medjool Dates', '1 KG', 2200, null, null, false, '1 kg', 35],
            ['Mariam Dates 500gm', 'Dates', "Nature's Gift", 'box', '#5a2d16', 'Mariam Dates', '500 GM', 850, 780, null, false, '500 gm', 45],
            ['Sukkari Dates 1kg', 'Dates', "Nature's Gift", 'box', '#9a5a26', 'Sukkari Dates', '1 KG', 1100, null, 'New Arrival', false, '1 kg', 50],

            ['Cashew Nuts 500gm', 'Nuts & Seeds', 'Pure Harvest', 'pouch', '#ead6a8', 'Cashew Nuts', '500 GM', 850, 780, null, true, '500 gm', 60],
            ['Almonds 500gm', 'Nuts & Seeds', 'Pure Harvest', 'pouch', '#b07445', 'Almonds', '500 GM', 790, null, null, false, '500 gm', 60],
            ['Chia Seeds 250gm', 'Nuts & Seeds', 'Pure Harvest', 'pouch', '#7b7b74', 'Chia Seeds', '250 GM', 350, 320, 'Organic', false, '250 gm', 80],
            ['Mixed Dry Fruits 500gm', 'Nuts & Seeds', 'Pure Harvest', 'pouch', '#9b5a2c', 'Mixed Dry Fruits', '500 GM', 990, 890, 'Hot Deal', false, '500 gm', 40],
            ['Kalojira (Black Seed) 250gm', 'Nuts & Seeds', 'Village Fresh', 'pouch', '#2b2b2b', 'Kalojira', '250 GM', 180, null, null, false, '250 gm', 90],

            ['Shahi Garam Masala 100gm', 'Spices', 'Desh Kitchen', 'pouch', '#8b3a1a', 'Garam Masala', '100 GM', 220, 190, null, false, '100 gm', 100],
            ['Turmeric Powder 500gm', 'Spices', 'Desh Kitchen', 'pouch', '#e3a21a', 'Turmeric Powder', '500 GM', 320, null, 'Organic', false, '500 gm', 100],
            ['Red Chili Powder 500gm', 'Spices', 'Desh Kitchen', 'pouch', '#b8261b', 'Chili Powder', '500 GM', 380, 350, null, false, '500 gm', 100],
            ['Cumin Powder 200gm', 'Spices', 'Desh Kitchen', 'pouch', '#8a6a3a', 'Cumin Powder', '200 GM', 260, null, null, false, '200 gm', 100],

            ['Chinigura Aromatic Rice 2kg', 'Rice & Pulses', 'Village Fresh', 'pouch', '#f5efdf', 'Chinigura Rice', '2 KG', 360, null, null, false, '2 kg', 80],
            ['Kataribhog Rice 5kg', 'Rice & Pulses', 'Village Fresh', 'pouch', '#f1e9d4', 'Kataribhog Rice', '5 KG', 650, 620, null, false, '5 kg', 60],
            ['Deshi Moshur Dal 1kg', 'Rice & Pulses', 'Village Fresh', 'pouch', '#e58b3a', 'Moshur Dal', '1 KG', 160, null, null, false, '1 kg', 120],

            ['Premium Green Tea 100gm', 'Tea', 'Pure Harvest', 'box', '#6b8e23', 'Green Tea', '100 GM', 280, null, 'Organic', false, '100 gm', 60],
            ['Sylhet Black Tea 400gm', 'Tea', 'Village Fresh', 'box', '#4a2c1a', 'Sylhet Tea', '400 GM', 260, 240, null, false, '400 gm', 80],

            ['Mango Pickle 400gm', 'Pickles', 'Grihosthali', 'jar', '#d98a1c', 'Mango Achar', '400 GM', 280, 250, null, false, '400 gm', 50],
            ['Olive Pickle (Jolpai) 400gm', 'Pickles', 'Grihosthali', 'jar', '#6b7d2a', 'Jolpai Achar', '400 GM', 300, null, 'New Arrival', false, '400 gm', 50],
            ['Garlic Pickle 400gm', 'Pickles', 'Grihosthali', 'jar', '#c79a4a', 'Rosun Achar', '400 GM', 320, null, null, false, '400 gm', 40],

            ['Ghee & Honey Combo', 'Combo Pack', 'Grihosthali', 'box', '#e9a60f', 'Ghee + Honey Combo', 'SAVE MORE', 3100, 2750, 'Hot Deal', true, '1 kg + 1 kg', 30],
            ['Healthy Family Combo', 'Combo Pack', 'Grihosthali', 'box', '#1f4d36', 'Family Combo', 'GHEE • HONEY • OIL', 3500, 2990, 'Best Selling', true, 'Combo', 25],
        ];

        foreach ($rows as [$name, $cat, $brand, $type, $color, $label, $sub, $price, $discount, $flag, $top, $unit, $stock]) {
            $slug = Str::slug($name);
            $gallery = [];
            if ($type === 'photo') {
                $image = $this->cropPhoto('products/premium-desi-ghee-jar.jpg', 120, 250, 580);
                $gallery = [
                    $this->cropPhoto('products/premium-desi-ghee-box.jpg', 474, 30, 790),
                    $this->cropPhoto('products/premium-desi-ghee-set.jpg', 60, 0, 843, 1100),
                ];
            } else {
                File::put("{$this->uploads}/products/{$slug}.svg", ProductArt::product($type, $label, $color, $sub));
                $image = "products/{$slug}.svg";
            }

            Product::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'category_id' => $categories[$cat]->id,
                'brand_id' => $brands[$brand]->id,
                'sku' => 'GH-' . strtoupper(Str::random(6)),
                'unit' => $unit,
                'short_description' => $this->shortDescription($cat, $name),
                'description' => $this->description($cat, $name),
                'price' => $price,
                'discount_price' => $discount,
                'stock' => $stock,
                'image' => $image,
                'gallery' => $gallery,
                'flag' => $flag,
                'is_top_selling' => $top,
                'is_active' => true,
                'sold_count' => random_int(10, 400),
            ]);
        }
    }

    /** Crop a square (or wider) region from the brand photo into a product image. */
    private function cropPhoto(string $target, int $x, int $y, int $w, ?int $srcW = null): string
    {
        $src = imagecreatefrompng("{$this->uploads}/sliders/ghee-hero.png");
        $srcW = $srcW ?? $w;
        $out = imagecreatetruecolor(800, (int) round(800 * $w / $srcW));
        imagecopyresampled($out, $src, 0, 0, $x, $y, 800, imagesy($out), $srcW, $w);
        imagejpeg($out, "{$this->uploads}/{$target}", 88);
        imagedestroy($out);
        imagedestroy($src);

        return $target;
    }

    private function sliders(): void
    {
        // Slide 1: the brand photo composed onto a green panel so overlay text stays readable.
        $src = imagecreatefrompng("{$this->uploads}/sliders/ghee-hero.png");
        $banner = imagecreatetruecolor(1280, 480);
        [$r1, $g1, $b1, $r2, $g2, $b2] = [0x14, 0x33, 0x24, 0x1f, 0x4d, 0x36];
        for ($i = 0; $i < 1280; $i++) {
            $t = $i / 1280;
            imageline($banner, $i, 0, $i, 480, imagecolorallocate($banner, (int) ($r1 + ($r2 - $r1) * $t), (int) ($g1 + ($g2 - $g1) * $t), (int) ($b1 + ($b2 - $b1) * $t)));
        }
        // Photo occupies the right side (scaled to 720×480 from a 3:2 source).
        imagecopyresampled($banner, $src, 560, 0, 0, 0, 720, 480, 1264, 843);
        for ($i = 0; $i < 160; $i++) {
            $alpha = (int) (127 * $i / 160);
            imageline($banner, 560 + $i, 0, 560 + $i, 480, imagecolorallocatealpha($banner, $r2, $g2, $b2, $alpha));
        }
        imagejpeg($banner, "{$this->uploads}/sliders/hero-ghee.jpg", 88);
        imagedestroy($banner);
        imagedestroy($src);

        File::put("{$this->uploads}/sliders/hero-honey.svg", ProductArt::banner('jar', '#c97a06', '#3b2304', '#7a4a0a', 'Sundarban Honey', '100% PURE'));
        File::put("{$this->uploads}/sliders/hero-oil.svg", ProductArt::banner('bottle', '#d9a514', '#163a28', '#2c6b4a', 'Mustard Oil', 'COLD PRESSED'));

        $slides = [
            ['hero-ghee.jpg', 'Premium Desi Ghee', 'খাঁটি স্বাদ, সবার রান্নায় — গ্রামের ঐতিহ্যবাহী পদ্ধতিতে তৈরি', 'Order Now', '/products/premium-desi-ghee-500ml'],
            ['hero-honey.svg', 'Pure Sundarban Honey', 'সুন্দরবনের প্রাকৃতিক চাক থেকে সংগৃহীত খাঁটি মধু', 'Shop Honey', '/category/honey'],
            ['hero-oil.svg', 'Cold Pressed Mustard Oil', 'ঘানি ভাঙা খাঁটি সরিষার তেল — ঝাঁঝালো স্বাদ ও ঘ্রাণ', 'Shop Oil', '/category/oil'],
        ];
        foreach ($slides as $i => [$img, $title, $sub, $btn, $link]) {
            Slider::updateOrCreate(['image' => "sliders/{$img}"], [
                'title' => $title, 'subtitle' => $sub, 'button_text' => $btn, 'link' => $link, 'sort_order' => $i + 1, 'is_active' => true,
            ]);
        }
    }

    private function testimonials(): void
    {
        $list = [
            ['Nusrat Jahan', 'Dhanmondi, Dhaka', 'গৃহস্থালীর ঘি এর ঘ্রাণ একদম ছোটবেলায় নানুবাড়িতে খাওয়া ঘি এর মতো। প্যাকেজিংও অসাধারণ!'],
            ['Rakib Hasan', 'Chattogram', 'Ordered Sundarban honey and mustard oil. Delivery was on time and the quality is truly premium.'],
            ['Farhana Akter', 'Sylhet', 'সরিষার তেলের ঝাঁঝ একদম খাঁটি। এখন থেকে নিয়মিত এখান থেকেই কিনবো।'],
            ['Tanvir Ahmed', 'Mirpur, Dhaka', 'Cash on delivery and quick phone confirmation — very smooth experience. Highly recommended.'],
        ];
        foreach ($list as [$name, $where, $msg]) {
            Testimonial::updateOrCreate(['name' => $name], ['designation' => $where, 'message' => $msg, 'rating' => 5, 'is_active' => true]);
        }
    }

    private function pages(): void
    {
        $pages = [
            'About Us' => '<p><strong>গৃহস্থালী (Grihosthali)</strong> started with a simple promise — to bring the pure, honest taste of village kitchens to every home in Bangladesh.</p><p>Our ghee is slow-cooked from fresh cow milk cream using traditional methods. Our honey is collected from the Sundarbans and flowering fields, and our mustard oil is cold pressed in a traditional ghani. No adulteration, no preservatives — just food the way it is meant to be.</p><h3>Our Promise</h3><ul><li>100% pure and natural products</li><li>Traditional preparation methods</li><li>Hygienic packaging</li><li>Cash on delivery all over Bangladesh</li></ul>',
            'Privacy Policy' => '<p>We respect your privacy. The information you provide (name, phone number, address and email) is used only to process and deliver your orders and to contact you about them.</p><p>We never sell or share your personal information with third parties except delivery partners who need it to deliver your order.</p>',
            'Terms & Conditions' => '<p>By placing an order on Grihosthali you agree to provide accurate contact and delivery information. Prices are listed in Bangladeshi Taka (BDT) and may change without notice.</p><p>Orders are confirmed by phone before dispatch. We reserve the right to cancel orders that cannot be verified.</p>',
            'Return & Refund Policy' => '<p>If you receive a damaged or incorrect product, please inform us within 24 hours of delivery with a photo of the product. We will replace the product or refund your money.</p><p>For hygiene reasons, opened food products cannot be returned unless they are defective.</p>',
            'Delivery Policy' => '<ul><li>Inside Dhaka: delivery within 1–2 working days.</li><li>Outside Dhaka: delivery within 2–4 working days.</li><li>Cash on delivery is available all over Bangladesh.</li><li>Please check the product in front of the delivery person before paying.</li></ul>',
        ];
        foreach ($pages as $title => $content) {
            Page::updateOrCreate(['slug' => Str::slug($title)], ['title' => $title, 'content' => $content, 'is_active' => true]);
        }
    }

    private function shortDescription(string $category, string $name): string
    {
        return match ($category) {
            'Ghee & Butter' => "গৃহস্থালীর {$name} তৈরি হয় দেশি গরুর দুধের সর থেকে, ঐতিহ্যবাহী পদ্ধতিতে ধীরে জ্বাল দিয়ে। দানাদার, সুগন্ধি ও সম্পূর্ণ ভেজালমুক্ত — খাঁটি স্বাদ, সবার রান্নায়।",
            'Honey' => "প্রাকৃতিক চাক থেকে সংগৃহীত খাঁটি {$name}। কোনো চিনি, সিরাপ বা প্রিজারভেটিভ মেশানো নেই। রোগ প্রতিরোধ ক্ষমতা বাড়াতে ও প্রতিদিনের সুস্থতায় আদর্শ।",
            'Oil' => "ঘানিতে ভাঙা {$name} — প্রাকৃতিক ঝাঁঝ, ঘ্রাণ ও পুষ্টিগুণ অটুট রাখতে কোনো তাপ বা কেমিক্যাল ব্যবহার করা হয়নি।",
            'Dates' => "বাছাই করা প্রিমিয়াম মানের {$name}। নরম, রসালো ও প্রাকৃতিকভাবে মিষ্টি — ইফতার ও প্রতিদিনের পুষ্টির জন্য সেরা।",
            'Nuts & Seeds' => "তাজা ও বাছাই করা {$name} — প্রোটিন, ফাইবার ও স্বাস্থ্যকর ফ্যাটে ভরপুর। স্বাস্থ্যকর নাস্তার জন্য আদর্শ।",
            'Spices' => "নিজস্ব তত্ত্বাবধানে রোদে শুকিয়ে গুঁড়া করা {$name}। কোনো রং বা ভেজাল নেই — রান্নায় আসল স্বাদ ও রঙ।",
            default => "গৃহস্থালীর {$name} — বিশুদ্ধ, প্রাকৃতিক ও ঐতিহ্যবাহী। সারা বাংলাদেশে ক্যাশ অন ডেলিভারি।",
        };
    }

    private function description(string $category, string $name): string
    {
        $benefits = match ($category) {
            'Ghee & Butter' => ['Rich in healthy fats and fat-soluble vitamins A, D, E & K', 'Improves digestion and boosts immunity', 'High smoke point — perfect for frying and tempering', 'Traditional grainy texture and rich aroma'],
            'Honey' => ['Natural antioxidants, enzymes and minerals', 'Soothes sore throat and cough', 'Natural energy booster without sugar crash', 'Great for skin and daily wellness'],
            'Oil' => ['Cold pressed — nutrients and aroma stay intact', 'Rich in omega-3 and omega-6 fatty acids', 'Traditional pungent flavour for bhorta and curries', 'Can be used for hair and body massage'],
            default => ['100% natural and pure', 'Carefully sourced and quality checked', 'Hygienically packed', 'No artificial colour or preservatives'],
        };
        $list = implode('', array_map(fn ($b) => "<li>{$b}</li>", $benefits));

        return "<h3>Product Details</h3><p>Experience the authentic taste of <strong>{$name}</strong> from Grihosthali — made the traditional way, the way our grandmothers trusted.</p>"
            . "<h3>Key Benefits</h3><ul>{$list}</ul>"
            . '<h3>Storage Instructions</h3><ul><li>Store in a cool, dry place away from direct sunlight.</li><li>Always use a dry spoon and keep the lid tightly closed.</li></ul>'
            . '<p><strong>Country of Origin:</strong> Bangladesh</p>';
    }
}

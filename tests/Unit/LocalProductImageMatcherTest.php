<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\LocalProductImageMatcher;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LocalProductImageMatcherTest extends TestCase
{
    public function test_it_matches_only_the_configured_brand_and_product_pairs(): void
    {
        $matcher = app(LocalProductImageMatcher::class);

        $cases = [
            ["Jack 'n Jill", 'Piattos Cheese', 'products/catalog-piattos-cheese.png'],
            ["Jack 'n Jill", 'Piattos Sour Cream & Onion', 'products/catalog-jack-piattos-sour-cream.jpg'],
            ["Jack 'n Jill", 'Piattos Roast Beef', 'products/catalog-jack-piattos-roast-beef.jpg'],
            ["Jack 'n Jill", 'Piattos Roadhouse Barbecue', 'products/catalog-jack-piattos-roadhouse.jpg'],
            ['Jack ’n Jill', 'Nova Country Cheddar', 'products/catalog-nova-cheddar.webp'],
            ['Jack and Jill', 'Chippy Barbecue', 'products/catalog-jack-chippy.jpg'],
            ["Jack 'n Jill", 'Nova Homestyle Barbecue', 'products/catalog-jack-nova-homestyle-bbq.jpg'],
            ["Jack 'n Jill", 'Chippy Chili & Cheese', 'products/catalog-jack-chippy-chili-cheese.png'],
            ["Jack 'n Jill", 'Chippy Garlic & Vinegar', 'products/catalog-jack-chippy-garlic-vinegar.jpg'],
            ["Jack 'n Jill", 'V-Cut Barbecue', 'products/catalog-jack-vcut.jpg'],
            ["Jack 'n Jill", 'V-Cut Spicy Barbecue', 'products/catalog-jack-vcut.jpg'],
            ["Jack 'n Jill", 'Mr. Chips Nacho Cheese', 'products/catalog-jack-mr-chips.jpg'],
            ["Jack 'n Jill", 'Roller Coaster Cheddar Cheese', 'products/catalog-jack-roller-coaster-cheddar.jpg'],
            ["Jack 'n Jill", 'Roller Coaster Barbecue', 'products/catalog-jack-roller-coaster-bbq.png'],
            ["Jack 'n Jill", 'Tostillas Nacho Cheese', 'products/catalog-jack-tostillas-nacho.jpg'],
            ["Jack 'n Jill", 'Tostillas Barbecue', 'products/catalog-jack-tostillas-bbq-mix.jpg'],
            ["Jack 'n Jill", 'Mang Juan Chicharron', 'products/catalog-jack-mang-juan-klasik.jpg'],
            ["Jack 'n Jill", "Mang Juan Chik'n Skin", 'products/catalog-jack-mang-juan-chikn-skin.png'],
            ["Jack 'n Jill", 'Chicharron ni Mang Juan Klasik', 'products/catalog-jack-mang-juan-klasik.jpg'],
            ["Jack 'n Jill", "Chik'n Skin ni Mang Juan", 'products/catalog-jack-mang-juan-chikn-skin.png'],
            ["Jack 'n Jill", 'Mang Juan Sukang Paombong', 'products/catalog-mang-juan.png'],
            ["Jack 'n Jill", "Mang Juan Espesyal Suka't Sili", 'products/catalog-jack-mang-juan-espesyal.png'],
            ["Jack 'n Jill", 'Mang Juan Espesyal Sukang Paombong', 'products/catalog-jack-mang-juan-espesyal.png'],
            ["Jack 'n Jill", 'Chiz Curls Cheese', 'products/catalog-jack-chiz-curls.jpg'],
            ["Jack 'n Jill", "Jack 'n Jill Potato Chips Barbecue", 'products/catalog-jack-potato-chips-bbq.jpg'],
            ["Jack 'n Jill", 'Potato Chips Classic Barbecue', 'products/catalog-jack-potato-chips-bbq.jpg'],
            ["Jack 'n Jill", "Jack 'n Jill Potato Chips Classic", 'products/catalog-jack-potato-chips-classic.jpg'],
            ["Jack 'n Jill", 'Cream-O', 'products/catalog-jack-cream-o.png'],
            ["Jack 'n Jill", 'Cream-O Deluxe', 'products/catalog-jack-cream-o-deluxe.jpg'],
            ["Jack 'n Jill", 'Presto Creams', 'products/catalog-jack-presto-creams.png'],
            ["Jack 'n Jill", 'Presto Creams Peanut Butter', 'products/catalog-jack-presto-creams.png'],
            ["Jack 'n Jill", 'Presto Creams Peanut Butter & Chocolate', 'products/catalog-jack-presto-choco-peanut-butter.jpg'],
            ["Jack 'n Jill", 'Magic Flakes', 'products/catalog-jack-magic-flakes.png'],
            ["Jack 'n Jill", 'Magic Flakes Original', 'products/catalog-jack-magic-flakes.png'],
            ["Jack 'n Jill", 'Magic Flakes Cheese', 'products/catalog-jack-magic-flakes-cheese.jpg'],
            ["Jack 'n Jill", 'Magic Flakes Peanut Butter', 'products/catalog-jack-magic-creams-peanut-butter.jpg'],
            ["Jack 'n Jill", 'Dewberry', 'products/catalog-jack-dewberry.jpg'],
            ["Jack 'n Jill", 'Dewberry Blueberry', 'products/catalog-jack-dewberry-blueberry.jpg'],
            ["Jack 'n Jill", 'Dewberry Strawberry', 'products/catalog-jack-dewberry.jpg'],
            ["Jack 'n Jill", 'Wafrets', 'products/catalog-jack-wafrets.jpg'],
            ["Jack 'n Jill", 'Wafrets Chocolate', 'products/catalog-jack-wafrets.jpg'],
            ["Jack 'n Jill", 'Wafrets Cheese', 'products/catalog-jack-wafrets-cheese.jpg'],
            ["Jack 'n Jill", 'Wafrets Vanilla', 'products/catalog-jack-wafrets-vanilla.jpg'],
            ["Jack 'n Jill", 'Choco Knots', 'products/catalog-jack-choco-knots.png'],
            ["Jack 'n Jill", 'Quake', 'products/catalog-jack-quake.jpg'],
            ["Jack 'n Jill", 'Cloud 9', 'products/catalog-jack-cloud-9.jpg'],
            ["Jack 'n Jill", 'Cloud 9 Choco Fudge', 'products/catalog-jack-cloud-9-choco-fudge.jpg'],
            ["Jack 'n Jill", 'Cloud 9 White', 'products/catalog-jack-cloud-9-white.jpg'],
            ["Jack 'n Jill", 'Chooey Choco', 'products/catalog-jack-chooey-choco.jpg'],
            ["Jack 'n Jill", 'Nips', 'products/catalog-jack-nips.png'],
            ["Jack 'n Jill", 'Nips Milk Chocolate', 'products/catalog-jack-nips.png'],
            ["Jack 'n Jill", 'Nips Peanut', 'products/catalog-jack-nips-peanut.png'],
            ["Jack 'n Jill", 'Nips White Chocolate', 'products/catalog-jack-nips-white.jpg'],
            ["Jack 'n Jill", 'Maxx', 'products/catalog-jack-maxx.png'],
            ["Jack 'n Jill", 'Maxx Menthol', 'products/catalog-jack-maxx.png'],
            ["Jack 'n Jill", 'Maxx Honey Lemon', 'products/catalog-jack-maxx-honey-lemon.png'],
            ["Jack 'n Jill", 'Maxx Cherry', 'products/catalog-jack-maxx-cherry.jpg'],
            ["Jack 'n Jill", 'Dynamite', 'products/catalog-jack-dynamite.png'],
            ["Jack 'n Jill", 'Dynamite Choco Mint', 'products/catalog-jack-dynamite.png'],
            ["Jack 'n Jill", 'Dynamite Menthol', 'products/catalog-jack-dynamite.png'],
            ["Jack 'n Jill", 'XO', 'products/catalog-jack-xo.webp'],
            ["Jack 'n Jill", 'XO Coffee Candy', 'products/catalog-jack-xo.webp'],
            ["Jack 'n Jill", 'XO Milk Candy', 'products/catalog-jack-xo-milk-tea.jpg'],
            ["Jack 'n Jill", 'Potchi', 'products/catalog-jack-potchi.jpg'],
            ["Jack 'n Jill", 'Lush', 'products/catalog-jack-lush.jpg'],
            ["Jack 'n Jill", 'Chooey Toffee', 'products/catalog-jack-chooey-toffee.jpg'],
            ['Oishi', 'Prawn Crackers', 'products/catalog-oishi-prawn-crackers.jpeg'],
            ['Oishi', 'Ribbed Cracklings', 'products/catalog-oishi-cracklings.png'],
            ['Oishi', 'Potato Fries', 'products/catalog-oishi-potato-fries.png'],
            ['Oishi', 'Pillows Choco', 'products/catalog-oishi-pillows.jpg'],
            ['Oishi', "Marty's Cracklin", 'products/catalog-oishi-martys.png'],
            ["Leslie's", 'Clover Chips', 'products/catalog-leslies-clover.png'],
            ["Leslie's", 'Cheezy', 'products/catalog-leslies-cheezy.jpg'],
            ["Leslie's", 'Farmer John', 'products/catalog-leslies-farmer-john.png'],
            ['Regent', 'Cheese Ring', 'products/catalog-regent-cheese-ring.jpg'],
            ['Regent', 'Golden Sweet Corn', 'products/catalog-regent-sweet-corn.jpg'],
            ['Regent', 'Tempura', 'products/catalog-regent-tempura.jpg'],
            ['Granny Goose', 'Tortillos', 'products/catalog-granny-tortillos.webp'],
            ['Granny Goose', 'Kornets', 'products/catalog-granny-kornets.png'],
            ['Granny Goose', 'Krrrrunch', 'products/catalog-granny-krrrunch.jpg'],
            ['NutriAsia', 'Mang Juan', 'products/catalog-mang-juan.png'],
            ["Lay's", "Lay's Potato Chips", 'products/catalog-lays-classic.png'],
            ['Pringles', 'Original', 'products/catalog-pringles-original.jpg'],
            ['Pringles', 'Sour Cream & Onion', 'products/catalog-pringles-sour-cream.jpg'],
            ['Pringles', 'Cheesy Cheese', 'products/catalog-pringles-cheese.jpg'],
        ];

        foreach ($cases as [$brand, $product, $expected]) {
            $this->assertSame($expected, $matcher->findSource($product, $brand), "$brand / $product did not match.");
        }

        $this->assertNull($matcher->findSource('Piattos Cheese', 'Oishi'));
        $this->assertNull($matcher->findSource('Piattos Unknown Flavor', "Jack 'n Jill"));
        $this->assertNull($matcher->findSource('Random Potato Chips', 'Unknown Brand'));
    }

    public function test_it_matches_the_extended_exact_product_catalog(): void
    {
        $matcher = app(LocalProductImageMatcher::class);

        $cases = [
            ['Oishi', 'Prawn Crackers Original', 'products/catalog-oishi-prawn-crackers.jpeg'],
            ['Oishi', 'Prawn Crackers Spicy', 'products/catalog-oishi-prawn-spicy.png'],
            ['Oishi', 'Cracklings Salt & Vinegar', 'products/catalog-oishi-cracklings.png'],
            ['Oishi', 'Cracklings Spicy', 'products/catalog-oishi-martys-spicy.jpg'],
            ['Oishi', 'Potato Fries', 'products/catalog-oishi-potato-fries.png'],
            ['Oishi', 'Fishda', 'products/catalog-oishi-fishda.png'],
            ['Oishi', 'Fish Crackers', 'products/catalog-oishi-fish-crackers.png'],
            ['Oishi', "Marty’s Cracklin’ Plain Salted", 'products/catalog-oishi-martys.png'],
            ['Oishi', "Marty’s Cracklin’ Salt & Vinegar", 'products/catalog-oishi-martys-salt-vinegar.jpg'],
            ['Oishi', "Marty’s Cracklin’ Spicy", 'products/catalog-oishi-martys-spicy.jpg'],
            ['Oishi', 'Ridges Potato Chips', 'products/catalog-oishi-ridges.jpg'],
            ['Oishi', 'Pillows Chocolate', 'products/catalog-oishi-pillows.jpg'],
            ['Oishi', 'Pillows Ube', 'products/catalog-oishi-pillows-ube.jpg'],
            ['Oishi', 'Pillows Oat Choco', 'products/catalog-oishi-pillows-oat-choco.png'],
            ['Oishi', 'Bread Pan Garlic', 'products/catalog-oishi-bread-pan-garlic.jpg'],
            ['Oishi', 'Bread Pan Cheese', 'products/catalog-oishi-bread-pan-cheese.png'],
            ['Oishi', 'Bread Pan Toasted Bread', 'products/catalog-oishi-bread-pan-toasted.jpg'],
            ['Oishi', 'Sponge Crunch', 'products/catalog-oishi-sponge-crunch.jpg'],
            ['Oishi', 'Kirei Yummy Flakes', 'products/catalog-oishi-kirei.jpg'],
            ['Oishi', 'Ribbed Cracklings', 'products/catalog-oishi-cracklings.png'],
            ['Oishi', 'Smart C', 'products/catalog-oishi-smart-c.png'],
            ['Oishi', 'Oishi Green Tea', 'products/catalog-oishi-green-tea.jpg'],
            ['Oishi', 'Oishi Black Tea', 'products/catalog-oishi-black-tea.jpg'],

            ['Oreo', 'Oreo Original', 'products/catalog-oreo-original.jpg'],
            ['Oreo', 'Oreo Vanilla', 'products/catalog-oreo-vanilla.jpg'],
            ['Oreo', 'Oreo Chocolate Creme', 'products/catalog-oreo-chocolate-creme.jpg'],
            ['Oreo', 'Oreo Strawberry Creme', 'products/catalog-oreo-strawberry-creme.png'],
            ['Oreo', 'Oreo Double Stuf', 'products/catalog-oreo-double-stuf.jpg'],
            ['Oreo', 'Oreo Golden', 'products/catalog-oreo-golden.png'],
            ['Oreo', 'Oreo Mini', 'products/catalog-oreo-mini.png'],
            ['Oreo', 'Oreo Wafer Roll Chocolate', 'products/catalog-oreo-wafer-roll-chocolate.jpg'],
            ['Oreo', 'Oreo Wafer Roll Vanilla', 'products/catalog-oreo-wafer-roll-vanilla.jpg'],

            ['Rebisco', 'Rebisco Crackers', 'products/catalog-rebisco-crackers.jpg'],
            ['Rebisco', 'Rebisco Sandwich Chocolate', 'products/catalog-rebisco-sandwich-chocolate.jpg'],
            ['Rebisco', 'Rebisco Sandwich Strawberry', 'products/catalog-rebisco-sandwich-strawberry.jpg'],
            ['Rebisco', 'Rebisco Sandwich Vanilla', 'products/catalog-rebisco-sandwich-vanilla.png'],
            ['Rebisco', 'Rebisco Sandwich Peanut Butter', 'products/catalog-rebisco-sandwich-peanut-butter.jpg'],
            ['Rebisco', 'Hansel Premium', 'products/catalog-rebisco-hansel-premium.jpg'],
            ['Rebisco', 'Hansel Mocha', 'products/catalog-rebisco-hansel-mocha.jpg'],
            ['Rebisco', 'Hansel Milk', 'products/catalog-rebisco-hansel-milk.jpg'],
            ['Rebisco', 'Hansel Butter', 'products/catalog-rebisco-hansel-butter.jpg'],
            ['Rebisco', 'Hansel Chocolate', 'products/catalog-rebisco-hansel-chocolate.jpg'],
            ['Rebisco', 'Combi', 'products/catalog-rebisco-combi.jpg'],
            ['Rebisco', 'Marie Time', 'products/catalog-rebisco-marie-time.jpg'],
            ['Rebisco', 'Bravo Biscuits', 'products/catalog-rebisco-bravo.jpg'],
            ['Rebisco', 'Choco Mucho', 'products/catalog-rebisco-choco-mucho.jpg'],
            ['Rebisco', 'Choco Mucho Dark Chocolate', 'products/catalog-rebisco-choco-mucho-dark.png'],
            ['Rebisco', 'Choco Mucho Cookies & Cream', 'products/catalog-rebisco-choco-mucho-cookies-cream.jpg'],
            ['Rebisco', 'Choco Mucho White', 'products/catalog-rebisco-choco-mucho-white.jpg'],
            ['Rebisco', 'Fudgee Barr Chocolate', 'products/catalog-rebisco-fudgee-barr-chocolate.png'],
            ['Rebisco', 'Fudgee Barr Vanilla', 'products/catalog-rebisco-fudgee-barr-vanilla.jpg'],
            ['Rebisco', 'Fudgee Barr Mocha', 'products/catalog-rebisco-fudgee-barr-mocha.jpg'],
            ['Rebisco', 'Fudgee Barr Macapuno', 'products/catalog-rebisco-fudgee-barr-macapuno.jpg'],
            ['Rebisco', 'Superstix Chocolate', 'products/catalog-rebisco-superstix-chocolate.jpg'],
            ['Rebisco', 'Superstix Ube', 'products/catalog-rebisco-superstix-ube.jpg'],
            ['Rebisco', 'Superstix Strawberry', 'products/catalog-rebisco-superstix-strawberry.jpg'],
            ['Rebisco', 'Superstix Milk', 'products/catalog-rebisco-superstix-milk.jpg'],
            ['Rebisco', 'Creamline Wafer', 'products/catalog-rebisco-creamline-wafer.jpg'],
            ['Rebisco', 'Doowee Donut', 'products/catalog-rebisco-doowee-donut.jpg'],
            ['Rebisco', 'Doowee Choco', 'products/catalog-rebisco-doowee-choco.jpg'],
            ['Rebisco', 'Frootees', 'products/catalog-rebisco-frootees.jpg'],

            ['SkyFlakes', 'SkyFlakes Original', 'products/catalog-skyflakes-original.jpg'],
            ['SkyFlakes', 'SkyFlakes Fit', 'products/catalog-skyflakes-fit.png'],
            ['SkyFlakes', 'SkyFlakes Condensada', 'products/catalog-skyflakes-condensada.png'],
            ['SkyFlakes', 'SkyFlakes Cracker Sandwich Cheese', 'products/catalog-skyflakes-cheese.jpg'],
            ['SkyFlakes', 'SkyFlakes Cracker Sandwich Chocolate', 'products/catalog-skyflakes-chocolate.jpg'],
            ['SkyFlakes', 'SkyFlakes Cracker Sandwich Peanut Butter', 'products/catalog-skyflakes-peanut-butter.jpg'],

            ['Nestlé', 'Nestlé Fresh Milk', 'products/catalog-nestle-fresh-milk.jpg'],
            ['Nestlé', 'Nestlé Low Fat Milk', 'products/catalog-nestle-low-fat-milk.jpg'],
            ['Nestlé', 'Nestlé All Purpose Cream', 'products/catalog-nestle-all-purpose-cream.jpg'],
            ['Nestlé', 'Nestlé Carnation Evaporated Milk', 'products/catalog-nestle-carnation-evaporated.jpg'],
            ['Nestlé', 'Nestlé Carnation Condensada', 'products/catalog-nestle-carnation-condensada.jpg'],
            ['Nestlé', 'Nestlé Yogurt', 'products/catalog-nestle-yogurt.png'],
            ['Nestlé', 'Nestlé Koko Krunch', 'products/catalog-nestle-koko-krunch.jpg'],
            ['Nestlé', 'Nestlé Koko Krunch Duo', 'products/catalog-nestle-koko-krunch-duo.jpg'],
            ['Nestlé', 'Nestlé Honey Stars', 'products/catalog-nestle-honey-stars.jpg'],
            ['Nestlé', 'Nestlé Corn Flakes', 'products/catalog-nestle-corn-flakes.jpg'],
            ['Nestlé', 'Nestlé Fitnesse', 'products/catalog-nestle-fitnesse.jpg'],
            ['Nestlé', 'Nestlé Chuckie', 'products/catalog-nestle-chuckie.jpg'],
            ['Nestlé', 'Nestlé Bear Brand', 'products/catalog-nestle-bear-brand.jpg'],
            ['Nestlé', 'Nestlé Milo', 'products/catalog-nestle-milo.jpg'],
            ['Nestlé', 'Nestlé Nescafé', 'products/catalog-nestle-nescafe.jpg'],

            ['Milo', 'Milo Chocolate Malt Powder', 'products/catalog-milo-chocolate-malt.jpg'],
            ['Milo', 'Milo Activ-Go', 'products/catalog-milo-activ-go.jpg'],
            ['Milo', 'Milo Ready-to-Drink', 'products/catalog-milo-rtd.jpg'],
            ['Milo', 'Milo Champion Formula', 'products/catalog-milo-champion.png'],
            ['Milo', 'Milo 3-in-1', 'products/catalog-milo-3in1.png'],
            ['Milo', 'Milo Sachet', 'products/catalog-milo-sachet.webp'],
            ['Milo', 'Milo Twin Pack', 'products/catalog-milo-twin-pack.jpg'],

            ['Nescafé', 'Nescafé Classic', 'products/catalog-nescafe-classic.jpg'],
            ['Nescafé', 'Nescafé Classic Decaf', 'products/catalog-nescafe-classic-decaf.jpg'],
            ['Nescafé', 'Nescafé Gold', 'products/catalog-nescafe-gold.jpg'],
            ['Nescafé', 'Nescafé Gold Intense', 'products/catalog-nescafe-gold-intense.jpg'],
            ['Nescafé', 'Nescafé 3-in-1 Original', 'products/catalog-nescafe-3in1-original.jpg'],
            ['Nescafé', 'Nescafé 3-in-1 Creamy White', 'products/catalog-nescafe-3in1-creamy-white.jpg'],
            ['Nescafé', 'Nescafé 3-in-1 Brown & Creamy', 'products/catalog-nescafe-3in1-brown-creamy.png'],
            ['Nescafé', 'Nescafé Creamy Latte', 'products/catalog-nescafe-creamy-latte.jpg'],
            ['Nescafé', 'Nescafé Cappuccino', 'products/catalog-nescafe-cappuccino.jpeg'],
            ['Nescafé', 'Nescafé Café Creations Caramel Latte', 'products/catalog-nescafe-caramel-latte.jpg'],
            ['Nescafé', 'Nescafé Café Creations Mocha Latte', 'products/catalog-nescafe-mocha-latte.webp'],
            ['Nescafé', 'Nescafé Black Roast', 'products/catalog-nescafe-black-roast.png'],
            ['Nescafé', 'Nescafé Ready-to-Drink', 'products/catalog-nescafe-rtd.png'],

            ['Kopiko', 'Kopiko Brown Coffee', 'products/catalog-kopiko-brown-single.png'],
            ['Kopiko', 'Kopiko Blanca', 'products/catalog-kopiko-blanca.jpg'],
            ['Kopiko', 'Kopiko Black 3-in-One', 'products/catalog-kopiko-black.jpg'],
            ['Kopiko', 'Kopiko L.A. Coffee', 'products/catalog-kopiko-la.jpg'],
            ['Kopiko', 'Kopiko Cappuccino', 'products/catalog-kopiko-cappuccino.png'],
            ['Kopiko', 'Kopiko Lucky Day', 'products/catalog-kopiko-lucky-day.jpg'],
            ['Kopiko', 'Kopiko 78°C', 'products/catalog-kopiko-78c.jpg'],
            ['Kopiko', 'Kopiko Coffee Candy', 'products/catalog-kopiko-coffee-candy.jpg'],
            ['Kopiko', 'Kopiko Cappuccino Candy', 'products/catalog-kopiko-cappuccino-candy.jpg'],

            ['Great Taste', 'Great Taste Granules', 'products/catalog-great-taste-granules.jpg'],
            ['Great Taste', 'Great Taste Premium', 'products/catalog-great-taste-premium.png'],
            ['Great Taste', 'Great Taste White', 'products/catalog-great-taste-white.jpg'],
            ['Great Taste', 'Great Taste White Caramel', 'products/catalog-great-taste-white-caramel.jpg'],
            ['Great Taste', 'Great Taste White Crema', 'products/catalog-great-taste-white-crema.png'],
            ['Great Taste', 'Great Taste Choco', 'products/catalog-great-taste-choco.jpg'],
            ['Great Taste', 'Great Taste Original', 'products/catalog-great-taste-original.jpg'],
            ['Great Taste', 'Great Taste Strong', 'products/catalog-great-taste-strong.jpg'],
            ['Great Taste', 'Great Taste 3-in-1', 'products/catalog-great-taste-3in1-single-clean.png'],
            ['Great Taste', 'Great Taste Black Forest', 'products/catalog-great-taste-black-forest.png'],

            ['Bear Brand', 'Bear Brand Fortified Powdered Milk', 'products/catalog-bear-brand-fortified.jpg'],
            ['Bear Brand', 'Bear Brand Adult Plus', 'products/catalog-bear-brand-adult-plus.jpg'],
            ['Bear Brand', 'Bear Brand Sterilized Milk', 'products/catalog-bear-brand-sterilized.jpg'],
            ['Bear Brand', 'Bear Brand Ready-to-Drink', 'products/catalog-bear-brand-rtd.jpg'],
            ['Bear Brand', 'Bear Brand Choco Milk', 'products/catalog-bear-brand-choco.jpg'],
            ['Bear Brand', 'Bear Brand Fortified Powdered Milk Sachet', 'products/catalog-bear-brand-sachet.jpg'],
            ['Bear Brand', 'Bear Brand Fortified Powdered Milk Pouch', 'products/catalog-bear-brand-pouch.jpg'],
        ];

        $this->assertCount(128, $cases);

        foreach ($cases as [$brand, $product, $expected]) {
            $this->assertSame($expected, $matcher->findSource($product, $brand), "$brand / $product did not match.");
        }
    }

    public function test_it_matches_the_requested_chips_and_drink_batch(): void
    {
        $matcher = app(LocalProductImageMatcher::class);

        $cases = [
            ['Pringles', 'Pringles Original', 'products/catalog-pringles-original.jpg'],
            ['Pringles', 'Pringles Sour Cream & Onion', 'products/catalog-pringles-sour-cream.jpg'],
            ['Pringles', 'Pringles Cheddar Cheese', 'products/catalog-pringles-cheese.jpg'],
            ['Pringles', 'Pringles Barbecue', 'products/catalog-new-pringles-barbecue-clean.jpg'],
            ['Pringles', 'Pringles Pizza', 'products/catalog-new-pringles-pizza-clean.jpg'],
            ['Pringles', 'Pringles Hot & Spicy', 'products/catalog-new-pringles-hot-spicy-clean.jpg'],
            ['Pringles', 'Pringles Salt & Vinegar', 'products/catalog-new-pringles-salt-vinegar-clean.jpg'],
            ['Pringles', 'Pringles Ranch', 'products/catalog-new-pringles-ranch-clean.jpg'],
            ['Pringles', 'Pringles Honey Mustard', 'products/catalog-new-pringles-honey-mustard-clean.jpeg'],
            ['Pringles', 'Pringles Jalapeno', 'products/catalog-new-pringles-jalapeno-clean.jpg'],
            ['Coca-Cola', 'Coca-Cola Original Taste', 'products/catalog-new-coca-cola-original-clean.jpg'],
            ['Coca-Cola', 'Coca-Cola Zero Sugar', 'products/catalog-new-coca-cola-zero-sugar-clean.jpg'],
            ['Coca-Cola', 'Coca-Cola Light', 'products/catalog-new-coca-cola-light-clean.jpg'],
            ['Coca-Cola', 'Coca-Cola in Can', 'products/catalog-new-coca-cola-can-clean.jpg'],
            ['Coca-Cola', 'Coca-Cola in Bottle', 'products/catalog-new-coca-cola-bottle-clean.png'],
            ['Coca-Cola', 'Coca-Cola 1.5L', 'products/catalog-new-coca-cola-1-5l-clean.jpg'],
            ['Coca-Cola', 'Coca-Cola 2L', 'products/catalog-new-coca-cola-2l-clean.png'],
            ['Pepsi', 'Pepsi Original', 'products/catalog-new-pepsi-original.jpg'],
            ['Pepsi', 'Pepsi Zero Sugar', 'products/catalog-new-pepsi-zero-sugar-clean.jpg'],
            ['Pepsi', 'Pepsi Black', 'products/catalog-new-pepsi-black-clean.jpg'],
            ['Pepsi', 'Pepsi in Can', 'products/catalog-new-pepsi-in-can.jpg'],
            ['Pepsi', 'Pepsi in Bottle', 'products/catalog-new-pepsi-original.jpg'],
            ['Pepsi', 'Pepsi 1.5L', 'products/catalog-new-pepsi-1-5l.jpg'],
            ['Pepsi', 'Pepsi 2L', 'products/catalog-new-pepsi-2l.jpg'],
            ['Milo', 'Milo Pouch', 'products/catalog-new-milo-pouch-clean.png'],
        ];

        $this->assertCount(25, $cases);

        foreach ($cases as [$brand, $product, $expected]) {
            $this->assertFileExists(storage_path('app/public/'.$expected));
            $this->assertSame($expected, $matcher->findSource($product, $brand));
        }
    }

    public function test_it_matches_the_requested_lucky_me_and_alaska_batch(): void
    {
        $matcher = app(LocalProductImageMatcher::class);

        $cases = [
            ['Lucky Me!', 'Lucky Me! Pancit Canton Original', 'products/catalog-new-lucky-me-pancit-canton-original-clean.jpg'],
            ['Lucky Me!', 'Lucky Me! Pancit Canton Kalamansi', 'products/catalog-new-lucky-me-pancit-canton-kalamansi.png'],
            ['Lucky Me!', 'Lucky Me! Pancit Canton Chilimansi', 'products/catalog-new-lucky-me-pancit-canton-chilimansi.jpg'],
            ['Lucky Me!', 'Lucky Me! Pancit Canton Sweet & Spicy', 'products/catalog-new-lucky-me-pancit-canton-sweet-and-spicy.png'],
            ['Lucky Me!', 'Lucky Me! Pancit Canton Extra Hot Chili', 'products/catalog-new-lucky-me-pancit-canton-extra-hot-chili.jpg'],
            ['Lucky Me!', 'Lucky Me! Pancit Canton Hot Chili', 'products/catalog-new-lucky-me-pancit-canton-hot-chili-clean.png'],
            ['Lucky Me!', 'Lucky Me! Beef Na Beef', 'products/catalog-new-lucky-me-beef-na-beef-clean.webp'],
            ['Lucky Me!', 'Lucky Me! Chicken Na Chicken', 'products/catalog-new-lucky-me-chicken-na-chicken-clean.jpg'],
            ['Lucky Me!', 'Lucky Me! Bulalo', 'products/catalog-new-lucky-me-bulalo-clean.jpg'],
            ['Lucky Me!', 'Lucky Me! La Paz Batchoy', 'products/catalog-new-lucky-me-la-paz-batchoy-clean.jpeg'],
            ['Lucky Me!', 'Lucky Me! Spicy Labuyo Beef', 'products/catalog-new-lucky-me-spicy-labuyo-beef.png'],
            ['Lucky Me!', 'Lucky Me! Jjamppong', 'products/catalog-new-lucky-me-jjamppong-clean.jpg'],
            ['Lucky Me!', 'Lucky Me! Beef Mami', 'products/catalog-new-lucky-me-beef-na-beef-clean.webp'],
            ['Lucky Me!', 'Lucky Me! Chicken Mami', 'products/catalog-new-lucky-me-chicken-na-chicken-clean.jpg'],
            ['Alaska', 'Alaska Powdered Milk', 'products/catalog-new-alaska-powdered-milk-clean.jpg'],
            ['Alaska', 'Alaska Fortified Powdered Milk', 'products/catalog-new-alaska-fortified-powdered-milk.png'],
            ['Alaska', 'Alaska Evaporated Filled Milk', 'products/catalog-new-alaska-evaporated-filled-milk.jpg'],
            ['Alaska', 'Alaska Classic Evaporated Filled Milk', 'products/catalog-new-alaska-classic-evaporated-filled-milk.png'],
            ['Alaska', 'Alaska Crema All-Purpose Creamer', 'products/catalog-new-alaska-crema-clean.jpg'],
            ['Alaska', 'Alaska Condensada', 'products/catalog-new-alaska-condensada.jpg'],
            ['Alaska', 'Alaska Sweetened Condensed Filled Milk', 'products/catalog-new-alaska-sweetened-condensed-clean.png'],
            ['Alaska', 'Alaska Choco Condensada', 'products/catalog-new-alaska-choco-condensada-clean.jpg'],
            ['Alaska', 'Alaska Fresh Milk', 'products/catalog-new-alaska-fresh-milk.jpg'],
            ['Alaska', 'Alaska Choco Milk', 'products/catalog-new-alaska-choco-milk-rtd-clean.jpg'],
            ['Alaska', 'Alaska Yoghurt Drink', 'products/catalog-new-alaska-yoghurt-drink.jpg'],
        ];

        $this->assertCount(25, $cases);

        foreach ($cases as [$brand, $product, $expected]) {
            $this->assertFileExists(storage_path('app/public/'.$expected));
            $this->assertSame($expected, $matcher->findSource($product, $brand));
        }
    }

    public function test_it_matches_the_requested_payless_century_and_mega_batch(): void
    {
        $matcher = app(LocalProductImageMatcher::class);

        $cases = [
            ['Payless', 'Payless Pancit Canton Original', 'products/catalog-new-payless-original-clean.jpg'],
            ['Payless', 'Payless Pancit Canton Kalamansi', 'products/catalog-new-payless-kalamansi-clean.jpeg'],
            ['Payless', 'Payless Pancit Canton Extra Hot', 'products/catalog-new-payless-extra-hot-clean.png'],
            ['Payless', 'Payless Xtra Big Original', 'products/catalog-new-payless-original-clean.jpg'],
            ['Payless', 'Payless Xtra Big Kalamansi', 'products/catalog-new-payless-pancit-canton-kalamansi.jpg'],
            ['Payless', 'Payless Xtra Big Chilimansi', 'products/catalog-new-payless-xtra-big-chilimansi-clean.jpg'],
            ['Payless', 'Payless Xtra Big Sweet & Spicy', 'products/catalog-new-payless-xtra-big-sweet-spicy-clean.jpg'],
            ['Payless', 'Payless Xtra Big Hot & Spicy', 'products/catalog-new-payless-pancit-canton-extra-hot.jpg'],
            ['Payless', 'Payless Instant Mami Beef', 'products/catalog-new-payless-mami-beef-clean.jpeg'],
            ['Payless', 'Payless Instant Mami Chicken', 'products/catalog-new-payless-instant-mami-chicken.jpg'],
            ['Century Tuna', 'Century Tuna Flakes in Oil', 'products/catalog-new-century-tuna-flakes-in-oil.jpg'],
            ['Century Tuna', 'Century Tuna Flakes in Vegetable Oil', 'products/catalog-new-century-tuna-vegetable-oil-clean.jpeg'],
            ['Century Tuna', 'Century Tuna Hot & Spicy', 'products/catalog-new-century-tuna-hot-and-spicy.jpg'],
            ['Century Tuna', 'Century Tuna Calamansi', 'products/catalog-new-century-tuna-calamansi.jpg'],
            ['Century Tuna', 'Century Tuna Adobo', 'products/catalog-new-century-tuna-adobo.jpg'],
            ['Century Tuna', 'Century Tuna Afritada', 'products/catalog-new-century-tuna-afritada.png'],
            ['Century Tuna', 'Century Tuna Mechado', 'products/catalog-new-century-tuna-mechado-clean.jpg'],
            ['Century Tuna', 'Century Tuna Sisig', 'products/catalog-new-century-tuna-sisig.jpg'],
            ['Century Tuna', 'Century Tuna Bangus', 'products/catalog-new-century-bangus-sisig-clean.jpg'],
            ['Century Tuna', 'Century Tuna Solid in Oil', 'products/catalog-new-century-tuna-solid-oil-clean.png'],
            ['Century Tuna', 'Century Tuna Lite', 'products/catalog-new-century-tuna-lite.png'],
            ['Mega', 'Mega Sardines in Tomato Sauce', 'products/catalog-new-mega-sardines-tomato-clean.jpg'],
            ['Mega', 'Mega Sardines in Tomato Sauce with Chili', 'products/catalog-new-mega-sardines-chili-clean.png'],
            ['Mega', 'Mega Sardines Spanish Style', 'products/catalog-new-mega-sardines-spanish-style.jpg'],
            ['Mega', 'Mega Tuna Flakes in Oil', 'products/catalog-new-mega-tuna-flakes-in-oil.jpg'],
            ['Mega', 'Mega Tuna Hot & Spicy', 'products/catalog-new-mega-tuna-hot-and-spicy.jpg'],
            ['Mega', 'Mega Tuna Caldereta', 'products/catalog-new-mega-tuna-caldereta.jpg'],
            ['Mega', 'Mega Tuna Afritada', 'products/catalog-new-mega-tuna-afritada.jpg'],
            ['Mega', 'Mega Mackerel in Natural Oil', 'products/catalog-new-mega-mackerel-in-natural-oil.jpg'],
            ['Mega', 'Mega Mackerel in Tomato Sauce', 'products/catalog-new-mega-mackerel-tomato-clean.png'],
        ];

        $this->assertCount(30, $cases);

        foreach ($cases as [$brand, $product, $expected]) {
            $this->assertFileExists(storage_path('app/public/'.$expected));
            $this->assertSame($expected, $matcher->findSource($product, $brand));
        }
    }

    public function test_it_matches_the_requested_canned_meat_and_fish_batch(): void
    {
        $matcher = app(LocalProductImageMatcher::class);

        $cases = [
            ['555', '555 Sardines in Tomato Sauce', 'products/catalog-new-555-sardines-tomato-clean.jpeg'],
            ['555', '555 Sardines Hot', 'products/catalog-new-555-sardines-hot.png'],
            ['555', '555 Sardines Spanish Style', 'products/catalog-new-555-sardines-spanish-clean.png'],
            ['555', '555 Tuna Flakes in Oil', 'products/catalog-new-555-tuna-flakes-in-oil.jpg'],
            ['555', '555 Tuna Hot & Spicy', 'products/catalog-new-555-tuna-hot-and-spicy.jpg'],
            ['555', '555 Tuna Adobo', 'products/catalog-new-555-tuna-adobo.png'],
            ['555', '555 Tuna Caldereta', 'products/catalog-new-555-tuna-caldereta.png'],
            ['555', '555 Tuna Afritada', 'products/catalog-new-555-tuna-afritada-clean.jpeg'],
            ['555', '555 Tuna Mechado', 'products/catalog-new-555-tuna-mechado.jpg'],
            ['Argentina', 'Argentina Corned Beef', 'products/catalog-new-argentina-corned-beef.png'],
            ['Argentina', 'Argentina Corned Beef Hot & Spicy', 'products/catalog-new-argentina-corned-beef-hot-spicy-clean.jpg'],
            ['Argentina', 'Argentina Corned Beef Giniling', 'products/catalog-new-argentina-giniling-clean.jpg'],
            ['Argentina', 'Argentina Beef Loaf', 'products/catalog-new-argentina-beef-loaf-clean.jpg'],
            ['Argentina', 'Argentina Meat Loaf', 'products/catalog-new-argentina-meat-loaf-clean.jpg'],
            ['Argentina', 'Argentina Chicken Loaf', 'products/catalog-new-argentina-corned-chicken-clean.jpg'],
            ['Argentina', 'Argentina Liver Spread', 'products/catalog-new-argentina-liver-spread.jpg'],
            ['Argentina', 'Argentina Vienna Sausage', 'products/catalog-new-argentina-vienna-sausage-clean.jpg'],
            ['Purefoods', 'Purefoods Corned Beef', 'products/catalog-new-purefoods-corned-beef.jpg'],
            ['Purefoods', 'Purefoods Chunkee Corned Beef', 'products/catalog-new-purefoods-chunkee-corned-beef.jpg'],
            ['Purefoods', 'Purefoods Corned Beef Hot & Spicy', 'products/catalog-new-purefoods-corned-beef-hot-and-spicy.jpg'],
            ['Purefoods', 'Purefoods Luncheon Meat', 'products/catalog-new-purefoods-luncheon-meat-clean.jpg'],
            ['Purefoods', 'Purefoods Vienna Sausage', 'products/catalog-new-purefoods-vienna-sausage-clean.jpg'],
            ['Purefoods', 'Purefoods Liver Spread', 'products/catalog-new-purefoods-liver-spread.jpg'],
            ['Purefoods', 'Purefoods Tender Juicy Hotdog', 'products/catalog-new-purefoods-tender-juicy-hotdog.jpg'],
            ['Purefoods', 'Purefoods Chicken Nuggets', 'products/catalog-new-purefoods-chicken-breast-nuggets.jpg'],
            ['Purefoods', 'Purefoods Chicken Breast Nuggets', 'products/catalog-new-purefoods-chicken-breast-nuggets.jpg'],
            ['Purefoods', 'Purefoods Ham', 'products/catalog-new-purefoods-ham.jpg'],
            ['Purefoods', 'Purefoods Bacon', 'products/catalog-new-purefoods-bacon.jpg'],
            ['Purefoods', 'Purefoods Tocino', 'products/catalog-new-purefoods-tocino-clean.jpg'],
            ['Purefoods', 'Purefoods Longganisa', 'products/catalog-new-purefoods-longganisa-clean.jpg'],
        ];

        $this->assertCount(30, $cases);

        foreach ($cases as [$brand, $product, $expected]) {
            $this->assertFileExists(storage_path('app/public/'.$expected));
            $this->assertSame($expected, $matcher->findSource($product, $brand));
        }
    }

    public function test_it_copies_the_matched_catalog_image_for_a_new_product(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/catalog-jack-piattos-sour-cream.jpg', 'package-image');

        $product = new Product;
        $product->forceFill([
            'id' => 321,
            'product_name' => 'Piattos Sour Cream & Onion',
            'brand' => "Jack 'n Jill",
        ]);

        $destination = app(LocalProductImageMatcher::class)->matchAndCopy($product);

        $this->assertSame('products/auto-321.jpg', $destination);
        Storage::disk('public')->assertExists($destination);
        $this->assertSame('package-image', Storage::disk('public')->get($destination));
    }
}

<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalProductImageMatcher
{
    /** Exact catalog names supplied by the user, normalized by normalizeCatalogValue(). */
    private const EXACT_PRODUCT_MATCHES = [
        'joy' => [
            'lemon' => 'products/catalog-joy-lemon.jpg',
            'kalamansi' => 'products/catalog-joy-kalamansi.jpg',
            'ultra lemon' => 'products/catalog-joy-ultra-lemon-v2.jpg',
            'ultra power' => 'products/catalog-joy-ultra-power-v2.jpg',
            'antibacterial' => 'products/catalog-joy-antibac.jpg',
            'dishwashing liquid' => 'products/catalog-joy-dishwashing-liquid.jpg',
        ],
        'smart' => [
            'dishwashing liquid lemon' => 'products/catalog-smart-lemon.jpg',
            'dishwashing liquid kalamansi' => 'products/catalog-smart-kalamansi.jpg',
            'antibacterial dishwashing liquid' => 'products/catalog-smart-antibacterial-pouch.jpg',
        ],
        'axion' => [
            'lemon' => 'products/catalog-axion-lemon.jpg',
            'kalamansi' => 'products/catalog-axion-kalamansi-liquid.jpg',
            'antibacterial' => 'products/catalog-axion-antibacterial-doy.jpg',
            'dishwashing paste' => 'products/catalog-axion-paste-kalamansi.jpg',
        ],
        'mr muscle' => [
            'dishwashing liquid lemon' => 'products/catalog-mr-muscle-lemon.jpg',
        ],
        'brite' => [
            'dishwashing liquid lemon' => 'products/catalog-brite-dishwashing.png',
            'dishwashing liquid kalamansi' => 'products/catalog-brite-kalamansi.svg',
            'dishwashing liquid antibacterial' => 'products/catalog-brite-antibacterial.svg',
        ],
        'oishi' => [
            'prawn crackers original' => 'products/catalog-oishi-prawn-crackers.jpeg',
            'prawn crackers spicy' => 'products/catalog-oishi-prawn-spicy.png',
            'cracklings salt and vinegar' => 'products/catalog-oishi-cracklings.png',
            'cracklings spicy' => 'products/catalog-oishi-martys-spicy.jpg',
            'potato fries' => 'products/catalog-oishi-potato-fries.png',
            'fishda' => 'products/catalog-oishi-fishda.png',
            'fish crackers' => 'products/catalog-oishi-fish-crackers.png',
            'martys cracklin plain salted' => 'products/catalog-oishi-martys.png',
            'martys cracklin salt and vinegar' => 'products/catalog-oishi-martys-salt-vinegar.jpg',
            'martys cracklin spicy' => 'products/catalog-oishi-martys-spicy.jpg',
            'ridges potato chips' => 'products/catalog-oishi-ridges.jpg',
            'pillows chocolate' => 'products/catalog-oishi-pillows.jpg',
            'pillows ube' => 'products/catalog-oishi-pillows-ube.jpg',
            'pillows oat choco' => 'products/catalog-oishi-pillows-oat-choco.png',
            'bread pan garlic' => 'products/catalog-oishi-bread-pan-garlic.jpg',
            'bread pan cheese' => 'products/catalog-oishi-bread-pan-cheese.png',
            'bread pan toasted bread' => 'products/catalog-oishi-bread-pan-toasted.jpg',
            'sponge crunch' => 'products/catalog-oishi-sponge-crunch.jpg',
            'kirei yummy flakes' => 'products/catalog-oishi-kirei.jpg',
            'ribbed cracklings' => 'products/catalog-oishi-cracklings.png',
            'smart c' => 'products/catalog-oishi-smart-c.png',
            'oishi green tea' => 'products/catalog-oishi-green-tea.jpg',
            'oishi black tea' => 'products/catalog-oishi-black-tea.jpg',
        ],
        'oreo' => [
            'oreo original' => 'products/catalog-oreo-original.jpg',
            'oreo vanilla' => 'products/catalog-oreo-vanilla.jpg',
            'oreo chocolate creme' => 'products/catalog-oreo-chocolate-creme.jpg',
            'oreo strawberry creme' => 'products/catalog-oreo-strawberry-creme.png',
            'oreo double stuf' => 'products/catalog-oreo-double-stuf.jpg',
            'oreo golden' => 'products/catalog-oreo-golden.png',
            'oreo mini' => 'products/catalog-oreo-mini.png',
            'oreo wafer roll chocolate' => 'products/catalog-oreo-wafer-roll-chocolate.jpg',
            'oreo wafer roll vanilla' => 'products/catalog-oreo-wafer-roll-vanilla.jpg',
        ],
        'rebisco' => [
            'rebisco crackers' => 'products/catalog-rebisco-crackers.jpg',
            'rebisco sandwich chocolate' => 'products/catalog-rebisco-sandwich-chocolate.jpg',
            'rebisco sandwich strawberry' => 'products/catalog-rebisco-sandwich-strawberry.jpg',
            'rebisco sandwich vanilla' => 'products/catalog-rebisco-sandwich-vanilla.png',
            'rebisco sandwich peanut butter' => 'products/catalog-rebisco-sandwich-peanut-butter.jpg',
            'hansel premium' => 'products/catalog-rebisco-hansel-premium.jpg',
            'hansel mocha' => 'products/catalog-rebisco-hansel-mocha.jpg',
            'hansel milk' => 'products/catalog-rebisco-hansel-milk.jpg',
            'hansel butter' => 'products/catalog-rebisco-hansel-butter.jpg',
            'hansel chocolate' => 'products/catalog-rebisco-hansel-chocolate.jpg',
            'combi' => 'products/catalog-rebisco-combi.jpg',
            'marie time' => 'products/catalog-rebisco-marie-time.jpg',
            'bravo biscuits' => 'products/catalog-rebisco-bravo.jpg',
            'choco mucho' => 'products/catalog-rebisco-choco-mucho.jpg',
            'choco mucho dark chocolate' => 'products/catalog-rebisco-choco-mucho-dark.png',
            'choco mucho cookies and cream' => 'products/catalog-rebisco-choco-mucho-cookies-cream.jpg',
            'choco mucho white' => 'products/catalog-rebisco-choco-mucho-white.jpg',
            'fudgee barr chocolate' => 'products/catalog-rebisco-fudgee-barr-chocolate.png',
            'fudgee barr vanilla' => 'products/catalog-rebisco-fudgee-barr-vanilla.jpg',
            'fudgee barr mocha' => 'products/catalog-rebisco-fudgee-barr-mocha.jpg',
            'fudgee barr macapuno' => 'products/catalog-rebisco-fudgee-barr-macapuno.jpg',
            'superstix chocolate' => 'products/catalog-rebisco-superstix-chocolate.jpg',
            'superstix ube' => 'products/catalog-rebisco-superstix-ube.jpg',
            'superstix strawberry' => 'products/catalog-rebisco-superstix-strawberry.jpg',
            'superstix milk' => 'products/catalog-rebisco-superstix-milk.jpg',
            'creamline wafer' => 'products/catalog-rebisco-creamline-wafer.jpg',
            'doowee donut' => 'products/catalog-rebisco-doowee-donut.jpg',
            'doowee choco' => 'products/catalog-rebisco-doowee-choco.jpg',
            'frootees' => 'products/catalog-rebisco-frootees.jpg',
        ],
        'skyflakes' => [
            'skyflakes original' => 'products/catalog-skyflakes-original.jpg',
            'skyflakes fit' => 'products/catalog-skyflakes-fit.png',
            'skyflakes condensada' => 'products/catalog-skyflakes-condensada.png',
            'skyflakes cracker sandwich cheese' => 'products/catalog-skyflakes-cheese.jpg',
            'skyflakes cracker sandwich chocolate' => 'products/catalog-skyflakes-chocolate.jpg',
            'skyflakes cracker sandwich peanut butter' => 'products/catalog-skyflakes-peanut-butter.jpg',
        ],
        'nestle' => [
            'nestle fresh milk' => 'products/catalog-nestle-fresh-milk.jpg',
            'nestle low fat milk' => 'products/catalog-nestle-low-fat-milk.jpg',
            'nestle all purpose cream' => 'products/catalog-nestle-all-purpose-cream.jpg',
            'nestle carnation evaporated milk' => 'products/catalog-nestle-carnation-evaporated.jpg',
            'nestle carnation condensada' => 'products/catalog-nestle-carnation-condensada.jpg',
            'nestle yogurt' => 'products/catalog-nestle-yogurt.png',
            'nestle koko krunch' => 'products/catalog-nestle-koko-krunch.jpg',
            'nestle koko krunch duo' => 'products/catalog-nestle-koko-krunch-duo.jpg',
            'nestle honey stars' => 'products/catalog-nestle-honey-stars.jpg',
            'nestle corn flakes' => 'products/catalog-nestle-corn-flakes.jpg',
            'nestle fitnesse' => 'products/catalog-nestle-fitnesse.jpg',
            'nestle chuckie' => 'products/catalog-nestle-chuckie.jpg',
            'nestle bear brand' => 'products/catalog-nestle-bear-brand.jpg',
            'nestle milo' => 'products/catalog-nestle-milo.jpg',
            'nestle nescafe' => 'products/catalog-nestle-nescafe.jpg',
        ],
        'milo' => [
            'milo chocolate malt powder' => 'products/catalog-milo-chocolate-malt.jpg',
            'milo activ go' => 'products/catalog-milo-activ-go.jpg',
            'milo ready to drink' => 'products/catalog-milo-rtd.jpg',
            'milo champion formula' => 'products/catalog-milo-champion.png',
            'milo 3 in 1' => 'products/catalog-milo-3in1.png',
            'milo sachet' => 'products/catalog-milo-sachet.webp',
            'milo pouch' => 'products/catalog-new-milo-pouch-clean.png',
            'milo twin pack' => 'products/catalog-milo-twin-pack.jpg',
        ],
        'nescafe' => [
            'nescafe classic' => 'products/catalog-nescafe-classic.jpg',
            'nescafe classic decaf' => 'products/catalog-nescafe-classic-decaf.jpg',
            'nescafe gold' => 'products/catalog-nescafe-gold.jpg',
            'nescafe gold intense' => 'products/catalog-nescafe-gold-intense.jpg',
            'nescafe 3 in 1 original' => 'products/catalog-nescafe-3in1-original.jpg',
            'nescafe 3 in 1 creamy white' => 'products/catalog-nescafe-3in1-creamy-white.jpg',
            'nescafe 3 in 1 brown and creamy' => 'products/catalog-nescafe-3in1-brown-creamy.png',
            'nescafe creamy latte' => 'products/catalog-nescafe-creamy-latte.jpg',
            'nescafe cappuccino' => 'products/catalog-nescafe-cappuccino.jpeg',
            'nescafe cafe creations caramel latte' => 'products/catalog-nescafe-caramel-latte.jpg',
            'nescafe cafe creations mocha latte' => 'products/catalog-nescafe-mocha-latte.webp',
            'nescafe black roast' => 'products/catalog-nescafe-black-roast.png',
            'nescafe ready to drink' => 'products/catalog-nescafe-rtd.png',
        ],
        'kopiko' => [
            'kopiko brown coffee' => 'products/catalog-kopiko-brown-single.png',
            'kopiko blanca' => 'products/catalog-kopiko-blanca.jpg',
            'kopiko black 3 in one' => 'products/catalog-kopiko-black.jpg',
            'kopiko l a coffee' => 'products/catalog-kopiko-la.jpg',
            'kopiko cappuccino' => 'products/catalog-kopiko-cappuccino.png',
            'kopiko lucky day' => 'products/catalog-kopiko-lucky-day.jpg',
            'kopiko 78 c' => 'products/catalog-kopiko-78c.jpg',
            'kopiko coffee candy' => 'products/catalog-kopiko-coffee-candy.jpg',
            'kopiko cappuccino candy' => 'products/catalog-kopiko-cappuccino-candy.jpg',
        ],
        'great taste' => [
            'great taste granules' => 'products/catalog-great-taste-granules.jpg',
            'great taste premium' => 'products/catalog-great-taste-premium.png',
            'great taste white' => 'products/catalog-great-taste-white.jpg',
            'great taste white caramel' => 'products/catalog-great-taste-white-caramel.jpg',
            'great taste white crema' => 'products/catalog-great-taste-white-crema.png',
            'great taste choco' => 'products/catalog-great-taste-choco.jpg',
            'great taste original' => 'products/catalog-great-taste-original.jpg',
            'great taste strong' => 'products/catalog-great-taste-strong.jpg',
            'great taste 3 in 1' => 'products/catalog-great-taste-3in1-single-clean.png',
            'great taste black forest' => 'products/catalog-great-taste-black-forest.png',
        ],
        'pringles' => [
            'pringles original' => 'products/catalog-pringles-original.jpg',
            'pringles sour cream and onion' => 'products/catalog-pringles-sour-cream.jpg',
            'pringles cheddar cheese' => 'products/catalog-pringles-cheese.jpg',
            'pringles barbecue' => 'products/catalog-new-pringles-barbecue-clean.jpg',
            'pringles pizza' => 'products/catalog-new-pringles-pizza-clean.jpg',
            'pringles hot and spicy' => 'products/catalog-new-pringles-hot-spicy-clean.jpg',
            'pringles salt and vinegar' => 'products/catalog-new-pringles-salt-vinegar-clean.jpg',
            'pringles ranch' => 'products/catalog-new-pringles-ranch-clean.jpg',
            'pringles honey mustard' => 'products/catalog-new-pringles-honey-mustard-clean.jpeg',
            'pringles jalapeno' => 'products/catalog-new-pringles-jalapeno-clean.jpg',
        ],
        'coca cola' => [
            'coca cola original taste' => 'products/catalog-new-coca-cola-original-clean.jpg',
            'coca cola zero sugar' => 'products/catalog-new-coca-cola-zero-sugar-clean.jpg',
            'coca cola light' => 'products/catalog-new-coca-cola-light-clean.jpg',
            'coca cola in can' => 'products/catalog-new-coca-cola-can-clean.jpg',
            'coca cola in bottle' => 'products/catalog-new-coca-cola-bottle-clean.png',
            'coca cola 1 5l' => 'products/catalog-new-coca-cola-1-5l-clean.jpg',
            'coca cola 2l' => 'products/catalog-new-coca-cola-2l-clean.png',
        ],
        'pepsi' => [
            'pepsi original' => 'products/catalog-new-pepsi-original.jpg',
            'pepsi zero sugar' => 'products/catalog-new-pepsi-zero-sugar-clean.jpg',
            'pepsi black' => 'products/catalog-new-pepsi-black-clean.jpg',
            'pepsi in can' => 'products/catalog-new-pepsi-in-can.jpg',
            'pepsi in bottle' => 'products/catalog-new-pepsi-original.jpg',
            'pepsi 1 5l' => 'products/catalog-new-pepsi-1-5l.jpg',
            'pepsi 2l' => 'products/catalog-new-pepsi-2l.jpg',
        ],
        'lucky me' => [
            'lucky me pancit canton original' => 'products/catalog-new-lucky-me-pancit-canton-original-clean.jpg',
            'lucky me pancit canton kalamansi' => 'products/catalog-new-lucky-me-pancit-canton-kalamansi.png',
            'lucky me pancit canton chilimansi' => 'products/catalog-new-lucky-me-pancit-canton-chilimansi.jpg',
            'lucky me pancit canton sweet and spicy' => 'products/catalog-new-lucky-me-pancit-canton-sweet-and-spicy.png',
            'lucky me pancit canton extra hot chili' => 'products/catalog-new-lucky-me-pancit-canton-extra-hot-chili.jpg',
            'lucky me pancit canton hot chili' => 'products/catalog-new-lucky-me-pancit-canton-hot-chili-clean.png',
            'lucky me beef na beef' => 'products/catalog-new-lucky-me-beef-na-beef-clean.webp',
            'lucky me chicken na chicken' => 'products/catalog-new-lucky-me-chicken-na-chicken-clean.jpg',
            'lucky me bulalo' => 'products/catalog-new-lucky-me-bulalo-clean.jpg',
            'lucky me la paz batchoy' => 'products/catalog-new-lucky-me-la-paz-batchoy-clean.jpeg',
            'lucky me spicy labuyo beef' => 'products/catalog-new-lucky-me-spicy-labuyo-beef.png',
            'lucky me jjamppong' => 'products/catalog-new-lucky-me-jjamppong-clean.jpg',
            'lucky me beef mami' => 'products/catalog-new-lucky-me-beef-na-beef-clean.webp',
            'lucky me chicken mami' => 'products/catalog-new-lucky-me-chicken-na-chicken-clean.jpg',
        ],
        'alaska' => [
            'alaska powdered milk' => 'products/catalog-new-alaska-powdered-milk-clean.jpg',
            'alaska fortified powdered milk' => 'products/catalog-new-alaska-fortified-powdered-milk.png',
            'alaska evaporated filled milk' => 'products/catalog-new-alaska-evaporated-filled-milk.jpg',
            'alaska classic evaporated filled milk' => 'products/catalog-new-alaska-classic-evaporated-filled-milk.png',
            'alaska crema all purpose creamer' => 'products/catalog-new-alaska-crema-clean.jpg',
            'alaska condensada' => 'products/catalog-new-alaska-condensada.jpg',
            'alaska sweetened condensed filled milk' => 'products/catalog-new-alaska-sweetened-condensed-clean.png',
            'alaska choco condensada' => 'products/catalog-new-alaska-choco-condensada-clean.jpg',
            'alaska fresh milk' => 'products/catalog-new-alaska-fresh-milk.jpg',
            'alaska choco milk' => 'products/catalog-new-alaska-choco-milk-rtd-clean.jpg',
            'alaska yoghurt drink' => 'products/catalog-new-alaska-yoghurt-drink.jpg',
        ],
        'payless' => [
            'payless pancit canton original' => 'products/catalog-new-payless-original-clean.jpg',
            'payless pancit canton kalamansi' => 'products/catalog-new-payless-kalamansi-clean.jpeg',
            'payless pancit canton extra hot' => 'products/catalog-new-payless-extra-hot-clean.png',
            'payless xtra big original' => 'products/catalog-new-payless-original-clean.jpg',
            'payless xtra big kalamansi' => 'products/catalog-new-payless-pancit-canton-kalamansi.jpg',
            'payless xtra big chilimansi' => 'products/catalog-new-payless-xtra-big-chilimansi-clean.jpg',
            'payless xtra big sweet and spicy' => 'products/catalog-new-payless-xtra-big-sweet-spicy-clean.jpg',
            'payless xtra big hot and spicy' => 'products/catalog-new-payless-pancit-canton-extra-hot.jpg',
            'payless instant mami beef' => 'products/catalog-new-payless-mami-beef-clean.jpeg',
            'payless instant mami chicken' => 'products/catalog-new-payless-instant-mami-chicken.jpg',
        ],
        'century tuna' => [
            'century tuna flakes in oil' => 'products/catalog-new-century-tuna-flakes-in-oil.jpg',
            'century tuna flakes in vegetable oil' => 'products/catalog-new-century-tuna-vegetable-oil-clean.jpeg',
            'century tuna hot and spicy' => 'products/catalog-new-century-tuna-hot-and-spicy.jpg',
            'century tuna calamansi' => 'products/catalog-new-century-tuna-calamansi.jpg',
            'century tuna adobo' => 'products/catalog-new-century-tuna-adobo.jpg',
            'century tuna afritada' => 'products/catalog-new-century-tuna-afritada.png',
            'century tuna mechado' => 'products/catalog-new-century-tuna-mechado-clean.jpg',
            'century tuna sisig' => 'products/catalog-new-century-tuna-sisig.jpg',
            'century tuna bangus' => 'products/catalog-new-century-bangus-sisig-clean.jpg',
            'century tuna solid in oil' => 'products/catalog-new-century-tuna-solid-oil-clean.png',
            'century tuna lite' => 'products/catalog-new-century-tuna-lite.png',
        ],
        'mega' => [
            'mega sardines in tomato sauce' => 'products/catalog-new-mega-sardines-tomato-clean.jpg',
            'mega sardines in tomato sauce with chili' => 'products/catalog-new-mega-sardines-chili-clean.png',
            'mega sardines spanish style' => 'products/catalog-new-mega-sardines-spanish-style.jpg',
            'mega tuna flakes in oil' => 'products/catalog-new-mega-tuna-flakes-in-oil.jpg',
            'mega tuna hot and spicy' => 'products/catalog-new-mega-tuna-hot-and-spicy.jpg',
            'mega tuna caldereta' => 'products/catalog-new-mega-tuna-caldereta.jpg',
            'mega tuna afritada' => 'products/catalog-new-mega-tuna-afritada.jpg',
            'mega mackerel in natural oil' => 'products/catalog-new-mega-mackerel-in-natural-oil.jpg',
            'mega mackerel in tomato sauce' => 'products/catalog-new-mega-mackerel-tomato-clean.png',
        ],
        '555' => [
            '555 sardines in tomato sauce' => 'products/catalog-new-555-sardines-tomato-clean.jpeg',
            '555 sardines hot' => 'products/catalog-new-555-sardines-hot.png',
            '555 sardines spanish style' => 'products/catalog-new-555-sardines-spanish-clean.png',
            '555 tuna flakes in oil' => 'products/catalog-new-555-tuna-flakes-in-oil.jpg',
            '555 tuna hot and spicy' => 'products/catalog-new-555-tuna-hot-and-spicy.jpg',
            '555 tuna adobo' => 'products/catalog-new-555-tuna-adobo.png',
            '555 tuna caldereta' => 'products/catalog-new-555-tuna-caldereta.png',
            '555 tuna afritada' => 'products/catalog-new-555-tuna-afritada-clean.jpeg',
            '555 tuna mechado' => 'products/catalog-new-555-tuna-mechado.jpg',
        ],
        'argentina' => [
            'argentina corned beef' => 'products/catalog-new-argentina-corned-beef.png',
            'argentina corned beef hot and spicy' => 'products/catalog-new-argentina-corned-beef-hot-spicy-clean.jpg',
            'argentina corned beef giniling' => 'products/catalog-new-argentina-giniling-clean.jpg',
            'argentina beef loaf' => 'products/catalog-new-argentina-beef-loaf-clean.jpg',
            'argentina meat loaf' => 'products/catalog-new-argentina-meat-loaf-clean.jpg',
            'argentina chicken loaf' => 'products/catalog-new-argentina-corned-chicken-clean.jpg',
            'argentina liver spread' => 'products/catalog-new-argentina-liver-spread.jpg',
            'argentina vienna sausage' => 'products/catalog-new-argentina-vienna-sausage-clean.jpg',
        ],
        'purefoods' => [
            'purefoods corned beef' => 'products/catalog-new-purefoods-corned-beef.jpg',
            'purefoods chunkee corned beef' => 'products/catalog-new-purefoods-chunkee-corned-beef.jpg',
            'purefoods corned beef hot and spicy' => 'products/catalog-new-purefoods-corned-beef-hot-and-spicy.jpg',
            'purefoods luncheon meat' => 'products/catalog-new-purefoods-luncheon-meat-clean.jpg',
            'purefoods vienna sausage' => 'products/catalog-new-purefoods-vienna-sausage-clean.jpg',
            'purefoods liver spread' => 'products/catalog-new-purefoods-liver-spread.jpg',
            'purefoods tender juicy hotdog' => 'products/catalog-new-purefoods-tender-juicy-hotdog.jpg',
            'purefoods chicken nuggets' => 'products/catalog-new-purefoods-chicken-breast-nuggets.jpg',
            'purefoods chicken breast nuggets' => 'products/catalog-new-purefoods-chicken-breast-nuggets.jpg',
            'purefoods ham' => 'products/catalog-new-purefoods-ham.jpg',
            'purefoods bacon' => 'products/catalog-new-purefoods-bacon.jpg',
            'purefoods tocino' => 'products/catalog-new-purefoods-tocino-clean.jpg',
            'purefoods longganisa' => 'products/catalog-new-purefoods-longganisa-clean.jpg',
        ],
        'spam' => [
            'spam classic' => 'products/catalog-new-spam-classic-clean.webp',
            'spam less sodium' => 'products/catalog-new-spam-less-sodium.jpg',
            'spam lite' => 'products/catalog-new-spam-lite.jpg',
            'spam hot and spicy' => 'products/catalog-new-spam-hot-and-spicy.jpg',
            'spam bacon' => 'products/catalog-new-spam-bacon-clean.jpg',
            'spam tocino' => 'products/catalog-new-spam-tocino.jpg',
            'spam hickory smoke' => 'products/catalog-new-spam-hickory-smoke-clean.jpg',
        ],
        'datu puti' => [
            'datu puti vinegar' => 'products/catalog-new-datu-puti-vinegar.png',
            'datu puti soy sauce' => 'products/catalog-new-datu-puti-soy-sauce.jpg',
            'datu puti soy sauce and vinegar value pack' => 'products/catalog-new-datu-puti-soy-sauce-and-vinegar-value-pack.jpg',
            'datu puti spiced vinegar' => 'products/catalog-new-datu-puti-spiced-vinegar.jpg',
            'datu puti pinoy spice' => 'products/catalog-new-datu-puti-pinoy-spice-clean.jpg',
            'datu puti fish sauce' => 'products/catalog-new-datu-puti-fish-sauce-clean.jpeg',
            'datu puti oyster sauce' => 'products/catalog-new-datu-puti-oyster-sauce.png',
            'datu puti adobo series' => 'products/catalog-new-datu-puti-adobo-series.jpg',
        ],
        'silver swan' => [
            'silver swan soy sauce' => 'products/catalog-new-silver-swan-soy-sauce.jpg',
            'silver swan vinegar' => 'products/catalog-new-silver-swan-vinegar.jpg',
            'silver swan sukang puti' => 'products/catalog-new-silver-swan-sukang-puti.jpg',
            'silver swan cane vinegar' => 'products/catalog-new-silver-swan-cane-vinegar.jpg',
            'silver swan fish sauce' => 'products/catalog-new-silver-swan-fish-sauce-clean.jpg',
            'silver swan special soy sauce' => 'products/catalog-new-silver-swan-special-soy-clean.jpg',
            'silver swan wow sarap seasoning' => 'products/catalog-new-silver-swan-wow-sarap-seasoning.jpg',
        ],
        'bear brand' => [
            'bear brand fortified powdered milk' => 'products/catalog-bear-brand-fortified.jpg',
            'bear brand adult plus' => 'products/catalog-bear-brand-adult-plus.jpg',
            'bear brand sterilized milk' => 'products/catalog-bear-brand-sterilized.jpg',
            'bear brand ready to drink' => 'products/catalog-bear-brand-rtd.jpg',
            'bear brand choco milk' => 'products/catalog-bear-brand-choco.jpg',
            'bear brand fortified powdered milk sachet' => 'products/catalog-bear-brand-sachet.jpg',
            'bear brand fortified powdered milk pouch' => 'products/catalog-bear-brand-pouch.jpg',
        ],
    ];

    /** Only exact brand/product pairs are allowed to prevent wrong packaging. */
    private const PRODUCT_MATCHES = [
        ['brand' => '/\b(dona maria|jasponica)\b/i', 'product' => '/\brice\b/i', 'image' => 'products/catalog-dona-maria.jpg'],

        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\bpiattos\s+sour\s+cream(?:\s*(?:&|and)\s*onion)?\b/i', 'image' => 'products/catalog-jack-piattos-sour-cream.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\bpiattos\s+roast\s+beef\b/i', 'image' => 'products/catalog-jack-piattos-roast-beef.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\bpiattos\s+roadhouse\s+(?:barbecue|bbq)\b/i', 'image' => 'products/catalog-jack-piattos-roadhouse.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\bpiattos\s+cheese\b/i', 'image' => 'products/catalog-piattos-cheese.png'],

        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\bnova\s+country\s+cheddar\b/i', 'image' => 'products/catalog-nova-cheddar.webp'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\bnova\s+homestyle\s+(?:barbecue|bbq)\b/i', 'image' => 'products/catalog-jack-nova-homestyle-bbq.jpg'],

        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\bchippy\s+chili\s*(?:&|and)\s*cheese\b/i', 'image' => 'products/catalog-jack-chippy-chili-cheese.png'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\bchippy\s+garlic\s*(?:&|and)\s*vinegar\b/i', 'image' => 'products/catalog-jack-chippy-garlic-vinegar.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\bchippy\s+(?:barbecue|bbq)\b/i', 'image' => 'products/catalog-jack-chippy.jpg'],

        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\bv[\s-]?cut\s+(?:spicy\s+)?(?:barbecue|bbq)\b/i', 'image' => 'products/catalog-jack-vcut.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\bmr\.?\s*chips\s+nachos?\s+cheese\b/i', 'image' => 'products/catalog-jack-mr-chips.jpg'],

        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\broller\s+coaster\s+cheddar\s+cheese\b/i', 'image' => 'products/catalog-jack-roller-coaster-cheddar.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\broller\s+coaster\s+(?:barbecue|bbq)\b/i', 'image' => 'products/catalog-jack-roller-coaster-bbq.png'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\btostillas\s+nacho\s+cheese\b/i', 'image' => 'products/catalog-jack-tostillas-nacho.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\btostillas\s+(?:barbecue|bbq)\b/i', 'image' => 'products/catalog-jack-tostillas-bbq-mix.jpg'],

        ['brand' => '/\b(?:nutriasia|jack\s*(?:[\'\x{2019}]?n|and)\s*jill)\b/iu', 'product' => '/\bmang\s+juan\s+espesyal\s+(?:sukang\s+paombong|suka[\'\x{2019}]?t\s+sili)\b/iu', 'image' => 'products/catalog-jack-mang-juan-espesyal.png'],
        ['brand' => '/\b(?:nutriasia|jack\s*(?:[\'\x{2019}]?n|and)\s*jill)\b/iu', 'product' => '/(?:\bmang\s+juan\s+(?:chik[\'\x{2019}]?n|chicken)\s+skin\b|\b(?:chik[\'\x{2019}]?n|chicken)\s+skin\s+ni\s+mang\s+juan\b)/iu', 'image' => 'products/catalog-jack-mang-juan-chikn-skin.png'],
        ['brand' => '/\b(?:nutriasia|jack\s*(?:[\'\x{2019}]?n|and)\s*jill)\b/iu', 'product' => '/(?:\bmang\s+juan\s+chicharron\b|\bchicharron\s+ni\s+mang\s+juan(?:\s+klasik)?\b)/i', 'image' => 'products/catalog-jack-mang-juan-klasik.jpg'],
        ['brand' => '/\b(?:nutriasia|jack\s*(?:[\'\x{2019}]?n|and)\s*jill)\b/iu', 'product' => '/\bmang\s+juan\s+sukang\s+paombong\b/i', 'image' => 'products/catalog-mang-juan.png'],
        ['brand' => '/\b(?:nutriasia|jack\s*(?:[\'\x{2019}]?n|and)\s*jill)\b/iu', 'product' => '/^\s*mang\s+juan\s*$/i', 'image' => 'products/catalog-mang-juan.png'],

        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\bchiz\s+curls\s+cheese\b/i', 'image' => 'products/catalog-jack-chiz-curls.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\b(?:jack\s*(?:[\'\x{2019}]?n|and)\s*jill\s+)?potato\s+chips\s+(?:classic\s+)?(?:barbecue|bbq)\b/iu', 'image' => 'products/catalog-jack-potato-chips-bbq.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\b(?:jack\s*(?:[\'\x{2019}]?n|and)\s*jill\s+)?potato\s+chips\s+classic\b/iu', 'image' => 'products/catalog-jack-potato-chips-classic.jpg'],

        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\bcream[\s-]?o\s+deluxe\b/i', 'image' => 'products/catalog-jack-cream-o-deluxe.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*cream[\s-]?o\s*$/i', 'image' => 'products/catalog-jack-cream-o.png'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*presto\s+creams?\s+(?:(?:peanut\s+butter)\s*(?:&|and)\s*(?:chocolate|choco)|(?:chocolate|choco)\s*(?:&|and)?\s*peanut\s+butter)\s*$/i', 'image' => 'products/catalog-jack-presto-choco-peanut-butter.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*presto\s+creams?(?:\s+peanut\s+butter)?\s*$/i', 'image' => 'products/catalog-jack-presto-creams.png'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*magic\s+flakes\s+cheese\s*$/i', 'image' => 'products/catalog-jack-magic-flakes-cheese.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*magic\s+(?:flakes|creams?)\s+peanut\s+butter\s*$/i', 'image' => 'products/catalog-jack-magic-creams-peanut-butter.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*magic\s+flakes(?:\s+(?:original|plain))?\s*$/i', 'image' => 'products/catalog-jack-magic-flakes.png'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*dewberry\s+blueberr(?:y|ies)(?:\s*(?:&|and|n|\'n)\s*cream)?\s*$/i', 'image' => 'products/catalog-jack-dewberry-blueberry.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*dewberry(?:\s+strawberr(?:y|ies)(?:\s*(?:&|and|n|\'n)\s*cream)?)?\s*$/i', 'image' => 'products/catalog-jack-dewberry.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*wafrets\s+cheese(?:\s+(?:bar|brix))?\s*$/i', 'image' => 'products/catalog-jack-wafrets-cheese.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*wafrets(?:\s+choco)?\s+vanilla(?:\s+(?:bar|brix))?\s*$/i', 'image' => 'products/catalog-jack-wafrets-vanilla.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*wafrets(?:\s+chocolate|\s+choco)?\s*$/i', 'image' => 'products/catalog-jack-wafrets.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\bchoco\s+knots\b/i', 'image' => 'products/catalog-jack-choco-knots.png'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*quake(?:\s+overload(?:\s+black\s+forest)?)?\s*$/i', 'image' => 'products/catalog-jack-quake.jpg'],

        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\bcloud\s*9\s+choco\s+fudge\b/i', 'image' => 'products/catalog-jack-cloud-9-choco-fudge.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\bcloud\s*9\s+white\b/i', 'image' => 'products/catalog-jack-cloud-9-white.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*cloud\s*9(?:\s+classic)?\s*$/i', 'image' => 'products/catalog-jack-cloud-9.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\bchooey\s+choco\b/i', 'image' => 'products/catalog-jack-chooey-choco.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*nips\s+white\s+chocolate\s*$/i', 'image' => 'products/catalog-jack-nips-white.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*nips\s+peanut\s*$/i', 'image' => 'products/catalog-jack-nips-peanut.png'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*nips(?:\s+milk\s+chocolate)?\s*$/i', 'image' => 'products/catalog-jack-nips.png'],

        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*maxx\s+honey\s+lemon(?:\s+(?:menthol\s+)?candy)?\s*$/i', 'image' => 'products/catalog-jack-maxx-honey-lemon.png'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*maxx\s+cherry(?:\s+(?:menthol\s+)?candy)?\s*$/i', 'image' => 'products/catalog-jack-maxx-cherry.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*maxx(?:\s+(?:cool\s+)?(?:menthol|eucalyptus)(?:\s+candy)?)?\s*$/i', 'image' => 'products/catalog-jack-maxx.png'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*dynamite(?:\s+(?:choco\s+mint|menthol))?\s*$/i', 'image' => 'products/catalog-jack-dynamite.png'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*x\.?\s*o\.?\s+milk(?:\s+tea)?(?:\s+candy)?\s*$/i', 'image' => 'products/catalog-jack-xo-milk-tea.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*x\.?\s*o\.?(?:\s+(?:classics?\s+)?coffee(?:\s+candy)?)?\s*$/i', 'image' => 'products/catalog-jack-xo.webp'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*potchi(?:\s+strawberry\s+cream)?\s*$/i', 'image' => 'products/catalog-jack-potchi.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/^\s*lush(?:\s+chocolate)?\s*$/i', 'image' => 'products/catalog-jack-lush.jpg'],
        ['brand' => '/\bjack\s*(?:[\'\x{2019}]?n|and)\s*jill\b/iu', 'product' => '/\bchooey\s+toffee\b/i', 'image' => 'products/catalog-jack-chooey-toffee.jpg'],

        ['brand' => '/\boishi\b/i', 'product' => '/\bprawn\s+crackers?\b/i', 'image' => 'products/catalog-oishi-prawn-crackers.jpeg'],
        ['brand' => '/\boishi\b/i', 'product' => '/\b(?:ribbed\s+)?cracklings?\b/i', 'image' => 'products/catalog-oishi-cracklings.png'],
        ['brand' => '/\boishi\b/i', 'product' => '/\bpotato\s+fries\b/i', 'image' => 'products/catalog-oishi-potato-fries.png'],
        ['brand' => '/\boishi\b/i', 'product' => '/\bpillows?\b/i', 'image' => 'products/catalog-oishi-pillows.jpg'],
        ['brand' => '/\boishi\b/i', 'product' => '/\bmarty[\'\x{2019}]?s(?:\s+cracklin[\'\x{2019}]?)?\b/iu', 'image' => 'products/catalog-oishi-martys.png'],

        ['brand' => '/\bleslie[\'\x{2019}]?s\b/iu', 'product' => '/\bclover\s+chips?\b/i', 'image' => 'products/catalog-leslies-clover.png'],
        ['brand' => '/\bleslie[\'\x{2019}]?s\b/iu', 'product' => '/\bcheezy\b/i', 'image' => 'products/catalog-leslies-cheezy.jpg'],
        ['brand' => '/\bleslie[\'\x{2019}]?s\b/iu', 'product' => '/\bfarmer\s+john\b/i', 'image' => 'products/catalog-leslies-farmer-john.png'],

        ['brand' => '/\bregent\b/i', 'product' => '/\bcheese\s+rings?\b/i', 'image' => 'products/catalog-regent-cheese-ring.jpg'],
        ['brand' => '/\bregent\b/i', 'product' => '/\b(?:golden\s+)?sweet\s+corn\b/i', 'image' => 'products/catalog-regent-sweet-corn.jpg'],
        ['brand' => '/\bregent\b/i', 'product' => '/\btempura\b/i', 'image' => 'products/catalog-regent-tempura.jpg'],

        ['brand' => '/\bgranny\s+goose\b/i', 'product' => '/\btortillos\b/i', 'image' => 'products/catalog-granny-tortillos.webp'],
        ['brand' => '/\bgranny\s+goose\b/i', 'product' => '/\bkornets\b/i', 'image' => 'products/catalog-granny-kornets.png'],
        ['brand' => '/\bgranny\s+goose\b/i', 'product' => '/\bk+r+unch\b/i', 'image' => 'products/catalog-granny-krrrunch.jpg'],

        ['brand' => '/\blay[\'\x{2019}]?s\b/iu', 'product' => '/\b(?:lay[\'\x{2019}]?s\s+)?(?:classic|original|potato\s+chips?)\b/iu', 'image' => 'products/catalog-lays-classic.png'],

        ['brand' => '/\bpringles\b/i', 'product' => '/\bsour\s+cream\s*(?:&|and)?\s*onion\b/i', 'image' => 'products/catalog-pringles-sour-cream.jpg'],
        ['brand' => '/\bpringles\b/i', 'product' => '/\b(?:cheesy\s+)?cheese\b/i', 'image' => 'products/catalog-pringles-cheese.jpg'],
        ['brand' => '/\bpringles\b/i', 'product' => '/\b(?:pringles\s+)?(?:original|classic)\b/i', 'image' => 'products/catalog-pringles-original.jpg'],
    ];

    public function matchAndCopy(Product $product): ?string
    {
        $source = $this->findSource($product->product_name, $product->brand ?? '');

        return $source ? $this->copyForProduct($product, $source) : null;
    }

    public function findSource(string $productName, string $brand): ?string
    {
        $productName = trim($productName);
        $brand = trim($brand);

        $brandKey = $this->normalizeCatalogValue($brand);
        $brandKey = $brandKey === 'sky flakes' ? 'skyflakes' : $brandKey;
        $productKey = $this->normalizeCatalogValue($productName);
        $exactSource = self::EXACT_PRODUCT_MATCHES[$brandKey][$productKey] ?? null;

        // Allow either "Lemon" or "Joy Lemon" when the brand is Joy.
        if ($exactSource === null && str_starts_with($productKey, $brandKey.' ')) {
            $productKeyWithoutBrand = trim(Str::after($productKey, $brandKey.' '));
            $exactSource = self::EXACT_PRODUCT_MATCHES[$brandKey][$productKeyWithoutBrand] ?? null;
        }

        if ($exactSource !== null) {
            return Storage::disk('public')->exists($exactSource) ? $exactSource : null;
        }

        foreach (self::PRODUCT_MATCHES as $match) {
            if (preg_match($match['brand'], $brand) && preg_match($match['product'], $productName)) {
                return Storage::disk('public')->exists($match['image']) ? $match['image'] : null;
            }
        }

        return null;
    }

    private function normalizeCatalogValue(string $value): string
    {
        $value = str_replace(['&', '°', '’', '‘'], [' and ', ' ', "'", "'"], $value);
        $value = str_replace("'", '', Str::lower(Str::ascii($value)));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    private function copyForProduct(Product $product, string $source): ?string
    {
        if (! Storage::disk('public')->exists($source)) {
            return null;
        }

        $extension = pathinfo($source, PATHINFO_EXTENSION);
        $destination = "products/auto-{$product->id}.{$extension}";
        Storage::disk('public')->copy($source, $destination);

        return $destination;
    }
}

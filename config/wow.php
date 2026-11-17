<?php

return [
    'owner_email' => env('WOW_DASHBOARD_OWNER_EMAIL'),
    'battle_net' => [
        'client_id' => env('BLIZZARD_CLIENT_ID'),
        'client_secret' => env('BLIZZARD_CLIENT_SECRET'),
        'auction_house' => env('WOW_AUCTION_HOUSE_ID'),
    ],
    'auction_cut_percent' => 5,
    'items' => [
        2934 => 'Ruined Leather Scraps',
        2318 => 'Light Leather',
        2319 => 'Medium Leather',
        4234 => 'Heavy Leather',
        4304 => 'Thick Leather',
        8170 => 'Rugged Leather',
        783 => 'Light Hide',
        4231 => 'Cured Light Hide',
        4289 => 'Salt',
        2320 => 'Coarse Thread',
        2321 => 'Fine Thread',
    ],
    'vendor_inputs' => [4289 => 50, 2320 => 10, 2321 => 100],
    // WoW Forever vendor proceeds and reagent quantities, in copper.
    'recipes' => [
        ['Light Leather', 2318, 15, [2934 => 3]],
        ['Medium Leather', 2319, 50, [2318 => 4]],
        ['Heavy Leather', 4234, 150, [2319 => 5]],
        ['Cured Light Hide', 4231, 110, [783 => 1, 4289 => 1]],
        ["Hillman's Cloak", 3719, 1027, [4234 => 5, 2321 => 2]],
        ['Light Leather Pants', 7282, 599, [2318 => 10, 4231 => 1, 2321 => 1]],
        ['Heavy Armor Kit', 4265, 650, [4234 => 5, 2321 => 1]],
        ['Light Armor Kit', 2304, 15, [2318 => 1]],
        ['Embossed Leather Gloves', 4239, 71, [2318 => 3, 2320 => 2]],
    ],
];

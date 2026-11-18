<?php

return [
    'owner_email' => env('WOW_DASHBOARD_OWNER_EMAIL'),
    'battle_net' => [
        'client_id' => env('BLIZZARD_CLIENT_ID'),
        'client_secret' => env('BLIZZARD_CLIENT_SECRET'),
        'auction_house' => env('WOW_AUCTION_HOUSE_ID'),
    ],
    // Verified WoW Forever equipment that can be traded before equipping.
    'offerable_gear' => [
        'Back' => [
            ["Hillman's Cloak", 3719, 25, ['Armor' => 21, 'Spell Power' => 5, 'Stamina' => 4]],
        ],
        'Shoulders' => [
            ["Hillman's Shoulders", 4251, 21, ['Armor' => 65, 'Stamina' => 5, 'Spirit' => 3]],
        ],
        'Chest' => [
            ['Moonglow Vest', 6709, 13, ['Armor' => 74, 'Intellect' => 3, 'Spell Power' => 4]],
            ['Toughened Leather Armor', 2314, 19, ['Armor' => 84, 'Stamina' => 2, 'Spirit' => 7]],
        ],
        'Waist' => [
            ['Fine Leather Belt', 4246, 11, ['Armor' => 40, 'Agility' => 2, 'Stamina' => 2]],
            ['Dark Leather Belt', 4249, 20, ['Armor' => 48, 'Agility' => 4, 'Stamina' => 4]],
        ],
        'Legs' => [
            ['Light Leather Pants', 7282, 14, ['Armor' => 66, 'Agility' => 5, 'Stamina' => 3]],
            ['Dark Leather Pants', 5961, 18, ['Armor' => 72, 'Agility' => 8]],
        ],
        'Feet' => [
            ['Embossed Leather Boots', 2309, 10, ['Armor' => 48, 'Stamina' => 2, 'Spell Power' => 2]],
        ],
        'Wrist' => [
            ['Light Leather Bracers', 7281, 9, ['Armor' => 30, 'Agility' => 1, 'Strength' => 1]],
        ],
        'Hands' => [
            ['Nimble Leather Gloves', 7285, 19, ['Armor' => 52, 'Agility' => 4, 'Stamina' => 4]],
        ],
    ],
    // Ordered by fresh-server demand signals within each stage.
    'craft_demand' => [
        'Starter characters' => [
            'Light Leather Reagent Bag' => 'Six extra reagent slots for new characters.',
            'Medium Armor Kit' => 'A low-level upgrade for four gear slots and a reagent for upgraded kits.',
        ],
        'Midlevel characters' => [
            "Hillman's Cloak" => 'Level 25 caster upgrade and an Alliance quest turn-in for Bartolo’s Yeti Fur Cloak.',
            'Heavy Armor Kit' => 'An upgrade for four gear slots and a reagent for Forceful and Mystic kits.',
            'Heavy Quiver' => 'A 14-slot arrow upgrade for level 30 hunters.',
            'Heavy Leather Ammo Pouch' => 'A 14-slot ammunition upgrade for level 30 gun users.',
        ],
        'Endgame gearing' => [
            'Mystic Thick Armor Kit' => 'Caster gear enhancement as players reach later leveling brackets.',
            'Forceful Thick Armor Kit' => 'Physical gear enhancement as players reach later leveling brackets.',
            'Devilsaur Gauntlets' => 'Part of the crafted Devilsaur set for physical damage builds.',
            'Devilsaur Leggings' => 'Completes the crafted Devilsaur set.',
        ],
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
        ['Light Leather Reagent Bag', 277715, 200, [2318 => 8, 4231 => 1, 2320 => 3]],
        ['Medium Armor Kit', 2313, 200, [2319 => 4, 2320 => 1]],
        ['Heavy Quiver', 7371, 500, [4234 => 8, 2321 => 2]],
        ['Heavy Leather Ammo Pouch', 7372, 500, [4234 => 8, 2321 => 2]],
        ['Heavy Armor Kit', 4265, 650, [4234 => 5, 2321 => 1]],
        ['Light Armor Kit', 2304, 15, [2318 => 1]],
        ['Embossed Leather Gloves', 4239, 71, [2318 => 3, 2320 => 2]],
    ],
];

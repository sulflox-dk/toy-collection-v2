<?php

return [
    'up' => "
        -- Indsæt gamle og nye standard tags
        -- Vi bruger INSERT IGNORE for at undgå fejl, hvis nogle allerede eksisterer
        INSERT IGNORE INTO `media_tags` (`id`, `name`, `slug`) VALUES
        (1, 'Box (Top)', 'box-top'),
        (2, 'Box (Bottom)', 'box-bottom'),
        (3, 'Card (Front)', 'card-front'),
        (4, 'Figure (Front)', 'figure-front'),
        (5, 'Figure (Back)', 'figure-back'),
        (18, 'Figure', 'figure'),
        (19, 'Vehicle', 'vehicle'),
        (20, 'Playset', 'playset'),
        (21, 'Box (Front)', 'box-front'),
        (22, 'Box (Back)', 'box-back'),
        (23, 'Box (Left Side)', 'box-left-side'),
        (24, 'Box (Right Side)', 'box-right-side'),
        (25, 'Card (Back)', 'card-back'),
        (26, 'Insert/Tray', 'insert-tray'),
        (27, 'Bubble/Blister', 'bubble-blister'),
        (28, 'Loose Item', 'loose-item'),
        (29, 'Accessories', 'accessories'),
        (30, 'Weapons', 'weapons'),
        (31, 'Instructions', 'instructions'),
        (32, 'Sticker Sheet', 'sticker-sheet'),
        (33, 'Proof of Purchase/Points', 'proof-of-purchase-points'),
        (34, 'Damage Detail', 'damage-detail'),
        (35, 'Variation Detail', 'variation-detail'),
        (36, 'Group Shot', 'group-shot'),
        (38, 'Action Pose', 'action-pose'),
        (39, 'Close-up / Macro', 'close-up-macro'),
        (40, 'Packaging (Sealed)', 'packaging-sealed'),
        (41, 'Comic / Minicomic', 'comic-minicomic'),
        (42, 'Diorama / Display', 'diorama-display'),
        (43, 'Promo / Advertisement', 'promo-advertisement'),
        (44, 'Prototype', 'prototype'),
        (45, 'Mail-away', 'mail-away'),
        (46, 'Scale Reference', 'scale-reference'),
        (47, 'Catalog Image', 'catalog-image');
    ",

    'down' => "
        -- Fjern de seedede tags (bruges ved rollback)
        -- Vi sletter specifikt på ID, så evt. brugerskabte tags bevares
        DELETE FROM `media_tags` 
        WHERE `id` IN (
            1, 2, 3, 4, 5, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 
            31, 32, 33, 34, 35, 36, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47
        );
    ",
];
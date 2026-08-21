<?php

return [
    'up' => "
        -- No migration ever seeded importer_sources, and driver_class is a
        -- free-text field with no dropdown in the UI — so on a fresh install
        -- the six drivers that already exist in app/Modules/Importer/Drivers
        -- were completely unusable unless you already knew their exact
        -- fully-qualified class names to type in by hand. Seeding the six
        -- built-in sources so 'Run Import' actually works out of the box.
        INSERT IGNORE INTO `importer_sources` (`name`, `slug`, `base_url`, `driver_class`, `is_active`) VALUES
            ('Action Figure 411', 'action-figure-411', 'https://www.actionfigure411.com', 'App\\\\Modules\\\\Importer\\\\Drivers\\\\ActionFigure411Driver', 1),
            ('Galactic Collector', 'galactic-collector', 'https://www.galacticcollector.com', 'App\\\\Modules\\\\Importer\\\\Drivers\\\\GalacticCollectorDriver', 1),
            ('Galactic Figures', 'galactic-figures', 'https://www.galacticfigures.com', 'App\\\\Modules\\\\Importer\\\\Drivers\\\\GalacticFiguresDriver', 1),
            ('Jedi Temple Archives', 'jedi-temple-archives', 'https://www.jeditemplearchives.com', 'App\\\\Modules\\\\Importer\\\\Drivers\\\\JediTempleArchivesDriver', 1),
            ('Star Wars Collector', 'star-wars-collector', 'https://www.starwarscollector.com', 'App\\\\Modules\\\\Importer\\\\Drivers\\\\StarWarsCollectorDriver', 1),
            ('The Toy Collectors Guide', 'the-toy-collectors-guide', 'https://www.thetoycollectorsguide.com', 'App\\\\Modules\\\\Importer\\\\Drivers\\\\TheToyCollectorsGuideDriver', 1);
    ",

    'down' => "
        DELETE FROM `importer_sources` WHERE `slug` IN (
            'action-figure-411', 'galactic-collector', 'galactic-figures',
            'jedi-temple-archives', 'star-wars-collector', 'the-toy-collectors-guide'
        );
    "
];

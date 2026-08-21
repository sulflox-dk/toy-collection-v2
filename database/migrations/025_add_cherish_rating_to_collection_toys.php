<?php

return [
    'up' => "
        -- Personal 1-5 star rating for how much you actually cherish a toy,
        -- independent of its condition, value, or completeness. Nullable —
        -- unrated is a distinct state from '1 star', not the same thing.
        ALTER TABLE `collection_toys`
            ADD COLUMN `cherish_rating` TINYINT UNSIGNED NULL DEFAULT NULL AFTER `current_value`,
            ADD CONSTRAINT `chk_cherish_rating_range` CHECK (`cherish_rating` IS NULL OR (`cherish_rating` BETWEEN 1 AND 5));

        -- Rate a few of the demo toys from migration 024, so this feature
        -- (and the Missing Parts 'sort by cherish level' view) has something
        -- to actually demonstrate on a fresh install instead of everything
        -- reading 'Unrated'. Left most of them unrated deliberately — that's
        -- the realistic starting state for a collection you haven't gone
        -- through and rated yet.
        UPDATE `collection_toys` SET `cherish_rating` = 5 WHERE `id` = 101; -- Luke Skywalker (Tatooine)
        UPDATE `collection_toys` SET `cherish_rating` = 4 WHERE `id` = 102; -- Millennium Falcon
        UPDATE `collection_toys` SET `cherish_rating` = 5 WHERE `id` = 104; -- Darth Vader
        UPDATE `collection_toys` SET `cherish_rating` = 4 WHERE `id` = 109; -- He-Man
    ",

    'down' => "
        ALTER TABLE `collection_toys` DROP CONSTRAINT `chk_cherish_rating_range`;
        ALTER TABLE `collection_toys` DROP COLUMN `cherish_rating`;
    "
];

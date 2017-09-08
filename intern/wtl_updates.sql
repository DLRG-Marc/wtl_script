/**/
/* Automatic Waitinglist WTL */
/* Copyright (C) 2012-2020 Marc Busse */
/**/
/* This script is free software: you can redistribute it and/or */
/* modify it under the terms of the GNU General Public License */
/* as published by the Free Software Foundation, either */
/* version 3 of the License, or (at your option) any later version. */
/**/
/* This script is distributed in the hope that it will be useful, */
/* but WITHOUT ANY WARRANTY; without even the implied warranty of */
/* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU */
/* General Public License for more details */
/* at <http://www.gnu.org/licenses/>. */
/**/
/* @WTL version  1.7.4 */
/* @date - time  08.09.2017 - 19:00 */
/* @copyright    Marc Busse 2012-2020 */
/* @author       Marc Busse <http://www.eutin.dlrg.de> */
/* @license      GPL */
/**/


/* Version 1.6.0 update */
UPDATE_V160;
ALTER TABLE `wtl_lists` ADD `infoMail` mediumtext NOT NULL;
UPDATE_END;

/* Version 1.7.3 update */
UPDATE_V173;
ALTER TABLE `wtl_lists` ADD `infoMail` mediumtext NOT NULL;
ALTER TABLE `wtl_lists` ADD `feedbackMail` mediumtext NOT NULL;
UPDATE_END;

/* Version 1.7.4 update */
UPDATE_V174;
ALTER TABLE `wtl_members` DROP INDEX `person`, ADD UNIQUE `person` (`listId`, `firstname`, `lastname`, `dateOfBirth`, `deleted`, `entryId`) USING BTREE;
UPDATE_END;

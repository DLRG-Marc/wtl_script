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
/* @WTL version  1.6.0 */
/* @date - time  15.03.2015 - 19:00 */
/* @copyright    Marc Busse 2012-2020 */
/* @author       Marc Busse <http://www.eutin.dlrg.de> */
/* @license      GPL */
/**/


/* Version 1.6.0 update */
UPDATE_V160;
ALTER TABLE `wtl_lists` ADD `infoMail` mediumtext NOT NULL;
UPDATE_END;

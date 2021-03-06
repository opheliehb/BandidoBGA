
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Bandido implementation : © Ophélie Haurou-Béjottes <ophelie.hb@gmail.com> & Julien Plantier <julplantier@free.fr>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

CREATE TABLE IF NOT EXISTS `card` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` varchar(16) NOT NULL,
  `card_type_arg` int(11) NOT NULL,
  `card_location` varchar(16) NOT NULL,
  `card_location_arg` int(11) NOT NULL,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `grid` (
  `y` int(8) NOT NULL,
  `x` int(8) NOT NULL,
  `subcard_id` varchar(4),
  `rotation` int(8),
  PRIMARY KEY (`x`,`y`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `playermoves` (
  `player_id` int(8) NOT NULL,
  `card_id` int(8) NOT NULL,
  `rotation` int(8),
  `locations` varchar(500) NOT NULL,
  PRIMARY KEY (`card_id`,`rotation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `exits` (
  `subcard_id` varchar(4),
  `_left` varchar(4) DEFAULT NULL,
  `_right` varchar(4) DEFAULT NULL,
  `_top` varchar(4) DEFAULT NULL,
  `_bottom` varchar(4) DEFAULT NULL,
  PRIMARY KEY (`subcard_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



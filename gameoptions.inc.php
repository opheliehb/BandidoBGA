<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Bandido implementation : © Ophélie Haurou-Béjottes <ophelie.hb@gmail.com> & Julien Plantier <julplantier@free.fr>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gameoptions.inc.php
 *
 * Bandido game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in bandido.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = array(

    // note: game variant ID should start at 100 (ie: 100, 101, 102, ...). The maximum is 199.
    100 => array(
        'name' => totranslate('Game version'),
        'values' => array(
            1 => array('name' => totranslate('Standard')),
            2 => array(
                'name' => totranslate('Covid-19 edition'),
                'description' => totranslate('The Helvetiq team decided to pull one of the strongest cards against Covid-19. That is a print-and-play adaptation of best-selling Bandido.')
            ),
        )
    ),
    101 => array(
        'name' => totranslate('Exits on supercard'),
        'values' => array(
            71 => array(
                'name' => totranslate('5 exits'),
                'tmdisplay' => totranslate('5 exits on the first card')
            ),
            70 => array(
                'name' => totranslate('6 exits'),
                'tmdisplay' => totranslate('6 exits on the first card'),
                'nobeginner' => true
            ),
        ),
        'displaycondition' => array(
            array(
                'type' => 'otheroption',
                'id' => 100,
                'value' => 1
            )
        )
    ),

);

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
 * material.inc.php
 *
 * Bandido game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */

/*** 
 * Split each card in 2 subcards
 * Subcards possible exits are : left, right, top, bottom
 * Each coordinates can be
 * (-) -1 if there is an exit not yet linked to another card;
 * (-) null if there is no exit; or
 * (-) a positive integer i, symbolizing a card id, if the exit is linked to card i.
 */

$this->initial_card_exits = array(
    0 => [[-1, null, null, -1], [null, null, null, null]],
    1 => [[null, null, -1, null], [null, null, -1, -1]],
    2 => [[null, null, -1, null], [null, null, -1, null]],
    3 => [[null, null, null, -1], [null, null, null, -1]],
    4 => [[null, null, null, -1], [null, null, null, null]],
    5 => [[-1, null, -1, null], [null, -1, null, null]],
    6 => [[-1, null, null, null], [null, -1, -1, -1]],
    7 => [[-1, null, null, -1], [null, -1, null, -1]],
    8 => [[-1, null, -1, -1], [null, null, null, null]],
    9 => [[null, null, null, -1], [null, -1, null, -1]],
    10 => [[-1, null, null, -1], [null, -1, -1, null]],
    11 => [[-1, null, -1, null], [null, null, -1, null]],
    12 => [[null, null, -1, -1], [null, null, -1, -1]],
    13 => [[null, null, -1, null], [null, null, null, -1]],
    14 => [[null, null, -1, null], [null, -1, -1, null]],
    15 => [[-1, null, -1, null], [null, -1, null, -1]],
    16 => [[-1, null, null, null], [null, -1, null, null]],
    17 => [[-1, null, -1, null], [null, null, -1, -1]],
    18 => [[null, null, null, -1], [null, null, -1, null]],
    19 => [[null, null, null, null], [null, -1, null, -1]],
    20 => [[-1, null, null, -1], [null, -1, -1, null]],
    21 => [[-1, null, null, null], [null, -1, null, null]],
    22 => [[-1, null, null, -1], [null, null, -1, -1]],
    23 => [[null, null, -1, -1], [null, null, null, null]],
    24 => [[null, null, null, null], [null, -1, -1, -1]],
    25 => [[-1, null, -1, -1], [null, -1, null, null]],
    26 => [[-1, null, null, null], [null, null, null, -1]],
    27 => [[null, null, -1, -1], [null, -1, -1, null]],
    28 => [[-1, null, -1, -1], [null, null, null, -1]],
    29 => [[-1, null, null, null], [null, null, -1, null]],
    30 => [[null, null, -1, null], [null, -1, null, -1]],
    31 => [[-1, null, null, null], [null, null, -1, -1]],
    32 => [[null, null, -1, -1], [null, -1, null, -1]],
    33 => [[-1, null, -1, -1], [null, null, -1, null]],
    34 => [[-1, null, null, null], [null, null, null, -1]],
    35 => [[null, null, null, -1], [null, -1, -1, null]],
    36 => [[-1, null, null, null], [null, null, -1, null]],
    37 => [[null, null, -1, -1], [null, null, -1, -1]],
    38 => [[null, null, -1, null], [null, null, null, -1]],
    39 => [[null, null, null, -1], [null, -1, -1, null]],
    40 => [[-1, null, -1, null], [null, -1, null, -1]],
    41 => [[-1, null, null, -1], [null, -1, null, -1]],
    42 => [[-1, null, -1, -1], [null, null, -1, null]],
    43 => [[null, null, null, -1], [null, null, -1, null]],
    44 => [[null, null, -1, null], [null, null, null, null]],
    45 => [[null, null, null, null], [null, null, -1, null]],
    46 => [[null, null, -1, null], [null, -1, -1, null]],
    47 => [[-1, null, -1, -1], [null, null, null, -1]],
    48 => [[null, null, -1, -1], [null, null, null, null]],
    49 => [[-1, null, null, null], [null, null, null, null]],
    50 => [[null, null, null, null], [null, -1, null, -1]],
    51 => [[-1, null, null, -1], [null, null, null, null]],
    52 => [[null, null, -1, -1], [null, null, -1, null]],
    53 => [[null, null, -1, -1], [null, null, null, null]],
    54 => [[null, null, null, null], [null, null, null, -1]],
    55 => [[null, null, null, null], [null, -1, -1, -1]],
    56 => [[-1, null, -1, null], [null, null, null, -1]],
    57 => [[null, null, -1, -1], [null, null, null, -1]],
    58 => [[null, null, -1, null], [null, null, null, null]],
    59 => [[null, null, null, null], [null, -1, -1, null]],
    60 => [[-1, null, null, null], [null, -1, -1, null]],
    61 => [[-1, null, null, null], [null, null, -1, -1]],
    62 => [[null, null, -1, -1], [null, null, -1, null]],
    63 => [[null, null, null, -1], [null, null, null, null]],
    64 => [[-1, null, null, null], [null, null, null, null]],
    65 => [[null, null, null, null], [null, -1, null, null]],
    66 => [[-1, null, null, null], [null, -1, -1, null]],
    67 => [[-1, null, -1, null], [null, -1, null, null]],
    68 => [[-1, null, -1, null], [null, null, null, null]],
    69 => [[null, null, null, null], [null, null, null, null]],
    70 => [[-1, null, -1, -1], [null, -1, -1, -1]],
    71 => [[null, null, -1, -1], [null, -1, -1, -1]],
);

$this->covid_initial_card_exits = array(
    0 => [[null, null, null, null], [null, -1, null, null]],
    1 => [[null, null, null, null], [null, -1, null, null]],
    2 => [[null, null, -1, null], [null, null, null, null]],
    3 => [[null, null, null, null], [null, null, -1, null]],
    4 => [[null, null, null, null], [null, -1, -1, -1]],
    5 => [[null, null, null, null], [null, -1, -1, -1]],
    6 => [[null, null, null, null], [null, -1, -1, null]],
    7 => [[-1, null, -1, null], [null, null, null, null]],
    8 => [[null, null, null, null], [null, -1, null, -1]],
    9 => [[-1, null, null, -1], [null, null, null, null]],
    10 => [[-1, null, null, -1], [null, -1, -1, null]],
    11 => [[null, null, null, -1], [null, -1, null, -1]],
    12 => [[-1, null, null, -1], [null, null, null, -1]],
    13 => [[null, null, -1, -1], [null, -1, -1, null]],
    14 => [[-1, null, -1, null], [null, null, -1, -1]],
    15 => [[null, null, -1, null], [null, -1, null, null]],
    16 => [[-1, null, null, null], [null, null, -1, null]],
    17 => [[-1, null, null, null], [null, -1, null, -1]],
    18 => [[-1, null, null, -1], [null, -1, null, null]],
    19 => [[null, null, null, -1], [null, -1, -1, null]],
    20 => [[-1, null, -1, null], [null, null, null, -1]],
    21 => [[-1, null, -1, null], [null, -1, null, -1]],
    22 => [[null, null, -1, null], [null, null, null, -1]],
    23 => [[null, null, null, -1], [null, null, -1, null]],
    24 => [[-1, null, null, -1], [null, -1, null, -1]],
    25 => [[-1, null, null, null], [null, -1, null, null]],
    26 => [[null, null, -1, -1], [null, null, -1, null]],
    27 => [[null, null, null, -1], [null, null, null, -1]],
    28 => [[null, null, -1, -1], [null, -1, null, null]],
    29 => [[-1, null, -1, -1], [null, null, -1, null]],
    30 => [[-1, null, -1, -1], [null, null, null, -1]],
    31 => [[null, null, -1, -1], [null, null, null, -1]],
    32 => [[-1, null, -1, -1], [null, -1, -1, -1]]
);
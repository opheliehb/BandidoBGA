<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Bandido implementation : © <Your name here> <Your email address here>
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
 * Left subcard possible exits are : left, top, bottom
 * Right subcard possible exits are : top, right, bottom
 * Each coordinates can be
 * (-) -1 if there is an exit not yet linked to another card;
 * (-) null if there is no exit; or
 * (-) a positive integer i, symbolizing a card id, if the exit is linked to card i.
 */
$this->cards_to_subcards = array(
    0 => [[-1, null, -1],[null, null, null]],
    1 => [[null, -1, null],[-1, null, -1]],
    2 => [[null, -1, null],[-1, null, null]],
    3 => [[null, null, -1],[null, null, -1]],
    4 => [[null, null, -1],[null, null, null]],
    5 => [[-1, -1, null],[null, -1, null]],
    6 => [[-1, null, null],[-1, -1, -1]],
    7 => [[-1, null, -1],[null, -1, -1]],
    8 => [[-1, -1, -1],[null, null, null]],
    9 => [[null, null, -1],[null, -1, -1]],
    10 => [[-1, null, -1],[-1, -1, null]],
    11 => [[-1, -1, null],[-1, null, null]],
    12 => [[null, -1, -1],[-1, null, -1]],
    13 => [[null, -1, null],[null, null, -1]],
    14 => [[null, -1, null],[-1, -1, null]],
    15 => [[-1, -1, null],[null, -1, -1]],
    16 => [[-1, null, null],[null, -1, null]],
    17 => [[-1, -1, null],[-1, null, -1]],
    18 => [[null, null, -1],[-1, null, null]],
    19 => [[null, null, null],[null, -1, -1]],
    20 => [[-1, null, -1],[-1, -1, null]],
    21 => [[-1, null, null],[null, -1, null]],
    22 => [[-1, null, -1],[-1, null, -1]],
    23 => [[null, -1, -1],[null, null, null]],
    24 => [[null, null, null],[-1, -1, -1]],
    25 => [[-1, -1, -1],[null, -1, null]],
    26 => [[-1, null, null],[null, null, -1]],
    27 => [[null, -1, -1],[-1, -1, null]],
    28 => [[-1, -1, -1],[null, null, -1]],
    29 => [[-1, null, null],[-1, null, null]],
    30 => [[null, -1, null],[null, -1, -1]],
    31 => [[-1, null, null],[-1, null, -1]],
    32 => [[null, -1, -1],[null, -1, -1]],
    33 => [[-1, -1, -1],[-1, null, null]],
    34 => [[-1, null, null],[null, null, -1]],
    35 => [[null, null, -1],[-1, -1, null]],
    36 => [[-1, null, null],[-1, null, null]],
    37 => [[null, -1, -1],[-1, null, -1]],
    38 => [[null, -1, null],[null, null, -1]],
    39 => [[null, null, -1],[-1, -1, null]],
    40 => [[-1, -1, null],[null, -1, -1]],
    41 => [[-1, null, -1],[null, -1, -1]],
    42 => [[-1, -1, -1],[-1, null, null]],
    43 => [[null, null, -1],[-1, null, null]],
    44 => [[null, -1, null],[null, null, null]],
    45 => [[null, null, null],[-1, null, null]],
    46 => [[null, -1, null],[-1, -1, null]],
    47 => [[-1, -1, -1],[null, null, -1]],
    48 => [[null, -1, -1],[null, null, null]],
    49 => [[-1, null, null],[null, null, null]],
    50 => [[null, null, null],[null, -1, -1]],
    51 => [[-1, null, -1],[null, null, null]],
    52 => [[null, -1, -1],[-1, null, null]],
    53 => [[null, -1, -1],[null, null, null]],
    54 => [[null, null, null],[null, null, -1]],
    55 => [[null, null, null],[-1, -1, -1]],
    56 => [[-1, -1, null],[null, null, -1]],
    57 => [[null,-1, -1],[null, null, -1]],
    58 => [[null, -1, null],[null, null, null]],
    59 => [[null, null, null],[-1, -1, null]],
    60 => [[-1, null, null],[-1, -1, null]],
    61 => [[-1, null, null],[-1, null, -1]],
    62 => [[null, -1, -1],[-1, null, null]],
    63 => [[null, null, -1],[null, null, null]],
    64 => [[-1, null, null],[null, null, null]],
    65 => [[null, null, null],[null, -1, null]],
    66 => [[-1, null, null],[-1, -1, null]],
    67 => [[-1, -1, -1],[null, null, null]],
    68 => [[-1, -1, null],[null, null, null]],
    69 => [[null, null, null],[null, null, null]],
    70 => [[-1, -1, -1],[-1, -1, -1]],
    71 => [[null, -1, -1],[-1, -1, -1]],
);
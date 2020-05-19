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
 * Each card has 6 possible exits left, top left, top right, right ,bottom right, bottom left.
 * Each coordinates can be
 * (-) -1 if there is an exit not yet linked to another card;
 * (-) null if there is no exit; or
 * (-) a positive integer i, symbolizing a card id, if the exit is linked to card i.
 * By convention, the left subcard right exit is linked to their left subcard right exit.
 */

$this->cards_to_subcards = array(
    0 => [-1, null, null, null, null, -1],
    1 => [null, -1, -1, null, -1, null],
    2 => [null, -1, -1, null, null, null],
    3 => [null, null, null, null, -1, -1],
    4 => [null, null, null, null, null, -1],
    5 => [-1, -1, null, -1, null, null],
    6 => [-1, null, -1, -1, -1, null],
    7 => [-1, null, null, -1, -1, -1],
    8 => [-1, -1, null, null, null, -1],
    9 => [null, null, null, -1, -1, -1],
    10 => [-1, null, -1, -1, null, -1],
    11 => [-1, -1, -1, null, null, null],
    12 => [null, -1, -1, null, -1, -1],
    13 => [null, -1, null, null, -1, null],
    14 => [null, -1, -1, -1, null, null],
    15 => [-1, -1, null, -1, -1, null],
    16 => [-1, null, null, -1, null, null],
    17 => [-1, -1, -1, null, -1, null],
    18 => [null, null, -1, null, null, -1],
    19 => [null, null, null, -1, -1, null],
    20 => [-1, null, -1, -1, null, -1],
    21 => [-1, null, null, -1, null, null],
    22 => [-1, null, -1, null, -1, -1],
    23 => [null, -1, null, null, null, -1],
    24 => [null, null, -1, -1, -1, null],
    25 => [-1, -1, null, -1, null, -1],
    26 => [-1, null, null, null, -1, null],
    27 => [null, -1, -1, -1, null, -1],
    28 => [-1, -1, null, null, -1, -1],
    29 => [-1, null, -1, null, null, null],
    30 => [null, -1, null, -1, -1, null],
    31 => [-1, null, -1, null, -1, null],
    32 => [null, -1, null, -1, -1, -1],
    33 => [-1, -1, -1, null, null, -1],
    34 => [-1, null, null, null, -1, null],
    35 => [null, null, -1, -1, null, -1],
    36 => [-1, null, -1, null, null, null],
    37 => [null, -1, -1, null, -1, -1],
    38 => [null, -1, null, null, -1, null],
    39 => [null, null, -1, -1, null, -1],
    40 => [-1, -1, null, -1, -1, null],
    41 => [-1, null, null, -1, -1, -1],
    42 => [-1, -1, -1, null, null, -1],
    43 => [null, null, -1, null, null, -1],
    44 => [null, -1, null, null, null, null],
    45 => [null, null, -1, null, null, null],
    46 => [null, -1, -1, -1, null, null],
    47 => [-1, -1, null, null, -1, -1],
    48 => [null, -1, null, null, null, -1],
    49 => [-1, null, null, null, null, null],
    50 => [null, null, null, -1, -1, null],
    51 => [-1, null, null, null, null, -1],
    52 => [null, -1, -1, null, null, -1],
    53 => [null, -1, null, null, null, -1],
    54 => [null, null, null, null, -1, null],
    55 => [null, null, -1, -1, -1, null],
    56 => [-1, -1, null, null, -1, null],
    57 => [null,-1, null, null, -1, -1],
    58 => [null, -1, null, null, null, null],
    59 => [null, null, -1, -1, null, null],
    60 => [-1, null, -1, -1, null, null],
    61 => [-1, null, -1, null, -1, null],
    62 => [null, -1, -1, null, null, -1],
    63 => [null, null, null, null, null, -1],
    64 => [-1, null, null, null, null, null],
    65 => [null, null, null, -1, null, null],
    66 => [-1, null, -1, -1, null, null],
    67 => [-1, -1, null, null, null, -1],
    68 => [-1, -1, null, null, null, null],
    69 => [null, null, null, null, null, null],
    70 => [-1, -1, -1, -1, -1, -1],
    71 => [null, -1, -1, -1, -1, -1],
);

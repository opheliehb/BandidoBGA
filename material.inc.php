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
 * Each card is represented by 2 squares (or subcards).
 * Each subcards has 4 coordinates, top, bottom, left, and right.
 * Each coordinates can be
 * (-) -1 if there is an exit not yet linked to another card;
 * (-) null if there is no exit; or
 * (-) a positive integer i, symbolizing a card id, if the exit is linked to card i.
 * By convention, the left subcard right exit is linked to their left subcard right exit.
 */

$this->cards_to_subcards = array(
    70 => [[-1, -1, -1, 70],[-1, -1, 70, -1]]
);

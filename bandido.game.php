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
 * bandido.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */


require_once(APP_GAMEMODULE_PATH . 'module/table/table.game.php');
require_once('modules/BNDGrid.php');
require_once('modules/BNDCard.php');

class Bandido extends Table
{
    function __construct()
    {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        self::initGameStateLabels(array(
            "game_unwinnable" => 10,
            "covid_variant" => 100,
            "supercard_id" => 101
        ));

        $this->cards = self::getNew("module.common.deck");
        $this->cards->init("card");

        $this->game_wins = false;
        $this->players_win = false;
        $this->deck_size = 0;
        $this->supercard_id = 0;
    }

    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "bandido";
    }

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players, $options = array())
    {
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach ($players as $player_id => $player) {
            $color = array_shift($default_colors);
            $values[] = "('" . $player_id . "','$color','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "')";
        }
        $sql .= implode($values, ',');
        self::DbQuery($sql);
        self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
        self::reloadPlayersBasicInfos();

        $this->initGameStatistics();

        if (self::getGameStateValue('covid_variant') == 1) {
            $this->deck_size = 69;
            $this->supercard_id = self::getGameStateValue('supercard_id');
            BNDExitMap::initialize($this->initial_card_exits);
        } else {
            $this->deck_size = 32;
            $this->supercard_id = 32;
            BNDExitMap::initialize($this->covid_initial_card_exits);
        }

        for ($value = 0; $value < $this->deck_size; $value++) {
            $cards[] = array('type' => 'card', 'type_arg' => $value, 'nbr' => 1);
        }

        $this->cards->createCards($cards, 'deck');
        $this->dealStartingCards();

        BNDGrid::initializeGrid($this->supercard_id);
        self::setGameStateInitialValue("game_unwinnable", 0);
        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        $this->computePossibleMoves($this->getActivePlayerId());

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
        $result['covid'] = (self::getGameStateValue('covid_variant') == 2);

        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb($sql);

        // Cards in player hand      
        $result['hand'] = $this->cards->getCardsInLocation('hand', $current_player_id);

        $result['supercard_id'] = $this->supercard_id;

        $result['grid'] = BNDGrid::getGrid();
        $result['gameUnwinnable'] = self::getGameStateValue('game_unwinnable');
        $result['deckCount'] = $this->cards->countCardInLocation("deck");

        $active_player_id = $this->getActivePlayerId();
        if ($current_player_id == $active_player_id) {
            $result['possibleMoves'] = $this->getPossibleMoves($current_player_id);
        }

        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        $cards_in_locations = $this->cards->countCardsInLocations();
        $card_count = self::getUniqueValueFromDB("SELECT count(*) FROM card");
        $cards_to_place = 0;
        if (array_key_exists('hand', $cards_in_locations)) {
            $cards_to_place += $cards_in_locations['hand'];
        }
        if (array_key_exists('deck', $cards_in_locations)) {
            $cards_to_place += $cards_in_locations['deck'];
        }
        return 100 * ($card_count - $cards_to_place) / $card_count;
    }


    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    ////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    function getPlayableLocationForOtherSubcard($x, $y, $rotation)
    {
        switch ($rotation) {
            case 0:
                return array($x - 1, $y);
                break;
            case 90:
                return array($x, $y - 1);
                break;
            case 180:
                return array($x + 1, $y);
                break;
            case 270:
                return array($x, $y + 1);
                break;
        }
    }

    function computeWinner()
    {
        if (count(BNDGrid::getPlayableLocations()) == 0) {
            $this->players_win = true;
            return true;
        }

        /** Check if the active player can play */
        $active_player_id = $this->getActivePlayerId();
        if ($this->computePossibleMoves($active_player_id)) {
            return false;
        }

        /** If the active player can't play, check if any other play can play */
        $other_players = self::loadPlayersBasicInfos();
        unset($other_players[$active_player_id]);

        foreach ($other_players as $player) {
            $cards = $this->cards->getCardsInLocation('hand', $player['player_id']);

            if ($this->computePossibleMoves(null, $cards)) {
                return false;
            }
        }

        /** If no one can play, check if there is at least one card left in the deck that can be played */
        $deck_cards = $this->cards->getCardsInLocation('deck');
        if ($this->computePossibleMoves(null, $deck_cards)) {
            return false;
        }

        $this->game_wins = true;
        return true;
    }

    function computeScore()
    {
        if ($this->players_win) {
            self::DbQuery("UPDATE player SET player_score=1");
        } else if ($this->game_wins) {
            if (count(self::loadPlayersBasicInfos()) == 1) {
                /** to lose a solo game, your score must be negative or else it logs a victory */
                self::DbQuery("UPDATE player SET player_score=-1");
            }
        }
    }

    function gameHasEnded()
    {
        if (!$this->computeWinner()) {
            return false;
        }

        $this->computeScore();

        return true;
    }

    function dealStartingCards()
    {
        // Take back all cards (from any location => null) to deck and reshuffle
        $this->cards->moveAllCardsInLocation(null, "deck");
        $this->cards->shuffle('deck');

        // Deal 7 cards to each player
        $players = self::loadPlayersBasicInfos();
        foreach ($players as $player_id => $player) {
            // Put 7 cards in each player hand
            $this->cards->pickCards(3, 'deck', $player_id);
        }
    }

    function initGameStatistics()
    {
        $this->initStat("table", "number_of_turns", 0);
        $this->initStat("table", "cards_in_deck", 0);
        $this->initStat("table", "escape_count", -1);
        $this->initStat("table", "exits_opened", 0);
        $this->initStat("table", "exits_closed", 0);
        $this->initStat("table", "open_close_ratio", 0);

        $this->initStat("player", "cards_played", 0);
        $this->initStat("player", "exits_opened", 0);
        $this->initStat("player", "exits_closed", 0);
        $this->initStat("player", "exits_maintained", 0);
        $this->initStat("player", "open_close_ratio", 0);
    }

    function computeFinalStatistics()
    {
        /** Compute opened and closed exits */
        $player_list = self::loadPlayersBasicInfos();

        $exits_opened = 0;
        $exits_closed = 0;

        foreach ($player_list as $player) {
            $player_id = $player['player_id'];
            $exits_opened_player = $this->getStat("exits_opened", $player_id);
            $exits_closed_player = $this->getStat("exits_closed", $player_id);
            if (($exits_opened_player + $exits_closed_player) != 0) {
                $open_close_ratio = ((float) $exits_closed_player / ($exits_opened_player + $exits_closed_player)) * 100;
            } else {
                $open_close_ratio = 50;
            }
            $this->setStat($open_close_ratio, "open_close_ratio", $player_id);

            $exits_opened += $exits_opened_player;
            $exits_closed += $exits_closed_player;
        }

        $this->setStat($exits_opened, "exits_opened");
        $this->setStat($exits_closed, "exits_closed");
        if (($exits_opened + $exits_closed) != 0) {
            $open_close_ratio = ((float) $exits_closed / ($exits_opened + $exits_closed)) * 100;
        } else {
            $open_close_ratio = 50;
        }
        $this->setStat($open_close_ratio, "open_close_ratio");

        /** Get cards left in deck */
        $cards_in_deck = $this->cards->countCardInLocation('deck');
        $this->setStat($cards_in_deck, "cards_in_deck");

        $this->setStat(BNDGrid::getEscapeCount(), "escape_count");
    }

    function getPossibleMoves($player_id)
    {
        $sql_get_possible_moves = sprintf(
            "SELECT card_id, rotation, locations FROM playermoves WHERE player_id=%d",
            $player_id
        );

        $serialized_locations = self::getDoubleKeyCollectionFromDB($sql_get_possible_moves);
        $locations = array();
        foreach ($serialized_locations as $card_id => $card_rotations) {
            foreach ($card_rotations as $rotation => $location) {
                $locations[$card_id][$rotation] = unserialize($location["locations"]);
            }
        }

        return $locations;
    }

    function computePossibleMoves($player_id, $cards = null)
    {
        $found_possible_move = false;
        if ($cards == null && $player_id != null) {
            $cards = $this->cards->getCardsInLocation('hand', $player_id);
        }
        if (count($cards) == 0) {
            return false;
        }
        $playable_locations = BNDGrid::getPlayableLocations();

       BNDGrid::getGrid();
        foreach ($cards as $card) {
            foreach (array(0, 90, 180, 270) as $rotation) {
                $temp_possible_moves = array();
                foreach ($playable_locations as $location) {
                    if (BNDGrid::cardCanBePlaced($card['type_arg'], $location[0], $location[1], $rotation)) {
                        array_push($temp_possible_moves, $location);
                    }

                    $other_location = $this->getPlayableLocationForOtherSubcard($location[0], $location[1], $rotation);
                    if (BNDGrid::cardCanBePlaced(
                        $card['type_arg'],
                        $other_location[0],
                        $other_location[1],
                        $rotation
                    )) {
                        array_push($temp_possible_moves, $other_location);
                    }
                }

                if (count($temp_possible_moves)) {
                    if ($player_id == null) {
                        return true;
                    }
                    $found_possible_move = true;

                    /** array_values(array_unique()) used to have unique values
                     * but not preserve keys so it's undesrtood as an array, not a dictionary,
                     * by the client
                     */
                    $locations = serialize(array_values(array_unique($temp_possible_moves, 0)));
                    $sqlInsert = sprintf(
                        "INSERT INTO playermoves VALUES ( '%d', '%d', '%d', '%s' )
                        ON DUPLICATE KEY UPDATE locations='%s'",
                        $player_id,
                        $card['id'],
                        $rotation,
                        $locations,
                        $locations
                    );

                    self::DbQuery($sqlInsert);
                }
            }
        }
        return $found_possible_move;
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Player actions
    //////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in bandido.action.php)
    */

    function playCard($card_id, $x, $y, $rotation)
    {
        // Check that action is possible for player
        self::checkAction('playCard');

        $player_id = $this->getActivePlayerId();

        BNDGrid::getGrid();
        $card = $this->cards->getCard($card_id);

        if (!BNDGrid::cardCanBePlaced($card['type_arg'], $x, $y, $rotation)) {
            throw new feException("Invalid card placement!");
        }

        list($exits_opened, $exits_closed, $created_isolated_square) = BNDGrid::placeCard($card['type_arg'], $x, $y, $rotation);
        if ($created_isolated_square) {
            self::setGameStateValue("game_unwinnable", 1);
        }

        /** Handle open/close exits stats
         * exits opened = number of exits added by the player
         * exits closed = number of exits connected to the card the player just played
         * the diff between the 2 is the number of exits that have been added/removed from the game
         */
        if ($exits_opened > $exits_closed) {
            $this->incStat($exits_opened - $exits_closed, "exits_opened", $player_id);
        } else if ($exits_opened < $exits_closed) {
            $this->incStat($exits_closed - $exits_opened, "exits_closed", $player_id);
        } else {
            $this->incStat(1, "exits_maintained", $player_id);
        }
        $this->incStat(1, "cards_played", $player_id);

        // Location grid is not used to build the actual grid,
        // it's just to remove the card from the player's hand
        $this->cards->moveCard($card_id, 'grid');

        // New card as been placed, delete all previous player move as they can change
        self::DbQuery("DELETE FROM playermoves");

        // Pick a new card for the player
        $card_drawn = $this->cards->pickCard('deck', $player_id);

        $deckCount = $this->cards->countCardInLocation("deck");

        // Notify all players about the card played
        self::notifyAllPlayers("cardPlayed", clienttranslate('${player_name} plays a card'), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_type' => $card['type_arg'],
            'x' => $x,
            'y' => $y,
            'rotation' => $rotation,
            'deckCount' => $deckCount,
        ));
        // Notify active player about the card he's redrawn
        self::notifyPlayer($player_id, "cardDrawn", "", array('cardDrawn' => $card_drawn));

        $this->gamestate->nextState("nextPlayer");
    }

    function changeHand()
    {
        // Check that action is possible for player
        self::checkAction('changeHand');

        $player_id = $this->getActivePlayerId();

        $possible_moves = $this->getPossibleMoves($player_id);
        if (!empty($possible_moves)) {
            // Can't change hand if there is a possible move !
            return;
        }

        // Move all cards from the player's hand to the bottom of the deck
        $player_hand = $this->cards->getPlayerHand($player_id);
        foreach ($player_hand as $card) {
            $this->cards->insertCardOnExtremePosition($card["id"], 'deck', false);
        }

        // Notify active player about their new cards
        $new_player_hand = $this->cards->pickCards(3, 'deck', $player_id);
        $this->notifyPlayer($player_id, "changeHand", "", array('newHand' => $new_player_hand));
        $this->notifyAllPlayers(
            "playerChangedHand",
            clienttranslate('${player_name} could not play and changed their hand'),
            array('player_name' => $this->getActivePlayerName())
        );

        $this->gamestate->nextState("nextPlayer");
    }

    function stopGame()
    {
        // Check that action is possible for player
        self::checkAction('stopGame');

        if (self::getGameStateValue("game_unwinnable") == 0) {
            // Can't abandon if game is still winnable !
            return;
        }

        $this->game_wins = true;
        $this->computeScore();
        $this->computeFinalStatistics();
        $this->gamestate->nextState("stopGame");
    }


    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state arguments
    ////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    function argPossibleMoves()
    {
        $possible_moves = $this->getPossibleMoves($this->getActivePlayerId());
        $game_unwinnable = self::getGameStateValue('game_unwinnable');

        return array(
            'possibleMoves' => $possible_moves,
            'gameUnwinnable' => $game_unwinnable
        );
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state actions
    ////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

    function stNextPlayer()
    {
        // Active next player
        $player_id = $this->activeNextPlayer();

        if ($this->cards->countCardInLocation("hand", $player_id) == 0) {
            /** If this player left the game and then clicked on the "come back"
             * button, we need to deal them some cards back
             */
            $new_player_hand = $this->cards->pickCards(3, 'deck', $player_id);
            self::notifyPlayer($player_id, "changeHand", "", array('newHand' => $new_player_hand));
        }

        // Increment the number of turns statistic
        $this->incStat(1, "number_of_turns");

        if ($this->gameHasEnded()) {
            $this->computeFinalStatistics();
            $this->gamestate->nextState("endGame");
        } else {
            // This player can play. Give him some extra time
            $this->giveExtraTime($player_id);
            $this->gamestate->nextState('nextTurn');
        }
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Zombie
    ////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn($state, $active_player)
    {
        /** If the player just left, we put back their cards in the deck. Else we do nothing more. */
        if ($this->cards->countCardInLocation("hand", $active_player) != 0) {
            $this->cards->moveAllCardsInLocation("hand", "deck", $active_player);
            $this->notifyAllPlayers(
                "playerLeft",
                clienttranslate('A player left. Their hand has been sent back to the deck.'),
                array()
            );

            self::notifyPlayer($active_player, "changeHand", "", array('newHand' => array()));
        }
        $this->gamestate->nextState("zombiePass");
    }

    ///////////////////////////////////////////////////////////////////////////////////:
    ////////// DB upgrade
    //////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
        
        */

    function upgradeTableDb($from_version)
    {
    }
}

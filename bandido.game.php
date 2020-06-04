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
            "supercardId" => 100
        ));

        $this->cards = self::getNew("module.common.deck");
        $this->cards->init("card");

        $this->gameWins = false;
        $this->playersWin = false;
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

        BNDExitMap::Initialize();

        for ($value = 0; $value < 69; $value++) {
            $cards[] = array('type' => 'card', 'type_arg' => $value, 'nbr' => 1);
        }

        $this->cards->createCards($cards, 'deck');
        $this->dealStartingCards();

        BNDGrid::InitializeGrid(self::getGameStateValue('supercardId'));

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

        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb($sql);

        // Cards in player hand      
        $result['hand'] = $this->cards->getCardsInLocation('hand', $current_player_id);

        // supercard id
        $result['supercard_id'] = self::getGameStateValue('supercardId');

        $result['grid'] = BNDGrid::GetGrid();


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
        $cardsInLocations = $this->cards->countCardsInLocations();
        $cardCount = self::getUniqueValueFromDB("SELECT count(*) FROM card");
        $cardsToPlace = 0;
        if (array_key_exists('hand', $cardsInLocations)) {
            $cardsToPlace += $cardsInLocations['hand'];
        }
        if (array_key_exists('deck', $cardsInLocations)) {
            $cardsToPlace += $cardsInLocations['deck'];
        }
        return 100 * ($cardCount - $cardsToPlace) / $cardCount;
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
            $this->playersWin = true;
            return true;
        }

        /** Check if the active player can play */
        $active_player_id = $this->getActivePlayerId();
        if ($this->computePossibleMoves($active_player_id)) {
            return false;
        }

        /** If the active player can't play, check if any other play can play */
        $otherplayers = self::loadPlayersBasicInfos();
        unset($otherplayers[$active_player_id]);

        foreach ($otherplayers as $player) {
            $cards = $this->cards->getCardsInLocation('hand', $player['player_id']);

            if ($this->computePossibleMoves(null, $cards)) {
                return false;
            }
        }

        /** If no one can play, check if there is at least one card left in the deck that can be played */
        $deckCards = $this->cards->getCardsInLocation('deck');
        if ($this->computePossibleMoves(null, $deckCards)) {
            return false;
        }

        $this->gameWins = true;
        return true;
    }

    function gameHasEnded()
    {
        if (!$this->computeWinner()) {
            return false;
        }

        if ($this->playersWin) {
            self::DbQuery("UPDATE player SET player_score=1");
        }
        else if ($this->gameWins) {
            if (count(self::loadPlayersBasicInfos()) == 1) {
                /** to lose a solo game, your score must be negative or else it logs a victory */
                self::DbQuery("UPDATE player SET player_score=-1");
            }
        }
        
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
        $this->initStat("table", "longest_path", 0);
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
            if ($exits_opened_player != 0) {
                $open_close_ratio = ((float) $exits_closed_player / $exits_opened_player) * 100;
            } else {
                $open_close_ratio = 100;
            }
            $this->setStat($open_close_ratio, "open_close_ratio", $player_id);

            $exits_opened += $exits_opened_player;
            $exits_closed += $exits_closed_player;
        }

        $this->setStat($exits_opened, "exits_opened");
        $this->setStat($exits_closed, "exits_closed");
        if ($exits_opened != 0) {
            $open_close_ratio = ((float) $exits_closed / $exits_opened) * 100;
        } else {
            $open_close_ratio = 100;
        }
        $this->setStat($open_close_ratio, "open_close_ratio");

        /** Get cards left in deck */
        $cards_in_deck = $this->cards->countCardInLocation('deck');
        $this->setStat($cards_in_deck, "cards_in_deck");
    }

    function getPossibleMoves($player_id)
    {
        $sqlGetPossibleMoves = sprintf(
            "SELECT card_id, rotation, locations FROM playermoves WHERE player_id=%d",
            $player_id
        );

        $serializedLocations = self::getDoubleKeyCollectionFromDB($sqlGetPossibleMoves);
        $locations = array();
        foreach ($serializedLocations as $card_id => $card_rotations) {
            foreach ($card_rotations as $rotation => $location) {
                $locations[$card_id][$rotation] = unserialize($location["locations"]);
            }
        }

        return $locations;
    }

    function debugGetPossibleMoves($card_id, $rotation)
    {
        $cards = $this->cards->getCards(array($card_id));
        $this->computePossibleMoves($this->getActivePlayerId(), $cards);
    }

    function computePossibleMoves($player_id, $cards = null)
    {
        $foundPossibleMove = false;
        if ($cards == null && $player_id != null) {
            $cards = $this->cards->getCardsInLocation('hand', $player_id);
        }
        if (count($cards) == 0) {
            return false;
        }
        $playableLocations = BNDGrid::getPlayableLocations();

        $grid = BNDGrid::GetFullGrid();
        // var_dump("grid[-1][1]");
        // var_dump($grid[-1][1]);
        foreach ($cards as $card) {
            foreach (array(0, 90, 180, 270) as $rotation) {
                $tempPossibleMoves = array();
                foreach ($playableLocations as $location) {
                    // var_dump("Testing location :");
                    // var_dump($location);
                    // var_dump("rotation");
                    // var_dump($rotation);
                    if (BNDGrid::cardCanBePlaced($card['type_arg'], $location[0], $location[1], $rotation, $grid)) {
                        // var_dump("Card can be placed");
                        array_push($tempPossibleMoves, $location);
                    }

                    $other_location = $this->getPlayableLocationForOtherSubcard($location[0], $location[1], $rotation);
                    // var_dump("Testing other location :");
                    // var_dump($other_location);
                    if (BNDGrid::cardCanBePlaced(
                        $card['type_arg'],
                        $other_location[0],
                        $other_location[1],
                        $rotation,
                        $grid
                    )) {
                        array_push($tempPossibleMoves, $other_location);
                    }
                }

                if (count($tempPossibleMoves)) {
                    if ($player_id == null) {
                        return true;
                    }
                    $foundPossibleMove = true;

                    /** array_values(array_unique()) used to have unique values
                     * but not preserve keys so it's undesrtood as an array, not a dictionary,
                     * by the client
                     */
                    $locations = serialize(array_values(array_unique($tempPossibleMoves, 0)));
                    $sqlInsert = sprintf(
                        "INSERT INTO playermoves VALUES ( '%d', '%d', '%d', '%s' )
                        ON DUPLICATE KEY UPDATE locations='%s'",
                        $player_id,
                        $card['id'],
                        $rotation,
                        $locations,
                        $locations
                    );
                    // var_dump("Card can be placed");
                    // var_dump($sqlInsert);
                    self::DbQuery($sqlInsert);
                }
            }
        }
        return $foundPossibleMove;
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

        $grid = BNDGrid::GetFullGrid();
        $card = $this->cards->getCard($card_id);

        if (!BNDGrid::cardCanBePlaced($card['type_arg'], $x, $y, $rotation, $grid)) {
            throw new feException("Invalid card placement!");
        }

        list($exits_opened, $exits_closed) = BNDGrid::placeCard($card['type_arg'], $x, $y, $rotation, $grid);

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

        // Notify all players about the card played
        self::notifyAllPlayers("cardPlayed", clienttranslate('${player_name} plays a card'), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_type' => $card['type_arg'],
            'x' => $x,
            'y' => $y,
            'rotation' => $rotation
        ));

        // Pick a new card for the player
        $cardDrawn = $this->cards->pickCard('deck', $player_id);
        // Notify active player about the card he's redrawn
        self::notifyPlayer($player_id, "cardDrawn", "", array('cardDrawn' => $cardDrawn));

        $this->gamestate->nextState("nextPlayer");
    }

    function changeHand()
    {
        // Check that action is possible for player
        self::checkAction('changeHand');

        $player_id = $this->getActivePlayerId();

        // Move all cards from the player's hand to the bottom of the deck
        $playerHand = $this->cards->getPlayerHand($player_id);
        foreach ($playerHand as $card) {
            $this->cards->insertCardOnExtremePosition($card["id"], 'deck', false);
        }

        // Notify active player about their new cards
        $newPlayerHand = $this->cards->pickCards(3, 'deck', $player_id);
        $this->notifyPlayer($player_id, "changeHand", "", array('newHand' => $newPlayerHand));
        $this->notifyAllPlayers(
            "playerChangedHand",
            clienttranslate('${player_name} could not play and changer their hand'),
            array('player_name' => $this->getActivePlayerName())
        );

        $this->gamestate->nextState("nextPlayer");
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
        $possibleMoves = $this->getPossibleMoves($this->getActivePlayerId());
        $action = "play a card";
        if (empty($possibleMoves)) {
            $action = "change your hand";
        }
        return array(
            'possibleMoves' => $this->getPossibleMoves($this->getActivePlayerId()),
            'action' => $action
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
            $newPlayerHand = $this->cards->pickCards(3, 'deck', $player_id);
            self::notifyPlayer($player_id, "changeHand", "", array('newHand' => $newPlayerHand));
        }

        // Increment the number of turns statistic
        $this->incStat(1, "number_of_turns");

        // var_dump("call gameHasEnded");
        if ($this->gameHasEnded()) {
            // var_dump("game is finished");
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

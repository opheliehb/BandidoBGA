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


        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        for ($value = 0; $value < 69; $value++) {
            $cards[] = array('type' => 'card', 'type_arg' => $value, 'nbr' => 1);
        }

        $this->cards->createCards($cards, 'deck');
        self::dealStartingCards();

        BNDGrid::InitializeGrid(self::getGameStateValue('supercardId'));

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

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
        // TODO: compute and return the game progression

        return 0;
    }


    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    ////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    function testExitMatchesNeighbors($currentCardExit, $neighborCardExit)
    {
        $canBePlaced = true;
        if ($currentCardExit == null) {
            // if there is no exit for the current card, the neighbor card musn't have exits either
            $neighborHasExit = $neighborCardExit != null;
            $canBePlaced = $canBePlaced && !$neighborHasExit;
        } else {
            // if there is no exit for the current card, the neighbor card must have a free exit to match
            $canBePlaced = $canBePlaced && $neighborCardExit == -1;
        }
        return $canBePlaced;
    }

    function testExits($currentSubcard, $x, $y)
    {
        $canBePlaced = true;
        $hasAtLeastOneNeighbor = false;
        $grid = BNDGrid::GetFullGrid();

        $leftNeighborSubcard = BNDSubcard::getSubcard($grid[$x - 1][$y]);
        if ($leftNeighborSubcard != null) {
            $hasAtLeastOneNeighbor = true;
            $canBePlaced = $canBePlaced &&
                self::testExitMatchesNeighbors($currentSubcard->_left, $leftNeighborSubcard->_right);
        }

        $rightNeighborSubcard = BNDSubcard::getSubcard($grid[$x + 1][$y]);
        if ($rightNeighborSubcard != null) {
            $hasAtLeastOneNeighbor = true;
            $canBePlaced = $canBePlaced &&
                self::testExitMatchesNeighbors($currentSubcard->_right, $rightNeighborSubcard->_left);
        }

        $topNeighborSubcard = BNDSubcard::getSubcard($grid[$x][$y - 1]);
        if ($topNeighborSubcard != null) {
            $hasAtLeastOneNeighbor = true;
            $canBePlaced = $canBePlaced &&
                self::testExitMatchesNeighbors($currentSubcard->_top, $topNeighborSubcard->_bottom);
        }

        $bottomNeighborSubcard = BNDSubcard::getSubcard($grid[$x][$y + 1]);
        if ($bottomNeighborSubcard != null) {
            $hasAtLeastOneNeighbor = true;
            $canBePlaced = $canBePlaced &&
                self::testExitMatchesNeighbors($currentSubcard->_bottom, $bottomNeighborSubcard->_top);
        }

        return array($canBePlaced, $hasAtLeastOneNeighbor);
    }

    function cardCanBePlaced($id, $x, $y, $rotation)
    {
        $grid = BNDGrid::GetFullGrid();
        if ($grid[$x][$y]["subcard_id"] != null) {
            return false;
        }

        $subcard_0 = new BNDSubcard($id + "_0", $rotation);
        $subcard_1 = new BNDSubcard($id + "_1", $rotation);
        // $subcard_0 = null;
        // $subcard_1 = null;
        switch ($rotation) {
            case 0:
                // Check that the grid is empty where we want to place the card
                if ($grid[$x + 1][$y]["subcard_id"] != null) {
                    return false;
                }
                // Check if the subcards exits match their neighbor's and if they have at least 1 neighbor
                list($firstSubcardCanBePlaced, $firstSubcardHasNeighbor) = self::testExits($subcard_0, $x, $y);
                list($secondSubcardCanBePlaced, $secondSubcardHasNeighbor) = self::testExits($subcard_1, $x + 1, $y);
                break;
            case 90:
                if ($grid[$x][$y + 1]["subcard_id"] != null) {
                    return false;
                }
                list($firstSubcardCanBePlaced, $firstSubcardHasNeighbor) = self::testExits($subcard_0, $x, $y);
                list($secondSubcardCanBePlaced, $secondSubcardHasNeighbor) = self::testExits($subcard_1, $x, $y + 1);
                break;
            case 180:
                if ($grid[$x - 1][$y]["subcard_id"] != null) {
                    return false;
                }
                list($firstSubcardCanBePlaced, $firstSubcardHasNeighbor) = self::testExits($subcard_0, $x, $y);
                list($secondSubcardCanBePlaced, $secondSubcardHasNeighbor) = self::testExits($subcard_1, $x - 1, $y);
                break;
            case 270:
                if ($grid[$x][$y - 1]["subcard_id"] != null) {
                    return false;
                }
                list($firstSubcardCanBePlaced, $firstSubcardHasNeighbor) = self::testExits($subcard_0, $x, $y);
                list($secondSubcardCanBePlaced, $secondSubcardHasNeighbor) = self::testExits($subcard_1, $x, $y - 1);
                break;
        }

        return ($firstSubcardCanBePlaced &&
            $secondSubcardCanBePlaced &&
            ($firstSubcardHasNeighbor || $secondSubcardHasNeighbor));
    }

    function gameHasEnded()
    {
        // TODO code me!
        return false;
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

        $player_id = self::getActivePlayerId();

        $card = $this->cards->getCard($card_id);
        if (!self::cardCanBePlaced($card['type_arg'], $x, $y, $rotation)) {
            throw new feException("Invalid card placement!");
        }

        BNDGrid::placeCard($card['type_arg'], $x, $y, $rotation);

        // Location grid is not used to build the actual grid,
        // it's just to remove the card from the player's hand
        $this->cards->moveCard($card_id, 'grid');


        // Notify all players about the card played
        self::notifyAllPlayers("cardPlayed", clienttranslate('${player_name} plays a card'), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_type' => $card['type_arg'],
            'x' => $x,
            'y' => $y,
            'rotation' => $rotation
        ));

        // Pick a new card forn the player
        $cardDrawn = $this->cards->pickCard('deck', $player_id);
        // Notify active player about the card he's redrawn
        self::notifyPlayer($player_id, "cardDrawn", "", array('cardDrawn' => $cardDrawn));

        if (self::gameHasEnded()) {
            $this->gamestate->nextState("endGame");
        } else {
            $this->gamestate->nextState("nextPlayer");
        }
    }

    function getPossibleMoves($card_id, $rotation)
    {
        // Check that action is possible for player
        self::checkAction('getPossibleMoves');

        $player_id = self::getActivePlayerId();
        $card = $this->cards->getCard($card_id);
        $subcard_0 = new BNDSubcard($card_id + "_0", $rotation);
        $subcard_1 = new BNDSubcard($card_id + "_1", $rotation);

        $playableLocations = BNDGrid::getPlayableLocations();
        foreach ($playableLocations as $location) {
        }
    }

    function changeHand()
    {
        // Check that action is possible for player
        self::checkAction('changeHand');

        $player_id = self::getActivePlayerId();

        // Move all cards from the player's hand to the bottom of the deck
        $playerHand = $this->cards->getPlayerHand($player_id);
        foreach ($playerHand as $card) {
            $this->cards->insertCardOnExtremePosition($card["id"], 'deck', false);
        }

        // Notify active player about their new cards
        $newPlayerHand = $this->cards->pickCards(3, 'deck', $player_id);
        self::notifyPlayer($player_id, "changeHand", "", array('newHand' => $newPlayerHand));

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

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state actions
    ////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

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
        $statename = $state['name'];

        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState("zombiePass");
                    break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive($active_player, '');

            return;
        }

        throw new feException("Zombie mode not supported at this game state: " . $statename);
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

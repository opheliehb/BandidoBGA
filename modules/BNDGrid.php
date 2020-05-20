<?php

class BNDGrid extends APP_DbObject {
    function __construct()
    {
        $grid = self::getDoubleKeyCollectionFromDB(
            "SELECT x, y, subcard_id, rotation
            FROM grid");
    }

    public static function InitializeGrid($supercardId)
    {
        // TODO add supercard at start
        // Create an empty 69*69 grid in database
        for ($x = -69; $x <= 69; $x++) {
            for ($y = -69; $y <= 69; $y++) {
                $sqlInsert = sprintf("INSERT INTO grid (x, y) VALUES ( '%d', '%d' )", $x, $y);
                self::DbQuery($sqlInsert);
            }
        }
        // $grid[0][0] = array('card_id' => self::getGameStateValue('supercardId'), 'card' => $this->card[70][0]);
        // $grid[0][1] = array('card_id' => self::getGameStateValue('supercardId'), 'card' => $this->cards_to_subcards[70][1]);
    }

    function placeSubcard($x, $y, $id, $rotation)
    {
        $sqlInsert = sprintf("INSERT INTO grid VALUES ( '%d', '%d', %s, %d )", $x, $y, $id, $rotation);
        self::DbQuery($sqlInsert);
    }
}
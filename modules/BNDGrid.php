<?php
require_once('BNDCard.php');

class BNDGrid extends APP_DbObject
{

    public static function GetGrid()
    {
        $grid = self::getDoubleKeyCollectionFromDB(
            "SELECT x, y, subcard_id, rotation
            FROM grid WHERE subcard_id IS NOT NULL"
        );
        return $grid;
    }

    public static function GetFullGrid()
    {
        $grid = self::getDoubleKeyCollectionFromDB(
            "SELECT x, y, subcard_id, rotation
            FROM grid"
        );
        return $grid;
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

        $grid = BNDGrid::GetFullGrid();
        self::placeSubcard("70_0", 0, 0, 0, $grid);
        self::placeSubcard("70_1", 1, 0, 0, $grid);
    }

    public static function placeCard($id, $x, $y, $rotation)
    {
        $grid = self::GetFullGrid();
        switch ($rotation) {
            case 0:
                self::placeSubcard($id . "_0", $x, $y, $rotation, $grid);
                self::placeSubcard($id . "_1", $x + 1, $y, $rotation, $grid);
                break;
            case 90:
                self::placeSubcard($id . "_0", $x, $y, $rotation, $grid);
                self::placeSubcard($id . "_1", $x, $y + 1, $rotation, $grid);
                break;
            case 180:
                self::placeSubcard($id . "_0", $x, $y, $rotation, $grid);
                self::placeSubcard($id . "_1", $x - 1, $y, $rotation, $grid);
                break;
            case 270:
                self::placeSubcard($id . "_0", $x, $y, $rotation, $grid);
                self::placeSubcard($id . "_1", $x, $y - 1, $rotation, $grid);
                break;
        }
    }

    public static function placeSubcard($id, $x, $y, $rotation, $grid)
    {
        $subcard = NEW BNDSubcard($id, $rotation);
        if($subcard->_left == -1)
        {
            $leftNeighborSubcard = BNDSubcard::getSubcard($grid[$x - 1][$y]);
            if ($leftNeighborSubcard != null) {
                $subcard->setLeftExit($leftNeighborSubcard->_card_id);
                $subcard->setRightExit($subcard->_card_id);
            }
        }
        if($subcard->_right == -1)
        {
            $rightNeighborSubcard = BNDSubcard::getSubcard($grid[$x + 1][$y]);
            if ($rightNeighborSubcard != null) {
                $subcard->setRightExit($rightNeighborSubcard->_card_id);
                $subcard->setLeftExit($subcard->_card_id);
            }
        }
        if($subcard->_top == -1)
        {
            $topNeighborSubcard = BNDSubcard::getSubcard($grid[$x][$y - 1]);
            if ($topNeighborSubcard != null) {
                $subcard->setTopExit($topNeighborSubcard->_card_id);
                $subcard->setBottomExit($subcard->_card_id);
            }
        }
        if($subcard->_bottom == -1)
        {
            $bottomNeighborSubcard = BNDSubcard::getSubcard($grid[$x][$y + 1]);
            if ($bottomNeighborSubcard != null) {
                $subcard->setBottomExit($bottomNeighborSubcard->_card_id);
                $subcard->setTopExit($subcard->_card_id);
            }
        }
        $sqlInsert = sprintf("UPDATE grid SET subcard_id='%s', rotation=%d WHERE x=%d AND y=%d",  $id, $rotation, $x, $y);
        self::DbQuery($sqlInsert);
    }

    public static function getPlayableLocationsFromCard($subcard, $x, $y)
    {
        $playableLocations = array();
        var_dump($subcard);
        if ($subcard->_left == -1) {
            array_push($playableLocations, array($x - 1, $y));
        }
        if ($subcard->_right == -1) {
            array_push($playableLocations, array($x + 1, $y));
        }
        if ($subcard->_top == -1) {
            array_push($playableLocations, array($x, $y - 1));
        }
        if ($subcard->_bottom == -1) {
            array_push($playableLocations, array($x, $y + 1));
        }
        return $playableLocations;
    }

    public static function getPlayableLocations()
    {
        $listOfPlayableLocations = array();
        $grid = self::GetGrid();
        foreach ($grid as $x => $gridXCoord) {
            foreach ($gridXCoord as $y => $dbsubcard) {
                $subcard = BNDSubcard::getSubcard($dbsubcard);
                $locations = self::getPlayableLocationsFromCard($subcard, $x, $y);
                $listOfPlayableLocations = array_merge($listOfPlayableLocations, $locations);
            }
        }
        return $listOfPlayableLocations;
    }
}

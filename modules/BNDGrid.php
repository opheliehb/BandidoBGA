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
                list($exits_opened_0, $exits_closed_0) = self::placeSubcard($id . "_0", $x, $y, $rotation, $grid);
                list($exits_opened_1, $exits_closed_1) = self::placeSubcard($id . "_1", $x + 1, $y, $rotation, $grid);
                break;
            case 90:
                list($exits_opened_0, $exits_closed_0) = self::placeSubcard($id . "_0", $x, $y, $rotation, $grid);
                list($exits_opened_1, $exits_closed_1) = self::placeSubcard($id . "_1", $x, $y + 1, $rotation, $grid);
                break;
            case 180:
                list($exits_opened_0, $exits_closed_0) = self::placeSubcard($id . "_0", $x, $y, $rotation, $grid);
                list($exits_opened_1, $exits_closed_1) = self::placeSubcard($id . "_1", $x - 1, $y, $rotation, $grid);
                break;
            case 270:
                list($exits_opened_0, $exits_closed_0) = self::placeSubcard($id . "_0", $x, $y, $rotation, $grid);
                list($exits_opened_1, $exits_closed_1) = self::placeSubcard($id . "_1", $x, $y - 1, $rotation, $grid);
                break;
        }
        return array($exits_opened_0 + $exits_opened_1, $exits_closed_0 + $exits_closed_1);
    }

    public static function placeSubcard($id, $x, $y, $rotation, $grid)
    {
        $exits_closed = 0;
        $exits_opened = 0;
        $subcard = new BNDSubcard($id, $rotation);
        // var_dump("Before     Exit Update");
        // var_dump($subcard);
        if ($subcard->_left == -1) {
            $leftNeighborSubcard = BNDSubcard::getSubcard($grid[$x - 1][$y]);
            if ($leftNeighborSubcard != null) {
                $exits_closed++;
                $subcard->setLeftExit($leftNeighborSubcard->_card_id);
                $leftNeighborSubcard->setRightExit($subcard->_card_id);
            }
            else {
                $exits_opened++;
            }
        }
        if ($subcard->_right == -1) {
            $rightNeighborSubcard = BNDSubcard::getSubcard($grid[$x + 1][$y]);
            if ($rightNeighborSubcard != null) {
                $exits_closed++;
                $subcard->setRightExit($rightNeighborSubcard->_card_id);
                $rightNeighborSubcard->setLeftExit($subcard->_card_id);
            }
            else {
                $exits_opened++;
            }
        }
        if ($subcard->_top == -1) {
            $topNeighborSubcard = BNDSubcard::getSubcard($grid[$x][$y - 1]);
            if ($topNeighborSubcard != null) {
                $exits_closed++;
                $subcard->setTopExit($topNeighborSubcard->_card_id);
                $topNeighborSubcard->setBottomExit($subcard->_card_id);
            }
            else {
                $exits_opened++;
            }
        }
        if ($subcard->_bottom == -1) {
            $bottomNeighborSubcard = BNDSubcard::getSubcard($grid[$x][$y + 1]);
            if ($bottomNeighborSubcard != null) {
                $exits_closed++;
                $subcard->setBottomExit($bottomNeighborSubcard->_card_id);
                $bottomNeighborSubcard->setTopExit($subcard->_card_id);
            }
            else {
                $exits_opened++;
            }
        }
        // var_dump("After Exit Update");
        // var_dump($subcard);
        $sqlInsert = sprintf("UPDATE grid SET subcard_id='%s', rotation=%d WHERE x=%d AND y=%d",  $id, $rotation, $x, $y);
        self::DbQuery($sqlInsert);

        return array($exits_opened, $exits_closed);
    }

    public static function getPlayableLocationsFromCard($subcard, $x, $y)
    {
        $playableLocations = array();
        // var_dump($subcard);
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

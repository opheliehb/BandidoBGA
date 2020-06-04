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
        // Create an empty 138*138 grid in database
        for ($x = -69; $x <= 69; $x++) {
            for ($y = -69; $y <= 69; $y++) {
                $sqlInsert = sprintf("INSERT INTO grid (x, y) VALUES ( '%d', '%d' )", $x, $y);
                self::DbQuery($sqlInsert);
            }
        }

        $grid = BNDGrid::GetFullGrid();
        self::placeCard($supercardId, 0, 0, 0);
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

    
    public static function testExitMatchesNeighbors($currentCardExit, $neighborCardExit)
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

    public static function testExits($currentSubcard, $x, $y, $grid)
    {
        $canBePlaced = true;
        $hasAtLeastOneNeighbor = false;

        $leftNeighborSubcard = BNDSubcard::getSubcard($grid[$x - 1][$y]);
        if ($leftNeighborSubcard != null) {
            // var_dump("CURRENT");
            // var_dump($currentSubcard->_left);
            // var_dump("LEFT NEIG");
            // var_dump($leftNeighborSubcard->_right);
            $hasAtLeastOneNeighbor = true;
            $canBePlaced = $canBePlaced &&
                self::testExitMatchesNeighbors($currentSubcard->_left, $leftNeighborSubcard->_right);
        }

        $rightNeighborSubcard = BNDSubcard::getSubcard($grid[$x + 1][$y]);
        // var_dump($rightNeighborSubcard);
        if ($rightNeighborSubcard != null) {
            // var_dump("CURRENT");
            // var_dump($currentSubcard->_right);
            // var_dump("RIGHT NEIG");
            // var_dump($rightNeighborSubcard->_left);
            $hasAtLeastOneNeighbor = true;
            $canBePlaced = $canBePlaced &&
                self::testExitMatchesNeighbors($currentSubcard->_right, $rightNeighborSubcard->_left);
        }

        $topNeighborSubcard = BNDSubcard::getSubcard($grid[$x][$y - 1]);
        if ($topNeighborSubcard != null) {
            $hasAtLeastOneNeighbor = true;
            // var_dump("CURRENT");
            // var_dump($currentSubcard->_top);
            // var_dump("TOP NEIG");
            // var_dump($topNeighborSubcard->_bottom);
            $canBePlaced = $canBePlaced &&
                self::testExitMatchesNeighbors($currentSubcard->_top, $topNeighborSubcard->_bottom);
        }

        $bottomNeighborSubcard = BNDSubcard::getSubcard($grid[$x][$y + 1]);
        if ($bottomNeighborSubcard != null) {
            // var_dump("CURRENT");
            // var_dump($currentSubcard->_right);
            // var_dump("BOTTOM NEIG");
            // var_dump($bottomNeighborSubcard->_top);
            $hasAtLeastOneNeighbor = true;
            $canBePlaced = $canBePlaced &&
                self::testExitMatchesNeighbors($currentSubcard->_bottom, $bottomNeighborSubcard->_top);
        }

        return array($canBePlaced, $hasAtLeastOneNeighbor);
    }

    public static function cardCanBePlaced($id, $x, $y, $rotation, $grid)
    {
        if ($grid[$x][$y]["subcard_id"] != null) {
            return false;
        }

        $subcard_0 = new BNDSubcard($id . "_0", $rotation);
        $subcard_1 = new BNDSubcard($id . "_1", $rotation);

        // var_dump("Testing card :");
        // var_dump($id);
        // var_dump("subcard 0");
        // var_dump($subcard_0);
        // var_dump("subcard_1");
        // var_dump($subcard_1);
        switch ($rotation) {
            case 0:
                // Check that the grid is empty where we want to place the card
                if ($grid[$x + 1][$y]["subcard_id"] != null) {
                    return false;
                }
                // Check if the subcards exits match their neighbor's and if they have at least 1 neighbor
                list($firstSubcardCanBePlaced, $firstSubcardHasNeighbor) = self::testExits($subcard_0, $x, $y, $grid);
                list($secondSubcardCanBePlaced, $secondSubcardHasNeighbor) = self::testExits($subcard_1, $x + 1, $y, $grid);
                break;
            case 90:
                // var_dump("grid[x][y + 1]");
                // var_dump($grid[$x][$y + 1]);
                if ($grid[$x][$y + 1]["subcard_id"] != null) {
                    return false;
                }
                list($firstSubcardCanBePlaced, $firstSubcardHasNeighbor) = self::testExits($subcard_0, $x, $y, $grid);
                list($secondSubcardCanBePlaced, $secondSubcardHasNeighbor) = self::testExits($subcard_1, $x, $y + 1, $grid);
                break;
            case 180:
                if ($grid[$x - 1][$y]["subcard_id"] != null) {
                    return false;
                }
                list($firstSubcardCanBePlaced, $firstSubcardHasNeighbor) = self::testExits($subcard_0, $x, $y, $grid);
                list($secondSubcardCanBePlaced, $secondSubcardHasNeighbor) = self::testExits($subcard_1, $x - 1, $y, $grid);
                break;
            case 270:
                if ($grid[$x][$y - 1]["subcard_id"] != null) {
                    return false;
                }
                list($firstSubcardCanBePlaced, $firstSubcardHasNeighbor) = self::testExits($subcard_0, $x, $y, $grid);
                list($secondSubcardCanBePlaced, $secondSubcardHasNeighbor) = self::testExits($subcard_1, $x, $y - 1, $grid);
                break;
        }


        // // var_dump($firstSubcardCanBePlaced);
        // // var_dump($secondSubcardCanBePlaced);
        // // var_dump($firstSubcardHasNeighbor);
        // // var_dump($secondSubcardHasNeighbor);
        return ($firstSubcardCanBePlaced &&
            $secondSubcardCanBePlaced &&
            ($firstSubcardHasNeighbor || $secondSubcardHasNeighbor));
    }

}

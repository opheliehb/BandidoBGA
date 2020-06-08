<?php
require_once('BNDCard.php');

class BNDGrid extends APP_DbObject
{

    public static function getGrid()
    {
        $grid = self::getDoubleKeyCollectionFromDB(
            "SELECT x, y, subcard_id, rotation
            FROM grid WHERE subcard_id IS NOT NULL"
        );
        return $grid;
    }

    public static function getFullGrid()
    {
        $grid = self::getDoubleKeyCollectionFromDB(
            "SELECT x, y, subcard_id, rotation
            FROM grid"
        );
        return $grid;
    }

    public static function initializeGrid($supercard_id)
    {
        $sql = "INSERT INTO grid (x, y) VALUES ";
        // Create an empty 138*138 grid in database
        for ($x = -69; $x <= 69; $x++) {
            for ($y = -69; $y <= 69; $y++) {
                $newSquare = sprintf(
                    " ( '%d', '%d' ),",
                    $x,
                    $y
                );
                $sql = $sql.$newSquare;
            }
        }

        $sqlTrimmed = trim($sql, ',');
        self::DbQuery($sqlTrimmed);
        
        $grid = BNDGrid::getFullGrid();
        self::placeCard($supercard_id, 0, 0, 0, $grid);
    }

    /** Place a card on the grid by placing the 2 subcards one after the other.
     * Returns the exits opened and closed for player stats.
     */
    public static function placeCard($id, $x, $y, $rotation, $grid)
    {
        switch ($rotation) {
            case 0:
                list($exits_opened_0, $exits_closed_0, $grid) = self::placeSubcard($id . "_0", $x, $y, $rotation, $grid);
                list($exits_opened_1, $exits_closed_1, $grid) = self::placeSubcard($id . "_1", $x + 1, $y, $rotation, $grid);
                $created_isolated_square = (
                    self::isIsolatedSquare($x - 1, $y, $grid)
                    || self::isIsolatedSquare($x + 2, $y, $grid)
                    || self::isIsolatedSquare($x, $y - 1, $grid)
                    || self::isIsolatedSquare($x, $y + 1, $grid)
                    || self::isIsolatedSquare($x + 1, $y - 1, $grid)
                    || self::isIsolatedSquare($x + 1, $y + 1, $grid));
                break;
            case 90:
                list($exits_opened_0, $exits_closed_0, $grid) = self::placeSubcard($id . "_0", $x, $y, $rotation, $grid);
                list($exits_opened_1, $exits_closed_1, $grid) = self::placeSubcard($id . "_1", $x, $y + 1, $rotation, $grid);
                $created_isolated_square = (
                    self::isIsolatedSquare($x, $y - 1, $grid)
                    || self::isIsolatedSquare($x, $y + 2, $grid)
                    || self::isIsolatedSquare($x - 1, $y, $grid)
                    || self::isIsolatedSquare($x + 1, $y, $grid)
                    || self::isIsolatedSquare($x - 1, $y + 1, $grid)
                    || self::isIsolatedSquare($x + 1, $y + 1, $grid));
                break;
            case 180:
                list($exits_opened_0, $exits_closed_0, $grid) = self::placeSubcard($id . "_0", $x, $y, $rotation, $grid);
                list($exits_opened_1, $exits_closed_1, $grid) = self::placeSubcard($id . "_1", $x - 1, $y, $rotation, $grid);
                $created_isolated_square = (
                    self::isIsolatedSquare($x + 1, $y, $grid)
                    || self::isIsolatedSquare($x - 2, $y, $grid)
                    || self::isIsolatedSquare($x, $y + 1, $grid)
                    || self::isIsolatedSquare($x, $y - 1, $grid)
                    || self::isIsolatedSquare($x - 1, $y - 1, $grid)
                    || self::isIsolatedSquare($x - 1, $y + 1, $grid));
                break;
            case 270:
                list($exits_opened_0, $exits_closed_0, $grid) = self::placeSubcard($id . "_0", $x, $y, $rotation, $grid);
                list($exits_opened_1, $exits_closed_1, $grid) = self::placeSubcard($id . "_1", $x, $y - 1, $rotation, $grid);
                $created_isolated_square = (
                    self::isIsolatedSquare($x, $y + 1, $grid)
                    || self::isIsolatedSquare($x, $y - 2, $grid)
                    || self::isIsolatedSquare($x - 1, $y, $grid)
                    || self::isIsolatedSquare($x + 1, $y, $grid)
                    || self::isIsolatedSquare($x - 1, $y - 1, $grid)
                    || self::isIsolatedSquare($x + 1, $y - 1, $grid));
                break;
        }

        return array(
            $exits_opened_0 + $exits_opened_1,
            $exits_closed_0 + $exits_closed_1,
            $created_isolated_square
        );
    }

    /** Place a subcard on the grid by inserting it in the DB.
     * Updates the neighbors subcards so that all cards connect.
     * Computes the exits opened and closed for player stats and returns them.
     */
    public static function placeSubcard($id, $x, $y, $rotation, $grid)
    {
        $exits_closed = 0;
        $exits_opened = 0;
        $subcard = new BNDSubcard($id, $rotation);

        if ($subcard->_left == -1) {
            $left_neighbor_subcard = BNDSubcard::getSubcard($grid[$x - 1][$y]);
            if ($left_neighbor_subcard != null) {
                $exits_closed++;
                $subcard->setLeftExit($left_neighbor_subcard->_card_id);
                $left_neighbor_subcard->setRightExit($subcard->_card_id);
            } else {
                $exits_opened++;
            }
        }
        if ($subcard->_right == -1) {
            $right_neighbor_subcard = BNDSubcard::getSubcard($grid[$x + 1][$y]);
            if ($right_neighbor_subcard != null) {
                $exits_closed++;
                $subcard->setRightExit($right_neighbor_subcard->_card_id);
                $right_neighbor_subcard->setLeftExit($subcard->_card_id);
            } else {
                $exits_opened++;
            }
        }
        if ($subcard->_top == -1) {
            $top_neighbor_subcard = BNDSubcard::getSubcard($grid[$x][$y - 1]);
            if ($top_neighbor_subcard != null) {
                $exits_closed++;
                $subcard->setTopExit($top_neighbor_subcard->_card_id);
                $top_neighbor_subcard->setBottomExit($subcard->_card_id);
            } else {
                $exits_opened++;
            }
        }
        if ($subcard->_bottom == -1) {
            $bottom_neighbor_subcard = BNDSubcard::getSubcard($grid[$x][$y + 1]);
            if ($bottom_neighbor_subcard != null) {
                $exits_closed++;
                $subcard->setBottomExit($bottom_neighbor_subcard->_card_id);
                $bottom_neighbor_subcard->setTopExit($subcard->_card_id);
            } else {
                $exits_opened++;
            }
        }

        $sql = sprintf(
            "UPDATE grid SET subcard_id='%s', rotation=%d WHERE x=%d AND y=%d",
            $id,
            $rotation,
            $x,
            $y
        );
        self::DbQuery($sql);

        $grid[$x][$y]["subcard_id"] = $id;
        $grid[$x][$y]["rotation"] = $rotation;

        return array($exits_opened, $exits_closed, $grid);
    }

    public static function isIsolatedSquare($x, $y, $grid)
    {
        $is_isolated_square = false;

        // if the square is empty and surrounded by cards
        if ($grid[$x][$y]["subcard_id"] == null
            && $grid[$x - 1][$y]["subcard_id"] != null
            && $grid[$x + 1][$y]["subcard_id"] != null
            && $grid[$x][$y - 1]["subcard_id"] != null
            && $grid[$x][$y + 1]["subcard_id"] != null
        ) {
            $left_subcard = BNDSubcard::getSubcard($grid[$x - 1][$y]);
            $right_subcard = BNDSubcard::getSubcard($grid[$x + 1][$y]);
            $top_subcard = BNDSubcard::getSubcard($grid[$x][$y - 1]);
            $bottom_subcard = BNDSubcard::getSubcard($grid[$x][$y + 1]);

            // If there is an exit leading to the square, 
            // it is isolated and game is unwinnable
            $is_isolated_square = ($left_subcard->_right == -1
                || $right_subcard->_left == -1
                || $top_subcard->_bottom == -1
                || $bottom_subcard->_top == -1);
        }

        return $is_isolated_square;
    }

    public static function getPlayableLocationsFromCard($subcard, $x, $y)
    {
        $playable_locations = array();
        if ($subcard->_left == -1) {
            array_push($playable_locations, array($x - 1, $y));
        }
        if ($subcard->_right == -1) {
            array_push($playable_locations, array($x + 1, $y));
        }
        if ($subcard->_top == -1) {
            array_push($playable_locations, array($x, $y - 1));
        }
        if ($subcard->_bottom == -1) {
            array_push($playable_locations, array($x, $y + 1));
        }
        return $playable_locations;
    }

    public static function getPlayableLocations()
    {
        $playable_locations = array();
        $grid = self::getGrid();
        foreach ($grid as $x => $gridXCoord) {
            foreach ($gridXCoord as $y => $dbsubcard) {
                $subcard = BNDSubcard::getSubcard($dbsubcard);
                $locations = self::getPlayableLocationsFromCard($subcard, $x, $y);
                $playable_locations = array_merge($playable_locations, $locations);
            }
        }
        return $playable_locations;
    }

    public static function getEscapeCount()
    {
        $escape_count = 0;
        $grid = self::getGrid();
        foreach ($grid as $x => $gridXCoord) {
            foreach ($gridXCoord as $y => $dbsubcard) {
                $subcard = BNDSubcard::getSubcard($dbsubcard);
                $escape_count += $subcard->getEscapeCount();
            }
        }
        return $escape_count;
    }


    public static function testExitMatchesNeighbors($current_card_exit, $neighbor_card_exit)
    {
        $can_be_placed = true;
        if ($current_card_exit == null) {
            // if there is no exit for the current card, the neighbor card musn't have exits either
            $neighbor_has_exit = $neighbor_card_exit != null;
            $can_be_placed = $can_be_placed && !$neighbor_has_exit;
        } else {
            // if there is no exit for the current card, the neighbor card must have a free exit to match
            $can_be_placed = $can_be_placed && $neighbor_card_exit == -1;
        }
        return $can_be_placed;
    }

    public static function testExits($current_subcard, $x, $y, $grid)
    {
        $can_be_placed = true;
        $has_at_least_one_neighbor = false;

        $left_neighbor_subcard = BNDSubcard::getSubcard($grid[$x - 1][$y]);
        if ($left_neighbor_subcard != null) {
            $has_at_least_one_neighbor = true;
            $can_be_placed = $can_be_placed &&
                self::testExitMatchesNeighbors($current_subcard->_left, $left_neighbor_subcard->_right);
        }

        $right_neighbor_subcard = BNDSubcard::getSubcard($grid[$x + 1][$y]);
        if ($right_neighbor_subcard != null) {
            $has_at_least_one_neighbor = true;
            $can_be_placed = $can_be_placed &&
                self::testExitMatchesNeighbors($current_subcard->_right, $right_neighbor_subcard->_left);
        }

        $top_neighbor_subcard = BNDSubcard::getSubcard($grid[$x][$y - 1]);
        if ($top_neighbor_subcard != null) {
            $has_at_least_one_neighbor = true;
            $can_be_placed = $can_be_placed &&
                self::testExitMatchesNeighbors($current_subcard->_top, $top_neighbor_subcard->_bottom);
        }

        $bottom_neighbor_subcard = BNDSubcard::getSubcard($grid[$x][$y + 1]);
        if ($bottom_neighbor_subcard != null) {
            $has_at_least_one_neighbor = true;
            $can_be_placed = $can_be_placed &&
                self::testExitMatchesNeighbors($current_subcard->_bottom, $bottom_neighbor_subcard->_top);
        }

        return array($can_be_placed, $has_at_least_one_neighbor);
    }

    public static function cardCanBePlaced($id, $x, $y, $rotation, $grid)
    {
        if ($grid[$x][$y]["subcard_id"] != null) {
            return false;
        }

        $subcard_0 = new BNDSubcard($id . "_0", $rotation);
        $subcard_1 = new BNDSubcard($id . "_1", $rotation);

        switch ($rotation) {
            case 0:
                // Check that the grid is empty where we want to place the card
                if ($grid[$x + 1][$y]["subcard_id"] != null) {
                    return false;
                }
                // Check if the subcards exits match their neighbor's and if they have at least 1 neighbor
                list($first_subcard_can_be_placed, $first_subcard_has_neighbor) = self::testExits($subcard_0, $x, $y, $grid);
                list($second_subcard_can_be_placed, $second_subcard_has_neighbor) = self::testExits($subcard_1, $x + 1, $y, $grid);
                break;
            case 90:
                if ($grid[$x][$y + 1]["subcard_id"] != null) {
                    return false;
                }
                list($first_subcard_can_be_placed, $first_subcard_has_neighbor) = self::testExits($subcard_0, $x, $y, $grid);
                list($second_subcard_can_be_placed, $second_subcard_has_neighbor) = self::testExits($subcard_1, $x, $y + 1, $grid);
                break;
            case 180:
                if ($grid[$x - 1][$y]["subcard_id"] != null) {
                    return false;
                }
                list($first_subcard_can_be_placed, $first_subcard_has_neighbor) = self::testExits($subcard_0, $x, $y, $grid);
                list($second_subcard_can_be_placed, $second_subcard_has_neighbor) = self::testExits($subcard_1, $x - 1, $y, $grid);
                break;
            case 270:
                if ($grid[$x][$y - 1]["subcard_id"] != null) {
                    return false;
                }
                list($first_subcard_can_be_placed, $first_subcard_has_neighbor) = self::testExits($subcard_0, $x, $y, $grid);
                list($second_subcard_can_be_placed, $second_subcard_has_neighbor) = self::testExits($subcard_1, $x, $y - 1, $grid);
                break;
        }

        return ($first_subcard_can_be_placed &&
            $second_subcard_can_be_placed &&
            ($first_subcard_has_neighbor || $second_subcard_has_neighbor));
    }
}

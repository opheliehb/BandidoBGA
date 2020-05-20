<?php

class BNDGrid extends APP_DbObject {

    public static function GetGrid()
    {
        $grid = self::getDoubleKeyCollectionFromDB(
            "SELECT x, y, subcard_id, rotation
            FROM grid WHERE subcard_id IS NOT NULL");
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
        
        self::placeSubcard(0, 0, "70_0", 0);
        self::placeSubcard(0, 1, "70_1", 0);
    }

    public static function placeSubcard($x, $y, $id, $rotation)
    {
        $sqlInsert = sprintf("UPDATE grid SET subcard_id='%s', rotation=%d WHERE x=%d AND y=%d",  $id, $rotation, $x, $y);
        self::DbQuery($sqlInsert);
    }
}
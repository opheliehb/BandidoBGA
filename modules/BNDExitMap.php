<?php

class BNDExitMap {

    public static $_exits = array(
        0 => [[-1, null, null, -1],[null, null, null, null]],
        1 => [[null, null, -1, null],[null, null, -1, -1]],
        2 => [[null, null, -1, null],[null, null, -1, null]],
        3 => [[null, null, null, -1],[null, null, null, -1]],
        4 => [[null, null, null, -1],[null, null, null, null]],
        5 => [[-1, null, -1, null],[null, -1, null, null]],
        6 => [[-1, null, null, null],[null, -1, -1, -1]],
        7 => [[-1, null, null, -1],[null, -1, null, -1]],
        8 => [[-1, null, -1, -1],[null, null, null, null]],
        9 => [[null, null, null, -1],[null, -1, null, -1]],
        10 => [[-1, null, null, -1],[null, -1, -1, null]],
        11 => [[-1, null, -1, null],[null, null, -1, null]],
        12 => [[null, null, -1, -1],[null, null, -1, -1]],
        13 => [[null, null, -1, null],[null, null, null, -1]],
        14 => [[null, null, -1, null],[null, -1, -1, null]],
        15 => [[-1, null, -1, null],[null, -1, null, -1]],
        16 => [[-1, null, null, null],[null, -1, null, null]],
        17 => [[-1, null, -1, null],[null, null, -1, -1]],
        18 => [[null, null, null, -1],[null, null, -1, null]],
        19 => [[null, null, null, null],[null, -1, null, -1]],
        20 => [[-1, null, null, -1],[null, -1, -1, null]],
        21 => [[-1, null, null, null],[null, -1, null, null]],
        22 => [[-1, null, null, -1],[null, null, -1, -1]],
        23 => [[null, null, -1, -1],[null, null, null, null]],
        24 => [[null, null, null, null],[null, -1, -1, -1]],
        25 => [[-1, null, -1, -1],[null, -1, null, null]],
        26 => [[-1, null, null, null],[null, null, null, -1]],
        27 => [[null, null, -1, -1],[null, -1, -1, null]],
        28 => [[-1, null, -1, -1],[null, null, null, -1]],
        29 => [[-1, null, null, null],[null, null, -1, null]],
        30 => [[null, null, -1, null],[null, -1, null, -1]],
        31 => [[-1, null, null, null],[null, null, -1, -1]],
        32 => [[null, null, -1, -1],[null, -1, null, -1]],
        33 => [[-1, null, -1, -1],[null, null, -1, null]],
        34 => [[-1, null, null, null],[null, null, null, -1]],
        35 => [[null, null, null, -1],[null, -1, -1, null]],
        36 => [[-1, null, null, null],[null, null, -1, null]],
        37 => [[null, null, -1, -1],[null, null, -1, -1]],
        38 => [[null, null, -1, null],[null, null, null, -1]],
        39 => [[null, null, null, -1],[null, -1, -1, null]],
        40 => [[-1, null, -1, null],[null, -1, null, -1]],
        41 => [[-1, null, null, -1],[null, -1, null, -1]],
        42 => [[-1, null, -1, -1],[null, null, -1, null]],
        43 => [[null, null, null, -1],[null, null, -1, null]],
        44 => [[null, null, -1, null],[null, null, null, null]],
        45 => [[null, null, null, null],[null, null, -1, null]],
        46 => [[null, null, -1, null],[null, -1, -1, null]],
        47 => [[-1, null, -1, -1],[null, null, null, -1]],
        48 => [[null, null, -1, -1],[null, null, null, null]],
        49 => [[-1, null, null, null],[null, null, null, null]],
        50 => [[null, null, null, null],[null, -1, null, -1]],
        51 => [[-1, null, null, -1],[null, null, null, null]],
        52 => [[null, null, -1, -1],[null, null, -1, null]],
        53 => [[null, null, -1, -1],[null, null, null, null]],
        54 => [[null, null, null, null],[null, null, null, -1]],
        55 => [[null, null, null, null],[null, -1, -1, -1]],
        56 => [[-1, null, -1, null],[null, null, null, -1]],
        57 => [[null, null,-1, -1],[null, null, null, -1]],
        58 => [[null, null, -1, null],[null, null, null, null]],
        59 => [[null, null, null, null],[null, -1, -1, null]],
        60 => [[-1, null, null, null],[null, -1, -1, null]],
        61 => [[-1, null, null, null],[null, null, -1, -1]],
        62 => [[null, null, -1, -1],[null, null, -1, null]],
        63 => [[null, null, null, -1],[null, null, null, null]],
        64 => [[-1, null, null, null],[null, null, null, null]],
        65 => [[null, null, null, null],[null, -1, null, null]],
        66 => [[-1, null, null, null],[null, -1, -1, null]],
        67 => [[-1, null, -1, null],[null, -1, null, null]],
        68 => [[-1, null, -1, null],[null, null, null, null]],
        69 => [[null, null, null, null],[null, null, null, null]],
        70 => [[-1, null, -1, -1],[null, -1, -1, -1]],
        71 => [[null, null, -1, -1],[null, -1, -1, -1]],
    );
    
    public static function get($dbsubcard_id)
    {
        list($card_id, $subcard_id) = explode('_', $dbsubcard_id);
        return self::$_exits[$card_id][$subcard_id];
    }
    
    public static function set($dbsubcard_id, $exits)
    {
        list($card_id, $subcard_id) = explode('_', $dbsubcard_id);
        self::$_exits[$card_id][$subcard_id] = $exits;
    }

}
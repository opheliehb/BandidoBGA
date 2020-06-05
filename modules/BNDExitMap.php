<?php

class BNDExitMap extends APP_DbObject
{
    public static function initialize($initial_exits)
    {
        for ($card = 0; $card < 72; $card++) {
            for ($subcard = 0; $subcard < 2; $subcard++) {
                $sql = sprintf(
                    "INSERT INTO exits VALUES ( '%s', %s, %s, %s, %s )",
                    $card . "_" . $subcard,
                    empty($initial_exits[$card][$subcard][0]) ? "NULL" : "'" . $initial_exits[$card][$subcard][0] . "'",
                    empty($initial_exits[$card][$subcard][1]) ? "NULL" : "'" . $initial_exits[$card][$subcard][1] . "'",
                    empty($initial_exits[$card][$subcard][2]) ? "NULL" : "'" . $initial_exits[$card][$subcard][2] . "'",
                    empty($initial_exits[$card][$subcard][3]) ? "NULL" : "'" . $initial_exits[$card][$subcard][3] . "'"
                );
                self::DbQuery($sql);
            }
        }
    }

    public static function get($dbsubcard_id)
    {
        $sql = sprintf(
            "SELECT _left, _right, _top, _bottom FROM exits where subcard_id='%s'",
            $dbsubcard_id
        );
        return self::getObjectFromDB($sql);
    }

    public static function set($dbsubcard_id, $exits)
    {
        $sql =  sprintf(
            "UPDATE exits SET _left=%s, _right=%s, _top=%s, _bottom=%s where subcard_id='%s'",
            empty($exits[0]) ? "NULL" : "'" . $exits[0] . "'",
            empty($exits[1]) ? "NULL" : "'" . $exits[1] . "'",
            empty($exits[2]) ? "NULL" : "'" . $exits[2] . "'",
            empty($exits[3]) ? "NULL" : "'" . $exits[3] . "'",
            $dbsubcard_id
        );

        self::DbQuery($sql);
    }
}

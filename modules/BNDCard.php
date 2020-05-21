<?php
require_once('BNDExitMap.php');

class BNDCard
{
    var $_id;
    var $_subcards;

    function __construct($id, $card_definition)
    {
        $this->_id = $id;
        $this->_subcards[0] = new BNDSubcard($card_definition[0][0], null, $card_definition[0][1], $card_definition[0][2]);
        $this->_subcards[1] = new BNDSubcard(null, $card_definition[1][1], $card_definition[1][0], $card_definition[1][2]);
    }
}

class BNDSubcard
{
    var $_card_id;
    var $_left;
    var $_right;
    var $_top;
    var $_bottom;

    function __construct($subcard_id, $rotation)
    {
        $this->_card_id = $subcard_id;
        $origExits = BNDExitMap::get($this->_card_id);
        $exits = self::getRotation($origExits, $rotation);
        $this->_left = $exits[0];
        $this->_right = $exits[1];
        $this->_top = $exits[2];
        $this->_bottom = $exits[3];
    }

    function getSubcard($dbsubcard)
    {
        if ($dbsubcard["subcard_id"]) {
            return new self($dbsubcard["subcard_id"], $dbsubcard["rotation"]);
        }
        return null;
    }

    function getRotation($subcard, $rotation) {
        switch ($rotation) {
            case 0:
                return $subcard;
            break;
            case 90:
                return array($subcard[0], $subcard[2], $subcard[0], $subcard[1]);
            break;
            case 180:
                return array($subcard[1], $subcard[0], $subcard[3], $subcard[2]);
            break;
            case 270:
                return array($subcard[2], $subcard[3], $subcard[1], $subcard[0]);
            break;
        }
    }
}

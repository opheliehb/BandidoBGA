<?php

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

    function __construct($left, $right, $top, $bottom)
    {
        $this->_left = $left;
        $this->_right = $right;
        $this->_top = $top;
        $this->_bottom = $bottom;
    }

    function get90rotation($subcard)
    {
        return self::getRotation($subcard, 90);
    }
    
    function get180rotation($subcard)
    {
        return self::getRotation($subcard, 180);
    }
    
    function get270rotation($subcard)
    {
        return self::getRotation($subcard, 270);
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

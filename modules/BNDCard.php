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
        return array($subcard[2], $subcard[3], $subcard[1], $subcard[0]);
    }
    
    function get180rotation($subcard)
    {
        return array($subcard[1], $subcard[0], $subcard[3], $subcard[2]);
    }
    
    function get270rotation($subcard)
    {
        return array($subcard[3], $subcard[2], $subcard[0], $subcard[1]);
    }
}

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
    var $_rotation;
    var $_left;
    var $_right;
    var $_top;
    var $_bottom;

    function __construct($subcard_id, $rotation)
    {
        $this->_card_id = $subcard_id;
        $this->_rotation = $rotation;
        $origExits = BNDExitMap::get($this->_card_id);
        $exits = $this->getRotation($origExits);
        // var_dump($subcard_id);
        // var_dump($exits);
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

    function setLeftExit($exit_id)
    {
        $this->_left = $exit_id;
        BNDExitMap::set($this->_card_id, $this->undoRotation());
    }

    function setRightExit($exit_id)
    {
        $this->_right = $exit_id;
        BNDExitMap::set($this->_card_id, $this->undoRotation());
    }

    function setTopExit($exit_id)
    {
        $this->_top = $exit_id;
        BNDExitMap::set($this->_card_id, $this->undoRotation());
    }

    function setBottomExit($exit_id)
    {
        $this->_bottom = $exit_id;
        BNDExitMap::set($this->_card_id, $this->undoRotation());
    }

    function getRotation($subcard) {
        switch ($this->_rotation) {
            case 0:
                return array($subcard["_left"], $subcard["_right"], $subcard["_top"], $subcard["_bottom"]);
            break;
            case 90:
                return array($subcard["_bottom"], $subcard["_top"], $subcard["_left"], $subcard["_right"]);
            break;
            case 180:
                return array($subcard["_right"], $subcard["_left"], $subcard["_bottom"], $subcard["_top"]);
            break;
            case 270:
                return array($subcard["_top"], $subcard["_bottom"], $subcard["_right"], $subcard["_left"]);
            break;
        }
    }

    function undoRotation() {
        switch ($this->_rotation) {
            case 0:
                return array($this->_left, $this->_right, $this->_top, $this->_bottom);
            break;
            case 90:
                return array($this->_top, $this->_bottom, $this->_right, $this->_left);
            break;
            case 180:
                return array($this->_right, $this->_left, $this->_bottom, $this->_top);
            break;
            case 270:
                return array($this->_bottom, $this->_top, $this->_left, $this->_right);
            break;
        }
    }
}

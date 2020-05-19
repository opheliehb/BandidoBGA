<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Bandido implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * bandido.action.php
 *
 * Bandido main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/bandido/bandido/myAction.html", ...)
 *
 */


class action_bandido extends APP_GameAction
{
  // Constructor: please do not modify
  public function __default()
  {
    if (self::isArg('notifwindow')) {
      $this->view = "common_notifwindow";
      $this->viewArgs['table'] = self::getArg("table", AT_posint, true);
    } else {
      $this->view = "bandido_bandido";
      self::trace("Complete reinitialization of board game");
    }
  }


  public function playCard()
  {
    self::setAjaxMode();
    $cardId = self::getArg("cardId", AT_posint, true);
    $x = self::getArg("x", AT_int, true);
    $y = self::getArg("y", AT_int, true);
    $rotation = self::getArg("rotation", AT_int, true);
    $result = $this->game->playCard($cardId, $x, $y, $rotation);
    self::ajaxResponse();
  }

}

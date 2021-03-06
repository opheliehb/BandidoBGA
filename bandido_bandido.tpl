{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Bandido implementation : © Ophélie Haurou-Béjottes <ophelie.hb@gmail.com> & Julien Plantier <julplantier@free.fr>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    bandido_bandido.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->

<div>

    <div class="row">
        <div id="deck_wrap" class="whiteblock deck_wrap">
            <h3>{DECK}</h3>
            <div id="deck" class="deck"></div>
        </div>
        <div id="playerhand_wrap" class="whiteblock playerhand_wrap">
            <h3>{MY_HAND}</h3>
            <div id="playerhand" class="playerhand">
            </div>
        </div>
    </div>

    <div id="board" class="board">
        <div id="map_container">
            <div id="map_scrollable"></div>
            <div id="map_surface"></div>
            <div id="map_scrollable_oversurface">
                <div id="supercard" class="card"></div>
            </div>
            <a id="zoomin" class="map_zoom map_zoom_in" href="#"></a>
            <a id="zoomout" class="map_zoom map_zoom_out" href="#"></a>
            <a id="resize" class="map_zoom map_resize" href="#"></a>
            <a id="movetop" class="map_arrow" href="#"></a>
            <a id="moveleft" class="map_arrow" href="#"></a>
            <a id="moveright" class="map_arrow" href="#"></a>
            <a id="movedown" class="map_arrow" href="#"></a>
        </div>
        <div id="map_footer" class="whiteblock">
            <a href="#" id="enlargedisplay">↓ {LABEL_ENLARGE_DISPLAY} ↓</a>
        </div>
    </div>

</div>


<script type="text/javascript">

    var jstpl_cardontable = '<div class="cardontable"\
                              id="cardontable_${id}"\
                              style="background-position:0px -${y}px">\
                            </div>';

    var jstpl_rotatemobile = '<img src="{GAMETHEMEURL}img/rotate.svg" \
                                class="manipulation-arrow rotate-mobile"\
                                style="position: absolute; left: ${left}px; top:35px; "/>';
    var jstpl_rotateleft = '<img src="{GAMETHEMEURL}img/rotate.svg" \
                                class="manipulation-arrow rotate-left"\
                                style="position: absolute; left: ${left}px;"/>';
    var jstpl_rotateright = '<img src="{GAMETHEMEURL}img/rotate.svg" \
                                class="manipulation-arrow"\
                                style="position: absolute; left: ${left}px;"/>';
    var jstpl_player_board = '\<div class="cp_board">\
                                    <img id="hand_${id}" class="cp_board_hand" src="{GAMETHEMEURL}img/hand.png" />\
                                    <span id="handcount_${id}"></span>\
                                </div>';
</script>

{OVERALL_GAME_FOOTER}
{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Bandido implementation : © <Your name here> <Your email address here>
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
        <div class="deck">

        </div>

        <div id="playerhand">
        </div>

    </div>

    <div class="row">
        <div id="board" class="board" style="border: 2px solid">
            <div id="map_container" style="width: {SIZE}px; min-height: {SIZE}px">
                <div id="map_scrollable"></div>
                <div id="map_surface"></div>
                <div id="map_scrollable_oversurface">
                    <div id="supercard" class="card"></div>
                </div>
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

</div>


<script type="text/javascript">

// Javascript HTML templates

    /*
    // Example:
    var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';
    
    */

</script>

{OVERALL_GAME_FOOTER}
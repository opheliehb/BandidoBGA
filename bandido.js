/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Bandido implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * bandido.js
 *
 * Bandido user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo", "dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/scrollmap",
    "ebg/stock"
],
    function (dojo, declare) {
        return declare("bgagame.bandido", ebg.core.gamegui, {
            constructor: function () {
                console.log('bandido constructor');

                this.scrollmap = new ebg.scrollmap();
                this.cardwidth = 261;
                this.cardheight = 134;
            },

            /*
                setup:
                
                This method must set up the game user interface according to current game situation specified
                in parameters.
                
                The method is called each time the game interface is displayed to a player, ie:
                _ when the game starts
                _ when a player refreshes the game page (F5)
                
                "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
            */

            setup: function (gamedatas) {
                console.log("Starting game setup");

                // Setting up player boards
                for (var player_id in gamedatas.players) {
                    var player = gamedatas.players[player_id];

                    // TODO: Setting up players boards if needed
                }

                // Add supercard in the center of the scrollmap
                dojo.place(
                    this.format_block('jstpl_cardontable',
                        { id: this.gamedatas.supercard_id, x: 0, y: this.gamedatas.supercard_id * 134 }),
                    $('map_scrollable_oversurface'));
                dojo.style('cardontable_' + this.gamedatas.supercard_id, 'left', '0px');
                dojo.style('cardontable_' + this.gamedatas.supercard_id, 'top', '0px');

                this.playerHand = new ebg.stock();
                this.playerHand.create(this, $('playerhand'), this.cardwidth, this.cardheight);
                this.playerHand.image_items_per_row = 1;

                // Create cards types:
                for (var row = 1; row <= 69; row++) {
                    this.playerHand.addItemType(row, row, g_gamethemeurl + 'img/cards.jpg', row - 1);
                }

                // Cards in player's hand
                for (var i in this.gamedatas.hand) {
                    var card = this.gamedatas.hand[i];
                    this.playerHand.addToStockWithId(card.type_arg, card.id);
                }
                this.playerHand.setSelectionMode(1);

                // Setup game notifications to handle (see "setupNotifications" method below)
                this.setupNotifications();

                /** Begin scrollmap setup */
                this.scrollmap.create(
                    $('map_container'),
                    $('map_scrollable'),
                    $('map_surface'),
                    $('map_scrollable_oversurface'));
                this.scrollmap.setupOnScreenArrows(150);
                dojo.connect($('movetop'), 'onclick', this, 'onMoveTop');
                dojo.connect($('moveleft'), 'onclick', this, 'onMoveLeft');
                dojo.connect($('moveright'), 'onclick', this, 'onMoveRight');
                dojo.connect($('movedown'), 'onclick', this, 'onMoveDown');
                dojo.connect($('enlargedisplay'), 'onclick', this, 'onIncreaseDisplayHeight');
                /** End scrollmap setup */

                console.log("Ending game setup");
            },


            ///////////////////////////////////////////////////
            //// Game & client states

            // onEnteringState: this method is called each time we are entering into a new game state.
            //                  You can use this method to perform some user interface changes at this moment.
            //
            onEnteringState: function (stateName, args) {
                console.log('Entering state: ' + stateName);

                switch (stateName) {
                    case 'playerTurn':
                        break;
                }
            },

            // onLeavingState: this method is called each time we are leaving a game state.
            //                 You can use this method to perform some user interface changes at this moment.
            //
            onLeavingState: function (stateName) {
                console.log('Leaving state: ' + stateName);

                switch (stateName) {

                    /* Example:
                    
                    case 'myGameState':
                    
                        // Hide the HTML block we are displaying only during this game state
                        dojo.style( 'my_html_block_id', 'display', 'none' );
                        
                        break;
                   */


                    case 'dummmy':
                        break;
                }
            },

            /** Begin scrollmap handlers */
            onMoveTop: function (evt) {
                console.log("onMoveTop");
                evt.preventDefault();
                this.scrollmap.scroll(0, 300);
            },
            onMoveLeft: function (evt) {
                console.log("onMoveLeft");
                evt.preventDefault();
                this.scrollmap.scroll(300, 0);
            },
            onMoveRight: function (evt) {
                console.log("onMoveRight");
                evt.preventDefault();
                this.scrollmap.scroll(-300, 0);
            },
            onMoveDown: function (evt) {
                console.log("onMoveDown");
                evt.preventDefault();
                this.scrollmap.scroll(0, -300);
            },
            onIncreaseDisplayHeight: function (evt) {
                console.log('$$$$ Event : onIncreaseDisplayHeight');
                evt.preventDefault();

                var cur_h = toint(dojo.style($('map_container'), 'height'));
                dojo.style($('map_container'), 'height', (cur_h + 300) + 'px');
            },
            /** End scrollmap handlers */

            // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
            //                        action status bar (ie: the HTML links in the status bar).
            //        
            onUpdateActionButtons: function (stateName, args) {
                console.log('onUpdateActionButtons: ' + stateName);

                if (this.isCurrentPlayerActive()) {
                    switch (stateName) {
                        case 'playerTurn':
                            this.addActionButton('debug', _('debug'), 'onDebugPlaceCard');
                            break;
                    }
                }
            },

            ///////////////////////////////////////////////////
            //// Utility methods

            /*
            
                Here, you can defines some utility methods that you can use everywhere in your javascript
                script.
            
            */


            ///////////////////////////////////////////////////
            //// Player's action

            /*
            
                Here, you are defining methods to handle player's action (ex: results of mouse click on 
                game objects).
                
                Most of the time, these methods:
                _ check the action is possible at this game state.
                _ make a call to the game server
            
            */

            onDebugPlaceCard: function (evt) {
                dojo.stopEvent(evt);
                this.placeCard(16, {x: -1, y: -1});
            },

            placeCard: function(card_id, position) {
                var backgroundpos_y = card_id * this.cardheight;
                dojo.place(
                    // TODO change jstpl_cardontable because x is always 0
                    this.format_block('jstpl_cardontable', { id: card_id, x: 0, y: backgroundpos_y }),
                    $('map_scrollable_oversurface'));
                dojo.style('cardontable_' + card_id, 'left', position.x * this.cardwidth/2 + 'px');
                dojo.style('cardontable_' + card_id, 'top', position.y * this.cardheight + 'px');
            },
            /* Example:
            
            onMyMethodToCall1: function( evt )
            {
                console.log( 'onMyMethodToCall1' );
                
                // Preventing default browser reaction
                dojo.stopEvent( evt );
    
                // Check that this action is possible (see "possibleactions" in states.inc.php)
                if( ! this.checkAction( 'myAction' ) )
                {   return; }
    
                this.ajaxcall( "/bandido/bandido/myAction.html", { 
                                                                        lock: true, 
                                                                        myArgument1: arg1, 
                                                                        myArgument2: arg2,
                                                                        ...
                                                                     }, 
                             this, function( result ) {
                                
                                // What to do after the server call if it succeeded
                                // (most of the time: nothing)
                                
                             }, function( is_error) {
    
                                // What to do after the server call in anyway (success or failure)
                                // (most of the time: nothing)
    
                             } );        
            },        
            
            */


            ///////////////////////////////////////////////////
            //// Reaction to cometD notifications

            /*
                setupNotifications:
                
                In this method, you associate each of your game notifications with your local method to handle it.
                
                Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                      your bandido.game.php file.
            
            */
            setupNotifications: function () {
                console.log('notifications subscriptions setup');

                // TODO: here, associate your game notifications with local methods

                // Example 1: standard notification handling
                // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );

                // Example 2: standard notification handling + tell the user interface to wait
                //            during 3 seconds after calling the method in order to let the players
                //            see what is happening in the game.
                // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
                // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
                // 
            },

            // TODO: from this point and below, you can write your game notifications handling methods

            /*
            Example:
            
            notif_cardPlayed: function( notif )
            {
                console.log( 'notif_cardPlayed' );
                console.log( notif );
                
                // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
                
                // TODO: play the card in the user interface.
            },    
            
            */
        });
    });

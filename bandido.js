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

                this.playerHand = new ebg.stock();
                this.playerHand.create(this, $('playerhand'), this.cardwidth, this.cardheight);
                this.playerHand.image_items_per_row = 1;

                // Create cards types:
                for (var row = 0; row < 69; row++) {
                    this.playerHand.addItemType(row, row, g_gamethemeurl + 'img/cards.jpg', row);
                }

                // Cards in player's hand
                for (var i in this.gamedatas.hand) {
                    var card = this.gamedatas.hand[i];
                    this.playerHand.addToStockWithId(card.type_arg, card.id);
                }
                this.playerHand.setSelectionMode(1);

                // Setup game notifications to handle (see "setupNotifications" method below)
                this.setupNotifications();

                this.placeCardsOnGrid(this.gamedatas.grid);

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
                            this.addActionButton('debug change hand', _('debugchangehand'), 'onDebugChangeHand');
                            break;
                    }
                }
            },

            ///////////////////////////////////////////////////
            //// Utility methods

            /*** get the div id of a possible move in (x,y) rotated by (rotation) degrees */
            getPossibleMoveId: function (x, y, rotation) {
                return 'possiblemove_' + x + '_' + y + '_' + rotation;
            },

            /*** Creates a div corresponding to the card indicated by card_id,
             * places it at position.
             */
            placeCard: function (card_id, position) {
                var backgroundpos_y = card_id * this.cardheight;
                dojo.place(
                    // TODO change jstpl_cardontable because x is always 0
                    this.format_block('jstpl_cardontable', { id: card_id, x: 0, y: backgroundpos_y }),
                    $('map_scrollable_oversurface'));

                var divid = 'cardontable_' + card_id;
                this.placeCardDiv(divid, position)
            },

            /*** Moves divid on the position {position.x, position.y}, rotated by (position.rotation) degrees
             * position.rotation can be 0, 90, 180, -90
             * The left square of the card will be the one placed to (x,y)
             * divid can be a possiblemove or a cardontable.
             */
            placeCardDiv: function (divid, position) {
                switch (position.rotation.toString()) {
                    case "90":
                        dojo.style(divid, 'left', position.x * this.cardwidth / 2 - this.cardwidth / 4 + 'px');
                        dojo.style(divid, 'top', position.y * this.cardheight + this.cardheight / 2 + 'px');
                        dojo.style(divid, 'transform', 'rotate(90deg)');
                        break;
                    case "180":
                        dojo.style(divid, 'left', position.x * this.cardwidth / 2 - this.cardwidth / 2 + 'px');
                        dojo.style(divid, 'top', position.y * this.cardheight + 'px');
                        dojo.style(divid, 'transform', 'rotate(180deg)');
                        break;
                    case "270":
                        dojo.style(divid, 'left', position.x * this.cardwidth / 2 - this.cardwidth / 4 + 'px');
                        dojo.style(divid, 'top', position.y * this.cardheight - this.cardheight / 2 + 'px');
                        dojo.style(divid, 'transform', 'rotate(-90deg)');
                        break;
                    default: // no rotation or rotation = 0
                        dojo.style(divid, 'left', position.x * this.cardwidth / 2 + 'px');
                        dojo.style(divid, 'top', position.y * this.cardheight + 'px');
                        break;
                }
            },

            placeCardsOnGrid: function (gridData) {
                for (var subCardX in gridData) {
                    for (var subCardY in gridData[subCardX]) {
                        var subCard = gridData[subCardX][subCardY];
                        if (subCard.subcard_id.split("_")[1] == '1') {
                            // We only focus on the left subcard here, placeCard will place both subcards
                            // and we never have only 1 subcard on the grid
                            continue;
                        }
                        var card_id = subCard.subcard_id.split("_")[0];
                        this.placeCard(card_id, {x: subCard.x, y: subCard.y, rotation: subCard.rotation});
                    }
                }
            },

            ///////////////////////////////////////////////////
            //// Player's action

            /*
            
                Here, you are defining methods to handle player's action (ex: results of mouse click on 
                game objects).
                
                Most of the time, these methods:
                _ check the action is possible at this game state.
                _ make a call to the game server
            
            */

            onDebugPlaceCard: function(evt) {
                dojo.stopEvent(evt);

                var x = -1;
                var y = -1;
                var rotation = 90;

                dojo.place(
                    "<div id=" + this.getPossibleMoveId(x, y, rotation) + " class=possiblemove></div>",
                    $('map_scrollable_oversurface'));
                this.placeCardDiv(this.getPossibleMoveId(x, y, rotation), { x: x, y: y, rotation: rotation });

                dojo.query('.possiblemove').connect('onclick', this, 'onClickPossibleMove');
            },

            onDebugChangeHand: function (evt) {
                dojo.stopEvent(evt);

                this.ajaxcall("/bandido/bandido/changeHand.html", {}, this, function (result) { });
            },

            onClickPossibleMove: function (evt) {
                dojo.stopEvent(evt);
                // Get the cliqued move x and y
                // Note: possiblemove id format is "possiblemove_X_Y_rotation"
                var coords = evt.currentTarget.id.split('_');
                var x = coords[1];
                var y = coords[2];
                var rotation = coords[3];

                if (!dojo.hasClass(this.getPossibleMoveId(x, y, rotation), 'possiblemove')) {
                    // This is not a possible move => the click does nothing
                    return;
                }

                if (this.checkAction('playCard'))    // Check that this action is possible at this moment
                {
                    var selected = this.playerHand.getSelectedItems();
                    if (selected.length > 0) {
                        card = selected[0];

                        this.ajaxcall("/bandido/bandido/playCard.html", {
                            x: x,
                            y: y,
                            rotation: rotation,
                            cardId: card.id,
                        }, this, function (result) { });
                    }
                }
            },


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
                dojo.subscribe('cardPlayed', this, "notif_cardPlayed");
                dojo.subscribe('cardDrawn', this, "notif_addCardToHand");
                dojo.subscribe('changeHand', this, "notif_changeHand");
            },

            notif_cardPlayed: function (notif) {
                console.log('notif_cardPlayed');
                console.log(notif);

                if (this.isCurrentPlayerActive()) {
                    this.playerHand.removeFromStock(
                        notif.args.card_type,
                        this.getPossibleMoveId(notif.args.x, notif.args.y, notif.args.rotation));
                }

                this.placeCard(notif.args.card_type,
                    { x: notif.args.x, y: notif.args.y, rotation: notif.args.rotation });
            },

            notif_addCardToHand: function (notif) {
                console.log('notif_addCardToHand');
                console.log(notif);

                this.playerHand.addToStockWithId(notif.args.cardDrawn.type_arg, notif.args.cardDrawn.id);
            },

            notif_changeHand: function (notif) {
                console.log('notif_addCardToHand');
                console.log(notif);

                this.playerHand.removeAllTo('deck');
                for (var newCardIdx in notif.args.newHand) {
                    var newCard = notif.args.newHand[newCardIdx];
                    this.playerHand.addToStockWithId(newCard.type_arg, newCard.id);
                }
            },
        });
    });

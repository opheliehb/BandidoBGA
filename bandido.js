/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Bandido implementation : © Ophélie Haurou-Béjottes <ophelie.hb@gmail.com> & Julien Plantier <julplantier@free.fr>
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
    "dojo",
    "dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/scrollmap",
    "ebg/stock"
],
    function (dojo, declare) {
        return declare("bgagame.bandido", ebg.core.gamegui, {
            constructor: function () {
                console.log('bandido constructor');
                this.covid_variant = false;
                this.mobile = false;

                this.scrollmap = new ebg.scrollmap();
                this.cardwidth = 200;
                this.cardheight = 100;
                this.cardRotations = {};
                this.card = null;
                this.zoom = 0.6;

                dojo.require("dojo.NodeList-traverse");
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

                this.playerHand = new ebg.stock();
                this.playerHand.create(this, $('playerhand'), this.cardwidth, this.cardheight);
                this.playerHand.image_items_per_row = 1;

                if (document.getElementById("ebd-body").classList.contains("touch-device")) {
                    this.mobile = true;
                }

                // Create cards types:
                this.covid_variant = this.gamedatas.covid;
                if (this.covid_variant == true) {
                    for (var row = 0; row < 32; row++) {
                        this.playerHand.addItemType(row, row, g_gamethemeurl + 'img/cards_covid.jpg', row);
                    }
                    dojo.addClass('deck', "deck_covid");
                }
                else {
                    for (var row = 0; row < 69; row++) {
                        // DEBUG
                        // for (var row = 0; row < 12; row++) {
                        this.playerHand.addItemType(row, row, g_gamethemeurl + 'img/cards.jpg', row);
                    }
                    dojo.addClass('deck', "deck_standard");
                }
                // Cards in player's hand
                for (var i in this.gamedatas.hand) {
                    var card = this.gamedatas.hand[i];
                    this.playerHand.addToStockWithId(card.type_arg, card.id);
                    this.cardRotations[card.id] = 0;
                }
                this.playerHand.setSelectionMode(1);
                dojo.connect(this.playerHand, 'onChangeSelection', this, 'onSelectCard');

                // Setting up player boards
                for (var player_id in gamedatas.players) {
                    var player = gamedatas.players[player_id];
                    // Setting up players boards if needed
                    var player_board_div = $('player_board_' + player_id);
                    dojo.place(this.format_block('jstpl_player_board', player), player_board_div);
                    dojo.byId("handcount_" + player_id).textContent = gamedatas.handcounts[player_id];
                }

                // Setup game notifications to handle (see "setupNotifications" method below)
                this.setupNotifications();

                this.placeCardsOnGrid(this.gamedatas.grid);

                this.possibleMoves = this.getSortedPossibleMoves(this.gamedatas.possibleMoves);

                if (this.gamedatas.gameUnwinnable != 0) {
                    this.addActionButton('Abandon game', _('Abandon game'), 'onStopGame');
                }

                this.setDeckLabel(this.gamedatas.deckCount);

                /** Begin scrollmap setup */
                this.scrollmap.create(
                    $('map_container'),
                    $('map_scrollable'),
                    $('map_surface'),
                    $('map_scrollable_oversurface'));
                this.scrollmap.setupOnScreenArrows(150);


                dojo.connect($('zoomin'), 'onclick', this, 'onClickMapZoomIn');
                dojo.connect($('zoomout'), 'onclick', this, 'onClickMapZoomOut');
                dojo.connect($('resize'), 'onclick', this, 'onClickMapResize');
                dojo.connect($('map_container'), 'onwheel', this, 'onMapMouseWheel');
                dojo.connect($('map_container'), 'touchmove', this, 'onMapTouchMove');
                dojo.connect($('movetop'), 'onclick', this, 'onMoveTop');
                dojo.connect($('moveleft'), 'onclick', this, 'onMoveLeft');
                dojo.connect($('moveright'), 'onclick', this, 'onMoveRight');
                dojo.connect($('movedown'), 'onclick', this, 'onMoveDown');
                dojo.connect($('enlargedisplay'), 'onclick', this, 'onIncreaseDisplayHeight');

                this.setMapZoom(this.zoom);
                /** End scrollmap setup */

                this.setupTooltips();

                console.log("Ending game setup");
            },

            setupTooltips: function () {
                this.addTooltipToClass("cp_board_hand", _('Number of cards in player\'s hand'), '');
                this.addTooltipToClass("map_zoom_in", _('Zoom in on the board'), '');
                this.addTooltipToClass("map_zoom_out", _('Zoom out on the board'), '');
                this.addTooltipToClass("map_resize", _('Resize the board to screen size'), '');
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
                        this.possibleMoves = this.getSortedPossibleMoves(args.args.possibleMoves);
                        // Display possible moves in the case where the player already selected a card
                        this.updatePossibleMoves();
                        if (args.args.possibleMoves.length == 0) {
                            if (this.deckCount === "0") {
                                this.gamedatas.gamestate.descriptionmyturn = dojo.string.substitute(
                                    _('${you} must pass (no possible move)'), { you: '${you}' });
                            }
                            else {
                                this.gamedatas.gamestate.descriptionmyturn = dojo.string.substitute(
                                    _('${you} must change your hand'), { you: '${you}' });
                            }
                            this.updatePageTitle();
                        }
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

            onClickMapZoomIn: function (evt) {
                evt.preventDefault();
                this.changeMapZoom(0.2);
            },
            onClickMapZoomOut: function (evt) {
                evt.preventDefault();
                this.changeMapZoom(-0.2);
            },
            onClickMapResize: function (evt) {
                evt.preventDefault();
                var map_pos = dojo.position($('map_container'));
                if (map_pos.y + map_pos.h > 0) // element is visible
                {
                    dojo.style($('map_container'), 'height',
                        Math.max(300, window.innerHeight - map_pos.y) + 'px');
                }
            },
            onMapMouseWheel: function (evt) {
                var scroll = 0;

                if (evt.ctrlKey) {
                    // If we are using a touchpad with the pinch action, we zoom
                    scroll = evt.deltaY / Math.abs(evt.deltaY) * -0.03;
                } else {
                    // from https://stackoverflow.com/questions/10744645/detect-touchpad-vs-mouse-in-javascript/62415754#62415754
                    if (evt.wheelDeltaY) {
                        // If we are using a touhchpad to scroll, we ignore (this scrolls the webpage)
                        if (evt.wheelDeltaY === (evt.deltaY * -3)) {
                            return;
                        }
                    }
                    else if (evt.deltaMode === 0) {
                        // If we are using a touhchpad to scroll, we ignore (this scrolls the webpage)
                        return;
                    }
                    // If we are using a mouse wheel to scroll, we zoom
                    scroll = evt.deltaY / Math.abs(evt.deltaY) * -0.2;
                }
                // If we end up zooming on the map, we stop propagation so that the page does not scroll/zoom
                evt.preventDefault();
                this.changeMapZoom(scroll);
            },
            onMapTouchMove: function (evt) {
                evt.preventDefault();
            },
            changeMapZoom: function (diff) {
                newZoom = this.zoom + diff;
                this.setMapZoom(newZoom);
            },
            setMapZoom: function (zoom) {
                // Keep zoom in the [0.2, 2] range
                this.zoom = zoom <= 0.2 ? 0.2 : zoom >= 2 ? 2 : zoom;

                dojo.style($('map_scrollable'), 'transform', 'scale(' + this.zoom + ')');
                dojo.style($('map_scrollable_oversurface'), 'transform', 'scale(' + this.zoom + ')');
            },

            onMoveTop: function (evt) {
                console.log("onMoveTop");
                evt.preventDefault();
                this.scrollmap.scroll(0, 50);
            },
            onMoveLeft: function (evt) {
                console.log("onMoveLeft");
                evt.preventDefault();
                this.scrollmap.scroll(50, 0);
            },
            onMoveRight: function (evt) {
                console.log("onMoveRight");
                evt.preventDefault();
                this.scrollmap.scroll(-50, 0);
            },
            onMoveDown: function (evt) {
                console.log("onMoveDown");
                evt.preventDefault();
                this.scrollmap.scroll(0, -50);
            },
            onIncreaseDisplayHeight: function (evt) {
                console.log('$$$$ Event : onIncreaseDisplayHeight');
                evt.preventDefault();

                var cur_h = toint(dojo.style($('map_container'), 'height'));
                dojo.style($('map_container'), 'height', (cur_h + 300) + 'px');
            },
            /** End scrollmap handlers */

            onUpdateActionButtons: function (stateName, args) {
                console.log('onUpdateActionButtons: ' + stateName);

                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                switch (stateName) {
                    case 'playerTurn':
                        if (args.possibleMoves.length == 0) {
                            if (this.deckCount === "0") {
                                this.addActionButton('pass', _('Pass'), 'onPass');
                            }
                            else {
                                this.addActionButton('change hand', _('Change hand'), 'onChangeHand');
                            }
                        }
                        if (args.gameUnwinnable != 0) {
                            this.addActionButton('Stop game', _('Stop game'), 'onStopGame');
                            this.addTooltip('Stop game', "",
                                _("The game is unwinnable (isolated square). Click here to stop it now."))
                        }
                        break;
                }
            },

            ///////////////////////////////////////////////////
            //// Utility methods

            setDeckLabel: function (deckCount) {
                this.deckCount = deckCount;
                if (deckCount == "0") {
                    dojo.style(dojo.byId("deck"), "visibility", "hidden");
                    return;
                }
                var deckLabel = dojo.string.substitute(_("×${deckCount}"), {
                    deckCount: deckCount
                });
                dojo.byId("deck").textContent = deckLabel;
            },

            /*** get the div id of a possible move in (x,y) rotated by (rotation) degrees */
            getPossibleMoveId: function (x, y, rotation) {
                return 'possiblemove_' + x + '_' + y + '_' + rotation;
            },

            /*** Creates a div corresponding to the card indicated by card_id,
             * places it at position.
             */
            placeCard: function (card_id, position, update_last_played) {
                var backgroundpos_y = card_id * this.cardheight;
                dojo.place(
                    this.format_block('jstpl_cardontable', { id: card_id, y: backgroundpos_y }),
                    $('map_scrollable'));

                var divid = 'cardontable_' + card_id;
                if (this.covid_variant) {
                    dojo.addClass(divid, "cardontable_covid");
                }
                else {
                    dojo.addClass(divid, "cardontable_standard");
                }

                this.placeCardDiv(divid, position)

                /** place red square around last played card */
                if (update_last_played) {
                    dojo.forEach(dojo.query('.lastcardplayed'), function (div) { dojo.removeClass(div, "lastcardplayed") });
                    dojo.addClass(divid, "lastcardplayed");
                }
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
                        this.placeCard(card_id, { x: subCard.x, y: subCard.y, rotation: subCard.rotation }, false);
                    }
                }
            },

            /***
             * Clears the possible moves displayed then recomputes them.
             * Takes into account card and rotation.
             */
            updatePossibleMoves: function () {
                if (!this.isCurrentPlayerActive() || !this.card) {
                    // Display possible moves only if the player is active and a card is selected
                    return;
                }
                this.removeActionButtons(); // removing actino buttons previously there in case of mobile game
                dojo.query('.possiblemove').forEach(dojo.destroy);

                // Return immediately if there is no possible move for the selected card
                if (!this.possibleMoves[this.card.id]) {
                    return;
                }

                var cardRotation = this.cardRotations[this.card.id];

                for (var idx in this.possibleMoves[this.card.id][cardRotation]) {
                    var possibleMove = this.possibleMoves[this.card.id][cardRotation][idx];

                    var x = possibleMove[0];
                    var y = possibleMove[1];

                    var divid = this.getPossibleMoveId(x, y, this.cardRotations[this.card.id]);
                    dojo.place(
                        "<div id=" + divid +
                        " class=possiblemove style=\"background-position:0px -" +
                        this.card.type * this.cardheight + "px\"></div>",
                        $('map_scrollable_oversurface')
                    );

                    if (this.covid_variant) {
                        dojo.addClass(divid, 'possiblemove_covid');
                    } else {
                        dojo.addClass(divid, 'possiblemove_standard');
                    }

                    this.placeCardDiv(divid,
                        { x: x, y: y, rotation: this.cardRotations[this.card.id] });
                }

                dojo.query('.possiblemove').connect('onclick', this, 'onClickPossibleMove');
            },

            rotate: function (cardElement, rotateClockwise) {
                if (this.card == null) {
                    return;
                }

                var rotation = rotateClockwise ? 90 : -90;
                var previousRotation = this.cardRotations[this.card.id];

                // Add rotation and keep the value under 360 with modulo
                this.cardRotations[this.card.id] = (this.cardRotations[this.card.id] + rotation) % 360;

                // Only time the rotation can be negative is when it was 0 and was rotated anti-clockwise (-90 degree)
                // So set it back to 270 degree
                if (this.cardRotations[this.card.id] < 0) {
                    this.cardRotations[this.card.id] = 270;
                }

                var animation = new dojo.Animation({
                    curve: [0, rotation],
                    onAnimate: function (v) {
                        cardElement.style['transform'] = 'rotate(' + (previousRotation + v) + 'deg)';
                    }
                }).play();


            },

            getSortedPossibleMoves: function (oldPossibleMoves) {
                /** sort possible moves so that the div are displayed in order and no div gets 
                 * un-clickable because it's hidden behind 2 others
                */
                var possibleMoves = {};
                for (var cardId in oldPossibleMoves) {
                    possibleMoves[cardId] = {};
                    for (var rotation in oldPossibleMoves[cardId]) {
                        possibleMoves[cardId][rotation] = oldPossibleMoves[cardId][rotation].sort(function (a, b) {
                            if (a[0] == b[0]) {
                                return a[1] - b[1];
                            }
                            return a[0] - b[0];
                        });
                    }
                }
                return possibleMoves;
            },

            createCardPlaceHolderAndButtons: function (x, y, rotation) {
                var card_id = this.getPossibleMoveId(x, y, rotation);
                dojo.addClass(card_id, "cardPlaceHolder");
                if (this.covid_variant == true) {
                    dojo.style(card_id, "background-image", "url('" + g_gamethemeurl + "img/cards_covid.jpg')");
                }
                else {
                    dojo.style(card_id, "background-image", "url('" + g_gamethemeurl + "img/cards.jpg')");
                }

                this.addActionButton('Validate', _('Validate'), 'onValidateCard');
                this.addActionButton('Cancel', _('Cancel'), 'onCancelCard');
            },

            removeCardPlaceHolderAndButtons: function (cardPlaceHolderElements) {
                cardPlaceHolderElements.forEach(function (element) {
                    dojo.style(element.id, "background-image", "");
                    dojo.removeClass(element.id, "cardPlaceHolder");
                });
                this.removeActionButtons();
            },

            sendMoveToServer: function (x, y, rotation, cardId) {
                this.ajaxcall("/bandido/bandido/playCard.html", {
                    x: x,
                    y: y,
                    rotation: rotation,
                    cardId: cardId,
                    lock: true
                }, this, function (result) { });
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

            onChangeHand: function (evt) {
                dojo.stopEvent(evt);

                this.ajaxcall("/bandido/bandido/changeHand.html", { lock: true }, this, function (result) { });
            },

            onPass: function (evt) {
                dojo.stopEvent(evt);

                this.ajaxcall("/bandido/bandido/pass.html", { lock: true }, this, function (result) { });
            },

            onSelectCard: function (control_name, item_id) {
                var cards = this.playerHand.getSelectedItems();
                this.divIdToRotate = this.playerHand.getItemDivId(item_id);

                if (this.mobile && this.card && this.card.id === item_id) {
                    // In mobile mode, clicking an already selected card rotates it
                    this.playerHand.selectItem(item_id);
                    this.rotate(dojo.query("#" + this.divIdToRotate)[0], true);
                    this.updatePossibleMoves();
                    return;
                }
                
                if (cards.length === 0) {
                    // Clear arrows on card and possible moves, reset this.card and this.rotation, removes action buttons added in mobile version
                    this.removeActionButtons();
                    dojo.query('.possiblemove').forEach(dojo.destroy);
                    dojo.query('.manipulation-arrow').forEach(dojo.destroy);
                    this.card = null;
                    return;
                }
                this.card = cards[0];

                // Place arrows on card
                var cardDiv = dojo.query("#" + this.divIdToRotate);
                var leftPos = parseInt(cardDiv[0].style.left, 10) + this.cardwidth / 2;
                if (this.mobile) {
                    // mobile mode, only place 1 arrow
                    dojo.place(this.format_block('jstpl_rotatemobile', { left: leftPos - 12 }), $("playerhand"));
                }
                else {
                    // 24 is the arrow image size
                    dojo.place(this.format_block('jstpl_rotateleft', { left: leftPos - 24 }), $("playerhand"));
                    dojo.place(this.format_block('jstpl_rotateright', { left: leftPos }), $("playerhand"));
                    dojo.query('.manipulation-arrow').connect('onclick', this, 'onClickRotateCard');
                }

                this.updatePossibleMoves();
            },

            // Rotates card according to the arrow clicked
            onClickRotateCard: function (evt) {
                dojo.stopEvent(evt);
                if (dojo.hasClass(evt.currentTarget, "rotate-left")) {
                    this.rotate(dojo.query("#" + this.divIdToRotate)[0], true);
                }
                else {
                    this.rotate(dojo.query("#" + this.divIdToRotate)[0], false);
                }
                this.updatePossibleMoves();
            },

            onClickPossibleMove: function (evt) {
                dojo.stopEvent(evt);

                // Get the clicked move x and y
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
                    if (selected.length == 0) {
                        return;
                    }
                    card = selected[0];

                    if (this.mobile) {

                        var cardPlaceHolderElements = dojo.query('.cardPlaceHolder');
                        if (cardPlaceHolderElements.length != 0) {
                            var coordsCardToValidate = cardPlaceHolderElements[0].id.split('_');
                            this.removeCardPlaceHolderAndButtons(cardPlaceHolderElements);

                            if (x === coordsCardToValidate[1]
                                && y === coordsCardToValidate[2]
                                && rotation === coordsCardToValidate[3]) {
                                this.sendMoveToServer(x, y, rotation, card.id);
                                return;
                            }

                        }

                        this.createCardPlaceHolderAndButtons(x, y, rotation);
                        return;
                    }

                    this.sendMoveToServer(x, y, rotation, card.id);

                }
            },

            onValidateCard: function (evt) {
                dojo.stopEvent(evt);

                var cardPlaceHolderElements = dojo.query('.cardPlaceHolder');
                if (cardPlaceHolderElements.length === 0) {
                    return;
                }
                var coords = cardPlaceHolderElements[0].id.split('_');

                var x = coords[1];
                var y = coords[2];
                var rotation = coords[3];
                this.removeCardPlaceHolderAndButtons(cardPlaceHolderElements);

                this.sendMoveToServer(x, y, rotation, card.id);
            },

            onCancelCard: function (evt) {
                dojo.stopEvent(evt);
                var cardPlaceHolderElements = dojo.query('.cardPlaceHolder');
                if (cardPlaceHolderElements.length != 0) {
                    this.removeCardPlaceHolderAndButtons(cardPlaceHolderElements);
                }
            },

            onStopGame: function (evt) {
                dojo.stopEvent(evt);

                this.ajaxcall("/bandido/bandido/stopGame.html", { lock: true }, this, function (result) { });
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
                    possibleMoveDivId = this.getPossibleMoveId(notif.args.x, notif.args.y, notif.args.rotation)

                    if (dojo.byId(possibleMoveDivId) != null) {
                        this.playerHand.removeFromStock(
                            notif.args.card_type,
                            possibleMoveDivId);
                    }
                    else {
                        // When replaying the game, we don't have the possible moves div
                        // so we just remove the card from the player hand
                        this.playerHand.removeFromStock(notif.args.card_type);
                    }


                    this.card = null;
                    dojo.query('.possiblemove').forEach(dojo.destroy);
                    dojo.query('.manipulation-arrow').forEach(dojo.destroy);
                }

                this.setDeckLabel(notif.args.deckCount);
                dojo.byId("handcount_" + notif.args.player_id).textContent = notif.args.handCount;

                this.placeCard(notif.args.card_type,
                    { x: notif.args.x, y: notif.args.y, rotation: notif.args.rotation },
                    true);
            },

            notif_addCardToHand: function (notif) {
                console.log('notif_addCardToHand');
                console.log(notif);

                if (notif.args.cardDrawn != null) {
                    this.playerHand.addToStockWithId(notif.args.cardDrawn.type_arg,
                        notif.args.cardDrawn.id, "deck");
                    this.cardRotations[notif.args.cardDrawn.id] = 0;
                }
            },

            notif_changeHand: function (notif) {
                console.log('notif_addCardToHand');
                console.log(notif);

                // Remove cards from the player hand and add the new cards
                this.playerHand.removeAllTo('deck');
                for (var newCardIdx in notif.args.newHand) {
                    var newCard = notif.args.newHand[newCardIdx];
                    this.playerHand.addToStockWithId(newCard.type_arg, newCard.id);
                    // Reset cardRotations 
                    this.cardRotations[newCard.id] = 0;
                }
                // Reset selected card
                this.card = null;
            },
        });
    });

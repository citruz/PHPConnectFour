var currentGames = {};
var currentGameId;

$(document).ready(function(){
  getGames();
  getUserGames();
  $(".gameslist, nav").on("click", "a", function(event){
    if ($(this).data('id') != undefined) 
      switchTo($(this).data('id'));
    else {
      $('.game-container').hide();
      $('#mainmenu').show();
      $('nav .elem').removeClass('active');
      $('nav .elem:first').addClass('active');
    }

    event.preventDefault();
    return false;
  });

  $('form.creategame').ajaxForm(
  {
     success: function(response, statusString, xhr, $form){
        getGameData(response, function() {switchTo2();});
    },
    error: function(response, status, err){
      errorOut(response);
    }
  }
  ); 
});

function switchTo(gameid) {
  if (currentGames[gameid] === undefined) {
    //join game
    $.getJSON('join.php', {'gameid': gameid}, function(data) {
      if (data.error) {
        error(data.msg);
      } else {
        currentGames[gameid] = data;
        currentGames[gameid].minId = 0;
        switchTo2(gameid);
      }
    })
    .fail(function(jqxhr, textStatus, error) { 
      errorOut("Error joining game.",jqxhr); 
    });
    
  } else {
    switchTo2(gameid);
  }
  

}
function switchTo2(gameid) {
  $('#mainmenu').hide();
  $('.game-container').hide();

  if ($('#container-'+gameid).length == 0) {
    $('.content').append('<div class="game-container" id="container-'+gameid+'"></div>');
    initGameCanvas(gameid);
  } else {
    $('#container-'+gameid).show();
  }
  $('nav .elem').removeClass('active');
  $('nav a[data-id='+gameid+']').parent().addClass('active');
  currentGameId = gameid;
  updateCurrentGame();
  setInterval(function(){updateCurrentGame();},1000);
}

function updateCurrentGame() {
  updateState(currentGameId);
}

function initGameCanvas(gameid) {
  var cont = $('#container-'+gameid);


  cont.append('<div class="status"><span></span></div><div class="canvas"></div>');
  var canvas = cont.find('.canvas');
  canvas.css('width', '100%');
  canvas.css('height',  cont.height() - 50 + "px");

  cont.on('click', function(e){
    if (e.offsetY > 50) {
      //Determine clicked col
      var colWidth = cont.width() / 7;
      var colNum = Math.floor(e.offsetX / colWidth) + 1;
      
      columnClicked(colNum);
    }
  });
}

function columnClicked(colNum) {
  if (isValidColumn(colNum)) {
    $.getJSON('submitmove.php?gameid='+currentGameId+'&x='+colNum, function(data) { 
      if (data.error == undefined) {
        currentGames[currentGameId].moves = data.moves;
        paintCoins(currentGameId);
      } else {
        errorOut(data.msg);
      }
    })    
    .fail(function(jqxhr, textStatus, error) { errorOut("Error getting game data.",jqxhr); });
  }
}

function isValidColumn(colNum) {
  return true;
}

function updateState(gameid) {
  if (currentGames[gameid].minId == undefined)
    currentGames[gameid].minId = 0;

  $.getJSON('getgamestate.php?gameid='+gameid+'&minId='+currentGames[gameid].minId, function(data) { 
    if (data.error == undefined) {
      currentGames[currentGameId] = data;
      paintCoins(gameid);
      switchPlayers(gameid);
    } else {
      errorOut(data.msg);
    }
  })    
  .fail(function(jqxhr, textStatus, error) { errorOut("Error getting game data.",jqxhr); });

}

function paintCoins(gameid) {
  $('#container-'+gameid+' .canvas').html('');
  if (currentGames[gameid].moves != undefined) {
    $.each(currentGames[gameid].moves, function(key, move) { 
      var playerNum = (move.userid == currentGames[gameid].game.players[0].id) ? 0 : 1;
      $('#container-'+gameid+' .canvas').append(
        '<div class="coin new player-'+playerNum+'" style="opacity: 1; position: absolute; top: '+((move.y-1)*100)+'px; left: '+((move.x-1)*100)+'px" />'
      );
    }); 
} }
function switchPlayers(gameid) {
  if (currentGames[gameid].game.players == undefined || currentGames[gameid].game.players.length == 1) {
    $('#container-'+gameid+' .status span').html('Warte auf zweiten Spieler.');
  } else {
    $.each(currentGames[gameid].game.players, function(key, player) {
      if (player.id == currentGames[gameid].currentPlayer) {
        $('#container-'+gameid+' .status span').html('Spieler ' + player.username + ' ist am Zug!');
      }
    });
  }
}

function getUserGames() {
   $.getJSON('getgames.php?scope=user', function(data) { 
      if (data.error == undefined) {
        currentGames.game = data;
        $.each(data, function(key, game) {
          if (currentGames[game.id] == undefined)
            currentGames[game.id] = {};

          currentGames[game.id].game=game;
          if ($('a[data-id='+game.id+']').length == 0)
            $("nav .nav-inner").append('<div class="elem"><a class="close" href="#">Close</a><a href="#" data-id="'+game.id+'">'+game.name+'</a></div>');
        });
      } else {
        errorOut(data.msg);
      }
    })    
    .fail(function(jqxhr, textStatus, error) { errorOut("Error getting game data.",jqxhr); });
}

function getGameData(gameid, callback) {
    $.getJSON('getgameinfo.php', {'gameid': gameid}, function(data) {
      if (data.error == undefined) {
        if (currentGames[gameid] == undefined)
          currentGames[gameid] = {};

        currentGames[gameid].game = data;
        if ($('a[data-id='+gameid+']').length == 0)
          $("nav .nav-inner").append('<div class="elem"><a class="close" href="#">Close</a><a href="#" data-id="'+data.game.id+'">'+data.game.name+'</a></div>');
        callback();
      } else {
        errorOut(data.msg);
      }
    })    
    .fail(function(jqxhr, textStatus, error) { errorOut("Error getting game data.",jqxhr); });

}

function getGames() {
  $.getJSON('getgames.php', function(data) {
    var items = [];

    $.each(data, function(key, game) {
      items.push('<li><a href="#" data-id="'+game.id+'">' + game.name + '</a></li>');
    });
   $('<ul/>', {
      'class': 'my-new-list',
      html: items.join('')
    }).appendTo('.gameslist');
    });
}

function errorOut(msg) {
  alert("fehler: "+msg);
}
function errorOut(msg, jqxhr) {
  alert("fehler: "+msg);
  console.log(jqxhr);
}

$(function() {
  var $document = $(document);
  $document.trigger('dom_loaded', $document);
});

;(function($, doc, win) {
  "use strict";

  var name = 'vier-gewinnt';

  function VierGewinnt(el, opts) {
    this.$el      = $(el);
    this.$el.data(name, this);

    this.defaults = {
      numCols: 7,
      numRows: 6,
      heightStatusbar: 50
    };

    var meta      = this.$el.data(name + '-opts');
    this.opts     = $.extend(this.defaults, opts, meta);

    this.data = new Array(this.opts.numCols);
    for (var i = 0; i < this.opts.numCols; i++) {
      this.data[i] = new Array(this.opts.numRows);
    }

    this.curPlayer = 1;
    this.playerSymbols = ["", "O", "X"];


    this.init();
  }

  VierGewinnt.prototype.init = function() {
    var self = this;

    self.$el.css('background', '#EEE');
    self.$el.append('<div class="status" style="height: '+self.opts.heightStatusbar+'px; border-bottom: 1px solid black;" ><span>Spieler 1 ist am Zug!</span></div>');

    self.$el.append('<div class="canvas" />');
    self.$canvas = self.$el.find('.canvas');
    self.$canvas.css('width', '100%');
    self.$canvas.css('height',  self.$el.height() - self.opts.heightStatusbar + "px");

    self.$el.on('click', function(e){
      if (e.offsetY > self.opts.heightStatusbar) {
        //Determine clicked col
        var colWidth = self.$el.width() / self.opts.numCols;
        var colNum = Math.floor(e.offsetX / colWidth) + 1;
        
        self.columnClicked(colNum);
      }
    });
  };

  VierGewinnt.prototype.columnClicked = function(colNum) {
    var column = this.data[colNum - 1];
    if (column[this.opts.numRows -1] == undefined) {
      var i = 0;
      while (column[i] != undefined) {i++;}
      
      column[i] = this.playerSymbols[this.curPlayer];

      this.animateCoin(colNum, i+1);

      this.switchPlayers();
    }
  };

  VierGewinnt.prototype.animateCoin = function(colNum, rowNum) {
    var self = this;

    var colWidth = self.$el.width() / self.opts.numCols;
    var rowHeight = self.$canvas.height() / self.opts.numRows;

    this.$canvas.append('<div class="coin new player-'+this.curPlayer+'" style="opacity: 1;" />');

    var coin = this.$canvas.find('.new');
    coin.removeClass('new');
    coin.css('position', 'absolute');
    coin.css('top', rowHeight*-1);
    coin.css('left', (colNum - 1) * colWidth);

    coin.animate({
      opacity: 1,
      top: ((this.opts.numRows - rowNum) * rowHeight) + "px"
    }, 500, function() {
      // Animation complete.
    });

  };

  VierGewinnt.prototype.switchPlayers = function() {
    if (this.curPlayer == 1)
      this.curPlayer = 2;
    else
      this.curPlayer = 1;    

    var statusbar = this.$el.find('.status');
    statusbar.html('');
    statusbar.append('<span>Spieler '+ this.curPlayer+' ist am Zug!');
  }

  VierGewinnt.prototype.destroy = function() {
    this.$el.off('.' + name);
    this.$el.find('*').off('.' + name);
    this.$el.removeData(name);
    this.$el = null;
  };

  $.fn.vierGewinnt = function(opts) {
    return this.each(function() {
      new VierGewinnt(this, opts);
    });
  };

  $(doc).on('dom_loaded ajax_loaded', function(e, nodes) {
    console.log("dom loaded");
    var $nodes = $(nodes);
    var $elements = $nodes.find('.' + name);
    $elements = $elements.add($nodes.filter('.' + name));

    $elements.vierGewinnt();
  });
})(jQuery, document, window);
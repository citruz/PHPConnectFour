var currentGames = {};
var currentGameId;


$(document).ready(function(){
  getGames();
  setInterval("getGames()", 5000);
  getUserGames();
  $(".gameslist, nav").on("click", "a", function(event){
    if ($(this).hasClass('close')) {
      leaveGame($(this).data('id'));
    } else if ($(this).data('id') != undefined) { 
      switchTo($(this).data('id'));
    } else if ($(this).hasClass('lobby')) {
      //Show lobby
      $('.game-container').hide();
      $('#mainmenu').show();
      $('nav .elem').removeClass('active');
      $('nav .elem:first').addClass('active');
      currentGameId = undefined;
      getGames();
    }

    event.preventDefault();
    return false;
  });

  $('.refresh').click(function(e) {
    getGames();
    e.preventDefault();
  });
  $(' .new').click(function(e) {
    //reset values
    $('.rightCol input[type=text]').val('');
    $('.rightCol .colorPicker a.active').removeClass('active');
    $('.rightCol').fadeIn();
    e.preventDefault();
  });
  $(' .rightCol .close').click(function(e) {
    $('.rightCol').fadeOut();
    e.preventDefault();
  });

  $('form.creategame').ajaxForm(
  {
    beforeSubmit: function(formData, jqForm, options) {
      $('form.creategame .error').removeClass('error');

      var error = false;
      if (formData[0].value.replace(' ','').length == 0) {
        //Kein Name eingegeben
        $('form.creategame input[type=text]').addClass('error');
        error = true;
      }

      var p1Color = $('#p1color a.active');
      if (p1Color.length == 0) {
        //Keine Farbe1 eingegeben
        $('#p1color').addClass('error');
        error = true;
      }
      var p2Color = $('#p2color a.active');
      if (p2Color.length == 0) {
        //Keine Farbe2 eingegeben
        $('#p2color').addClass('error');
        error = true;
      }
      if (p2Color.html() == p1Color.html()) {
        //Gleiche farbe
        $('#p2color, #p1color').addClass('error');
        error = true;
      }

      if (error)
        return false;
      

      $('.rightCol').fadeOut();

      formData.push({name: "player1color", value: p1Color.html()});
      formData.push({name: "player2color", value: p2Color.html()});
      console.log(formData);
      return true;
    },
    success: function(response, statusString, xhr, $form){
        getGameData(response, function(gameid) {switchTo2(gameid);});
    },
    error: function(response, status, err){
      errorOut(response);
    }
  }
  ); 


  //Colorpicker
  $(".colorPicker").on("click", "a", function(event){
    $(this).parent().parent().find('a.active').removeClass('active');
    $(this).addClass('active');
    event.preventDefault();
    return false;
  });
});

function leaveGame(gameid) {
  if (currentGames[gameid] != undefined) {
    //leave game
    $.getJSON('view.php?action=leave&gameid='+gameid, function(data) {
      currentGames[gameid] = undefined;
      $('nav .elem[data-id='+gameid+']').remove();
      $('#container-'+gameid).remove();
      if (currentGameId == gameid) {   
        $('#mainmenu').show();
        $('nav .elem').removeClass('active');
        $('nav .elem:first').addClass('active'); 
        currentGameId = undefined;
      }
    })
    .fail(function(jqxhr, textStatus, error) { 
      errorOut("Error leaving game.",jqxhr); 
    });
    
  }
}


function switchTo(gameid) {
  if (currentGames[gameid] === undefined) {
    //join game
    $.getJSON('view.php?action=join', {'gameid': gameid}, function(data) {
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
  getGameData(gameid, function(){$('nav a[data-id='+gameid+']').parent().addClass('active');});

  setInterval("updateCurrentGame()",1000);
}

function updateCurrentGame() {
  if (currentGameId != undefined)
    updateState(currentGameId);
}

function initGameCanvas(gameid) {
  var cont = $('#container-'+gameid);


  cont.append('<div class="status"><span></span></div><div class="canvas"></div>');
  var canvas = cont.find('.canvas');
  canvas.css('width', '100%');
  canvas.css('height',  cont.height() - 50 + "px");

  cont.on('click', function(e){
    if(typeof e.offsetX === "undefined" || typeof e.offsetY === "undefined") {
       var targetOffset = $(e.target).offset();
       e.offsetX = e.pageX - targetOffset.left;
       e.offsetY = e.pageY - targetOffset.top;
    }

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
    $.getJSON('view.php?action=submitmove&gameid='+currentGameId+'&x='+colNum, function(data) { 
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

  $.getJSON('view.php?action=getgamestate&gameid='+gameid+'&minId='+currentGames[gameid].minId, function(data) { 
    if (data.error == undefined) {
      currentGames[currentGameId] = data;
      if (data.game.game.closed == "1" && data.game.game.haswinner == "0" ) {
        //Spiel wurde beendet
        $('#container-'+gameid+' .status span').html('Das Spiel wurde vorzeitig beendet, es gibt keinen Sieger.');
      } else if (data.game.game.closed == "1" && data.game.game.haswinner == "1" ) {

        var winnerName;
        $.each(data.game.players, function(key, player) {
          if (player.id == data.game.game.winner) {
            winnerName = player.username;
          }
        });
 
        $('#container-'+gameid+' .status span').html('Das Spiel ist beendet, '+winnerName+' ist der Sieger!');
      } else {
      
        paintCoins(gameid);
        switchPlayers(gameid);
      }
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
      if (playerNum == 0)
        var color =  currentGames[gameid].game.game.player1color;
      else 
        var color =  currentGames[gameid].game.game.player2color;

      $('#container-'+gameid+' .canvas').append(
        '<div class="coin new player-'+playerNum+'" style="background: '+color+';opacity: 1; position: absolute; top: '+((move.y-1)*75)+'px; left: '+((move.x-1)*75)+'px" />'
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
   $.getJSON('view.php?action=getgames&scope=user', function(data) { 
      if (data.error == undefined) {
        currentGames.game = data;
        $.each(data, function(key, game) {
          if (currentGames[game.id] == undefined)
            currentGames[game.id] = {};

          currentGames[game.id].game=game;
          if ($('a[data-id='+game.id+']').length == 0)
            $("nav .nav-inner").append('<div class="elem" data-id="'+game.id+'" ><a class="close"  data-id="'+game.id+'" href="#">Close</a><a href="#" class="gamelink" data-id="'+game.id+'">'+game.name+'</a></div>');
        });
      } else {
        errorOut(data.msg);
      }
    })    
    .fail(function(jqxhr, textStatus, error) { errorOut("Error getting game data.",jqxhr); });
}

function getGameData(gameid, callback) {
    $.getJSON('view.php?action=getgameinfo', {'gameid': gameid}, function(data) {
      if (data.error == undefined) {
        if (currentGames[gameid] == undefined)
          currentGames[gameid] = {};

        currentGames[gameid].game = data;
        if ($('nav a[data-id='+gameid+']').length == 0)
          $("nav .nav-inner").append('<div class="elem" data-id="'+gameid+'"><a class="close" data-id="'+gameid+'" href="#">Close</a><a href="#" class="gamelink"  data-id="'+data.game.id+'">'+data.game.name+'</a></div>');
        callback(gameid);
      } else {
        errorOut(data.msg);
      }
    })    
    .fail(function(jqxhr, textStatus, error) { errorOut("Error getting game data.",jqxhr); });

}

function getGames() {
  if (currentGameId != undefined)
    return;

  $.getJSON('view.php?action=getgames', function(data) {
    if (data.length != 0) {
      var items = [];
      $.each(data, function(key, game) {
        items.push('<li><a href="#" data-id="'+game.id+'">' + game.name + '</a><span>  (gestartet von '+game.username+')</span></li>');
      });
     $('<ul/>', {
        'class': 'gameslist',
        html: items.join('')
      }).appendTo($('.gameslist').empty());
   } else {
    $('.gameslist').html('<div class="empty">Es gibt zu Zeit keine offenen Partien. Sei der Erste!</div>');
   }
  });
}

function errorOut(msg) {
  alert("fehler: "+msg);
}
function errorOut(msg, jqxhr) {
  alert("fehler: "+msg);
  console.log(jqxhr);
}

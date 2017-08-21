      function toggleUpDown(id) {
        if(document.getElementById(id).src.indexOf('down') > 0) {
            document.getElementById(id).src = '../../img/up.png';  
        }
        else {
          document.getElementById(id).src = '../../img/down.png';
        }
      }
      
      
       $(document).ready(function() {
          $('[data-toggle="popover"]').popover();
          $('[data-toggle="popover1"]').popover();   
          $("img[src$='../../img/down.png']").trigger('click');
          $('#txtarea').hide();
          $('#legend').hide();        
          window.scrollTo(200,500);
         
       });
            
       function createButton(id,name) {
        var send_func = "send_mesg(" + id + ")";
        var btn = $('<button type = "button" class = "btn btn-secondary btn-sm" id = "send_msg" style = "background-color: #b3b3b3; border-color: #737373;" onmouseover = "this.style.backgroundColor = \'#737373\'" onmouseout = "this.style.backgroundColor = \'#b3b3b3\'" onclick = "' + send_func + '">Message ' + name + '</button>');
        var btn2 = $('<button type = "button" class = "btn btn-secondary btn-sm" onclick = "showButtons()" id = "bck" style = "background-color: #b3b3b3; border-color: #737373;" onmouseover = "this.style.backgroundColor = \'#737373\'" onmouseout = "this.style.backgroundColor = \'#b3b3b3\'">Back</button>');
        var p = $('<span id = "spc">&nbsp;</span>');
        var area = $('<textarea name = "mesg_sp" cols = "28" rows = "3" placeholder = "Inform service provider company" id = "msg"></textarea>');
        var br = $('<span id = "br"><br/></span>');
        $('#txtarea').append(area);
        $('#txtarea').append(br);
        $('#txtarea').append(btn);
        $('#txtarea').append(p);
        $('#txtarea').append(btn2);
        $('#txtarea').show();
        $('#btns').hide();
       }
       
       function showButtons() {
        $('#btns').show();
        $('#send_msg').remove();
        $('#bck').remove();
        $('#spc').remove();
        $('#br').remove();
        $('#msg').remove();
        $('#txtarea').hide();
       }
       
       function send_mesg(spid) { 
        var m = $('#msg').val();
        if(m) {
          $.ajax({
                type: 'post',
                url: 'fetch_data.php',
                data: {
                    sprovid: spid,
                    content: m
                },
                success: function(response) {
                  alert('Successfully notified to ' + response + '.');
                  showButtons();
                }
            });
        }
        else {
          alert('No blank messages please.');
        }
       }
       
       setInterval(function() {
          window.location.reload();
       }, 60000);
       
       function closeLegend() {
        $('#legend').hide();
        $('#showlegend').show();
       }
       
       function showLegend() {
        $('#showlegend').hide();
        $('#legend').show();
       }     
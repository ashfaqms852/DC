    function toggleUpDown(id) {
      if(document.getElementById(id).src.indexOf('down') > 0) {
          document.getElementById(id).src = '../../img/up.png';  
      }
      else {
        document.getElementById(id).src = '../../img/down.png';
      }
    }
    
    function fetch_select(val) {
        $.ajax({
            type: 'post',
            url: 'fetch_data.php',
            data: {
                get_option: val
            },
            success: function(response) {
              if($('#onoff').prop('checked'))
                location.reload();            
            }
            });
    }
    
    function notify(val, sd) {
        $.ajax({
            type: 'post',
            url: 'fetch_data.php',
            data: {
                serv_prov_id: val,
                sDate1: sd
            },
            success: function(response) {             
              alert('Successfully notified to ' + response + '.');
            }
            });
    }
    
    function confirmNotifyAll(val, sd) {
      var reply = confirm('Are you sure you want to notify everyone?');
      if(reply == true) {
        notifyAll(val, sd);
      }
    }
    
    function notifyAll(val, sd) {
        $.ajax({
            type: 'post',
            url: 'fetch_data.php',
            data: {
                all_serv_prov_comp: val,
                sDate2: sd
            },
            success: function(response) {             
              alert('Successfully notified to everyone.');
            }
            });
    }
        
    $(document).ready(function() {
      $('[data-toggle="popover"]').popover();
      $('[data-toggle="popover1"]').popover();   
      $("img[src$='../../img/down.png']").trigger('click');
      $('#b1').trigger('click');

    });
    
    $('#unasg').submit(function(e) {
      e.preventDefault();
      $.ajax({
        type: 'post',
        url: 'fetch_data.php',
        data: $('#unasg').serialize(),
        success: function(response) {
          alert(response);
        }
      });
    });
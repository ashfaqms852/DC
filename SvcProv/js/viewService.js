    $('#popover').popover({
        html: true,
        placement: 'right',
        title: function () {
            return $("#popover-head").html();
        },
        content: function () {
            return $("#popover-content").html();
        }
    });
    
    $('#popover2').popover({
        html: true,
        placement: 'bottom',
        title: function () {
            return $("#popover-head2").html();
        },
        content: function () {
            return $("#popover-content2").html();
        }
    });
     
    function confirmPU(trid) {
        var reply = confirm("Have you picked up the clothes?");
        if(reply == true) {
            pickUp(trid);
        }
    }
    
    function pickUp(trid) {
      $.ajax({
            type: 'post',
            url: 'serviceProcessing.php',
            data: {
                pu_trid: trid
            },
            success: function(response) {
              window.location.reload();
              alert('Successfull pick-up.');
            }
        });
    }
    
    function confirmDO(trid) {
        var reply = confirm("Have you dropped off the clothes?");
        if(reply == true) {
            dropOff(trid);
        }
    }
    
    function dropOff(trid) {
      $.ajax({
            type: 'post',
            url: 'serviceProcessing.php',
            data: {
                do_trid: trid
            },
            success: function(response) {
              window.location.reload();
              alert('Successfull drop-off.');
            }
        });
    }
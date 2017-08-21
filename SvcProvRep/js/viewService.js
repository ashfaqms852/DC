$('#popoveraptnotes').popover({
    html: true,
    placement: 'right',
    title: function () {
        return $("#popover-head").html();
    },
    content: function () {
        return $("#popover-content").html();
    }
});

$('#popover').popover({
    html: true,
    placement: 'bottom',
    title: function () {
        return $("#popover-head2").html();
    },
    content: function () {
        return $("#popover-content2").html();
    }
});

window.onload = function(){

(function(){
  var counter = document.getElementById("count").value;

  setInterval(function() {
    counter++;

      span = document.getElementById("count");
      span.value = counter;
  }, 60000);

})();

};

function informCustomer(cust_id) {
    var information = $('#message_cust').val();
    if(information) {
    $.ajax({
        type: 'post',
        url: 'fetch_data.php',
        data: {
            customer: cust_id,
            info: information
        },
        success: function(response) {
          $('#message_cust').val("");
          $('#message_cust').attr("placeholder", "Inform customer.");
          alert('Successfully notified customer.');
        }
    });
    }
    else {
        alert("No blank messages please.");
    }
}


function resetPickUp(trid, link, sname) {
    var reply = confirm("Are you sure you want to reset pick-up?");
    if(reply) {
        $.ajax({
            type: 'post',
            url: 'fetch_data.php',
            data: {
                rpu_trid: trid,
                rpu_link: link,
                rpu_sname: sname
            },
            success: function(response) {
                alert("Pick-up reset successful.");
                location.reload();
            }
        });
    }
}

function resetDropOff(trid, link, sname) {
    var reply = confirm("Are you sure you want to reset drop-off?");
    if(reply) {
        $.ajax({
            type: 'post',
            url: 'fetch_data.php',
            data: {
                rdo_trid: trid,
                rdo_link: link,
                rdo_sname: sname
            },
            success: function(response) {
                alert("Drop-off reset successful.");
                location.reload();
            }
        });
    }
}

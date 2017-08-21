$('#cust_id').blur(function() {
    if($('#cust_id').val() > 0) {
        $('#email').val('').prop('disabled', true);
        $('#apt_name').val('').prop('disabled', true);
        $('#unit').val('').prop('disabled', true);
    }
    else {
        $('#email').val('').prop('disabled', false);
        $('#apt_name').val('').prop('disabled', false);
        $('#unit').val('').prop('disabled', false);
    }
});

$('#email').blur(function() {
    if($('#email').val() != "") {
        $('#cust_id').val('').prop('disabled', true);
    }
    else {
        if($('#apt_name').val() == "" && $('#unit').val() <= 0) {
            $('#cust_id').val('').prop('disabled', false);
        }
    }
});

$('#apt_name').blur(function() {
    if($('#apt_name').val() != "") {
        $('#cust_id').val('').prop('disabled', true);
    }
    else {
        if($('#email').val() == "" && $('#unit').val() <= 0) {
            $('#cust_id').val('').prop('disabled', false);
        }
    }
});

$('#unit').blur(function() {
    if($('#unit').val() > 0) {
        $('#cust_id').val('').prop('disabled', true);
    }
    else {
        if($('#apt_name').val() == "" && $('#apt_name').val() == "") {
            $('#cust_id').val('').prop('disabled', false);
        }
    }
});

$('#reset').click(function() {
    $('#cust_id').val('');
    $('#email').val('');
    $('#apt_name').val('');
    $('#unit').val('');
    $('#start_svc_date').val('');
    $('#end_svc_date').val('');
    $('#cancellations').prop('checked', false);
});

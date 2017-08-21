<?php
session_start();
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');

require_once "../../../api/include/dbconnect.php";

date_default_timezone_set("America/Chicago");

$spcID = isset($_GET['spcID'])? $_GET['spcID'] : 0;
$trID = isset($_GET['trID'])? $_GET['trID'] : 0;
$sD = isset($_GET['sD'])? $_GET['sD'] : date('Y-m-d');
$csID = isset($_GET['csID'])? $_GET['csID'] : 0;

$toViewService = "viewService.php?spcID=" . $spcID . "&trID=" . $trID . "&sD=" . $sD;

$stmt1 = $link->prepare("select cs.DCNotes, cs.KeyReceivedOn, cs.ReturnKeyOn, cs.KeyReceivedBy
                        from Customers cs 
                        where cs.CustomerId = :cust_id");
$stmt1->execute(["cust_id" => $csID]);
$row1 = $stmt1->fetch(PDO::FETCH_ASSOC);

$keyReceivedOn = ($row1['KeyReceivedOn'] != "") ? $row1['KeyReceivedOn'] : "";
$returnKeyOn = ($row1['ReturnKeyOn'] != "") ? $row1['ReturnKeyOn'] : "";

$stmt2 = $link->prepare("select ServiceProviderId, PreferredName from ServiceProviders where ServiceProviderCompany = :spc");
$stmt2->execute(["spc" => $spcID]);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="expires" content="0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet">
    <link href="../../css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="../../css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
</head>

<body>

    <div class="slider_cont">
        <div class="container">
            <ul class="bxslider">
                <li>
                    <div class="outer_content">
                        <div class="inner_content">
                          
                            <div class="botton_line">
                            CUSTOMER DETAILS</div>
                            <div class="botton_line" style = "font-size: 25px;"><?php echo date('D M j Y', strtotime($sD)) . " | Customer Name";  ?></div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    
    
    <br/>
    <div class="container">
        <button class = "btn btn-warning" name = "dashboard" onclick = "window.location.href = '<?php echo $toViewService; ?>'" style = "font-size: 16px;">
        <img src = "../../img/back.png" width = "20" height = "20">&nbsp;<b>View Service</b></button>
        <br/><br/>
       
        <fieldset>
            <legend>Customer Details</legend>
            
            <div class="col-xs-10">
                <label for = "dwnotes">DC Notes: </label>
                <textarea class="form-control" id = "notes" rows="3"><?php echo $row1['DCNotes']; ?></textarea>
            </div><br/>
            <div class = "col-xs-10"><p></p></div>
            <div class="col-xs-4">
                <label for = "date1">Key Received On</label>
                <div class="controls input-append date form_datetime" data-date="" data-date-format="yyyy-mm-dd hh:ii:00" data-link-field="dtp_input1">
                    <input size="24" type="text" value="<?php echo $keyReceivedOn; ?>" class = "form-control" id = "key_received_on">
                    <span class="add-on"><i class="icon-remove"></i></span>
					<span class="add-on"><i class="icon-th"></i></span>
                </div>
				<input type="hidden" id="dtp_input1" value="" /><br/>
            </div><br/><br/>
            <div class = "col-xs-10"><p></p></div>
            <div class="col-xs-4" style = "display: block;">
                <label for = "date1">Return Key On</label>
                <div class="controls input-append date form_datetime" data-date="" data-date-format="yyyy-mm-dd hh:ii:00" data-link-field="dtp_input1">
                    <input size="24" type="text" value="<?php echo $returnKeyOn; ?>" class = "form-control" id = "return_key_on">
                    <span class="add-on"><i class="icon-remove"></i></span>
					<span class="add-on"><i class="icon-th"></i></span>
                </div>
				<input type="hidden" id="dtp_input1" value="" /><br/>
            </div><br/><br/>
            <div class = "col-xs-10"><p></p></div>
            <div class="col-xs-4">
                <label for = "date1">Key Received By</label>
                <select class="form-control" id = "key_received_by">
                    <option value = "<?php echo $csID . ':0'; ?>">Select Service Runner</option>
                    <?php
                    while($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                        $selected = "";
                        if($row1['KeyReceivedBy'] == $row2['ServiceProviderId']) {
                            $selected = "selected";
                        }
                    ?>
                    <option value = "<?php echo $csID . ':' . $row2['ServiceProviderId']; ?>" <?php echo $selected; ?>>
                    <?php echo $row2['ServiceProviderId'] . " : " . $row2['PreferredName']; ?></option>
                    <?php
                    }
                    ?>
                </select>
                
            </div><br/><br/>
            <div class = "col-xs-10"><p></p></div>
            <div class = "col-xs-10"><p></p></div>
            <div class = "col-xs-12">
            <button class = "btn btn-warning" id = "update_customer_details">Submit</button>
            </div>
        </fieldset>
     

    </div>
    
    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/bootstrap.min.js"></script>
    <script src="../../js/jquery.bxslider.min.js"></script>
    <script type="text/javascript" src="../../js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
    <script type="text/javascript" src="../../js/bootstrap-datetimepicker.fr.js" charset="UTF-8"></script>
    <script>
        
    $('.form_datetime').datetimepicker({
        //language:  'fr',
        weekStart: 1,
        todayBtn:  1,
		autoclose: 1,
		todayHighlight: 1,
		startView: 2,
		forceParse: 0,
        showMeridian: 1
    });
	$('.form_date').datetimepicker({
        language:  'fr',
        weekStart: 1,
        todayBtn:  1,
		autoclose: 1,
		todayHighlight: 1,
		startView: 2,
		minView: 2,
		forceParse: 0
    });
	$('.form_time').datetimepicker({
        language:  'fr',
        weekStart: 1,
        todayBtn:  1,
		autoclose: 1,
		todayHighlight: 1,
		startView: 1,
		minView: 0,
		maxView: 1,
		forceParse: 0
    });
        
        
        $('#update_customer_details').on('click', function() {
            var formdata = new FormData();
            var kro = $('#key_received_on').val();
            var krb = $('#key_received_by').val();
            var flag = false;
            var no_sp = '<?php echo $csID . ":0"; ?>';  
            if(kro && (krb != no_sp)) flag = true;
            if(!kro && (krb == no_sp)) flag = true;
            formdata.append('ucd_notes', $('#notes').val());
            formdata.append('ucd_key_rec_on', kro);
            formdata.append('ucd_return_key_on', $('#return_key_on').val());
            formdata.append('ucd_key_received_by', krb);
            if(flag) {
                $.ajax({
                    type: 'post',
                    url: 'fetch_data.php',
                    datatype: 'text',
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: formdata,
                    success: function(response) {
                        alert(response);
                        location.reload();
                    }
                });
            }
            else {
                alert('You either need to enter both Key Received On and Received By or leave both of them as blank.');
            }
            
        });
    </script>


</body>

</html>
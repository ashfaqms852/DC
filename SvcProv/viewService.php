<?php
//start
session_start();
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
date_default_timezone_set("America/Chicago");

require_once "../../../api/include/dbconnect.php";

$spID = isset($_GET['spID'])? $_GET['spID'] : 0;
$trID = isset($_GET['trID'])? $_GET['trID'] : 0;
$servDate = isset($_GET['sD'])? $_GET['sD'] : date('Y-m-d');
$toServForToday = "servicesForToday.php?spID=" . $spID . "&sD=" . $servDate;

$disabled1 = "";
$disabled2 = "";

$confirmPUFunc = "confirmPU(" . $trID . ")";
$confirmDOFunc = "confirmDO(" . $trID . ")";

$stmt1 = $link->prepare("select cs.FirstName, cs.MobilePhone, sp.ServiceProviderId, ac.ApartmentComplexName, cs.UnitNumber, sv.ServiceName, sv.ServiceType, tr.ServiceTime,
          ac.Address, ac.City, ac.State, ac.ZipCode, sp.Name, tr.TransactionId,tr.Notes, cs.Notes as CustNotes, cs.LastName, tr.ServiceId, cs.CustomerId,
          tr.ClockedIn, tr.ClockedOut, ac.DCNotes, cs.DCNotes as custDCNotes, sp.Active,
          case weekday(:servicedate)
          when 0 then ac.MondayOpen when 1 then ac.TuesdayOpen when 2 then ac.WednesdayOpen when 3 then ac.ThursdayOpen when 4 then ac.FridayOpen
          when 5 then ac.SaturdayOpen when 6 then ac.SundayOpen end as Open,
          case weekday(:servicedate)
          when 0 then ac.MondayClose when 1 then ac.TuesdayClose when 2 then ac.WednesdayClose when 3 then ac.ThursdayClose when 4 then ac.FridayClose
          when 5 then ac.SaturdayClose when 6 then ac.SundayClose end as Close  
          from Transactions tr inner join Customers cs on tr.CustomerId = cs.CustomerId
          inner join ApartmentComplexes ac on cs.ApartmentId = ac.ApartmentId
          inner join Services sv on tr.ServiceId = sv.ServiceId 
          inner join ServiceProviders sp on tr.ServiceProviderId = sp.ServiceProviderId 
          where tr.TransactionId = :transactionid");

$stmt1->execute(["servicedate" => $servDate, "transactionid" => $trID]);
$row1 = $stmt1->fetch(PDO::FETCH_ASSOC);

$apthours = date("g:ia", strtotime($row1['Open'])) . " to " . date("g:ia", strtotime($row1['Close']));
$address = explode(' ', $row1['Address']);
$googleMapsLink = "https://www.google.com/maps?q=";
foreach($address as $a) {
    $googleMapsLink .= $a . '+';
}
$googleMapsLink .= $row1['City'] . '+' . $row1['State'] . '+' . $row1['ZipCode'];

if($servDate == date('Y-m-d')) {
   
    if($row1['ServiceName'] == 'DryCleaning Drop-Off') {
        $disabled1 = "disabled";
        if($row1['ClockedOut'] == "") {
            $disabled2 = "";
        }
        else {
            $disabled2 = "disabled";
        }
    }
    else {
        if($row1['ClockedIn'] == "") {
            $disabled1 = "";
        }
        else {
            $disabled1 = "disabled";
        }
        $disabled2 = "disabled";
    }
}
else {
    $disabled1 = "disabled";
    $disabled2 = "disabled";
}

if($row1['Active']) {    
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
    <title>DC Runner: View Service</title>
</head>

<body>


    <div class="slider_cont">
        <div class="container">
            <ul class="bxslider">
                <li>
                    <div class="outer_content">
                        <div class="inner_content">
                          
                            <div class="botton_line">RUNNER: SERVICE DETAILS</div>
                            <div class="botton_line" style = "font-size: 25px;"><?php echo date('D M j Y', strtotime($servDate)) . " | " . $row1['Name'];  ?></div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div><br/><br/>
    
    <div class = "container">
        <div class = "row">
            <div class = "col-xs-6">
                <h4 class="title"><?php
                    date_default_timezone_set("America/Chicago");
                    echo date('F j, Y', strtotime($servDate));
                    ?>
                </h4>
            </div>
            <div class = "dropdown col-xs-6">
                  <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Report &nbsp;<span class="caret"></span>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li><a  class = "dropdown-item" style = "cursor: pointer;" onclick = "unitNotAccessible()">Unit Not Accessible</a></li>
                    <li><a  class = "dropdown-item" style = "cursor: pointer;" onclick = "declinedService()">Declined Service</a></li>
                    <li><a  class = "dropdown-item" style = "cursor: pointer;" onclick = "keyReleaseIssue()">Key Release Issue</a></li>
                    <li><a  class = "dropdown-item" style = "cursor: pointer;" onclick = "noPickUp()">No Pick-up</a></li>
                  </div>
        </div>
        </div>
    </div>
    
    <div class="container">
                    <h3><a href="#" id="popover" class="button blue"><?php echo $row1['ApartmentComplexName']; ?></a>
                    <?php
                    if($row1['DCNotes'] != "") { ?>
                    <img src = "../../img/info.png" width = "25" height = "25" alt = "More Info" />
                    <?php    
                    }
                    ?>
                    </h3>
                    <h4>Office Hours: <?php echo $apthours; ?></h4>
                    <h4><a target = "_blank" href = "<?php echo $googleMapsLink; ?>"><?php echo $row1['Address'] . ", " . $row1['City'] . ", " . $row1['State'] . " " . $row1['ZipCode']; ?></a></h4>
                    <h4>Unit Number: <?php echo $row1['UnitNumber']; ?></h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>SERVICE</th>
                                    <th>CUST_ID</th>
                                    <th>CUST NAME</th>
                                    <th>PETS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo $row1['ServiceName']; ?></td>
                                    <td><?php echo $row1['CustomerId']; ?></td>
                                    <td><a id = "popover2" class = "button blue"><?php echo $row1['FirstName'];
                                    if($row1['custDCNotes'] != "") { ?>
                                        <img src = "../../img/info.png" width = "20" height = "20" alt = "More Info" />    
                                    <?php
                                    }
                                    ?></a></td>
                                    <td>
                                        <?php
                                        $stmt5 = $link->prepare("select count(*) as pets from Customer_Pets where CustomerId = :customerid");
                                        $stmt5->execute(["customerid" => $row1['CustomerId']]);
                                        $row5 = $stmt5->fetch(PDO::FETCH_ASSOC);
                                        if($row5['pets'] >0) {
                                            echo 'Yes';
                                        }
                                        else {
                                            echo 'No';
                                        }
                                        ?>
                                    </td> 
                                </tr>
                            </tbody>
                        </table>
                        
                    </div><br/>                  

                    <?php
                    $notes = "";
                    if($row1['Notes'] == "") {
                        $notes = "No notes from this customer.";
                    }
                    else {
                        $notes = $row1['Notes'];
                    }
                    
                    if($row1['CustNotes'] != "") { ?>
                    <h4><b>Permanent Customer Notes: </b><?php echo $row1['custDCNotes']; ?></h4>                    
                    <?php    
                    }
                    ?>
                    <h4><b>Today's Notes: </b><?php echo $notes; ?></h4><br/>
                    
                    <h4>Picked up at: <?php
                    if($row1['ClockedIn'] != "") {
                        echo date("F j, Y, g:i a", strtotime($row1['ClockedIn']));
                    }
                    else {
                        echo "N/A";
                    }
                    ?></h4>
                    <h4>Dropped off at: <?php
                    if($row1['ClockedOut'] != "") {
                        echo date("F j, Y, g:i a", strtotime($row1['ClockedOut']));
                    }
                    else {
                        echo "N/A";
                    }
                    ?></h4><br/>                    
                

           
    </div><br/>
    
    <div class="container" id = "clockinout">  
                <button class = "btn btn-warning" name = "clockin" <?php echo $disabled1; ?> onclick = "<?php echo $confirmPUFunc; ?>">Pick Up</button>
                <button class = "btn btn-warning" name = "clockout"<?php echo $disabled2; ?> onclick = "<?php echo $confirmDOFunc; ?>">Drop Off</button>
                <button class = "btn btn-warning" name = "dashboard" onclick = "window.location.href = '<?php echo $toServForToday; ?>'">Dashboard</button>
           
    </div><br/>
    
    <div id="popover-head" class="hide"><h4>Apt Notes</h4></div>
    <div id="popover-content" class="hide">        
            <textarea rows = "3" cols = "30" id = "apt_notes" disabled style = "color: black;"><?php echo $row1['DCNotes']; ?></textarea>            
    </div>
    
    <div id="popover-head2" class="hide"><h4>Cust Notes</h4></div>
    <div id="popover-content2" class="hide">        
            <textarea rows = "3" cols = "30" id = "apt_notes" disabled style = "color: black;"><?php echo $row1['custDCNotes']; ?></textarea>            
    </div> 
                
 
    


    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/bootstrap.min.js"></script>
    <script src="../../js/jquery.bxslider.min.js"></script>
    <script src = "js/viewService.js"></script>
    <script type="text/javascript">
        

    
    function unitNotAccessible() {
        var reply = confirm('Are you sure you want to report unit not accessible?');
        if(reply) {
            $.ajax({
                type: 'post',
                url: 'serviceProcessing.php',
                data: {
                    una_trid: '<?php echo $trID; ?>'
                },
                success: function(response) {
                    alert(response);
                }
            });       
        }
    }
    
    function declinedService() {
        var reply = confirm('Are you sure you want to report declined service by customer?');
        if(reply) {
            $.ajax({
                type: 'post',
                url: 'serviceProcessing.php',
                data: {
                    ds_trid: '<?php echo $trID; ?>'
                },
                success: function(response) {
                    alert(response);
                }
            });       
        }
    }
    
    function keyReleaseIssue() {
        var reply = confirm('Are you sure you want to report key release issue?');
        if(reply) {
            $.ajax({
                type: 'post',
                url: 'serviceProcessing.php',
                data: {
                    kri_trid: '<?php echo $trID; ?>'
                },
                success: function(response) {
                    alert(response);
                }
            });       
        }
    }
    
    function noPickUp() {
        var reply = confirm('Are you sure you want to report no pick-up?');
        if(reply) {
            $.ajax({
                type: 'post',
                url: 'serviceProcessing.php',
                data: {
                    npu_trid: '<?php echo $trID; ?>'
                },
                success: function(response) {
                    alert(response);
                }
            });
        }
    }
   

    </script>
    
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

</body>

</html>

<?php
}
else { ?>
<!DOCTYPE html>
<html lang="en">
<head><title>Inactive Runner</title></head>
<body><h1>Access Denied.</h1></body>
</html>
<?php  
}
?>
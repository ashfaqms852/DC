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

$toDashboard = "dashboard.php?spcID=" . $spcID . "&sD=" . $sD;
$toScheduler = "scheduler.php?spcID=" . $spcID . "&sD=" . $sD;

$stmt1 = $link->prepare("select cs.FirstName, cs.Email, cs.MobilePhone, sp.ServiceProviderId, ac.ApartmentComplexName, cs.UnitNumber, sv.ServiceName, sv.ServiceType, tr.ServiceTime,
          ac.Address, ac.City, ac.State, ac.ZipCode, sp.Name, tr.TransactionId, tr.Notes, cs.LastName, tr.ServiceId, cs.CustomerId, tr.ServiceProviderCompany, cs.DCNotes as custDCNotes,
          tr.ClockedIn, tr.ClockedOut, cs.Notes as CustNotes, tr.TransactionType, tr.OnTheWay, spr.FirstName as SPRName, tr.Price, cs.ApartmentId, ac.DCNotes,
          case weekday(:servicedate)
          when 0 then MondayOpen when 1 then TuesdayOpen when 2 then WednesdayOpen when 3 then ThursdayOpen when 4 then FridayOpen
          when 5 then SaturdayOpen when 6 then SundayOpen end as Open,
          case weekday(:servicedate)
          when 0 then MondayClose when 1 then TuesdayClose when 2 then WednesdayClose when 3 then ThursdayClose when 4 then FridayClose
          when 5 then SaturdayClose when 6 then SundayClose end as Close          
          from Transactions tr inner join Customers cs on tr.CustomerId = cs.CustomerId
          inner join ApartmentComplexes ac on cs.ApartmentId = ac.ApartmentId
          inner join Services sv on tr.ServiceId = sv.ServiceId 
          inner join ServiceProviders sp on tr.ServiceProviderId = sp.ServiceProviderId
          inner join ServiceProviderRepresentatives spr on tr.ServiceProviderCompany = spr.ServiceProviderRepresentativeId
          where tr.TransactionId = :transactionid");

$stmt1->execute(["transactionid" => $trID, "servicedate" => $sD]);
$row1 = $stmt1->fetch(PDO::FETCH_ASSOC);

$companyLogo = "../../img/" . $row1['SPRName'] . ".png";
$hours = date("g:ia", strtotime($row1['Open'])) . " to " . date("g:ia", strtotime($row1['Close']));

$petOwner = "No";
$ab_link = "http://www.apartmentbutler.com/Ops/viewService.php?trID=" . $trID . "&sD=" . $sD . "&sT=All";
$informABFunc = "informAB('" . $ab_link . "', '" . $row1['SPRName'] . "')";
$resetPickUpFunc = "resetPickUp(" . $trID . ", '" . $ab_link . "', '" . $row1['SPRName'] . "')";
$resetDropOffFunc = "resetDropOff(" . $trID . ", '" . $ab_link . "', '" . $row1['SPRName'] . "')";

$stmt7 = $link->prepare("select Name from Customer_Pets where CustomerId = :custid");
$stmt7->execute(["custid" => $row1['CustomerId']]);
$row7 = $stmt7->fetchAll();
if($row7) {
    $petOwner = "Yes";
}


    $stmt3 = $link->prepare("select PreferredName from ServiceProviders where ServiceProviderId = :serviceproviderid");
    $stmt3->execute(["serviceproviderid" => $row1['ServiceProviderId']]);
    $row3 = $stmt3->fetch(PDO::FETCH_ASSOC);
    $servProv = $row3['PreferredName'] . " (ID: " . $row1['ServiceProviderId'] . ")";  
    $informCustomer = "informCustomer(" . $row1['CustomerId'] . ")";
    
    $stmt8 = $link->prepare("select count(*) as dw from Transactions where CustomerId = :customerid and ServiceStatus != 'CANCELED' and ServiceDate < :servicedate and ServiceId in
                             (select ServiceId from Services where ServiceType = 'Dog Walking');");
    $stmt8->execute(["customerid" => $row1['CustomerId'], "servicedate" => $sD]);
    $row8 = $stmt8->fetch(PDO::FETCH_ASSOC);
    
    $stmt9 = $link->prepare("select count(*) as hk from Transactions where CustomerId = :customerid and ServiceStatus != 'CANCELED' and ServiceDate < :servicedate and ServiceId in
                             (select ServiceId from Services where ServiceType = 'Housekeeping' or ServiceType = 'HousekeepingDeep');");
    $stmt9->execute(["customerid" => $row1['CustomerId'], "servicedate" => $sD]);
    $row9 = $stmt9->fetch(PDO::FETCH_ASSOC);
    
    $stmt10 = $link->prepare("select count(*) as dc from Transactions where CustomerId = :customerid and ServiceStatus != 'CANCELED' and ServiceDate < :servicedate and ServiceId in
                             (select ServiceId from Services where ServiceType = 'Dry Cleaning');");
    $stmt10->execute(["customerid" => $row1['CustomerId'], "servicedate" => $sD]);
    $row10 = $stmt10->fetch(PDO::FETCH_ASSOC);
    
    $customerDetails = $row1['CustomerId'] . " | " . $row1['FirstName'] . " " . $row1['LastName'] . " | " . $row1['Email'] . " | " . $row1['MobilePhone'] . " | DW: " . $row8['dw'] . " | HK:"
                        . $row9['hk'] . " | DC: " . $row10['dc'];
    
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
    <title>DC SP Manager: View Service</title>
</head>

<body>

    <div class="slider_cont">
        <div class="container">
            <ul class="bxslider">
                <li>
                    <div class="outer_content">
                         <div class="inner_content">
                            <div class="botton_line"><img style = "position: absolute; right: 20px; top: 20px;"
                            src = "<?php echo $companyLogo; ?>" class = "right_section img-responsive" width = "170" height = "100">
                            MANAGER: SERVICE DETAILS</div>
                            <div class="botton_line" style = "font-size: 25px;"><?php echo date('D M j Y', strtotime($sD)) . " | " . $row1['Name'];  ?></div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <br/><br/>
    
    <div class="container">
        <div class = "row">
           <div class="col-xs-9">
                <button class = "btn btn-warning" name = "dashboard" onclick = "window.location.href = '<?php echo $toDashboard; ?>'" style = "font-size: 16px;"><img src = "../../img/back.png" width = "20" height = "20">&nbsp;<b>Dashboard</b></button>
                <button class = "btn btn-warning" name = "scheduler" onclick = "window.location.href = '<?php echo $toScheduler; ?>'" style = "font-size: 16px;"><img src = "../../img/back.png" width = "20" height = "20">&nbsp;<b>Scheduler</b></button><br/>    
            </div>
           <div class="col-xs-3">
                <div id="google_translate_element"></div>
           </div>
        </div>
    </div>
    
    <div class="container">
       <br/><br/>
                    <h4 class="title"><?php
                    date_default_timezone_set("America/Chicago");
                    echo date('F j, Y', strtotime($sD));
                    ?>
                    </h4>
                    <h3><a href="#" id="popoveraptnotes" class="button blue"><?php echo $row1['ApartmentComplexName']; ?></a>
                    <?php
                    if($row1['DCNotes'] != "") { ?>
                    <img src = "../../img/info.png" width = "25" height = "25" alt = "More Info" />
                    <?php    
                    }
                    ?>
                    </h3>   
                    <h4><?php echo $row1['Address'] . ", " . $row1['City'] . ", " . $row1['State'] . " " . $row1['ZipCode']; ?></h4>
                    <h4>Unit Number: <?php echo $row1['UnitNumber']; ?></h4>
                    <h4>Apartment Hours: <?php echo $hours; ?></h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>SERVICE PROVIDER</th>
                                    <th>SERVICE</th>
                                    <th>PRICE</th>
                                    <th>PRIOR DC</th>
                                    <th>CUST ID</th>
                                    <th>CUST NAME</th>
                                    <th>PETS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo $servProv; ?></td>
                                    <td><?php echo $row1['ServiceName']; ?></td>
                                    <td><?php echo $row1['Price']; ?></td>
                                    <td>
                                        <?php
                                            $stmt11 = $link->prepare("select count(*) as priors from Transactions where CustomerId = :custid and ServiceId in
                                                                     (select ServiceId from Services where ServiceType = 'Dry Cleaning')
                                                                     and ServiceDate < :servicedate and ServiceStatus != 'CANCELED';");
                                            $stmt11->execute(["custid" => $row1['CustomerId'], "servicedate" => $sD]);
                                            $row11 = $stmt11->fetch(PDO::FETCH_ASSOC);
                                            
                                            echo $row11['priors'];
                                        ?>
                                    </td>
                                    <td><?php echo $row1['CustomerId']; ?></td>
                                    <td><a id = "popover" class = "blue button"><?php echo $row1['FirstName'];
                                    if($row1['custDCNotes'] != "") { ?>
                                        <img src = "../../img/info.png" width = "20" height = "20" alt = "More Info" />    
                                    <?php
                                    }
                                    ?></a></td>
                                    <td><?php echo $petOwner; ?></td>                                    
                                </tr>
                            </tbody>
                        </table>
                        
                    </div>
                    <?php
                    
                    $notes = "";
                    if($row1['Notes'] == "") {
                        $notes = "No notes from this customer.";
                    }
                    else {
                        $notes = $row1['Notes'];
                    }
                    if($row1['custDCNotes'] != "") { ?>
                    <h4><b>Petmanent Customer Notes: </b><?php echo $row1['custDCNotes']; ?></h4>
                    <?php    
                    }
                    ?>
                    <h4><b>Today's Notes: </b><?php echo $notes; ?></h4>
                    
                    <h4>Picked up at: <?php
                    if($row1['ClockedIn'] != "") {
                        echo date("F j, Y, g:i a", strtotime($row1['ClockedIn']));
                    }
                    else {
                        echo "N/A";
                    }
                    ?>
                    <button class = "btn btn-warning btn-xs" onclick = "<?php echo $resetPickUpFunc; ?>">Reset</button>
                    </h4>
                    <h4>Dropped off at: <?php
                    if($row1['ClockedOut'] != "") {
                        echo date("F j, Y, g:i a", strtotime($row1['ClockedOut']));
                    }
                    else {
                        echo "N/A";
                    }
                    ?>
                    <button class = "btn btn-warning btn-xs" onclick = "<?php echo $resetDropOffFunc; ?>">Reset</button>
                    </h4>                    
              
    </div>
    
     
    <div class="container">
         <textarea cols = "35" rows = "5" placeholder = "Inform Apartment Butler." id = "message_ab"></textarea><br/><br/>
         <button class = "btn btn-warning" name = "inform_ab" onclick =
         "<?php echo $informABFunc; ?>" 
         style = "background-color: #b3b3b3; border-color: #737373" onmouseover= "this.style.backgroundColor = '#737373'"
                                    onmouseout = "this.style.backgroundColor = '#b3b3b3'">Send Message</button><br/>
    </div><br/><br/>
    
    <div id="popover-head" class="hide"><h4>Apt Notes</h4></div>
    <div id="popover-content" class="hide">
        
            <textarea rows = "3" cols = "30" id = "apt_notes" style = "color: black;"><?php echo $row1['DCNotes']; ?></textarea>
            <p></p>
            <button class = "btn btn-warning" id="submit_apt_notes" onclick = "submitAptNotes();">Submit</button>
        
    </div>
    
    <div id="popover-head2" class="hide"><h4>Cust Notes</h4></div>
    <div id="popover-content2" class="hide">
        
            <textarea rows = "3" cols = "30" id = "cust_notes" style = "color: black;"><?php echo $row1['custDCNotes']; ?></textarea>
            <p></p>
            <button class = "btn btn-warning" id="submit_cust_notes" onclick = "submitCustNotes();">Submit</button>
        
    </div>



    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/bootstrap.min.js"></script>
    <script src="../../js/jquery.bxslider.min.js"></script>
    <script src = "js/viewService.js"></script>
    <script type="text/javascript">

    function submitAptNotes() {
    $.ajax({
        type: 'post',
        url: 'fetch_data.php',
        data: {
            san_notes: $('#apt_notes').val(),
            san_aptid: '<?php echo $row1["ApartmentId"]; ?>'
        },
        success: function(response) {
          alert(response);
          location.reload();
        }
    });
   }
   
   
    
    function submitCustNotes() {
    $.ajax({
        type: 'post',
        url: 'fetch_data.php',
        data: {
            scn_notes: $('#cust_notes').val(),
            scn_custid: '<?php echo $row1["CustomerId"]; ?>'
        },
        success: function(response) {
          alert(response);
          location.reload();
        }
    });
   }

   function informAB(link, sname) {
        var information = $('#message_ab').val();
        if(information) {
            var userAgent = navigator.userAgent || navigator.vendor || window.opera;
           
            if (/android/i.test(userAgent)) {
                $('#message_ab').val("");
                $('#message_ab').attr("placeholder", "Inform Apartment Butler.");
                window.location.href = "sms:+18174422677?body=" + '<?php echo $trID; ?>' + '@@ ' + information;
            }
        
            // iOS detection from: http://stackoverflow.com/a/9039885/177710
            if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
                $('#message_ab').val("");
                $('#message_ab').attr("placeholder", "Inform Apartment Butler.");
                window.location.href = "sms:+18174422677&body=" + '<?php echo $trID; ?>' + '@@ ' + information;
            }
            else {
                $.ajax({
                type: 'post',
                url: 'fetch_data.php',
                data: {
                    ab_link: link,
                    ab_sname: sname,
                    ab_info: information,
                    ab_spcid: '<?php echo $spcID; ?>',
                    ab_trid: '<?php echo $trID; ?>'
                },
                success: function(response) {
                  $('#message_ab').val("");
                  $('#message_ab').attr("placeholder", "Inform Apartment Butler.");
                  alert('Successfully notified Apartment Butler.');
                }
                });
            }
            
        }
        else {
            alert('No blank messages please.');
        }
    }
    
    </script>
    <script type="text/javascript">
    function googleTranslateElementInit() {
      new google.translate.TranslateElement({pageLanguage: 'en', includedLanguages: 'en,es', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
    }
    </script>
    
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

</body>

</html>
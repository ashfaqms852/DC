<?php
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
date_default_timezone_set("America/Chicago");

require_once "../../../api/include/dbconnect.php";

$spcID = isset($_GET['spcID'])? $_GET['spcID'] : 0;

$stmt1 = $link->prepare("select FirstName from ServiceProviderRepresentatives where
                        ServiceProviderRepresentativeId = :sprID");
$stmt1->execute(["sprID" => $spcID]);
$row1 = $stmt1->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['serv_date'])) {
  $servDate = $_POST['serv_date'];
}
else {
  if(isset($_GET['sD'])) {
    $servDate = $_GET['sD'];
  }
  else {
    $servDate = date('Y-m-d');
  }
}

$companyLogo = "../../img/" . $row1['FirstName'] . ".png";
$toDashboard = "dashboard.php?spcID=" . $spcID . "&sD=" . $servDate;
$toScheduler = "scheduler.php?spcID=" . $spcID . "&sD=" . $servDate;
$toLookUpTransactions = "lookUpTransactions.php?spcID=" . $spcID . "&sD=" . $servDate;
$toListApartments = "listApartments.php?spcID=" . $spcID . "&sD=" . $servDate;
$informABFunc = "informAB('link', '" . $row1['FirstName'] . "')";

$stmt11 = $link->prepare("select count(*) as services from Transactions tr where tr.ServiceProviderCompany = :spc and tr.ServiceProviderId = :serviceproviderid
                         and tr.ServiceDate = :servicedate and (tr.ServiceStatus = 'YES' or tr.ServiceStatus = 'NO') and tr.ServiceId in
                         (select sv.ServiceId from Services sv where sv.ServiceType = 'Dry Cleaning')");
$stmt11->execute(["spc" => $spcID, "serviceproviderid" => 0, "servicedate" => $servDate]);
$row11 = $stmt11->fetch(PDO::FETCH_ASSOC);

$stmt10 = $link->prepare("select count(*) as services from Transactions tr inner join Services sv on tr.ServiceId = sv.ServiceId where tr.ServiceProviderCompany = :spc
                         and tr.ServiceProviderId != :spid and tr.ServiceDate = :servicedate and (tr.ServiceStatus = 'YES' or tr.ServiceStatus = 'NO') and sv.ServiceType = :servicetype");
$stmt10->execute(["spc" => $spcID, "spid" => 0, "servicedate" => $servDate, "servicetype" => "Dry Cleaning"]);
$row10 = $stmt10->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="expires" content="0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->

    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet">
    <link href="../../css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/css/bootstrap-select.min.css">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>

    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
   
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>


    <title>DC SP Manager: Dashboard</title>
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
                            MANAGER: DASHBOARD</div>
                            <div class="botton_line" style = "font-size: 25px;"><?php echo date('D M j Y', strtotime($servDate));  ?></div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <br/>
    
    <div class = "container">
        <ul class = "nav nav-tabs">
            <li class = "active"><a href = "<?php echo $toDashboard; ?>">Dashboard</a></li>
            <li><a href = "<?php echo $toScheduler; ?>">Scheduler</a></li>
            <li><a href = "<?php echo $toListApartments; ?>">Apartments</a></li>
            <li><a href = "<?php echo $toLookUpTransactions; ?>">Search</a></li>
        </ul>
    </div>
    
    <br/>
    <div class="container">
      <div class = row>

        <div class = "col-xs-7">
                <form action = "<?php echo $toDashboard; ?>" method = "post">
                  <label for = "serv_date"><b style = "font-size: large;">Services On: </b></label>
                  <input type = "date" name = "serv_date" value = "<?php echo $servDate; ?>">
            
                  <button type = "submit" class = "btn btn-secondary" style = "padding-top: 4px; padding-bottom: 0px;padding-left: 6px;padding-right: 5px;
                          background-color: #b3b3b3; border-color: #737373" onmouseover= "this.style.backgroundColor = '#737373'" onmouseout = "this.style.backgroundColor = '#b3b3b3'">
                  <span class="glyphicon glyphicon-search"></span>
                  </button> 

                </form>
        </div>
        <div class = "dropdown col-xs-5">
                 <a style = "cursor: pointer;" onclick = "showLegend()" id = "showlegend">Show Legend</a>
        
        </div>
      </div>
    </div>
    
    <br/><br/>
                <div class = "container">
                  <div class = "row">
                    <div class = "col-xs-12">
                      <div id = btns>
                        <?php
                        $stmt12 = $link->prepare("select sp.ServiceProviderId, sp.PreferredName, sp.ServiceProviderCompany, count(tr.TransactionId) as bookings from ServiceProviders sp left join Transactions tr
                                                 on sp.ServiceProviderId = tr.ServiceProviderId                                                
                                                 and tr.ServiceDate = :servicedate and tr.ServiceStatus != :servicestatus
                                                 and tr.ServiceId in (select ServiceId from Services where ServiceType = 'Dry Cleaning')
                                                 group by sp.ServiceProviderId order by sp.PreferredName");
                        
                        $stmt12->execute(["servicedate" => $servDate, "servicestatus" => "CANCELED"]);
                        $count12 = 0;
                        
                        while($row12 = $stmt12->fetch(PDO::FETCH_ASSOC)) {
                          if($row12['ServiceProviderCompany'] == $spcID) {
                            $createButton = "createButton(" . $row12['ServiceProviderId'] . ", '" . $row12['PreferredName'] . "')";
                          ?>
                         
                         <button type="button" class="btn btn-primary" style = "border-radius: 20px;" onclick = "<?php echo $createButton; ?>"><?php echo $row12['PreferredName'] . " "; ?>
                         <span class="badge"><?php echo $row12['bookings']; ?></span></button>
                         
                        
                        <?php
                          }
                        } ?>
                        
                      </div>
                    
                    <div id = "txtarea">
                    </div>
                    
                  </div>
                </div>
                </div>
                
               
          <br/><br/>
    
  
  <div class="container" id = "legend">
  <div class = "well well-sm">
                        <span><b>Legend: </b></span>
                        Late For Apt Hours: <img src = "../../img/apt_late.png" width = "15" height = "15">&nbsp;&nbsp;
                        Late For Service: <img src = "../../img/serv_late.png" width = "15" height = "15">&nbsp;&nbsp;
                        <a onclick = "closeLegend()" style = "float: right; cursor: pointer;"><b>x</b></a>
    </div></div>
    
      


    <div class="container" id = "scroll">
                <div class="table-responsive well well-sm" style = "background-color: #d8f3e5;">                           
                      <span><b style = "font-size: large;">Unassigned Services</b>&nbsp;&nbsp;
                      <span class = "badge" style = "font-size: 15px;"><?php echo $row11['services']; ?></span></span>
                      <img src = "../../img/down.png" name = "down" height = "20" width = "20" data-toggle = "collapse" data-target = "#unassigned" id = "updown" onclick = "toggleUpDown('updown')">
                  
                  
                  <?php
                      $collapse11 = "collapse";
                      if($row11['services'] == 0) {
                          $collapse11 = "collapse in";
                      }         
                  ?>
                  
                  <div id = "unassigned" class = "<?php echo $collapse11; ?>">
                        <table class="table table-hover" style = "border-collapse: collapse;">
                            <thead>
                                <tr style = "padding-top: 0;">
                                    <th style = "padding-top: 0;">TR_ID</th>
                                    <th style = "padding-top: 0;">APT</th>
                                    <th style = "padding-top: 0;">CUST_ID</th>
                                    <th style = "padding-top: 0;">SERVICE</th>
                                    <th style = "padding-top: 0;">UNIT</th>                                    
                                    <th style = "padding-top: 0;">REGION</th>
                                    <th style = "padding-top: 0;">NOTES</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $stmt2 = $link->prepare("select tr.TransactionId, sp.Name, ac.ApartmentComplexName, cs.UnitNumber, sv.ServiceName, sv.ServiceType,
                                  tr.ServiceTime, sp.ServiceProviderId, tr.ServiceId, ac.Region, tr.TransactionType, tr.CustomerId, tr.Notes, cs.DCNotes,
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
                                  where tr.ServiceDate = :servicedate and tr.ServiceProviderCompany = :serviceprovidercompany and
                                  (tr.ServiceStatus = 'YES' or tr.ServiceStatus = 'NO' or tr.ServiceStatus = 'RECLEAN')
                                  and sv.ServiceType = 'Dry Cleaning' and tr.ServiceProviderId = :serviceproviderid
                                  order by tr.NoofPetsWalk, ac.Region, ac.ApartmentComplexName, cs.UnitNumber");
                            
                            $stmt2->execute(["servicedate" => $servDate, "serviceprovidercompany" => $spcID, "serviceproviderid" => 0]);
                            while($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                                $hours1 = date("g:ia", strtotime($row2['Open'])) . " to " . date("g:ia", strtotime($row2['Close']));
                                $viewService1 = "viewService.php?spcID=" . $spcID . "&trID=" . $row2['TransactionId'] . "&sD=" . $servDate;
                                $n = explode('@clockout', $row2['Notes']);
                                $popnotes1 = $n[0] == "" ? "N/A" : $n[0];
                                $popnotes1 .= " || " . $row2['DCNotes'];
                                //$popnotes1 = explode(' ', $popnotes1, 4);
                                $displayNotes1 = substr($popnotes1, 0, 20);
                                if(strlen($popnotes1) > 20) $displayNotes1 .= "....";
                            ?>
                                <tr style = "cursor: pointer; padding-top: 0;">
                                    <td onclick = "window.location.href = '<?php echo $viewService1; ?>'" style = "padding-top: 0;"><?php echo $row2['TransactionId']; ?></td>
                                    <td onclick = "window.location.href = '<?php echo $viewService1; ?>'" style = "padding-top: 0;"><?php echo $row2['ApartmentComplexName']; ?></td>
                                    <td onclick = "window.location.href = '<?php echo $viewService1; ?>'" style = "padding-top: 0;"><?php echo $row2['CustomerId']; ?></td>
                                    <td onclick = "window.location.href = '<?php echo $viewService1; ?>'" style = "padding-top: 0;"><?php echo $row2['ServiceName']; ?></td>
                                    <td onclick = "window.location.href = '<?php echo $viewService1; ?>'" style = "padding-top: 0;"><?php echo $row2['UnitNumber']; ?></td>
                                    <td onclick = "window.location.href = '<?php echo $viewService1; ?>'" style = "padding-top: 0;"><?php echo $row2['Region']; ?></td>                                
                                    <td style = "padding-top: 0"><a data-toggle = "popover1" data-placement = "bottom"
                                    data-content = "<?php echo $popnotes1; ?>">
                                    <?php  echo $displayNotes1; ?>
                                    </a></td>
                                </tr>
                            <?php
                            }
                            ?>
                            </tbody>
                        </table>
          
                </div>
        </div>
    </div>
    
    

    <div class="container">
                <div class="table-responsive well well-sm" style = "background-color: #d8f3e5;">                                    
                      <span><b style = "font-size: large;">Assigned Services</b>&nbsp;&nbsp;
                      <span class = "badge" style = "font-size: 15px;"><?php echo $row10['services']; ?></span></span>
                      <img src = "../../img/down.png" name = "down" height = "20" width = "20" data-toggle = "collapse" data-target = "#assigned" id = "updown1" onclick = "toggleUpDown('updown1')">
                      
                        <?php
                        $collapse10 = "collapse";
                        if($row10['services'] == 0) {
                            $collapse10 = "collapse in";
                        }         
                        ?>                      
                        <div id = "assigned" class = "<?php echo $collapse10; ?>">
                        <table class="table table-hover" style = "border-collapse: collapse; cursor: pointer;">
                            <thead>
                                <tr style = "padding-top: 0;">
                                    <th style = "padding-top: 0;">TR_ID</th>
                                    <th style = "padding-top: 0;">SP</th>
                                    <th style = "padding-top: 0;"></th>
                                    <th style = "padding-top: 0;">APT</th>
                                    <th style = "padding-top: 0;">CUST_ID</th>
                                    <th style = "padding-top: 0;">SERVICE</th>
                                    <th style = "padding-top: 0;">UNIT</th>                                    
                                    <th style = "padding-top: 0;">REGION</th>
                                    <th style = "padding-top: 0;">NOTES</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            
                                                       
                            $stmt5 = $link->prepare("select tr.TransactionId, tr.ServiceProviderId, spr.FirstName, ac.ApartmentComplexName, tr.OnTheWay, tr.CustomerId,
                                    cs.UnitNumber, sv.ServiceName, tr.ServiceTime, ac.Region, sp.PreferredName, tr.ClockedIn, tr.ClockedOut, sv.ServiceType, tr.TransactionType,
                                    tr.Notes, cs.DCNotes,
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
                                    inner join ServiceProviderRepresentatives spr on tr.ServiceProviderCompany = spr.ServiceProviderRepresentativeId
                                    where tr.ServiceProviderCompany = :spcID and tr.ServiceDate = :servicedate and
                                    (tr.ServiceStatus = 'YES' or tr.ServiceStatus = 'NO' or tr.ServiceStatus = 'RECLEAN')
                                    and sv.ServiceType = 'Dry Cleaning' and tr.ServiceProviderId != :serviceproviderid 
                                    order by tr.ServiceProviderId, tr.NoofPetsWalk, ac.Region, ac.ApartmentComplexName, cs.UnitNumber");
                            $stmt5->execute(["spcID" => $spcID, "servicedate" => $servDate, "serviceproviderid" => 0]);
                            $dateZero = date('Y-m-d H:i:s', 0);
                            
                           
                            
                            while($row5 = $stmt5->fetch(PDO::FETCH_ASSOC)) {
                               $hours2 = date("g:ia", strtotime($row5['Open'])) . " to " . date("g:ia", strtotime($row5['Close']));
                               $viewService = "viewService.php?spcID=" . $spcID . "&trID=" . $row5['TransactionId'] . "&sD=" . $servDate;
                             
                              $greyOut = ""; $arrivingLate = 0; $lateAptHours = 0;
                              
                               $n = explode('@clockout', $row5['Notes']);
                               
                               $popnotes = $n[0] == "" ? "N/A" : $n[0];
                               $popnotes .= " || " . $row5['DCNotes'];
                               $displayNotes = substr($popnotes, 0, 20);
                               if(strlen($popnotes) > 20) $displayNotes .= "....";
                            
                              
                              if($row5['ServiceName'] == "DryCleaning Drop-Off") {
                                $greyOut = "style = 'background-color:rgb(204,204,204);'";
                                
                                if($row5['ClockedOut'] != "") {
                                  $greyOut = "style = 'background-color:rgb(140,140,140);'";
                                  if(date('H:i:s', strtotime($row5['ClockedOut'])) > $row5['Close']){
                                   $lateAptHours = 1;
                                  }
                                  if(date('H:i:s', strtotime($row5['ClockedOut'])) > '16:00:00') {
                                     $arrivingLate = 1;                                  
                                  }
                                }
                                
                                if($row5['ClockedOut'] == "") {
                                  if($servDate == date('Y-m-d')) {
                                    if(round((strtotime('16:00:00') - strtotime(date('H:i:s'))) / 60,2) < 10.0) {
                                      $arrivingLate = 1;
                                      $greyOut = "style = 'background-color:rgb(255,179,179);'";           
                                    }
                                    if(round((strtotime($row5['Close']) - strtotime(date('H:i:s'))) / 60,2) < 10.0) {
                                      $lateAptHours = 1;
                                      $greyOut = "style = 'background-color:rgb(255,179,179);'";           
                                    } 
                                  }
                                }
                                
                              }
                              else {
                                if($row5['ClockedIn'] != "") {
                                  $greyOut = "style = 'background-color:rgb(204,204,204);'";
                                  if(date('H:i:s', strtotime($row5['ClockedIn'])) > $row5['Close']){
                                   $lateAptHours = 1;
                                  }
                                  if(date('H:i:s', strtotime($row5['ClockedIn'])) > '16:00:00') {
                                     $arrivingLate = 1;                                  
                                  }
                                }
                                
                                if($row5['ClockedIn'] == "") {
                                  if($servDate == date('Y-m-d')) {
                                    if(round((strtotime('16:00:00') - strtotime(date('H:i:s'))) / 60,2) < 10.0) {
                                      $arrivingLate = 1;
                                      $greyOut = "style = 'background-color:rgb(255,179,179);'";           
                                    }
                                    if(round((strtotime($row5['Close']) - strtotime(date('H:i:s'))) / 60,2) < 10.0) {
                                      $lateAptHours = 1;
                                      $greyOut = "style = 'background-color:rgb(255,179,179);'";           
                                    } 
                                  }
                                }                                
                              }

                              
                              
                            ?>
                                <tr <?php echo $greyOut; ?>>
                                    <td onclick = "window.location.href = '<?php echo $viewService; ?>'" style = "padding-top: 0;"><?php echo $row5['TransactionId']; ?></td>
                                    <td onclick = "window.location.href = '<?php echo $viewService; ?>'" style = "padding-top: 0;"><?php echo $row5['PreferredName']; ?></td>
                                    <td onclick = "window.location.href = '<?php echo $viewService; ?>'" style = "padding-top: 0;">
                                    <?php  
                                    if($arrivingLate == 1) { ?>
                                      <img src="../../img/serv_late.png" alt="" height="15" width="15">
                                    <?php }
                                    else { ?>
                                      <img src="../../img/transparent.png" alt="" height="15" width="15">
                                    <?php
                                    }
                                    if($lateAptHours == 1)  { ?>
                                      <img src="../../img/apt_late.png" alt="" height="15" width="15">
                                    <?php }
                                    else { ?>
                                      <img src="../../img/transparent.png" alt="" height="25" width="25">
                                    <?php
                                    }
                                    ?>
                                    </td>
                                    <td onclick = "window.location.href = '<?php echo $viewService; ?>'" style = "padding-top: 0;"><?php echo $row5['ApartmentComplexName']; ?></td>
                                    <td onclick = "window.location.href = '<?php echo $viewService; ?>'" style = "padding-top: 0;"><?php echo $row5['CustomerId']; ?></td>
                                    <td onclick = "window.location.href = '<?php echo $viewService; ?>'" style = "padding-top: 0;"><?php echo $row5['ServiceName']; ?></td>
                                    <td onclick = "window.location.href = '<?php echo $viewService; ?>'" style = "padding-top: 0;"><?php echo $row5['UnitNumber']; ?></td>
                                    <td onclick = "window.location.href = '<?php echo $viewService; ?>'" style = "padding-top: 0;"><?php echo $row5['Region']; ?></td>
                                    <td style = "padding-top: 0"><a data-toggle = "popover" data-placement = "bottom"
                                    data-content = "<?php echo $popnotes; ?>">
                                    <?php  echo $displayNotes; ?>
                                    </a></td>
                                   
                                    
                                </tr>
                            <?php
                            }
                            ?>
                            
                            </tbody>                            
                            </tbody>
                        </table>
          </div>
        </div>
    </div>
  
    <div class="container">
      <textarea cols = "35" rows = "5" placeholder = "Inform Apartment Butler." id = "message_ab"></textarea><br/><br/>
      <button class = "btn btn-warning" name = "inform_ab" onclick =
      "<?php echo $informABFunc; ?>" 
      style = "background-color: #b3b3b3; border-color: #737373" onmouseover= "this.style.backgroundColor = '#737373'"
                                 onmouseout = "this.style.backgroundColor = '#b3b3b3'">Send Message</button><br/>
   </div><br/><br/>

  <script src = "js/dashboard.js"></script>  
  <script type="text/javascript">
    function googleTranslateElementInit() {
      new google.translate.TranslateElement({pageLanguage: 'en', includedLanguages: 'en,es', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
    }
  </script>
    
  <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
  <script>     
       function informAB(link, sname) {
          var information = $('#message_ab').val();
          if(information) {
              var userAgent = navigator.userAgent || navigator.vendor || window.opera;
             
              if (/android/i.test(userAgent)) {
                  $('#message_ab').val("");
                  $('#message_ab').attr("placeholder", "Inform Apartment Butler.");
                  window.location.href = "sms:+18174422677?body=0@@" + information;
              }
          
              if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
                  $('#message_ab').val("");
                  $('#message_ab').attr("placeholder", "Inform Apartment Butler.");
                  window.location.href = "sms:+18174422677&body=0@@ " + information;
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
                      ab_trid: 0
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
</body>
</html>
<?php
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
date_default_timezone_set("America/Chicago");

require_once "../../../api/include/dbconnect.php";
$spID = isset($_GET['spID'])? $_GET['spID'] : 0;

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

$toServForToday = "servicesForToday.php?spID=" . $spID . "&sD=" . $servDate;

try {
    $stmt1 = $link->prepare("select tr.TransactionId, sp.Name, ac.ApartmentComplexName, cs.UnitNumber, sv.ServiceName, sv.ServiceType,
          tr.ServiceTime, sp.ServiceProviderId, tr.ServiceId, ac.Region, tr.ClockedIn, tr.ClockedOut, cs.CustomerId, tr.Notes,
          case weekday(curdate())
          when 0 then MondayOpen when 1 then TuesdayOpen when 2 then WednesdayOpen when 3 then ThursdayOpen when 4 then FridayOpen
          when 5 then SaturdayOpen when 6 then SundayOpen end as Open,
          case weekday(curdate())
          when 0 then MondayClose when 1 then TuesdayClose when 2 then WednesdayClose when 3 then ThursdayClose when 4 then FridayClose
          when 5 then SaturdayClose when 6 then SundayClose end as Close
          from Transactions tr inner join Customers cs on tr.CustomerId = cs.CustomerId
          inner join ApartmentComplexes ac on cs.ApartmentId = ac.ApartmentId
          inner join Services sv on tr.ServiceId = sv.ServiceId 
          inner join ServiceProviders sp on tr.ServiceProviderId = sp.ServiceProviderId
          where tr.ServiceDate = :servicedate and tr.ServiceProviderId = :serviceproviderid and
          (tr.ServiceStatus = 'YES' or tr.ServiceStatus = 'NO' or tr.ServiceStatus = 'RECLEAN')
          and sv.ServiceType = 'Dry Cleaning' 
          order by tr.NoofPetsWalk, ac.Region, ac.ApartmentComplexName, cs.UnitNumber");
    
    $stmt1->execute(["servicedate" => $servDate, "serviceproviderid" => $spID]);
    
    $stmt3 = $link->prepare("select Name, Active from ServiceProviders where ServiceProviderId = :serviceproviderid");
    $stmt3->execute(["serviceproviderid" => $spID]);
    $row3 = $stmt3->fetch(PDO::FETCH_ASSOC);
    
}
catch(PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}

?>
<?php

if($row3['Active']) {

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
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <title>DC Runner: Today's Services</title>
</head>


<body>

    <div class="slider_cont">
        <div class="container">
            <ul class="bxslider">
                <li>
                    <div class="outer_content">
                        <div class="inner_content">
                          
                            <div class="botton_line">RUNNER: DASHBOARD</div>
                            <div class="botton_line" style = "font-size: 25px;"><?php echo date('D M j Y', strtotime($servDate)) . " | " . $row3['Name'];  ?></div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <br/><br/>
    
    <div class="container">
      <div class="row">
        <div class = "col-xs-8">
                  <form action = "<?php echo $toServForToday; ?>" method = "post" style = "display: inline;">
                  <label for = "serv_date"><b style = "font-size: large;">Services On: </b></label>
                  <input type = "date" name = "serv_date" value = "<?php echo $servDate; ?>">
                  <button type = "submit" class = "btn btn-secondary" style = "padding-top: 4px; padding-bottom: 0px;padding-left: 6px;padding-right: 5px;
                          background-color: #b3b3b3; border-color: #737373" onmouseover= "this.style.backgroundColor = '#737373'" onmouseout = "this.style.backgroundColor = '#b3b3b3'">
                  <span class="glyphicon glyphicon-search"></span>
                  </button> 

                </form>
        </div>
        
        <div class="col-xs-2">
            <div id="google_translate_element"></div>
        </div>
        <a class = "col-xs-2" style = "display: inline; float: right; margin-right: 0px; margin-top: 2px; cursor: pointer;" onclick = "showLegend()" id = "showlegend">Show Legend</a>
      </div>
    </div>
    <br/>
    
  <div class="container" id = "legend">
  <div class = "well well-sm">
                        <span><b>Legend: </b></span>
                        Service Finished: <img src = "../../img/check.png" width = "15" height = "15">&nbsp;&nbsp;
                        Late For Apt Hours: <img src = "../../img/apt_late.png" width = "15" height = "15">&nbsp;&nbsp;
                        Late For Service: <img src = "../../img/serv_late.png" width = "15" height = "15">&nbsp;&nbsp;
                        Key With Other Runner: <img src = "../../img/key.png" width = "20" height = "20">&nbsp;&nbsp;
                        <a onclick = "closeLegend()" style = "float: right; cursor: pointer;"><b>x</b></a>
    </div></div>
    
    <div class="container">
                <div class="table-responsive well well-sm" style = "background-color: #d8f3e5;">                                   
                        <table class="table table-hover" style = "cursor: pointer;">
                            <thead>
                                <tr>
                                    
                                    <th></th>
                                    <th>APT</th>
                                    <th>UNIT</th>
                                    <th>SERVICE</th>
                                    <th>CUST_ID</th>
                                    <th>STARCH</th>
                                    <th>DELIVERY LOCATION</th>
                                    <th>APT HRS</th>
                                </tr>
                            </thead>
                            <tbody>
                               <?php

                               while($row1 = $stmt1->fetch(PDO::FETCH_ASSOC)) {
                                                               
                                  $greyOut = ""; $arrivingLate = 0; $lateAptHours = 0; $finished = 0;
                                  
                                  if($row1['ServiceName'] == 'DryCleaning Drop-Off') {
                                    $greyOut = "style = 'background-color:rgb(204,204,204);'";
                                    if($row1['ClockedOut'] == "" and $servDate == date('Y-m-d')) {
                                      if(round((strtotime('16:00:00') - strtotime(date('H:i:s'))) / 60,2) < 10.0) {
                                        $arrivingLate = 1;
                                        $greyOut = "style = 'background-color:rgb(255,179,179);'";           
                                      }
                                      if(round((strtotime($row1['Close']) - strtotime(date('H:i:s'))) / 60,2) < 10.0) {
                                        $lateAptHours = 1;
                                        $greyOut = "style = 'background-color:rgb(255,179,179);'";           
                                      } 
                                    }
                                    elseif($row1['ClockedOut'] != "") {

                                      $greyOut = "style = 'background-color:rgb(140,140,140);'";
                                      $finished = 1;
                                      if((date('H:i:s', strtotime($row1['ClockedOut'])) > $row1['Close']) or
                                       (date('H:i:s', strtotime($row1['ClockedIn'])) > $row1['Close'])){
                                          $lateAptHours = 1;
                                       }
                                       if($row1['ServiceTime'] == "9 to 4" and (((date('H:i:s', strtotime($row1['ClockedIn'])) > '16:00:00'))
                                          or (date('H:i:s', strtotime($row1['ClockedOut'])) > '16:00:00'))) {
                                          $arrivingLate = 1;                                   
                                       }
                                    }
                                  }
                                  else {
                                    if($row1['ClockedIn'] == "" and $servDate == date('Y-m-d')) {
                                      if(round((strtotime('16:00:00') - strtotime(date('H:i:s'))) / 60,2) < 10.0) {
                                        $arrivingLate = 1;
                                        $greyOut = "style = 'background-color:rgb(255,179,179);'";           
                                      }
                                      if(round((strtotime($row1['Close']) - strtotime(date('H:i:s'))) / 60,2) < 10.0) {
                                        $lateAptHours = 1;
                                        $greyOut = "style = 'background-color:rgb(255,179,179);'";           
                                      } 
                                    }
                                    elseif($row1['ClockedIn'] != "") {

                                      $greyOut = "style = 'background-color:rgb(204,204,204);'";
                                      $finished = 1;
                                      if(date('H:i:s', strtotime($row1['ClockedIn'])) > $row1['Close']){
                                          $lateAptHours = 1;
                                       }
                                       if($row1['ServiceTime'] == "9 to 4" and (date('H:i:s', strtotime($row1['ClockedIn'])) > '16:00:00')) {
                                          $arrivingLate = 1;                                   
                                       }
                                    }
                                  }
                               
                                          
                                  $hours = date("g:ia", strtotime($row1['Open'])) . " to " . date("g:ia", strtotime($row1['Close']));
                                   
                                ?>
                                <?php $viewService = "viewService.php?spID=" . $row1['ServiceProviderId'] . "&trID=" . $row1['TransactionId'] . "&sD=" . $servDate; ?>
                                <tr onclick = "window.location.href = '<?php echo $viewService; ?>'" <?php echo $greyOut; ?>  style = "cursor: pointer;">
                                    
                                    <td>

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
                                      <img src="../../img/transparent.png" alt="" height="15" width="15">
                                    <?php
                                    }

                                    if($finished == 1) { ?>
                                      <img src="../../img/check.png" alt="" height="15" width="15">
                                    <?php  
                                    }
                                    else { ?>
                                      <img src="../../img/transparent.png" alt="" height="15" width="15">
                                    <?php  
                                    }
                                    
                                    $stmt = $link->prepare("select tr.CustomerId from
                                                           Transactions tr inner join Services sv on tr.ServiceId = sv.ServiceId
                                                           where tr.ClockedIn != '' and tr.ClockedOut is null
                                                           and (sv.ServiceType = 'Housekeeping' or sv.ServiceType = 'HousekeepingDeep' or sv.ServiceType = 'Dog Walking')
                                                           and tr.ServiceDate = :sdate and tr.CustomerId = :custid");
                                    $stmt->execute(["sdate" => $servDate, "custid" => $row1['CustomerId']]);
                                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                    if($row['CustomerId']) { ?>
                                      <img src="../../img/key.png" alt="" height="20" width="20">
                                    <?php  
                                    }
                                    else { ?>
                                      <img src="../../img/transparent.png" alt="" height="15" width="15">   
                                    <?php
                                    }
                                    $dcnotes = explode(';', $row1['Notes']);
                                    if(count($dcnotes) > 2) {
                                      $del_loc = $dcnotes[1] . ", " . $dcnotes[2];
                                    }
                                    else {
                                      $del_loc = $dcnotes[1];
                                    }
                                    
                                    ?>
                                    </td>
                                    
                                    <td><?php echo $row1['ApartmentComplexName']; ?></td>
                                    <td><?php echo $row1['UnitNumber']; ?></td>
                                    <td><?php echo $row1['ServiceName']; ?></td>
                                    <td><?php echo $row1['CustomerId']; ?></td>
                                    <td><?php echo $dcnotes[0]; ?></td>
                                    <td><?php echo $del_loc; ?></td>
                                    <td><?php echo $hours; ?> </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    
        </div>
    </div>
    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/bootstrap.min.js"></script>
    <script src="../../js/jquery.bxslider.min.js"></script>
    <script>
     $(document).ready(function() {
        $('#legend').hide();            
     });        
        
     function closeLegend() {
      $('#legend').hide();
      $('#showlegend').show();
     }
     
     function showLegend() {
      $('#showlegend').hide();
      $('#legend').show();
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
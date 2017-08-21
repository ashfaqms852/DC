<?php
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
date_default_timezone_set("America/Chicago");

require_once "../../../api/include/dbconnect.php";

$spcID = isset($_GET['spcID']) ? $_GET['spcID'] : 0;
$servDate = isset($_GET['sD']) ? $_GET['sD'] : date('Y-m-d');
if(isset($_POST['search_transactions'])) {
    $cid = $_POST['cust_id'];
    $email = $_POST['email'];
    $apt_name = $_POST['apt_name'];
    $unit = $_POST['unit'];
    $start_svc_date = $_POST['start_svc_date'];
    $end_svc_date = $_POST['end_svc_date'];
    $cancellations = isset($_POST['cancellations']) ? "yes" : "";
    $checked = isset($_POST['cancellations']) ? "checked" : "";
    
    $stmt = $link->prepare("call lookup_transactions_from_spc(:custid, :email, :apt_name, :unit, :start_svc_date, :end_svc_date, :cancellations, :spc)");
    $stmt->execute(["custid" => $cid, "email" => $email, "apt_name" => $apt_name, "unit" => $unit, "start_svc_date" => $start_svc_date,
                    "end_svc_date" => $end_svc_date, "cancellations" => $cancellations, "spc" => $spcID]);
}
else {
    $email = ""; $apt_name = ""; $start_svc_date = ""; $end_svc_date = "";
    $stmt = $link->prepare("call lookup_transactions_from_spc('0','','','','','','',0)");
    $stmt->execute([]);
}

$toDashboard = "dashboard.php?spcID=" . $spcID . "&sD=" . $servDate;
$toScheduler = "scheduler.php?spcID=" . $spcID . "&sD=" . $servDate;
$toLookUpTransactions = "lookUpTransactions.php?spcID=" . $spcID . "&sD=" . $servDate;
$toListApartments = "listApartments.php?spcID=" . $spcID . "&sD=" . $servDate;

$recCount = 0;

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
    <!-- DYNATRACE -->
    <script type="text/javascript" src="https://js-cdn.dynatrace.com/jstag/145e12d594f/cvz00316/8408f6a3cebe1e6a_bs.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>

    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
   
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.11.1/bootstrap-table.min.css">
    
    <!-- Latest compiled and minified JavaScript -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.11.1/bootstrap-table.min.js"></script>
    
    <!-- Latest compiled and minified Locales -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.11.1/locale/bootstrap-table-en-US.min.js"></script>
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
    <script src="bootstrap-table-en-US.js"></script>
    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/bootstrap.min.js"></script>
    <script src="../../js/jquery.bxslider.min.js"></script>

    <title>DC Manager: Look Up Transactions</title>
    </head>

<body>

    <div class="slider_cont">
        <div class="container">
            <ul class="bxslider">
                <li>
                    <div class="outer_content">
                         <div class="inner_content">
            
                            <div class="botton_line">
                            DC Manager: Look-Up Trans</div>
                            <div class="botton_line" style = "font-size: 25px;"><?php echo date('D M j Y');  ?></div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <br/>
    
    <div class = "container">
        <ul class = "nav nav-tabs">
            <li><a href = "<?php echo $toDashboard; ?>">Dashboard</a></li>
            <li><a href = "<?php echo $toScheduler; ?>">Scheduler</a></li>
            <li><a href = "<?php echo $toListApartments; ?>">Apartments</a></li>
            <li class = "active"><a href = "<?php echo $toLookUpTransactions; ?>">Search</a></li>
        </ul>
    </div>
    
    <br/>
    
    <div class = "container">
        <form action = "" method = "post">
        <fieldset>
            <div class = "row">
                <div class = "col-xs-2">
                    <label for = "cust_id">CustomerID: </label>
                    <input type = "number" class = "form-control" name = "cust_id" id = "cust_id" min = "0" value = "<?php echo $cid; ?>">
                </div>
                <div class = "col-xs-4">
                    <label for = "cust_email">Email: </label>
                    <input type = "text" class = "form-control" name = "email" id = "email" value = "<?php echo $email; ?>">
                </div>
                <div class = "col-xs-4">
                    <label for = "cust_apt_name">Apt Name: </label>
                    <input type = "text" class = "form-control" name = "apt_name" id = "apt_name" value = "<?php echo $apt_name; ?>">
                </div>
                <div class = "col-xs-2">
                    <label for = "cust_unit">Unit: </label>
                    <input type = "number" class = "form-control" name = "unit" id = "unit" value = "<?php echo $unit; ?>">
                </div>        
            </div>
            
            <div class = "row">
                <div class = "col-xs-12"><p></p></div>
                <div class = "col-xs-12"><p></p></div>
            </div>
            
            <div class = "row">
                <div class = "col-xs-5">
                    <label for = "start_svc_date">Start Service Date: </label>
                    <input type = "text" class = "form-control" name = "start_svc_date" id = "start_svc_date" value = "<?php echo $start_svc_date; ?>"
                    placeholder = "e.g. yyyy-mm-dd, yymmdd, yyyy/mm/dd yy/mm/dd, w, t, t-1, t+1">
                </div>
                <div class = "col-xs-5">
                    <label for = "cust_apt_name">End Service Date: </label>
                    <input type = "text" class = "form-control" name = "end_svc_date" id = "end_svc_date" value = "<?php echo $end_svc_date; ?>"
                    placeholder = "e.g. yyyy-mm-dd, yymmdd, yyyy/mm/dd yy/mm/dd, w, t, t-1, t+1">
                </div>
                <div class = "col-xs-2">
                    <label for = "cancellations">Include cancellations?</label>
                    <input type = "checkbox"  data-toggle = "toggle" data-onstyle = "success" data-on = "Yes" data-off = "No"
                           data-offstyle = "danger" id = "cancellations" data-size="small" name = "cancellations" <?php echo $checked; ?>>
                </div>
                
            </div>
            
            <div class = "row">
            <div class = "col-xs-12"><p></p></div>
            <div class = "col-xs-12"><p></p></div>
            </div>
            
            <div class = "row">
            <div class = "col-xs-12">
            <button class = "btn btn-warning" id = "search_transactions" name = "search_transactions">Search</button>
            <button class = "btn btn-warning" id = "reset" name = "reset">Reset</button>
            </div>
            </div>
            
            

        </fieldset>
        </form>
    </div>
    <hr>
    <strong><p id = "rec_count"><b>0 record(s) found.</b></p></strong>
    <div>
        <div >
            <div id = "" class = "">
                        <table class="table table-hover" style = "border-collapse: collapse; background-color: #d8f3e5;" data-toggle = "table"
                        data-sort-name = "cname" data-sort-order = "asc">
                            <thead>
                                <tr style = "padding-top: 0;">
                                    <th style = "padding-top: 0;" data-field = "apt_name" data-sortable = "true">APT_NAME</th>
                                    <th style = "padding-top: 0;" data-field = "unit" data-sortable = "true">UNIT</th>
                                    <th style = "padding-top: 0;" data-field = "sp_id" data-sortable = "true">SPC</th>
                                    <th style = "padding-top: 0;" data-field = "sp_name" data-sortable = "true">SP_NAME</th>
                                    <th style = "padding-top: 0;" data-field = "cname" data-sortable = "true">CNAME</th>
                                    <th style = "padding-top: 0;" data-field = "mobile" data-sortable = "true">MOBILE</th>
                                    <th style = "padding-top: 0;" data-field = "email" data-sortable = "true">EMAIL</th>
                                    <th style = "padding-top: 0;" data-field = "pet_info" data-sortable = "true">PET_INFO</th>
                                    <th style = "padding-top: 0;" data-field = "serv_name" data-sortable = "true">SERV_NAME</th>
                                    <th style = "padding-top: 0;" data-field = "sp_time" data-sortable = "true">SERV_TIME</th>
                                    <th style = "padding-top: 0;" data-field = "tr_c_notes" data-sortable = "true">TR_NOTES;C_NOTES</th>
                                    <th style = "padding-top: 0;" data-field = "o_date" data-sortable = "true">O_DATE</th>
                                    <th style = "padding-top: 0;" data-field = "s_date" data-sortable = "true">S_DATE</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $recCount++;
                                    ?>
                                    <tr>
                                    <td style = "padding-top: 0;"><?php echo $row['ApartmentComplexName']; ?></td>
                                    <td style = "padding-top: 0;"><?php echo $row['UnitNumber']; ?></td>
                                    <td style = "padding-top: 0;"><?php echo $row['SvcProv_Comp']; ?></td>
                                    <td style = "padding-top: 0;"><?php echo $row['SvcProv_Name']; ?></td>
                                    <td style = "padding-top: 0;"><?php echo $row['FirstName'] . " " . $row['LastName']; ?></td>
                                    <td style = "padding-top: 0;"><?php echo $row['MObilePhone']; ?></td>
                                    <td style = "padding-top: 0;"><?php echo $row['Email']; ?></td>
                                    <td style = "padding-top: 0;"><?php echo $row['PetInfo']; ?></td>
                                    <?php
                                    if($row['ServiceType'] == "HousekeepingDeep") {
                                    ?>
                                        <td style = "padding-top: 0;"><?php echo $row['ServiceName'] . " (Deep)"; ?></td>
                                    <?php
                                    }
                                    else { ?>
                                        <td style = "padding-top: 0;"><?php echo $row['ServiceName']; ?></td>
                                    <?php
                                    }
                                    ?>
                                    <td style = "padding-top: 0;"><?php echo $row['ServiceTime']; ?></td>
                                    <td style = "padding-top: 0;"><?php echo $row['Transaction notes; Customer notes']; ?></td>
                                    <td style = "padding-top: 0;"><?php echo $row['Orderdate']; ?></td>
                                    <td style = "padding-top: 0;"><?php echo $row['ServiceDate']; ?></td>

                                    </tr>
                                    
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
            </div>
        </div>
    </div>
    <script src = "js/lookUpTransactions.js"></script>
    <script>
        $(document).ready(function() {
            $('.bxslider').bxSlider({
                pager: false
            });
            $('#verification').modal('hide');
            $('#rec_count').text('<?php echo $recCount; ?>' + ' record(s) found.');
            
        });
        
    </script>
</body>
</html>
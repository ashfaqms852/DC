<?php
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
date_default_timezone_set("America/Chicago");

require_once "../../../api/include/dbconnect.php";

$spcID = isset($_GET['spcID']) ? $_GET['spcID'] : 0;
$servDate = isset($_GET['sD']) ? $_GET['sD'] : date('Y-m-d');

$stmt1 = $link->prepare("select FirstName from ServiceProviderRepresentatives where
                        ServiceProviderRepresentativeId = :sprID");
$stmt1->execute(["sprID" => $spcID]);
$row1 = $stmt1->fetch(PDO::FETCH_ASSOC);

$companyLogo = "../../img/" . $row1['FirstName'] . ".png";
$toDashboard = "dashboard.php?spcID=" . $spcID . "&sD=" . $servDate;
$toScheduler = "scheduler.php?spcID=" . $spcID . "&sD=" . $servDate;
$toLookUpTransactions = "lookUpTransactions.php?spcID=" . $spcID . "&sD=" . $servDate;
$toListApartments = "listApartments.php?spcID=" . $spcID . "&sD=" . $servDate;
?>

<meta charset="utf-8">
<meta http-equiv="expires" content="0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Bootstrap -->

<link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet">
<link href="../../css/style.css" rel="stylesheet">

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<title>DC SP Manager: Apartments List</title>
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
    <br/><br/>
    
    <div class = "container">
        <ul class = "nav nav-tabs">
            <li><a href = "<?php echo $toDashboard; ?>">Dashboard</a></li>
            <li><a href = "<?php echo $toScheduler; ?>">Scheduler</a></li>
            <li class = "active"><a href = "<?php echo $toListApartments; ?>">Apartments</a></li>
            <li><a href = "<?php echo $toLookUpTransactions; ?>">Search</a></li>
        </ul>
    </div>
    <br/><br/>
    <div class = "container">
        <div class="table-responsive well well-sm" style = "background-color: #d8f3e5;">            
            <table class="table table-hover" style = "border-collapse: collapse;">
                <thead>
                    <tr style = "padding-top: 0;">
                        <th style = "padding-top: 0;">APT NAME</th>
                        <th style = "padding-top: 0;">ADDRESS</th>
                        <th style = "padding-top: 0;">REGION</th>
                        <th style = "padding-top: 0;">OFFICE HRS</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $stmt = $link->prepare("select ApartmentComplexName, Address, City, State, ZipCode, Region, PhoneNumber,
                                           case weekday(:servicedate)
                                           when 0 then MondayOpen when 1 then TuesdayOpen when 2 then WednesdayOpen when 3 then ThursdayOpen when 4 then FridayOpen
                                           when 5 then SaturdayOpen when 6 then SundayOpen end as Open,
                                           case weekday(:servicedate)
                                           when 0 then MondayClose when 1 then TuesdayClose when 2 then WednesdayClose when 3 then ThursdayClose when 4 then FridayClose
                                           when 5 then SaturdayClose when 6 then SundayClose end as Close
                                           from ApartmentComplexes where DCManager = :spcid");
                    $stmt->execute(["servicedate" => $servDate, "spcid" => $spcID]);
                    
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $googleMapsLink = "https://www.google.com/maps?q=";
                        $address = explode(' ', $row['Address']);
                        foreach($address as $a) {
                            $googleMapsLink .= $a . '+';
                        }
                        $googleMapsLink .= $row['City'] . '+' . $row['State'] . '+' . $row['ZipCode'];
                        ?>
                        <tr>
                            <td style = "padding-top: 0;"><?php echo $row['ApartmentComplexName']; ?></td>
                            <td style = "padding-top: 0;"><a target = "_blank" href = "<?php echo $googleMapsLink; ?>">
                            <?php echo $row['Address'] . ", " . $row['City'] . ", " . $row['State'] . " " . $row['ZipCode']; ?></a></td>
                            <td style = "padding-top: 0;"><?php echo $row['Region']; ?></td>
                            <td style = "padding-top: 0;"><?php echo date("g:ia", strtotime($row['Open'])) . " to " . date("g:ia", strtotime($row['Close'])); ?></td>
                        </tr>
                    <?php
                    }
                ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
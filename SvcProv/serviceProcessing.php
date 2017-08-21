<?php
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
date_default_timezone_set("America/Chicago");

require_once "../../../api/include/dbconnect.php";
require_once "../../../api/include/helperFunctions.php";

$helper = new HelperFunction();

if(isset($_POST['pu_trid'])) {
    
    $stmt1 = $link->prepare("select cs.FirstName, cs.MobilePhone, sp.PreferredName, cs.CustomerId
                            from Transactions tr inner join Customers cs on tr.CustomerId = cs.CustomerId
                            inner join ServiceProviders sp on tr.ServiceProviderId = sp.ServiceProviderId 
                            where tr.TransactionId = :trid");
    $stmt1->execute(["trid" => $_POST['pu_trid']]);
    $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);

    $body = "Hi " . $row1['FirstName'] . "! Apartment Butler has picked up your clothes and you will receive an itemized invoice within 24 hours.";
    
    $helper->sendSMSFromCICOLine($row1['MobilePhone'], $body);
    
    $stmt2 = $link->prepare("update Transactions set ClockedIn = :clockedin where TransactionId = :transactionid");
    $stmt2->execute(["clockedin" => date("Y-m-d H:i:s"), "transactionid" => $_POST['pu_trid']]);
    
}

if(isset($_POST['do_trid'])) {

    $stmt3 = $link->prepare("select cs.FirstName, cs.MobilePhone, sp.PreferredName, cs.CustomerId, tr.PaymentStatus
                            from Transactions tr inner join Customers cs on tr.CustomerId = cs.CustomerId
                            inner join ServiceProviders sp on tr.ServiceProviderId = sp.ServiceProviderId 
                            where tr.TransactionId = :trid");
    $stmt3->execute(["trid" => $_POST['do_trid']]);
    $row3 = $stmt3->fetch(PDO::FETCH_ASSOC);

    $body = "Hi " . $row3['FirstName'] . "!  Apartment Butler has dropped off your clean clothes in your home. We hope you found the service useful. Have a great day.";

    $helper->sendSMSFromCICOLine($row3['MobilePhone'], $body);
    
    $recordStatus = "";
    if($row3['PaymentStatus'] == 'YES') {
        $recordStatus = "CLOSED";
    }
    else {
        $recordStatus = "OPEN";
    }

    $stmt4 = $link->prepare("update Transactions set ClockedOut = :clockedout, ServiceStatus = 'YES', RecordStatus = :recordstatus
                            where TransactionId = :transactionid");
    $stmt4->execute(["clockedout" => date("Y-m-d H:i:s"), "recordstatus" => $recordStatus, "transactionid" => $_POST['do_trid']]);        
}

if(isset($_POST['una_trid'])) {
    
    $stmt = $link->prepare("select sp.PreferredName, ac.BelongsToMarket
                           from Transactions tr inner join ServiceProviders sp on tr.ServiceProviderId = sp.ServiceProviderId
                           inner join Customers cs on tr.CustomerId = cs.CustomerId
                           inner join ApartmentComplexes ac on cs.ApartmentId = ac.ApartmentId
                           where tr.TransactionId = :trid");
    $stmt->execute(["trid" => $_POST['una_trid']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $spcDetails = $helper->linkForManager($_POST['una_trid']);
    $numbers = explode(';', $spcDetails['phone']);
    foreach($numbers as $n) {
        $body = "Hello " . $spcDetails['name'] . ", " . $row['PreferredName'] . " has reported an unit accessibility issue. \r\n";
        $body .= "Here's the link - " . $spcDetails['link'];
        
        $helper->sendSMSFromCICOLine($n, $body);
    }
    
    $body = "Hi AB-Ops! " . $row['PreferredName'] . " has reported an accessibility issue. \r\n";
    $body .= "Here's the link - http://www.apartmentbutler.com/Ops/viewService.php?trID=" . $_POST['una_trid'];
    
    $helper->sendToFrontWithTrID($_POST['una_trid'], $spcDetails['name'], $spcDetails['phone'], $row['ServiceProviderCompany'], $body, false);
    $helper->tagWithTrIDInFront($_POST['una_trid']);
    
    echo $spcDetails['name'] . " will be getting back to you soon.";
    exit;
}

if(isset($_POST['ds_trid'])) {
    $stmt = $link->prepare("select sp.PreferredName, ac.BelongsToMarket
                           from Transactions tr inner join ServiceProviders sp on tr.ServiceProviderId = sp.ServiceProviderId
                           inner join Customers cs on tr.CustomerId = cs.CustomerId
                           inner join ApartmentComplexes ac on cs.ApartmentId = ac.ApartmentId
                           where tr.TransactionId = :trid");
    $stmt->execute(["trid" => $_POST['ds_trid']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $spcDetails = $helper->linkForManager($_POST['ds_trid']);
    $numbers = explode(';', $spcDetails['phone']);
    foreach($numbers as $n) {
        $body = "Hello " . $spcDetails['name'] . ", " . $row['PreferredName'] . " has reported declined service by customer. \r\n";
        $body .= "Here's the link - " . $spcDetails['link'];
        
        $helper->sendSMSFromCICOLine($n, $body);
    }
    
    $body = "Hi AB-Ops! " . $row['PreferredName'] . " has reported declined service by customer. \r\n";
    $body .= "Here's the link - http://www.apartmentbutler.com/Ops/viewService.php?trID=" . $_POST['ds_trid'];
    
    $helper->sendToFrontWithTrID($_POST['ds_trid'], $spcDetails['name'], $spcDetails['phone'], $row['ServiceProviderCompany'], $body, false);
    $helper->tagWithTrIDInFront($_POST['ds_trid']);
    
    echo $spcDetails['name'] . " will be getting back to you soon.";
    exit;
}


if(isset($_POST['kri_trid'])) {
    $stmt = $link->prepare("select sp.PreferredName, ac.BelongsToMarket
                           from Transactions tr inner join ServiceProviders sp on tr.ServiceProviderId = sp.ServiceProviderId
                           inner join Customers cs on tr.CustomerId = cs.CustomerId
                           inner join ApartmentComplexes ac on cs.ApartmentId = ac.ApartmentId
                           where tr.TransactionId = :trid");
    $stmt->execute(["trid" => $_POST['kri_trid']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $spcDetails = $helper->linkForManager($_POST['kri_trid']);
    $numbers = explode(';', $spcDetails['phone']);
    foreach($numbers as $n) {
        $body = "Hello " . $spcDetails['name'] . ", " . $row['PreferredName'] . " has reported a key release issue. \r\n";
        $body .= "Here's the link - " . $spcDetails['link'];
        
        $helper->sendSMSFromCICOLine($n, $body);
    }
    
    $body = "Hi AB-Ops! " . $row['PreferredName'] . " has reported a key release issue. \r\n";
    $body .= "Here's the link - http://www.apartmentbutler.com/Ops/viewService.php?trID=" . $_POST['kri_trid'];
    
    $helper->sendToFrontWithTrID($_POST['kri_trid'], $spcDetails['name'], $spcDetails['phone'], $row['ServiceProviderCompany'], $body, false);
    $helper->tagWithTrIDInFront($_POST['kri_trid']);
    
    echo $spcDetails['name'] . " will be getting back to you soon.";
    exit;
}

if(isset($_POST['npu_trid'])) {
       
    $stmt = $link->prepare("select sp.PreferredName, ac.BelongsToMarket, cs.FirstName, cs.MobilePhone
                           from Transactions tr inner join ServiceProviders sp on tr.ServiceProviderId = sp.ServiceProviderId
                           inner join Customers cs on tr.CustomerId = cs.CustomerId
                           inner join ApartmentComplexes ac on cs.ApartmentId = ac.ApartmentId
                           where tr.TransactionId = :trid");
    $stmt->execute(["trid" => $_POST['npu_trid']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $spcDetails = $helper->linkForManager($_POST['npu_trid']);
    $numbers = explode(';', $spcDetails['phone']);
    foreach($numbers as $n) {
        $body = "Hello " . $spcDetails['name'] . ", " . $row['PreferredName'] . " has reported that there are no laundry items for pick up at this stop. \r\n";
        $body .= "Here's the link - " . $spcDetails['link'];
        
        $helper->sendSMSFromCICOLine($n, $body);
    }
    
    $body = "Hi AB-Ops! " . $row['PreferredName'] . " has reported that there are no laundry items for pick up at this stop. \r\n";
    $body .= "Here's the link - http://www.apartmentbutler.com/Ops/viewService.php?trID=" . $_POST['npu_trid'];
    
    $helper->sendToFrontWithTrID($_POST['npu_trid'], $spcDetails['name'], $spcDetails['phone'], $row['ServiceProviderCompany'], $body, false);
    $helper->tagWithTrIDInFront($_POST['npu_trid']);
    
    $bodyForCust = "Hello " . $row['FirstName'] . ", Apartment Butler stopped by for your laundry pick up but we could not locate any items by your front door. \r\n";
    $bodyForCust .= "Please reschedule your laundry pick up in the app.";
    
    $helper->sendSMSFromCICOLine($row['MobilePhone'], $bodyForCust);
    
    echo $spcDetails['name'] . " will be getting back to you soon.";
    exit;

}

?>
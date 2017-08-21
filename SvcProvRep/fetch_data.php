<?php
require_once "../../../api/include/dbconnect.php";
require_once "../../../api/include/helperFunctions.php";
$helper = new HelperFunction();

if(isset($_POST['get_option'])) {
    $values = explode(":", $_POST['get_option']);
    $stmt1 = $link->prepare("update Transactions set ServiceProviderId = :serviceproviderid where TransactionId = :transactionid");
    $stmt1->execute(["serviceproviderid" => $values[1], "transactionid" => $values[0]]);
    exit;
}

if(isset($_POST['serv_prov_id']) and isset($_POST['sDate1'])) {
    $stmt2 = $link->prepare("select MobilePhone, PreferredName from ServiceProviders where ServiceProviderId = :serviceproviderid");
    $stmt2->execute(["serviceproviderid" => $_POST['serv_prov_id']]);
    $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);

    
    
    $body = "Hi " . $row2['PreferredName'] . "! Please visit this link to check your updated schedule - http://www.apartmentbutler.com/Ops/DC/SvcProv/servicesForToday.php?spID="
            . $_POST['serv_prov_id'] . "&sD=" . $_POST['sDate1'];
    $helper->sendSMSFromCICOLine($row2['MobilePhone'], $body);
    echo $row2['PreferredName'];
    exit;
}

if(isset($_POST['all_serv_prov_comp']) and isset($_POST['sDate2'])) {
    $stmt3 = $link->prepare("select ServiceProviderId, MobilePhone, PreferredName from ServiceProviders where ServiceProviderCompany = :spc");
    $stmt3->execute(["spc" => $_POST['all_serv_prov_comp']]);
    
    while($row3 = $stmt3->fetch(PDO::FETCH_ASSOC)) {
        $stmt5 = $link->prepare("select count(*) as services from Transactions where ServiceProviderId = :spid and ServiceDate = :servicedate and ServiceStatus != 'CANCELED'");
        $stmt5->execute(["spid" => $row3['ServiceProviderId'], "servicedate" => $_POST['sDate2']]);
        $row5 = $stmt5->fetch(PDO::FETCH_ASSOC);
        if($row5['services'] > 0) {
            $body = "Hi " . $row3['PreferredName'] . "! Please visit this link to check your updated schedule - http://www.apartmentbutler.com/Ops/DC/SvcProv/servicesForToday.php?spID="
                     . $row3['ServiceProviderId'] . "&sD=" . $_POST['sDate2'];
            $helper->sendSMSFromCICOLine($row3['MobilePhone'], $body);
        }
    }
    exit;
}



if(isset($_POST['sprovid']) and isset($_POST['content'])) {
    $stmt4 = $link->prepare("select MobilePhone, PreferredName from ServiceProviders where ServiceProviderId = :serviceproviderid");
    $stmt4->execute(["serviceproviderid" => $_POST['sprovid']]);
    $row4 = $stmt4->fetch(PDO::FETCH_ASSOC);
        
    $body = "Hi " . $row4['PreferredName'] . "! You have a message - " . $_POST['content'];
    $helper->sendSMSFromCICOLine($row4['MobilePhone'], $body);
    echo $row4['PreferredName'];
    exit;
}

if(isset($_POST['customer']) and isset($_POST['info'])) {
    
    $stmt6 = $link->prepare("select FirstName, MobilePhone from Customers where CustomerId = :customerid");
    $stmt6->execute(["customerid" => $_POST['customer']]);
    $row6 = $stmt6->fetch(PDO::FETCH_ASSOC);
    
    $body = "Hi " . $row6['FirstName'] . "! Here's a message from Apartment Butler - " . $_POST['info'];
    $helper->sendSMSFromCICOLine($row6['MobilePhone'], $body);
    exit;      
}

if(isset($_POST['USubmit'])) {
    for($i = 1; $i <= $_POST['total']; ++$i) {
        $svcname = "svcname" . $i;
        $route = "route" . $i;
        $servProv = "servProv" . $i;
        $price = "price" . $i;
        $ddate = "ddate" . $i;
        $ddvalue = explode(":", $_POST[$servProv]);
        if($_POST[$svcname] != "DryCleaning Drop-Off") {
            if($_POST[$svcname] == "Wash&Fold") {
                $serviceID = 219;
            }
            elseif($_POST[$svcname] == "DryC+Wash&Fold") {
                $serviceID = 220;
            }
            else {
                $serviceID = 69;
            }
            if($_POST[$price] == 0.0) {
                $stmt7 = $link->prepare("update Transactions set Price = :price, NoofPetsWalk = :nopw, ServiceProviderId = :spid where
                                        TransactionId = :trid");
                $stmt7->execute(["price" => $_POST[$price], "nopw" => $_POST[$route], "spid" => $ddvalue[1], "trid" => $ddvalue[0]]);
            }
            else {
                if($_POST[$price] < 0.0) {
                    $stmt7 = $link->prepare("update Transactions set Price = :price, NoofPetsWalk = :nopw, ServiceProviderId = :spid,
                                            PaymentStatus = 'YES', ServiceDate = :servicedate, ServiceId = :serviceid
                                            where TransactionId = :trid");
                    $stmt7->execute(["price" => 0.0, "nopw" => 0, "spid" => 0, "servicedate" => $_POST[$ddate], "serviceid" => $serviceID, "trid" => $ddvalue[0]]);
                }
                else {
                    $stmt7 = $link->prepare("update Transactions set Price = :price, NoofPetsWalk = :nopw, ServiceProviderId = :spid,
                                            ServiceDate = :servicedate, ServiceId = :serviceid
                                            where TransactionId = :trid");
                     $stmt7->execute(["price" => $_POST[$price], "nopw" => 0, "spid" => 0, "servicedate" => $_POST[$ddate], "serviceid" => $serviceID, "trid" => $ddvalue[0]]);
                }
                
            }
        }
        else {
            $stmt7 = $link->prepare("update Transactions set NoofPetsWalk = :nopw, ServiceDate = :servicedate, ServiceProviderId = :spid
                                    where TransactionId = :trid");
            $stmt7->execute(["nopw" => $_POST[$route], "servicedate" => $_POST[$ddate], "spid" => $ddvalue[1], "trid" => $ddvalue[0]]);
        }
    }
    $toScheduler = "scheduler.php?spcID=" . $_POST['spc'] . "&sD=" . $_POST['svcdate'];
    header('Location:' . $toScheduler);
}

if(isset($_POST['ASubmit'])) {
    $btnValue = explode("_", $_POST['ASubmit']);
    $total1 = "total" . $btnValue[1];
    $spc1 = "spc" . $btnValue[1];
    $svcdate1 = "svcdate" . $btnValue[1];
    
    for($j = 1; $j <= $_POST[$total1]; ++$j) {
        $svcname1 = $btnValue[1] . "_asgsvcname" . $j;
        $route1 = $btnValue[1] . "_asgroute" . $j;
        $servProv1 = $btnValue[1] . "_asgservProv" . $j;
        $price1 = $btnValue[1] . "_asgprice" . $j;
        $ddate1 = $btnValue[1] . "_asgddate" . $j;
        $ddvalue1 = explode(":", $_POST[$servProv1]);
        if($_POST[$svcname1] != "DryCleaning Drop-Off") {
            
            if($_POST[$svcname1] == "Wash&Fold") {
                $serviceID = 219;
            }
            elseif($_POST[$svcname1] == "DryC+Wash&Fold") {
                $serviceID = 220;
            }
            else {
                $serviceID = 69;
            }

            if($_POST[$price1] == 0.0) {
                $stmt8 = $link->prepare("update Transactions set Price = :price, NoofPetsWalk = :nopw, ServiceProviderId = :spid where
                                        TransactionId = :trid");
                $stmt8->execute(["price" => $_POST[$price1], "nopw" => $_POST[$route1], "spid" => $ddvalue1[1], "trid" => $ddvalue1[0]]);
            }
            else {
                if($_POST[$price1] < 0.0) {
                    $stmt8 = $link->prepare("update Transactions set Price = :price, NoofPetsWalk = :nopw, ServiceProviderId = :spid,
                                            PaymentStatus = 'YES', ServiceDate = :servicedate, ServiceId = :serviceid
                                            where TransactionId = :trid");
                    $stmt8->execute(["price" => 0.0, "nopw" => 0, "spid" => $ddvalue1[1], "servicedate" => $_POST[$ddate1], "serviceid" => $serviceID, "trid" => $ddvalue1[0]]);
                }
                else {
                    $stmt8 = $link->prepare("update Transactions set Price = :price, NoofPetsWalk = :nopw, ServiceProviderId = :spid,
                                            ServiceDate = :servicedate, ServiceId = :serviceid
                                            where TransactionId = :trid");
                     $stmt8->execute(["price" => $_POST[$price1], "nopw" => 0, "spid" => $ddvalue1[1], "servicedate" => $_POST[$ddate1], "serviceid" => $serviceID, "trid" => $ddvalue1[0]]);
                }
                
            }
        }
        else {
            $stmt8 = $link->prepare("update Transactions set NoofPetsWalk = :nopw, ServiceDate = :servicedate, ServiceProviderId = :spid
                                    where TransactionId = :trid");
            $stmt8->execute(["nopw" => $_POST[$route1], "servicedate" => $_POST[$ddate1], "spid" => $ddvalue1[1], "trid" => $ddvalue1[0]]);
        }
    }
    $toScheduler1 = "scheduler.php?spcID=" . $_POST[$spc1] . "&sD=" . $_POST[$svcdate1];
    header('Location:' . $toScheduler1);
    
}

if(isset($_POST['rpu_trid']) and isset($_POST['rpu_link']) and isset($_POST['rpu_sname'])) {
    $stmt9 = $link->prepare("update Transactions set ClockedIn = NULL where TransactionId = :trid");
    $stmt9->execute(["trid" => $_POST['rpu_trid']]);
    $message = "Here's a pick-up reset from " . $_POST['rpu_sname'] . PHP_EOL;
    $message .= "Transaction Link - " . $_POST['rpu_link'];
    mail('service@apartmentbutler.com', 'Pick-up Reset From ' . $_POST['rpu_sname'], $message);
    exit;
}

if(isset($_POST['rdo_trid']) and isset($_POST['rdo_link']) and isset($_POST['rdo_sname'])) {
    $stmt10 = $link->prepare("update Transactions set ClockedOut = NULL where TransactionId = :trid");
    $stmt10->execute(["trid" => $_POST['rdo_trid']]);
    $message = "Here's a drop-off reset from " . $_POST['rdo_sname'] . PHP_EOL;
    $message .= "Transaction Link - " . $_POST['rdo_link'];
    mail('service@apartmentbutler.com', 'Drop-off Reset From ' . $_POST['rdo_sname'], $message);    
    exit;
}

if(isset($_POST['san_notes']) and isset($_POST['san_aptid'])) {
    $stmt11 = $link->prepare("update ApartmentComplexes set DCNotes = :dcnotes where ApartmentId = :aptid");
    $stmt11->execute(["dcnotes" => $_POST['san_notes'], "aptid" => $_POST['san_aptid']]);
    echo 'Successful update.';
    exit;
}

if(isset($_POST['scn_notes']) and isset($_POST['scn_custid'])) {
    $stmt11 = $link->prepare("update Customers set DCNotes = :dcnotes where CustomerId = :custid");
    $stmt11->execute(["dcnotes" => $_POST['scn_notes'], "custid" => $_POST['scn_custid']]);
    echo 'Successful update.';
    exit;
}

if(isset($_POST['ab_link']) and isset($_POST['ab_sname']) and isset($_POST['ab_info'])) {
    $message = "Here's a message from " . $_POST['ab_sname'] . " - " . $_POST['ab_info'] . PHP_EOL;
    $message .= "Transaction Link - " . $_POST['ab_link'];
    
    mail("service@apartmentbutler.com", "Message from " . $_POST['ab_sname'], $message);
}

if(isset($_POST['ab_sname']) and isset($_POST['ab_info']) and isset($_POST['ab_spcid']) and isset($_POST['ab_trid'])) {
    
    $stmt = $link->prepare("select Phone from ServiceProviderRepresentatives where ServiceProviderRepresentativeId = :spcid");
    $stmt->execute(["spcid" => $_POST['ab_spcid']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
    if($_POST['ab_trid'] != 0) {
        $helper->sendToFrontWithTrID($_POST['ab_trid'], $_POST['ab_sname'], $row['Phone'], $_POST['ab_spcid'], $_POST['ab_info'], true, true);
        $helper->tagWithTrIDInFront($_POST['ab_trid']);
    }
    else {
        $helper->sendToFrontAsGeneral($_POST['ab_sname'], $row['Phone'], $_POST['ab_spcid'], $_POST['ab_info'], true);
        $helper->tagGeneralInFront($_POST['ab_spcid'], $_POST['ab_sname'], true);
    }

    exit;

}



?>
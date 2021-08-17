<?php
    require_once("utils/headerFunction.php");

    $templateParams["allBS"] = $dataBase->getBSs(date("Y"));

    foreach ($templateParams["allBS"] as $bs) {
        $dataBase->deleteRentPeriods($bs["idBS"]);
        $dataBase->addRentPeriods($bs["idBS"]);
    }

    $templateParams["pateTitle"] = "Loading";
    $templateParams["titoloScheda"] = "Loading";
    $templateParams["pageURL"] = "template/refreshReport.php";


    sleep(3);

    header("Location:./report.php");
?>

<?php
    require_once("utils/headerFunction.php");

    $templateParams["totalPrice"] = $dataBase->getTotalPrice(date("Y"));
    $templateParams["allBS"] = $dataBase->getAllBS(date("Y"));

    foreach ($templateParams["allBS"] as $bs) {
        $templateParams["BSlines"][$bs["idBS"]] = $dataBase->getBSperiods($bs["idBS"]);
    }

    $templateParams["pageTitle"] = "BeachService";
    $templateParams["tabTitle"] = "Report BS";
    $templateParams["pageURL"] = "template/reportTable.php";

    require('./template/base.php');
?>

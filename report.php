<?php
    require_once("utils/headerFunction.php");

    $a = isset($_GET["reportA"]) ? 1 : 0;

    if (isset ($_GET["lowerBound"]) and isset($_GET["upperBound"])) {
        $templateParams["totalPrice"] = $dataBase->getTotalPrice($_GET["lowerBound"], $_GET["upperBound"], $a);
        $templateParams["allBS"] = $dataBase->getAllBS($_GET["lowerBound"], $_GET["upperBound"], $a);
    } else {
        $lowerBound = new DateTime();
        $lowerBound->setDate($lowerBound->format('Y'), 1, 1);
        $lowerBound = date_format($lowerBound, "Y-m-d");

        $upperBound = new DateTime();
        $upperBound->setDate($upperBound->format('Y'), 12, 31);
        $upperBound = date_format($upperBound, "Y-m-d");

        $templateParams["totalPrice"] = $dataBase->getTotalPrice($lowerBound, $upperBound, $a);
        $templateParams["allBS"] = $dataBase->getAllBS($lowerBound, $upperBound, $a);
    }


    foreach ($templateParams["allBS"] as $bs) {
        $templateParams["BSlines"][$bs["idBS"]] = $dataBase->getBSperiods($bs["idBS"]);
    }

    $templateParams["pageTitle"] = "BeachService";
    $templateParams["tabTitle"] = "Report BS";
    $templateParams["pageURL"] = "template/reportTable.php";

    require('./template/base.php');
?>

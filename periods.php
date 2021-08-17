<?php
    require_once("utils/headerFunction.php");

    if(isset($_POST["submit"], $_POST["datein"], $_POST["dateout"], $_POST["price"])) {

        $name = isset($_POST["periodName"]) ? $_POST["periodName"] : "";

        $dataBase->addNewPeriod($name, $_POST["datein"], $_POST["dateout"],  $_POST["price"]);

        unset($_POST);
    }

    $templateParams["pageTitle"] = "BeachService";
    $templateParams["tabTitle"] = "Tariffe BS";
    $templateParams["pageURL"] = "template/addPeriod.php";

    require('./template/base.php');
?>

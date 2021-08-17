<?php
    require_once("utils/headerFunction.php");

    if(isset(   $_POST["submit"], $_POST["numBS"], $_POST["name"],
                $_POST["umbrellas"],  $_POST["beds"],
                $_POST["checkin"],  $_POST["checkout"])) {

        $a = isset($_POST["friendly"]) ? 1 : 0;
        $room = isset($_POST["room"]) ? $_POST["room"] : 0;

        $dataBase->addNewBS($_POST["numBS"], $a, $_POST["name"], $room, $_POST["umbrellas"],  $_POST["beds"],  $_POST["checkin"],  $_POST["checkout"]);

        unset($_POST);
    }

    $templateParams["lastBS"] = $dataBase->getLastBSs();
    $templateParams["nextBSnumber"] = $dataBase->getNextBSNumber();

    $templateParams["pageTitle"] = "BeachService";
    $templateParams["tabTitle"] = "BeachService";
    $templateParams["pageURL"] = "template/addBS.php";


    require('./template/base.php');
?>

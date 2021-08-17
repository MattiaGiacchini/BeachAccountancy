<?php
    require_once("utils/headerFunction.php");


    if(isset(   $_POST["submit"], $_POST["numBS"], $_POST["name"],
                $_POST["umbrellas"],  $_POST["beds"],
                $_POST["checkin"],  $_POST["checkout"])) {

        $a = isset($_POST["friendly"]) ? 1 : 0;
        $room = isset($_POST["room"]) ? $_POST["room"] : 0;

        $dataBase->editBS($_POST["numBS"], $a, $_POST["name"], $room, $_POST["umbrellas"],  $_POST["beds"],  $_POST["checkin"],  $_POST["checkout"], $_GET["BS"]);

        unset($_POST);
    }

    $templateParams["bs"] = $dataBase->getBS($_GET["BS"]);

    $templateParams["pageTitle"] = "BeachService";
    $templateParams["tabTitle"] = "BeachService";
    $templateParams["pageURL"] = "template/editBS.php";


    require('./template/base.php');
?>

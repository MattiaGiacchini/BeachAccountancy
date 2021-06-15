<?php
    require_once("utils/headerFunction.php");

    if(isUserLoggedIn() && getUserRole() == "admin") {

        if(isset($_POST["submit"], $_POST["name"],
                 $_POST["surname"], $_POST["cf"],
                 $_POST["birthday"], $_POST["email"],
                 $_POST["psw"])) {
            $dataBase->addNewCollaboratorUser($_POST["email"], $_POST["psw"], $_POST["name"], $_POST["surname"], $_POST["cf"],  $_POST["birthday"]);

            unset($_POST);

            header("Location:./index.php");
        }

        $templateParams["pateTitle"] = "Aggiungi Nuovo Collaboratore";
        $templateParams["titoloScheda"] = "World Wine Web";
        $templateParams["pageURL"] = "template/addBS.php";


        require('./template/base.php');
    } else {
        header("Location:./index.php");
    }
?>

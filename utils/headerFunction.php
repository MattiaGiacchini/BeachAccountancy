<?php
    session_start();
    define("UPLOAD_DIR", "./img/");
    define("AES_KEY", "02742984712323223132132135291233");
    require_once("db/db.php");
    $dataBase = new Database("localhost", "root", "", "beachservice", 3306);
    require_once("utils/functions.php");
?>

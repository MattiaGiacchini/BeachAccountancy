<?php
    function getLoggedUserId() {
        return isset($_SESSION["idUtente"]) ? $_SESSION["idUtente"] : null;
    }

    function createUserCookie($data, $tim){
        setcookie("user", myCrypt(json_encode($data)), $tim, "/");
    }

    function getUserCookie() {
        if(isset($_COOKIE["user"])) {
            return json_decode(myDecript($_COOKIE["user"]), true);
        }
        return null;
    }

    function logOut() {
        if(isUserLoggedIn()) {
            $keys = array_keys($_SESSION);
            foreach ($keys as $key){
                unset($_SESSION[$key]);
            }
            $data["tempo"] = 1;
            createUserCookie($data, time()-3600);
        }
    }

    function isUserLoggedIn() {
        global $dataBase;
        if(!isset($_SESSION["idUtente"])) {
            $cookie = getUserCookie();
            if($cookie == null) {
                return false;
            } else {
                if($dataBase->getAllUserInfo($cookie["idUtente"])["attivo"] == 1
                    && $cookie["tempo"] > time()) {
                    reactiveLoggedUser($cookie);
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return $dataBase->getAllUserInfo($_SESSION["idUtente"])["attivo"] == 1;
        }
    }

    function reactiveLoggedUser($cookie) {
        $_SESSION["idUtente"] = $cookie["idUtente"];
        $_SESSION["nomeUtente"] = $cookie["nomeUtente"];
    }

    function registerLoggedUser($user){
        $_SESSION["idUtente"] = $user["idUtente"];
        $data["idUtente"] = $user["idUtente"];
        if(isset($user["ragioneSociale"])) {
            $_SESSION["nomeUtente"] = $user["ragioneSociale"];
            $data["nomeUtente"] = $_SESSION["ragioneSociale"];
        } else {
            $_SESSION["nomeUtente"] = $user["nome"]." ".$user["cognome"];
            $data["nomeUtente"] = $user["nome"]." ".$user["cognome"];
        }
        $data["tempo"] = 60*60*24*30+ time();
        createUserCookie($data, 60*60*24*30 + time());
    }

    function getUserRole() {
        global $dataBase;
        return $dataBase->getUserRole(getLoggedUserId());
    }

    function getWineImgURL($idEtichetta, $idContenitore) {
        if (is_dir(WINE_PHOTO_DIR . $idEtichetta . "_" . $idContenitore)) {
            $path = scandir(WINE_PHOTO_DIR . $idEtichetta . "_" . $idContenitore ."/");
            return WINE_PHOTO_DIR . $idEtichetta . "_" . $idContenitore ."/" . $path[2];
        } else {
            return WINE_PHOTO_DIR . "default.png";
        }
    }

    function getUserImgURL($idUtente){
        if (is_dir(USER_PHOTO_DIR . $idUtente )) {
            $path = scandir(USER_PHOTO_DIR . $idUtente . "/");
            return USER_PHOTO_DIR . $idUtente . "/" . $path[2];
        } else {
            return USER_PHOTO_DIR . "defaultUser.png";
        }
    }

    function getUserName() {
        return isset($_SESSION["nomeUtente"]) ? $_SESSION["nomeUtente"] : "";
    }

    function upLoadWineImage($image, $idLabel, $idContainer) {
        $pathName = WINE_PHOTO_DIR . $idLabel . "_" . $idContainer;
        if (!is_dir($pathName)) {
            mkdir($pathName, 0755, true);
        }
        $pathDirectory = $pathName."/";
        $result = uploadImage($pathDirectory, $image, $idLabel . "_" . $idContainer);
        if($result[0] == 0 && !glob($pathDirectory . "*")) {
            rmdir($pathName);
        }
        return $result;
    }

    function upLoadUserImage($image, $idUser) {
        $pathName = USER_PHOTO_DIR . $idUser;
        if (!is_dir($pathName)) {
            mkdir($pathName, 0755, true);
        }
        $pathDirectory = $pathName."/";
        $result = uploadImage($pathDirectory, $image, $idUser);
        if($result[0] == 0 && !glob($pathDirectory . "*")) {
            rmdir($pathName);
        }
        return $result;
    }

    function uploadImage($path, $image, $imgNewName){
        $imageName = basename($image["name"]);
        $fullPath = $path.$imageName;

        $maxKB = 500;
        $acceptedExtensions = array("jpg", "jpeg", "png", "gif");
        $result = 0;
        $msg = "";

        //Controllo se immagine è veramente un'immagine
        $imageSize = getimagesize($image["tmp_name"]);
        if($imageSize === false) {
            $msg .= "File caricato non è un'immagine! ";
        }

        //Controllo dimensione dell'immagine < 500KB
        if ($image["size"] > $maxKB * 1024) {
            $msg .= "File caricato pesa troppo! Dimensione massima è $maxKB KB. ";
        }

        //Controllo estensione del file
        $imageFileType = strtolower(pathinfo($fullPath,PATHINFO_EXTENSION));    // pathinfo con l'enumerazione PATHINFO_EXTENSION ritorna l'estensione del file
        if(!in_array($imageFileType, $acceptedExtensions)){
            $msg .= "Accettate solo le seguenti estensioni: ".implode(",", $acceptedExtensions);
        }

        //Se non ci sono errori, sposto il file dalla posizione temporanea alla cartella di destinazione
        if(strlen($msg)==0){

            //Controllo se esiste uno o più file con stesso nome ed eventualmente, quello esistente lo cancello
            $files = scandir($path);
            if (count($files) > 0) {
                foreach ($files as $file) {
                    if($file != '.' && $file != '..') {
                        chmod($path.$file,0755);
                        unlink($path.$file);
                    }
                }
            }

            $newFullPath = $path.$imgNewName.".".$imageFileType;
            if(!move_uploaded_file($image["tmp_name"], $newFullPath)){
                $msg.= "Errore nel caricamento dell'immagine.";
            }
            else{
                $result = 1;
                $msg = $imageName;
            }
        }
        return array($result, $msg);
    }

    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////

    function myCrypt($string){
        $met_enc = 'aes256';
        $key_enc = AES_KEY;
        $iv = 'aksju39fjziurn34';   //una stringa random con 16 caratteri

        return openssl_encrypt($string, $met_enc, $key_enc, 0, $iv);
    }

    function myDecript($string) {
        $met_enc = 'aes256';
        $key_enc = AES_KEY;
        $iv = 'aksju39fjziurn34';   //una stringa random con 16 caratteri

        return openssl_decrypt($string, $met_enc, $key_enc, 0, $iv);
    }
?>

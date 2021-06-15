<?php

    class Database{
        private $basicUser = "user";
        private $db;

        public function __construct($servername, $username, $password, $dbname, $port){
            $this->db = new mysqli($servername, $username, $password, $dbname, $port);
            if ($this->db->connect_error) {
                die("Connection failed: " . $db->connect_error);
            }
            $this->db->query("SET NAMES 'utf8'");
        }

        public function getLastInsertIntoId() {
            return $this->db->insert_id;
        }

        // ritorna l'oggetto dei preferiti
        public function getAllFavoritesOfClientId($idClient) {
            $query =   "SELECT *
                        FROM preferenza AS p
                        WHERE p.idCliente = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $idClient);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            if(count($result)==0) {
                return null;
            } else {
                return $result;
            }
        }

        public function existSingleFavourite($idContainer, $idLabel, $idClient) {
            $query =   "SELECT *
                        FROM preferenza AS p
                        WHERE p.idCliente = ?
                        AND p.idEtichetta = ?
                        AND p.idContenitore = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iii', $idClient, $idLabel, $idContainer);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            if(count($result)==0) {
                return false;
            } else {
                return $result[0];
            }
        }

        private function createFavorite($idContainer, $idLabel, $idClient) {
            $query = "INSERT INTO preferenza (idContenitore, idEtichetta, idCliente)
                             VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iii', $idContainer, $idLabel, $idClient);

            return $stmt->execute();
        }

        private function deleteFavorite($idContainer, $idLabel, $idClient) {
        $query = "DELETE FROM preferenza
                  WHERE preferenza.idContenitore = ?
                  AND preferenza.idEtichetta = ?
                  AND preferenza.idCliente = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('iii', $idContainer, $idLabel, $idClient);

        return $stmt->execute();

        }

        public function toggleSingleFavorite($idContainer, $idLabel, $idClient) {
            if($this->existSingleFavourite($idContainer, $idLabel, $idClient) != NULL) {
                $this->deleteFavorite($idContainer, $idLabel, $idClient);
                return false;
            } else {
                $this->createFavorite($idContainer, $idLabel, $idClient);
                return true;
            }
        }

        // aggiunge un nuovo articolo a carrello
        private function addNewArticleToCart($idContainer, $idLabel, $idUser, $quantity) {
            $query = "INSERT INTO carrello (idContenitore, idEtichetta, idCliente, quantita) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iiii', $idContainer, $idLabel, $idUser, $quantity);

            return $stmt->execute();
        }

        // legge i dati da un articolo a carrello
        public function getSingleCartElement($idContainer, $idLabel, $idUser) {
            $query =   "SELECT *
                        FROM carrello AS c
                        WHERE c.idContenitore = ?
                            AND c.idEtichetta = ?
                            AND c.idCliente = ? ";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iii', $idContainer, $idLabel, $idUser);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            if(count($result)==0) {
                return null;
            } else {
                return $result[0];
            }
        }

        // aggiorna il valore di un articolo a carrello
        private function updateSingleArticleToCart($idContainer, $idLabel, $idUser, $quantity) {
            $query = "UPDATE carrello AS c
                      SET quantita = ?
                      WHERE c.idContenitore = ?
                        AND c.idEtichetta = ?
                        AND c.idCliente = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iiii', $quantity, $idContainer, $idLabel, $idUser);

            return $stmt->execute();
        }

        public function insertUpdateSingleCartElement($idContainer, $idLabel, $idUser, $quantity) {
            $cartQntDetail = $this->getSingleCartElement($idContainer, $idLabel, $idUser);
            $mag = $this->getProductAvailability($idLabel, $idContainer);   // carico la disponibilità a mag
            if($cartQntDetail) { // esiste l'articolo a carrello, allora ne aggiorno le quantità
                if($quantity + $cartQntDetail["quantita"] > $mag) {
                    $quantity = $mag;
                } else {
                    $quantity = $quantity + $cartQntDetail["quantita"];
                }
                $this->updateSingleArticleToCart($idContainer, $idLabel, $idUser, $quantity);
            } else {
                $quantity = $quantity > $mag ? $mag : $quantity;
                $this->addNewArticleToCart($idContainer, $idLabel, $idUser, $quantity);
            }
        }

        // ritorna tutti gli stati inseriti a database
        public function getStates(){
            $stmt = $this->db->prepare("SELECT * FROM stato ORDER BY nome ASC ");
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result;
        }

        // restituisce tutte le recensioni di un determinato prodotto
        public function getAllProductReviews($idContainer, $idLabel) {
            $query =   "SELECT r.idContenitore, r.idEtichetta, r.titolo, r.valutazione, r.testo, u.nome, u.cognome, u.ragioneSociale
                        FROM recensione AS r
                        JOIN utente AS u
                        ON u.idUtente = r.idCliente
                        WHERE r.idContenitore = ? AND r.idEtichetta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ii', $idContainer, $idLabel);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            if(count($result)==0) {
                return null;
            } else {
                return $result;
            }
        }

        private function updateAvarageReviewsOnProduct($idContainer, $idLabel){
            // calcolo la media delle recensioni del prodotto
            $query =   "SELECT AVG(r.valutazione) AS media
                        FROM recensione AS r
                        WHERE r.idContenitore = ? AND r.idEtichetta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ii', $idContainer, $idLabel);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            if(count($result)==0) {
               return false;
            }
            $result = round($result[0]["media"], 3);

            // aggiorno il valore della media dei voti al Prodotto
            $query = "UPDATE vino_confezionato AS v
                      SET mediaRecensioni = ?
                      WHERE v.idContenitore = ? AND v.idEtichetta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('dii', $result, $idContainer, $idLabel);
            if(!$stmt->execute()) {
                return false;
            }
            return true;
        }

        public function addNewProductReview($idContainer, $idLabel, $idUser, $title, $rating, $text) {
            // inserisco una nuova recensione
            $query = "INSERT INTO recensione (idContenitore, idEtichetta, idCliente, titolo, valutazione, testo) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iiisis', $idContainer, $idLabel, $idUser, $title, $rating, $text);
            if(!$stmt->execute()) {
                return false;
            }
            return $this->updateAvarageReviewsOnProduct($idContainer, $idLabel);
        }

        public function updateProductReview($idContainer, $idLabel, $idUser, $title, $rating, $text) {
            // aggiorno la recensione
            $query = "  UPDATE 	recensione AS r
                        SET		r.titolo = ?, r.testo = ?, r.valutazione = ?
                        WHERE 	r.idContenitore  = ?
                        AND 	r.idEtichetta    = ?
                        AND 	r.idCliente      = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ssiiii', $title, $text, $rating, $idContainer, $idLabel, $idUser);
            if(!$stmt->execute()) {
                return false;
            }
            return $this->updateAvarageReviewsOnProduct($idContainer, $idLabel);
        }

        public function getMyProductReview($idContainer, $idLabel, $idUser) {
            $query =   "SELECT r.idContenitore, r.idEtichetta, r.titolo, r.valutazione, r.testo, u.nome, u.cognome, u.ragioneSociale
                        FROM recensione AS r
                        JOIN utente AS u
                        ON u.idUtente = r.idCliente
                        WHERE r.idContenitore = ?
                        AND r.idEtichetta = ?
                        AND r.idCliente = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iii', $idContainer, $idLabel, $idUser);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            if(count($result)==0) {
                return null;
            } else {
                return $result[0];
            }
        }

        public function updateUserInfo($idUser, $email, $password) {
            return $this->adminUpdateUserInfo($idUser, $email, $password, 1);
        }

        public function adminUpdateUserInfo($idUser, $email, $password, $attivo) {
            $query = "UPDATE utente
                      SET email = ?, password = ?, attivo = ?
                      WHERE utente.idUtente = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ssii', $email, $password, $attivo, $idUser);
            return $stmt->execute();
        }

        public function getAllUserInfo($idUser){
            $query =   "SELECT *
                        FROM utente AS u
                        WHERE u.idUtente = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $idUser);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            if(count($result)==0) {
                return null;
            } else {
                return $result[0];
            }
        }

        // restituisce tutti i prodotti attivi
        public function getAllProductDetails($idLabel, $idContainer) {
            $query =   "SELECT co.*, vc.mediaRecensioni, vc.scorteMagazzino,
                        e.idEtichetta, e.nome AS nomeEtichetta, e.descrizione, e.colore, e.titoloAlcolico, e.solfiti, e.bio, e.categoria, e.tenoreZuccherino, e.temperaturaMinima AS tMin, e.temperaturaMassima as tMax, e.classificazione, e.gas, e.annata, e.indicazioneGeografica, e.specificazione, ca.idCantina, ca.nome AS nomeCantina, ca.stato, vi.coloreBacca, vi.nomeSpecie, me.menzione, pr.prezzo, pr.iva
                        FROM (SELECT * FROM vino_confezionato WHERE vino_confezionato.idContenitore = ? AND vino_confezionato.idEtichetta = ?) AS vc
                        JOIN contenitore AS co ON vc.idContenitore = co.idContenitore
                        JOIN etichetta AS e ON e.idEtichetta = vc.idEtichetta
                        JOIN cantina AS ca ON ca.idCantina = e.idCantina
                        JOIN prezzo_recente AS pr ON pr.idContenitore = co.idContenitore AND pr.idEtichetta = e.idEtichetta
                        LEFT JOIN vitigno AS vi ON e.vitigno = vi.idVitigno
                        LEFT JOIN menzione AS me ON me.idMenzione = e.menzione
                        WHERE 1";

            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ii', $idContainer, $idLabel);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            if(count($result)==0) {
                return null;
            } else {
                return $result[0];
            }
        }

        private function homeFilters() {
            $macroCategoria = [];
            $colore = [];
            $gas = [];
            $classificazione = [];

            /*Categoria vino*/
            if(isset($_GET["Vino"])){
                array_push($macroCategoria, "Vino");
            }

            if(isset($_GET["Spumante"])){
                array_push($macroCategoria, "Spumante");
            }

            $statusWhereCondition = "WHERE 1";

            if(!empty($macroCategoria)) {
                array_walk($macroCategoria, function(&$macroCategoria) {$macroCategoria = "'$macroCategoria'";});
                $statusWhereCondition .= " AND categoria IN (" .implode(", ", $macroCategoria) . ")";
            }

            /*Categoria vino*/
            if(isset($_GET["Rosso"])){
                array_push($colore, "Rosso");
            }

            if(isset($_GET["Rosato"])){
                array_push($colore, "Rosato");
            }

            if(isset($_GET["Bianco"])){
                array_push($colore, "Bianco");
            }

            if(!empty($colore)) {
                array_walk($colore, function(&$colore) {$colore = "'$colore'";});
                $statusWhereCondition .= " AND colore IN (" .implode(", ", $colore) . ")";
            }

            /*Gas vino*/
            if(isset($_GET["Fermo"])){
                array_push($gas, "Fermo");
            }

            if(isset($_GET["Frizzante"])){
                array_push($gas, "Frizzante");
            }

            if(!empty($gas)) {
                array_walk($gas, function(&$gas) {$gas = "'$gas'";});
                $statusWhereCondition .= " AND gas IN (" .implode(", ", $gas) . ")";
            }

            /*Classificazione vino*/
            if(isset($_GET["Varietale"])){
                array_push($classificazione, "Varietale");
            }

            if(isset($_GET["Generico"])){
                array_push($classificazione, "Generico");
            }

            if(isset($_GET["IGP"])){
                array_push($classificazione, "IGP");
            }

            if(isset($_GET["IGT"])){
                array_push($classificazione, "IGT");
            }

            if(isset($_GET["DOC"])){
                array_push($classificazione, "DOC");
            }

            if(isset($_GET["DOP"])){
                array_push($classificazione, "DOP");
            }

            if(isset($_GET["DOCG"])){
                array_push($classificazione, "DOCG");
            }

            if(!empty($classificazione)) {
                array_walk($classificazione, function(&$classificazione) {$classificazione = "'$classificazione'";});
                $statusWhereCondition .= " AND classificazione IN (" .implode(", ", $classificazione) . ")";
            }

            /*Gas vino*/
            if(isset($_GET["Preferiti"])){
                $statusWhereCondition .= " AND pref.idCliente IS NOT NULL ";
            }

            return $statusWhereCondition;
        }

        // restituisce tutti i dettagli di un prodotto
        public function getAllProductsHomePage() {
            $query =   "SELECT co.*, vc.mediaRecensioni, vc.scorteMagazzino,
                        e.idEtichetta, e.nome AS nomeEtichetta, e.descrizione, e.colore, e.titoloAlcolico, e.solfiti, e.bio, e.categoria, e.tenoreZuccherino, e.temperaturaMinima AS tMin, e.temperaturaMassima as tMax, e.classificazione, e.gas, e.annata, e.indicazioneGeografica, e.specificazione, ca.idCantina, ca.nome AS nomeCantina, ca.stato, vi.coloreBacca, vi.nomeSpecie, me.menzione, pr.prezzo, pr.iva
                        FROM (SELECT * FROM vino_confezionato WHERE vino_confezionato.attivo = 1) AS vc
                        JOIN contenitore AS co ON vc.idContenitore = co.idContenitore
                        JOIN etichetta AS e ON e.idEtichetta = vc.idEtichetta
                        JOIN cantina AS ca ON ca.idCantina = e.idCantina
                        JOIN prezzo_recente AS pr ON pr.idContenitore = co.idContenitore AND pr.idEtichetta = e.idEtichetta
                        LEFT JOIN vitigno AS vi ON e.vitigno = vi.idVitigno
                        LEFT JOIN menzione AS me ON me.idMenzione = e.menzione " . $this->homeFilters();
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result;
        }

        // restituisce tutti i dettagli di un prodotto
        public function getAllProductsHomePageByClient($userId) {
            $query =   'SELECT co.*, vc.mediaRecensioni, vc.scorteMagazzino, pref.idCliente AS favorite, e.idEtichetta, e.nome AS nomeEtichetta, e.descrizione, e.colore, e.titoloAlcolico, e.solfiti, e.bio, e.categoria, e.tenoreZuccherino, e.temperaturaMinima AS tMin, e.temperaturaMassima as tMax,
                        e.classificazione, e.gas, e.annata, e.indicazioneGeografica, e.specificazione, ca.idCantina, ca.nome AS nomeCantina, ca.stato, vi.coloreBacca, vi.nomeSpecie, me.menzione, pr.prezzo, pr.iva
                        FROM (SELECT * FROM vino_confezionato WHERE vino_confezionato.attivo = 1) AS vc
                        JOIN contenitore AS co ON vc.idContenitore = co.idContenitore
                        JOIN etichetta AS e ON e.idEtichetta = vc.idEtichetta
                        JOIN cantina AS ca ON ca.idCantina = e.idCantina
                        JOIN prezzo_recente AS pr ON pr.idContenitore = co.idContenitore AND pr.idEtichetta = e.idEtichetta
                        LEFT JOIN vitigno AS vi ON e.vitigno = vi.idVitigno
                        LEFT JOIN menzione AS me ON me.idMenzione = e.menzione
                        LEFT JOIN preferenza AS pref ON e.idEtichetta = pref.idEtichetta AND co.idContenitore = pref.idContenitore AND pref.idCliente = ? ' . $this->homeFilters();
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i',$userId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            return $result;
        }

        public function getProductsByIdLabel($idLabel) {
            $stmt = $this->db->prepare("SELECT c.idContenitore, c.tipologia as nomeContenitore, c.capacita as capacitaContenitore, v.idEtichetta, v.attivo, p.prezzo, p.iva
                                        FROM contenitore AS c
                                        LEFT JOIN ( SELECT * FROM vino_confezionato WHERE vino_confezionato.idEtichetta = ?) AS v ON v.idContenitore = c.idContenitore
                                        LEFT JOIN prezzo_recente AS p ON c.idContenitore = p.idContenitore AND v.idEtichetta = p.idEtichetta
                                        ORDER BY c.idContenitore");
            $stmt->bind_param('i',$idLabel);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            if(count($result)==0) {
                return null;
            } else {
                return $result;
            }
        }

        // ritorna tutte le informazioni specifiche di un'etichetta e della sua cantina
        public function getLabelFromId($idLabel) {
            $query = "SELECT e.nome as nomeEtichetta, c.nome as nomeCantina, c.stato as stato FROM etichetta e, cantina c WHERE idEtichetta = ? AND e.idCantina = c.idCantina";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i',$idLabel);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            if(count($result)==0) {
                return null;
            } else {
                return $result[0];
            }
        }

        // ritorna tutte le informazioni di un'etichetta attraverso il suo ID
        public function getLabelDetailsFromId($idLabel) {
            $query = "SELECT * FROM etichetta WHERE idEtichetta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i',$idLabel);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            if(count($result)==0) {
                return null;
            } else {
                return $result[0];
            }
        }

        // ritorna tutte le mezioni
        public function getMentions() {
            $stmt = $this->db->prepare("SELECT * FROM menzione ORDER BY menzione.menzione ASC ");
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result;
        }

        // ritorna tutte i vitigni
        public function getVitigni() {
            $stmt = $this->db->prepare("SELECT * FROM vitigno ORDER BY vitigno.coloreBacca, vitigno.nomeSpecie ASC");
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            return $result;
        }

        // restituisce l'id di una cantina
        public function getVitignoId($coloreBacca, $nomeSpecie) {
            $query = "SELECT idVitigno FROM vitigno WHERE coloreBacca = ? AND nomeSpecie = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ss',$coloreBacca, $nomeSpecie);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            if(count($result)==0) {
                return null;
            } else {
                return $result[0]["idVitigno"];
            }
        }

        // ritorna tutte le cantine
        public function getCantine() {
            $stmt = $this->db->prepare("SELECT idCantina, stato.nome as nomeStato, cantina.nome as nomeCantina FROM cantina, stato WHERE cantina.stato = stato.sigla ORDER BY stato.nome, cantina.nome ASC");
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            return $result;
        }

        // restituisce l'id di una cantina
        public function getWineryId($nomeCantina, $idStatoCantina) {
            $query = "SELECT idCantina FROM cantina WHERE nome = ? AND stato = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ss',$nomeCantina, $idStatoCantina);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            if(count($result)==0) {
                return null;
            } else {
                return $result[0]["idCantina"];
            }
        }

        // restituisce l'id di una cantina
        public function getMentionId($menzione) {
            $query = "SELECT idMenzione FROM menzione WHERE menzione = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('s',$menzione);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            if(count($result)==0) {
                return null;
            } else {
                return $result[0]["idMenzione"];
            }
        }

        // restituisce il ruolo ricoperto dall'utente di cui viene passato il suo id
        public function getUserRole($idUtente) {
            if(isset($idUtente)) {
                $query = "SELECT ruolo FROM utente WHERE idUtente = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param('i',$idUtente);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                if(count($result)==0) {
                    return $this->basicUser;
                } else {
                    return $result[0]["ruolo"];
                }
            }
            return $this->basicUser;
        }

        // restituisce l'id dell'utente se password e email vengono riconosciute
        public function checkLogin($email, $password){
            $query = "SELECT idUtente, nome, cognome, ragioneSociale, password, email FROM utente WHERE email = ? AND utente.attivo = 1";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            if (count($result) > 0) {
                $pwdCheck = password_verify($password, $result[0]["password"]);
                if ($pwdCheck) {
                    return $result;
                } else {
                    return array();
                }
            }
            return $result;
        }

        // aggiunge a database un nuovo prodotto
        public function addNewProduct($idContainer, $idLabel, $active) {
            $query = "INSERT INTO vino_confezionato (idContenitore, idEtichetta, scorteMagazzino, mediaRecensioni, attivo) VALUES (?, ?, '0', '0.0', ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iii', $idContainer, $idLabel, $active);

            return $stmt->execute();
        }

        // aggiorna a dataBase un prodotto
        public function updateProduct($idContainer, $idLabel, $active) {
            if($active == 0) {
                $query = "DELETE FROM carrello
                          WHERE carrello.idContenitore = ?
                          AND carrello.idEtichetta = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param('ii', $idContainer, $idLabel);
                $stmt->execute();
            }
            $query = "UPDATE vino_confezionato SET attivo = ? WHERE vino_confezionato.idContenitore = ? AND vino_confezionato.idEtichetta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iii', $active, $idContainer, $idLabel);

            return $stmt->execute();
        }

        // aggiunge una nuova valutazione al prodotto
        public function addNewEvaluationProduct($idLabel, $idContainer, $price, $iva) {
            $time = $this->getCurrentDateTime();
            $query = "INSERT INTO `prezzo` (`idContenitore`, `idEtichetta`, `data`, `prezzo`, `iva`) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iisdd', $idContainer, $idLabel, $time, $price, $iva);
            $result = $stmt->execute();

            $this->notificationFavouriteProductPriceChange($idLabel, $idContainer);
            return $result;
        }

        // aggiunge un nuovo utente business a database
        public function addNewBusinessUser($email, $psw, $company, $pIva){
            return $this->addNewUser($email, $psw, 'client', null, null, null, null, $company, $pIva);
        }

        // aggiunge un nuovo utente private a database
        public function addNewPrivateUser($email, $psw, $name, $surname, $cf, $birthday) {
            return $this->addNewUser($email, $psw, 'client', $name, $surname, $cf, $birthday, null, null);
        }

        // aggiunge un nuovo utente collaboratore a database
        public function addNewCollaboratorUser($email, $psw, $name, $surname, $cf, $birthday) {
            return $this->addNewUser($email, $psw, 'collaborator', $name, $surname, $cf, $birthday, null, null);
        }

        // aggiunge un nuovo utente amministratore a database
        public function addNewAdminUser($email, $psw, $name, $surname, $cf, $birthday) {
            return $this->addNewUser($email, $psw, 'admin', $name, $surname, $cf, $birthday, null, null);
        }

        // funzione privata per aggiungere un nuovo utente
        private function addNewUser($email, $psw, $ruolo, $name, $surname, $cf, $birthday, $company, $pIva) {
            $hashedPwd = password_hash($psw, PASSWORD_DEFAULT);
            $query = "INSERT INTO `utente` (`idUtente`, `email`, `password`, `ruolo`, `nome`, `cognome`, `dataDiNascita`, `cf`, `partitaIva`, `ragioneSociale`)
                      VALUES (null, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('sssssssis',$email, $hashedPwd, $ruolo, $name, $surname, $birthday, $cf, $pIva, $company);

            return $stmt->execute();
        }

      // aggiunge una nuova cantina a database
        public function addNewWinery($winery, $state) {
            $query = "INSERT INTO `cantina` (`idCantina`, `nome`, `stato`) VALUES (NULL, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ss', $winery, $state);

            return $stmt->execute();
        }

        // aggiunge una nuova cantina a database
        public function addNewVitigno($coloreBacca, $nomeSpecie) {
            $query = "INSERT INTO `vitigno` (`idVitigno`, `coloreBacca`, `nomeSpecie`) VALUES (NULL, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ss', $coloreBacca, $nomeSpecie);

            return $stmt->execute();
        }

        // aggiunge una nuova cantina a database
        public function addNewMention($mention) {
            $query = "INSERT INTO `menzione` (`idMenzione`, `menzione`) VALUES (NULL, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('s', $mention);

            return $stmt->execute();
        }

        public function addNewWine($categoria, $nome, $description, $color, $alcol, $zucchero, $gas, $idCantina, $solfiti, $bio, $tMin, $tMax, $classificazione, $idVitigno, $annata, $ig, $idMenzione, $specificazione) {
            if($solfiti === "true") {
                $solfiti = 1;
            } else {
                $solfiti = 0;
            }
            if($bio === "true") {
                $bio = 1;
            } else {
                $bio = 0;
            }
            $query = "INSERT INTO `etichetta` (`idEtichetta`, `nome`, `descrizione`, `colore`, `titoloAlcolico`, `solfiti`, `bio`, `categoria`, `tenoreZuccherino`, `temperaturaMinima`, `temperaturaMassima`, `classificazione`, `gas`, `annata`, `indicazioneGeografica`, `specificazione`, `vitigno`, `menzione`, `idCantina`) VALUES (null, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('sssdiissddssissiii', $nome, $description, $color, $alcol, $solfiti, $bio, $categoria, $zucchero, $tMin, $tMax, $classificazione, $gas, $annata, $ig, $specificazione, $idVitigno, $idMenzione, $idCantina);

            return $stmt->execute();
        }

        public function addNewSpumante($categoria, $nome, $description, $colore, $alcol, $zucchero, $idCantina, $solfiti, $biologico, $tMin, $tMax) {
            return $this->addNewWine($categoria, $nome, $description, $colore, $alcol, $zucchero, null, $idCantina, $solfiti, $biologico, $tMin, $tMax, null, null, null, null, null, null);
        }

        private function getCurrentDateTime() {
            return date("Y-m-d H:i:s");
        }

        public function warehouseLoad($idEtichetta, $idContenitore, $collaboratore, $amount){
            $currentdate = $this->getCurrentDateTime();
            $query = "INSERT INTO modifica_scorte(idContenitore, idEtichetta, idCollaboratore, quantita, data) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iiiis", $idContenitore, $idEtichetta, $collaboratore, $amount, $currentdate);
            $stmt->execute();

            $this->updateWarehouseAvailability($idEtichetta, $idContenitore, $amount);
        }

        public function getWarehouseMovements($idEtichetta, $idContenitore ) {
            if (isset($_GET["ordine"]) && $_GET["ordine"] === "crescente") {
                $sort = "ASC";
            } else {
                $sort = "DESC";
            }

            $query = "SELECT quantita, data, nome, cognome FROM modifica_scorte JOIN utente ON (modifica_scorte.idCollaboratore = utente.idUtente) WHERE modifica_scorte.idContenitore = ? AND modifica_scorte.idEtichetta = ? ORDER BY data " . $sort;
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ii', $idContenitore, $idEtichetta);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_all(MYSQLI_ASSOC);
        }


        private function updateWarehouseAvailability($idEtichetta, $idContenitore, $amount){
            $this->db->begin_transaction();

            try {
                $query = "SELECT scorteMagazzino FROM vino_confezionato WHERE idContenitore = ? AND idEtichetta = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param('ii', $idContenitore, $idEtichetta);
                $stmt->execute();
                $oldAmount = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $finalAmount = intval($oldAmount[0]["scorteMagazzino"]) + $amount;
                if ($finalAmount < 1) {
                    $this->outOfStockNotification($idEtichetta, $idContenitore);
                }
                // TODO: NOTIFICA ADMIN
                $query = "UPDATE vino_confezionato SET scorteMagazzino = ? WHERE idContenitore = ? AND idEtichetta = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param('iii', $finalAmount, $idContenitore, $idEtichetta);
                $stmt->execute();

                $this->db->commit();
            } catch (mysqli_sql_exception $exception) {
                $this->db->rollback();
                throw $exception;

            }
        }

        public function getProductDetails($idEtichetta, $idContenitore) {
            $query = "SELECT e.nome AS NomeVino, cantina.nome AS NomeCantina, p.prezzo, v.scorteMagazzino, c.capacita FROM contenitore AS c JOIN etichetta AS e JOIN prezzo_recente AS p JOIN vino_confezionato AS v JOIN cantina ON (v.idContenitore = c.idContenitore) AND (v.idEtichetta = e.idEtichetta) AND (v.idContenitore = p.idContenitore) AND (v.idEtichetta = p.idEtichetta) AND (e.idCantina = cantina.idCantina) WHERE v.idContenitore = ? AND v.idEtichetta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ii', $idContenitore, $idEtichetta);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_all(MYSQLI_ASSOC);
        }

            public function getWarehouseProducts() {
            if (isset($_GET["ordine"]) && $_GET["ordine"] === "decrescente") {
                $sort = "ORDER BY attivo DESC, e.nome DESC, cantina.nome ASC";
            } else {
                $sort = "ORDER BY attivo DESC, e.nome, cantina.nome ASC";
            }

            $status = [];
            if (isset($_GET["attivo"])) {
                array_push($status, 1);
            }

            if (isset($_GET["disattivato"])) {
                array_push($status, 0);
            }

            $statusWhereCondition = "";
            if (!empty($status)) {
                $statusWhereCondition = "AND v.attivo IN (" . implode(", ", $status) . ")";
            }

            $query = "SELECT e.nome AS NomeVino, cantina.nome AS NomeCantina, p.prezzo, v.scorteMagazzino, c.capacita, v.idEtichetta, v.idContenitore, v.attivo FROM contenitore AS c JOIN etichetta AS e JOIN prezzo_recente AS p JOIN vino_confezionato AS v JOIN cantina ON (v.idContenitore = c.idContenitore) AND (v.idEtichetta = e.idEtichetta) AND (v.idContenitore = p.idContenitore) AND (v.idEtichetta = p.idEtichetta) AND (e.idCantina = cantina.idCantina) WHERE p.idContenitore = v.idContenitore AND p.idEtichetta = v.idEtichetta " . $statusWhereCondition . " " . $sort;
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            return $result;
        }

        private function getProductAvailability($idEtichetta, $idContenitore) {
            $query = "SELECT scorteMagazzino FROM vino_confezionato WHERE idContenitore = ? AND idEtichetta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ii', $idContenitore, $idEtichetta );
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result[0]["scorteMagazzino"];
        }

        public function getCollaborators() {
            if (isset($_GET["ordine"]) && $_GET["ordine"] === "decrescente") {
                $sort = "ORDER BY attivo DESC, cognome DESC, nome ASC";
            } else {
                $sort = "ORDER BY attivo DESC, cognome, nome ASC";
            }

            $status = [];
            if (isset($_GET["attivo"])) {
                array_push($status, 1);
            }

            if (isset($_GET["disattivato"])) {
                array_push($status, 0);
            }

            $statusWhereCondition = "";
            if (!empty($status)) {
                $statusWhereCondition = "AND attivo IN (" . implode(", ", $status) . ")";
            }

            $query = "SELECT cognome, nome, idUtente, attivo FROM utente WHERE (ruolo = 'admin' OR ruolo = 'collaborator') " . $statusWhereCondition . " " . $sort;
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result;
        }

        public function getAllOrders(){
            $query = "SELECT o.idOrdine, sum(p.prezzo * d.quantita) as totaleOrdine, o.data, o.statoDiAvanzamento FROM ordine AS o LEFT JOIN dettaglio AS d ON o.idOrdine = d.idOrdine JOIN prezzo p ON p.idContenitore = d.idContenitore AND p.idEtichetta = d.idEtichetta WHERE p.data = (SELECT data FROM prezzo WHERE data < o.data AND prezzo.idContenitore = d.idContenitore AND prezzo.idEtichetta = d.idEtichetta ORDER BY data DESC LIMIT 1) " . $this->getOrderFilters(0);

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result;
        }

        public function getClientOrders($userId){
            $query = "SELECT o.idOrdine, sum(p.prezzo * d.quantita) as totaleOrdine, o.statoDiAvanzamento, o.data FROM ordine AS o JOIN dettaglio AS d ON o.idOrdine = d.idOrdine JOIN prezzo p ON p.idContenitore = d.idContenitore AND p.idEtichetta = d.idEtichetta WHERE o.idCliente = ? AND p.data = (SELECT data FROM prezzo WHERE data < o.data AND prezzo.idContenitore = d.idContenitore AND prezzo.idEtichetta = d.idEtichetta ORDER BY data DESC LIMIT 1) " . $this->getOrderFilters(1);
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $userId);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result;
        }

        private function getOrderFilters($orderView){
            if (isset($_GET["ordine"]) && $_GET["ordine"] === "crescente") {
                $sort = "ORDER BY o.data ASC";
            } else {
                $sort = "ORDER BY o.data DESC";
            }

            $status = [];
            if (isset($_GET["accettazione"])) {
                array_push($status, ORDER_STATUS[0]);
            }

            if (isset($_GET["elaborazione"])) {
                array_push($status, ORDER_STATUS[1]);
            }

            if (isset($_GET["spedito"])) {
                array_push($status, ORDER_STATUS[2]);
            }

            if (isset($_GET["consegnato"])) {
                array_push($status, ORDER_STATUS[3]);
            }

            if (isset($_GET["annullato"])) {
                array_push($status, ORDER_STATUS[-1]);
            }

            if ($orderView == 0) {
                $statusWhereCondition = "AND statoDiAvanzamento = 'Accettazione'";
            } else {
                $statusWhereCondition = "";
            }
            if (!empty($status)) {
                array_walk($status, function(&$status) {$status = "'$status'";});
                $statusWhereCondition = " AND statoDiAvanzamento IN (" . implode(', ', $status) . ")";
            }

            return $statusWhereCondition . " GROUP BY o.idOrdine, o.data, o.statoDiAvanzamento " . $sort;

        }

        public function getWineLabels() {
            if (isset($_GET["ordine"]) && $_GET["ordine"] === "decrescente") {
                $sort = "ORDER BY vino DESC, cantina ASC, annata DESC";
            } else {
                $sort = "ORDER BY vino ASC, cantina ASC, annata DESC";
            }

            $query = "SELECT e.idEtichetta, e.nome as vino, c.nome as cantina, c.stato, e.annata, e.indicazioneGeografica as origine, e.colore FROM etichetta e JOIN cantina c ON (e.idCantina = c.idCantina) " . $sort;
            $stmt = $this->db->prepare($query);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result;
        }

        public function getCartProducts($userId) {
            $query = "SELECT e.nome AS NomeVino, cantina.nome AS NomeCantina, p.prezzo, v.scorteMagazzino, c.capacita, v.idEtichetta, v.idContenitore, v.attivo, carrello.quantita, IF(carrello.quantita > v.scorteMagazzino, v.scorteMagazzino, carrello.quantita) as quantitaDefinitiva FROM contenitore AS c JOIN etichetta AS e JOIN prezzo_recente AS p JOIN vino_confezionato AS v JOIN cantina JOIN carrello ON (v.idContenitore = c.idContenitore) AND (v.idEtichetta = e.idEtichetta) AND (v.idContenitore = p.idContenitore) AND (v.idEtichetta = p.idEtichetta) AND (e.idCantina = cantina.idCantina) AND (carrello.idEtichetta = v.idEtichetta) AND (carrello.idContenitore = v.idContenitore) WHERE p.idContenitore = v.idContenitore AND p.idEtichetta = v.idEtichetta AND carrello.idCliente = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $userId);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result;
        }

        public function getCartValue($userId) {
            $query = "SELECT sum(totale_prodotto_carrello.totaleProdotto) AS totaleCarrello FROM totale_prodotto_carrello WHERE idCliente = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $userId);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result[0]["totaleCarrello"];
        }

        public function getUserAddresses($userId) {
            $query = "SELECT * FROM indirizzo WHERE idCliente = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $userId);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result;
        }

        public function getUserSpecificAddress($userId, $addressId) {
            $query = "SELECT * FROM indirizzo WHERE idCliente = ? AND idIndirizzo = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ii', $userId, $addressId);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            return $result[0];
        }

        public function getLastAddedAddress($userId) {
            $query = "SELECT idIndirizzo FROM indirizzo WHERE idCliente = ? ORDER BY idIndirizzo DESC LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $userId);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            return $result[0]["idIndirizzo"];
        }

        public function getUserPayments($userId) {
            $query = "SELECT * FROM metodo_di_pagamento WHERE idCliente = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $userId);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result;
        }

        public function getUserSpecificPayment($userId, $cardNumber) {
            $query = "SELECT * FROM metodo_di_pagamento WHERE idCliente = ? AND numeroCarta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('is', $userId, $cardNumber);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result[0];
        }

        public function insertNewAddress($userId, $nome, $via, $civico, $citta, $provincia, $cap, $stato){
            $query = "INSERT INTO indirizzo (idIndirizzo, idCliente, nome, via, civico, citta, provincia, cap, stato) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ississis', $userId, $nome, $via, $civico, $citta, $provincia, $cap, $stato);

            return $stmt->execute();
        }

        public function insertNewPayment($userId, $intestatario, $numeroCarta, $scadanza, $cvv, $tipologia) {
            $query = "INSERT INTO metodo_di_pagamento (idCliente, intestatario, numeroCarta, scadenza, cvv, tipologiaCarta) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('isssis', $userId, $intestatario, $numeroCarta, $scadanza, $cvv, $tipologia);

            return $stmt->execute();
        }

        /******************************************************************
                    NOTIFICHE
        ********************************************************************/

        /*Order creation process*/
        public function createOrder($userId, $cardNumber, $addressId) {

            //Begin transaction for order submit
            $this->db->begin_transaction();

            try {
                $this->checkOrderProductsAvailability($userId);
                $orderId = $this->createDefinitiveOrder($userId, $cardNumber, $addressId);
                $this->addProductsToDetail($userId, $orderId);

                $this->db->commit();
                $this->clearCart($userId);
                // TODO: notidica collaborator
            } catch (mysqli_sql_exception $exception) {
                $this->db->rollback();
                throw $exception;
            }
        }

        /*Checks if products are available, otherwise reduce*/
        private function checkOrderProductsAvailability($userId){
            $query = "UPDATE carrello AS c JOIN vino_confezionato AS v ON c.idEtichetta = v.idEtichetta AND c.idContenitore = v.idContenitore SET c.quantita = IF(c.quantita > v.scorteMagazzino, v.scorteMagazzino, c.quantita) WHERE c.idCliente = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
        }

        /*remove unavailable products from the cart before creating the order*/
        public function removeUnavailableProducts($userId) {
            $this->checkOrderProductsAvailability($userId);
            $products = $this->getProductsForOrder($userId);
            if (count($products) > 0) {
                foreach ($products as $product){
                    if ($product["quantita"] <= 0) {
                        $query = "DELETE FROM carrello WHERE idCliente = ? AND idContenitore = ? AND idEtichetta = ?";
                        $stmt = $this->db->prepare($query);
                        $stmt->bind_param('iii', $userId, $product["idContenitore"], $product["idEtichetta"]);
                        $stmt->execute();
                    }
                }
            }
            return;
        }

        /*create order instance in the database*/
        private function createDefinitiveOrder($userId, $cardNumber, $addressId) {
            $defaultState = ORDER_STATUS[0];
            $currentdate = $this->getCurrentDateTime();
            $address = $this->getUserSpecificAddress($userId, $addressId);
            $payment = $this->getUserSpecificPayment($userId, $cardNumber);

            //order creation
            $query = "INSERT INTO ordine (idOrdine, idCliente, data, statoDiAvanzamento, pagamentoIntestatario, pagamentoNumeroCarta, pagamentoScadenza, pagamentoCvv, pagamentoTipologiaCarta, spedizioneNome, spedizioneVia, spedizioneCivico, spedizioneCitta, spedizioneProvincia, spedizioneCap) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('isssisisssissi', $userId, $currentdate, $defaultState, $payment["intestatario"], $payment["numeroCarta"], $payment["scadenza"], $payment["cvv"], $payment["tipologiaCarta"], $address["nome"], $address["via"], $address["civico"], $address["citta"], $address["provincia"], $address["cap"]);
            $stmt->execute();

            return  $this->getLastOrderId($userId);
        }

        /*add products to the detail table in the database*/
        private function addProductsToDetail($userId, $orderId) {
            $orderProducts = $this->getProductsForOrder($userId);
            if (count($orderProducts) > 0) {
                foreach ($orderProducts as $product){
                    $query = "INSERT INTO dettaglio (idOrdine, idContenitore, idEtichetta, quantita) VALUES (?, ?, ?, ?)";
                    $stmt = $this->db->prepare($query);
                    $stmt->bind_param('iiii', $orderId, $product["idContenitore"], $product["idEtichetta"], $product["quantita"]);
                    $stmt->execute();

                    $this->updateWarehouseAvailability($product["idEtichetta"], $product["idContenitore"], -$product["quantita"]);
                }
            }
        }

        /*gets the products from the cart in order to create the order*/
        private function getProductsForOrder($userId) {
            $query = "SELECT idContenitore, idEtichetta, quantita FROM carrello WHERE idCliente = ? ";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $userId);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result;
        }

        /*gets the id of the last order created*/
        private function getLastOrderId($userId) {
            $query = "SELECT idOrdine FROM ordine WHERE idCliente = ? ORDER BY data DESC LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $userId);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            return $result[0]["idOrdine"];
        }

        /*removes all the products from the cart after creating the order*/
        private function clearCart($userId) {
            $query = "DELETE FROM carrello WHERE idCliente = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $userId);

            $stmt->execute();
        }

        public function getOrderProductsDetails($orderId) {
            $query = "SELECT d.idContenitore, d.idEtichetta, d.quantita, (100 * p.prezzo / (100 + p.iva)) as prezzo, c.nome as nomeCantina, e.nome as nomeVino FROM ordine AS o JOIN dettaglio AS d ON o.idOrdine = d.idOrdine JOIN prezzo AS p ON p.idContenitore = d.idContenitore AND p.idEtichetta = d.idEtichetta JOIN etichetta AS e ON d.idEtichetta = e.idEtichetta JOIN cantina as c ON e.idCantina = c.idCantina WHERE o.idOrdine = ? AND p.data = (SELECT data FROM prezzo WHERE data < o.data AND prezzo.idContenitore = d.idContenitore AND prezzo.idEtichetta = d.idEtichetta ORDER BY data DESC LIMIT 1) ORDER BY o.idOrdine";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $orderId);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result;
        }

        private function getProductsFromOrder($orderId) {
            $query = "SELECT * FROM dettaglio WHERE idOrdine = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $orderId);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result;
        }

        public function getOrderDetails($orderId) {
            $query = "SELECT * FROM ordine WHERE idOrdine = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $orderId);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result[0];
        }

        public function getOrderSubtotal($orderId) {
            $query = "SELECT o.idOrdine, sum(p.prezzo * d.quantita) as totaleOrdine FROM ordine AS o JOIN dettaglio AS d ON o.idOrdine = d.idOrdine JOIN prezzo p ON p.idContenitore = d.idContenitore AND p.idEtichetta = d.idEtichetta WHERE o.idOrdine = ? AND p.data = (SELECT data FROM prezzo WHERE data < o.data AND prezzo.idContenitore = d.idContenitore AND prezzo.idEtichetta = d.idEtichetta ORDER BY data DESC LIMIT 1) GROUP BY o.idOrdine ";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $orderId);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            return $result[0]["totaleOrdine"];
        }

        public function getUserData($userId){
            $query = "SELECT * FROM utente WHERE idUtente = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $userId);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result[0];
        }

        /*updates the order's state when update by the collaborator or the user*/
        public function updateOrderState($orderId, $collaboratorId, $state){
            $time = $this->getCurrentDateTime();

            $query = "UPDATE ordine SET statoDiAvanzamento = ? WHERE idOrdine = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('si', $state, $orderId);
            $stmt->execute();

            $query = "INSERT INTO gestione_ordine (idOrdine, idCollaboratore, data, stato) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iiss', $orderId, $collaboratorId, $time, $state);
            $stmt->execute();

            if ($state === ORDER_STATUS[-1]) {
                $this->rejectOrder($orderId);
            }


            $clientInfo = $this->getClientIdFromOrder($orderId);
            $username = $clientInfo["ragioneSociale"] == null ? $clientInfo["nome"] : $clientInfo["ragioneSociale"];
            $message = "Gentile " . $username . ", il tuo ordine #" . $orderId . " " . $this->getStateMessage($state);
            $this->addNewNotification($clientInfo["idCliente"], $message, "Ordine");
        }

        /*updates the cart with the new values setted*/
        public function updateCartValues($userId, $products) {
            foreach ($products as $product) {
                $this->updateSingleArticleToCart($product["idContenitore"], $product["idEtichetta"], $userId, $product["quantita"]);
            }
            return;
        }

        private function getClientIdFromOrder($orderId) {
            $query = "SELECT o.idCliente, u.nome, u.ragioneSociale FROM ordine o JOIN utente u ON o.idCliente = u.idUtente WHERE o.idOrdine = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $orderId);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result[0];
        }

        public function rejectOrder($orderId) {
            $products = $this->getProductsFromOrder($orderId);
            foreach ($products as $product) {
                $this->updateWarehouseAvailability($product["idEtichetta"], $product["idContenitore"], $product["quantita"]);
            }
        }



        /******************************************************************
                    NOTIFICHE
        ********************************************************************/

        public function getNumberNotificationsYetToBeRead($userId){
            $query = " SELECT COUNT(*) AS numeroNotifiche FROM notifica WHERE notifica.idUtente = ? AND notifica.visualizzato = 0";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result[0]["numeroNotifiche"];
        }

        private function addNewNotification($userId, $message, $category) {
            $time = $this->getCurrentDateTime();

            $query = "INSERT INTO notifica (idUtente, idNotifica, data, messaggio, visualizzato, categoria) VALUES (?, NULL, ?, ?, 0, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('isss', $userId, $time, $message, $category);
            $stmt->execute();
        }

        public function readNotification($idNotification) {
            $query = "UPDATE notifica SET visualizzato = 1 WHERE idNotifica = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $idNotification);

            return $stmt->execute();
        }

        public function getClientNotifications($userId) {
            if (isset($_GET["ordine"]) && $_GET["ordine"] === "crescente") {
                $sort = "ORDER BY visualizzato ASC, data ASC";
            } else {
                $sort = "ORDER BY visualizzato ASC, data DESC";
            }

            $status = [];
            if (isset($_GET["daLeggere"])) {
                array_push($status, "0");
            }

            if (isset($_GET["lette"])) {
                array_push($status, "1");
            }

            $statusWhereCondition = "AND visualizzato IN (0)";
            if (!empty($status)) {
                $statusWhereCondition = "AND visualizzato IN (" . implode(", ", $status) . ")";
            }

            $query = "SELECT * FROM notifica WHERE idUtente = ? " . $statusWhereCondition . " " . $sort;
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $userId);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result;
        }

        private function getStateMessage($state) {
            $status = "";
            switch ($state) {
                case ORDER_STATUS[0]:
                    $status = "è in fase di accettazione";
                    break;
                case ORDER_STATUS[1]:
                    $status = "è in fase di elaborazione";
                    break;
                case ORDER_STATUS[2]:
                    $status = "è stato spedito";
                    break;
                case ORDER_STATUS[3]:
                    $status = "è stato consegnato";
                    break;
                case ORDER_STATUS[-1]:
                    $status = "è stato annullato";
                    break;

                default:
                    break;
            }

            return $status;
        }

        private function notificationFavouriteProductPriceChange($idEtichetta, $idContenitore) {
            $query = "SELECT utente.idUtente, utente.nome as nomeUtente, utente.ragioneSociale, etichetta.nome as nomeVino FROM preferenza JOIN utente ON utente.idUtente = preferenza.idCliente JOIN etichetta ON preferenza.idEtichetta = etichetta.idEtichetta WHERE preferenza.idContenitore = ? AND preferenza.idEtichetta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ii', $idContenitore, $idEtichetta);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            foreach ($result as $data) {
                $username = $data["ragioneSociale"] == null ? $data["nomeUtente"] : $data["ragioneSociale"];
                $message = "Gentile " . $username . ", il prodotto #" . $idEtichetta . "_" . $idContenitore . " " . $data["nomeVino"] . " ha subito un cambio di prezzo.";
                $this->addNewNotification($data["idUtente"], $message, "Prodotto");
            }

            return;
        }

        private function outOfStockNotification($idEtichetta, $idContenitore) {
            $query = "SELECT idUtente FROM utente WHERE ruolo = 'admin'";
            $stmt = $this->db->prepare($query);

            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            if(count($result) > 0){
                foreach ($result as $admin) {
                    $message = "ATTENZIONE! Il prodotto #" . $idEtichetta . "_" . $idContenitore . " è esaurito.";
                    $this->addNewNotification($admin["idUtente"], $message, "Prodotto");
                }
            }
            return;
        }
    }

?>

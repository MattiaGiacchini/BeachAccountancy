<?php

    date_default_timezone_set('Europe/Rome');

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

        public function addNewBS($bs, $a, $name, $room, $umbrellas, $beds, $checkin, $checkout) {
            $query = "INSERT INTO beachservice (idBS, numBS, a, name, room, umbrellas, beds, checkin, checkout)
                        VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iisiiiss', $bs, $a, $name, $room, $umbrellas, $beds, $checkin, $checkout);

            $stmt->execute();

            $this->addRentPeriods(-1);

            return;
        }

        public function addRentPeriods($idBS) {
            if ($idBS < 0) {
                $BS = $this->getLastBS();
            } else {
                $BS = $this->getBS($idBS);
            }

            $checkin = new Datetime($BS["checkin"]);
            $checkout = new DateTime($BS["checkout"]);

            $days = $this->splitPeriod($checkin, $checkout);

            $variationBeds = $this->getBedVariation($BS["umbrellas"], $BS["beds"]);
            $variationUmbrellas = $BS["umbrellas"];

            foreach ($days as $key => $value) {
                if ($value > 0) {
                    $query = "INSERT INTO rentinperiod (idRent, bs, period, days, varBeds, varUmbrellas)
                    VALUES (NULL, ?, ?, ?, ?, ?)";
                    $stmt = $this->db->prepare($query);
                    $stmt->bind_param('iiidi', $BS["idBS"], $key, $value, $variationBeds, $variationUmbrellas);

                    $stmt->execute();
                }
            }

            return;

        }

        private function splitPeriod($checkin, $checkout) {
            $periods = $this->getPeriodsOfCurrentYear();
            $days = [];

            foreach ($periods as $period) {
                $days[$period["idPeriod"]] = 0;
            }

            $checkout->modify('+1 day');

            $interval = new DatePeriod($checkin, new DateInterval('P1D'), $checkout);

            foreach ($interval as $date) {
                foreach ($periods as $period) {
                    $datein = new Datetime($period["datein"]);
                    $dateout = new DateTime($period["dateout"]);
                    if ($date >= $datein and $date <= $dateout) {
                        $days[$period["idPeriod"]]++;
                    }
                }
            }

            return $days;
        }

        //DEPRECATED
        private function getBedVariations($umbrellas, $beds) {
            $variationBeds = 0;

            if ($umbrellas == 1 and $beds == 1) {
                $variationBeds = -0.2;
            } elseif ($umbrellas == 2 and $beds == 2) {
                $variationBeds = -0.4;
            } elseif ($umbrellas == 3 and $beds == 3) {
                $variationBeds = -0.6;
            } elseif ($umbrellas == 4 and $beds == 4) {
                $variationBeds = -0.8;
            } elseif ($umbrellas == 5 and $beds == 5) {
                $variationBeds = -1.0;
            } elseif ($umbrellas == 1 and $beds == 3) {
                $variationBeds = 0.2;
            } elseif ($umbrellas == 2 and $beds == 6) {
                $variationBeds = 0.4;
            } elseif ($umbrellas == 3 and $beds == 9) {
                $variationBeds = 0.6;
            } elseif ($umbrellas == 4 and $beds == 12) {
                $variationBeds = 0.8;
            } elseif ($umbrellas == 5 and $beds == 15) {
                $variationBeds = 1.0;
            } elseif ($umbrellas == 2 and $beds == 5) {
                $variationBeds = 0.2;
            } elseif ($umbrellas == 2 and $beds == 3) {
                $variationBeds = -0.2;
            } elseif ($umbrellas == 3 and $beds == 5) {
                $variationBeds = -0.2;
            } elseif ($umbrellas == 3 and $beds == 4) {
                $variationBeds = -0.4;
            } elseif ($umbrellas == 3 and $beds == 7) {
                $variationBeds = 0.2;
            } elseif ($umbrellas == 3 and $beds == 8) {
                $variationBeds = 0.4;
            }

            return $variationBeds;
        }

        private function getBedVariation($umbrellas, $beds) {
            return ($beds - (2 * $umbrellas)) * 0.2;
        }


        public function getLastBS() {
            $query = "  SELECT idBS, numBS, name, umbrellas, beds, checkin, checkout
                        FROM beachservice
                        WHERE (year(checkout) = ? OR year(checkin) = ?)
                        ORDER BY idBS DESC
                        LIMIT 1";

            $currentYear = date("Y");

            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ii', $currentYear, $currentYear);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_all(MYSQLI_ASSOC)[0];
        }

        public function getPeriodsOfCurrentYear() {
            $query = "  SELECT idPeriod, datein, dateout, price
                        FROM period
                        WHERE (year(datein) = ? OR year(dateout) = ?)
                        ORDER BY idPeriod";

            $currentYear = date("Y");

            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ii', $currentYear, $currentYear);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_all(MYSQLI_ASSOC);
        }

        public function getLastBSs() {
            $query = "  SELECT numBS, name, umbrellas, beds, checkin, checkout, (datediff(checkout, checkin) + 1) as 'days'
                        FROM beachservice
                        WHERE (year(checkout) = ? OR year(checkin) = ?)
                        AND a = 0
                        ORDER BY numBS DESC
                        LIMIT 15";

            $currentYear = date("Y");

            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ii', $currentYear, $currentYear);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_all(MYSQLI_ASSOC);
        }

        public function getBSs($year){
            $query = "  SELECT idBS, numBS, name, umbrellas, beds, checkin checkout
                        FROM beachservice
                        WHERE a = 0
                        AND (year(checkin) = ? OR year(checkout) = ?)
                        ORDER BY numBS";

            $currentYear = date("Y");

            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ii', $year, $year);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_all(MYSQLI_ASSOC);
        }

        public function getAllBS($year) {
            $query = "  SELECT bs.idBS, bs.numBS, bs.name, bs.umbrellas, bs.beds, bs.checkin as checkin, bs.checkout as checkout, sum(p.price * r.days * (r.varUmbrellas + r.varBeds)) as totBS, count(*) numLines, r.varUmbrellas, r.varBeds
                        FROM beachservice bs LEFT JOIN rentinperiod r ON bs.idBS = r.bs
                        LEFT JOIN period p ON r.period = p.idPeriod
                        WHERE bs.a = 0
                        AND (year(bs.checkin) = ? OR year(bs.checkout) = ?)
                        GROUP BY bs.idBS, bs.numBS, bs.name, bs.umbrellas, bs.beds, bs.checkin, bs.checkout
                        ORDER BY bs.numBS";

            $currentYear = date("Y");

            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ii', $currentYear, $currentYear);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_all(MYSQLI_ASSOC);
        }

        public function getBSperiods($idBS) {
            $query = "  SELECT r.days, p.price, (r.days * p.price * (r.varUmbrellas + r.varBeds)) as lineValue
                        FROM rentinperiod r, period p
                        WHERE r.bs = ?
                        AND r.period = p.idPeriod
                        HAVING lineValue > 0";

            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $idBS);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_all(MYSQLI_ASSOC);
        }

        public function getLastInsertedIntoId() {
            return $this->db->insert_id;
        }

        public function addNewPeriod($name, $datein, $dateout, $price) {
            $query = "  INSERT INTO period (idPeriod, name, datein, dateout, price)
                        VALUES (NULL, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('sssd', $name, $datein, $dateout, $price);

            $stmt->execute();

            return;
        }

        public function getBS($idBS) {
            $query = "  SELECT idBS, numBS, a, name, room, umbrellas, beds, checkin, checkout
                        FROM beachservice
                        WHERE idBS = ?";

            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $idBS);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result[0];
        }

        public function editBS($idBS, $bs, $a, $name, $room, $umbrellas, $beds, $checkin, $checkout) {
            $query = "  UPDATE beachservice
                        SET numBS = ?, a = ?, name = ?, room = ?, umbrellas = ?, beds = ?, checkin = ?, checkout = ?
                        WHERE idBS = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iisiiissi', $bs, $a, $name, $room, $umbrellas, $beds, $checkin, $checkout, $idBS);

            $stmt->execute();

            $this->deleteRentPeriods($idBS);
            $this->addRentPeriods($idBS);

            return;
        }

        public function deleteBS($idBS) {
            $this->deleteRentPeriods($idBS);

            $query = "  DELETE FROM beachservice
                        WHERE idBS = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $idBS);

            $stmt->execute();

            return;
        }


        public function deleteRentPeriods($idBS) {
            $query = "  DELETE FROM rentinperiod
                        WHERE bs = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $idBS);

            $stmt->execute();

            return;
        }

        public function getTotalPrice($year) {
            $query = "  SELECT sum(singleBS.totBS) as totalPrice
                        FROM (SELECT sum(p.price * r.days * (r.varUmbrellas + r.varBeds)) as totBS
                            FROM beachservice bs LEFT JOIN rentinperiod r ON bs.idBS = r.bs
                            LEFT JOIN period p ON r.period = p.idPeriod
                            WHERE bs.a = 0
                            AND (year(bs.checkin) = ? OR year(bs.checkout) = ?)
                            GROUP BY bs.idBS) as singleBS";

            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ss', $year, $year);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result[0]["totalPrice"];
        }

        public function getNextBSNumber() {
            $query = "  SELECT (numBS + 1) as nextBS
                        FROM beachservice
                        ORDER BY idBS DESC
                        LIMIT 1";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $result[0]["nextBS"];
        }

    }

?>

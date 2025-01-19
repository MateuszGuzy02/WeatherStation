<?php
$servername = "localhost";
$dbname = "esp_data";
$username = "root";
$password = "root";

class Charts
{
    private $conn;
    private $obecnaTemperaturaZewnetrzna;
    private $obecnaTemperaturaWewnetrzna;
    private $obecnaWilgotnoscZewnetrzna;
    private $obecnaWilgotnoscWewnetrzna;
    private $obecneCisnienieZewnetrzne;
    private $temperatura = array();
    private $wilgotnosc = array();
    private $cisnienie = array();
    private $czujniki = ["AHT10", "BMP280"];

    public function __construct($servername, $username, $password, $dbname)
    {
        $this->conn = new mysqli($servername, $username, $password, $dbname);

        if ($this->conn->connect_error) {
            die("Połączenie z bazą danych nieudane: " . $this->conn->connect_error);
        }
    }

    public function setData($location, $dataType) 
    {
        $column = '';
        switch($dataType) {
            case 'temperatura':
                $column = 'value1'; 
                break;
            case 'wilgotnosc':
                $column = 'value2'; 
                break;
            case 'cisnienie':
                $column = 'value3'; 
                break;
            default:
                throw new Exception("Nieznany typ danych: $dataType");
        }

        $query = "SELECT `$column`, `reading_time` FROM SensorData WHERE `location` = '$location'";
        $result = $this->conn->query($query);

        if ($result === false) {
            echo "Błąd zapytania: " . $this->conn->error;
            return;
        }

        while ($row = $result->fetch_assoc()) {

            if ($dataType == 'temperatura') {
                array_push($this->temperatura, array("y" => $row[$column], "label" => $row["reading_time"]));
            } elseif ($dataType == 'wilgotnosc') {
                array_push($this->wilgotnosc, array("y" => $row[$column], "label" => $row["reading_time"]));
            } elseif ($dataType == 'cisnienie') {
                array_push($this->cisnienie, array("y" => $row[$column], "label" => $row["reading_time"]));
            }
        }
    }

    public function setDataWithDate($location, $dataType, $fromDate, $toDate)
    {
        $column = '';
        switch ($dataType) {
            case 'temperatura':
                $column = 'value1';
                break;
            case 'wilgotnosc':
                $column = 'value2';
                break;
            case 'cisnienie':
                $column = 'value3';
                break;
            default:
                throw new Exception("Nieznany typ danych: $dataType");
        }

        $query = "SELECT `$column`, `reading_time` FROM SensorData 
                WHERE `location` = '$location' AND `reading_time` BETWEEN '$fromDate' AND '$toDate'";
        $result = $this->conn->query($query);

        if ($result === false) {
            echo "Błąd zapytania: " . $this->conn->error;
            return;
        }

        while ($row = $result->fetch_assoc()) {
            if ($dataType == 'temperatura') {
                array_push($this->temperatura, array("y" => $row[$column], "label" => $row["reading_time"]));
            } elseif ($dataType == 'wilgotnosc') {
                array_push($this->wilgotnosc, array("y" => $row[$column], "label" => $row["reading_time"]));
            } elseif ($dataType == 'cisnienie') {
                array_push($this->cisnienie, array("y" => $row[$column], "label" => $row["reading_time"]));
            }
        }
    }
    
    public function getData($dataType)
    {
        switch ($dataType) {
            case 'temperatura':
                return $this->getTemperatura();
            case 'wilgotnosc':
                return $this->getWilgotnosc();
            case 'cisnienie':
                return $this->getCisnienie();
            default:
                return [];
        }
    }

    public function setTemperature()
    {
        foreach ($this->czujniki as $czujnik) {
            $query = "SELECT `value1`, `reading_time` FROM SensorData WHERE `sensor` = '$czujnik'";
            $result = $this->conn->query($query);

            if ($result === false) {
                echo "Błąd zapytania: " . $this->conn->error;
                return;
            }

            while ($row = $result->fetch_assoc()) {
                array_push($this->temperatura, array("y" => $row["value1"], "label" => $row["reading_time"]));
            }
        }
    }

    public function setTemperaturaWithDate($fromDate, $toDate)
    {
        foreach ($this->czujniki as $czujnik) {
            $query = "SELECT `value1`, `reading_time` FROM SensorData WHERE `reading_time` BETWEEN '$fromDate' AND '$toDate' AND `sensor` = '$czujnik'";
            $result = $this->conn->query($query);

            if ($result === false) {
                echo "Błąd zapytania: " . $this->conn->error;
                return;
            }

            while ($row = $result->fetch_assoc()) {
                array_push($this->temperatura, array("y" => $row["value1"], "label" => $row["reading_time"]));
            }
        }
    }

    public function setWilgotnosc()
    {
        foreach ($this->czujniki as $czujnik) {
            $query = "SELECT `value2`, `reading_time` FROM SensorData WHERE `sensor` = '$czujnik'";
            $result = $this->conn->query($query);

            if ($result === false) {
                echo "Błąd zapytania: " . $this->conn->error;
                return;
            }

            while ($row = $result->fetch_assoc()) {
                if ($czujnik == 'AHT10') {
                    array_push($this->wilgotnosc, array("y" => $row["value2"], "label" => $row["reading_time"]));
                }
                else if($czujnik == "BMP280") {
                    array_push($this->wilgotnosc, array("y" => $row["value2"], "label" => $row["reading_time"]));
                }
            }
        }
    }

    public function setWilgotnoscWithDate($fromDate, $toDate)
    {
        foreach ($this->czujniki as $czujnik) {
            $query = "SELECT `value2`, `reading_time` FROM SensorData WHERE `reading_time` BETWEEN '$fromDate' AND '$toDate' AND `sensor` = '$czujnik'";
            $result = $this->conn->query($query);

            if ($result === false) {
                echo "Błąd zapytania: " . $this->conn->error;
                return;
            }

            while ($row = $result->fetch_assoc()) {
                if ($czujnik == 'AHT10') {
                    array_push($this->wilgotnosc, array("y" => $row["value2"], "label" => $row["reading_time"]));
                }
                else if($czujnik == "BMP280") {
                    array_push($this->wilgotnosc, array("y" => $row["value2"], "label" => $row["reading_time"]));
                }
            }
        }
    }

    public function setCisnienie()
    {
        foreach ($this->czujniki as $czujnik) {
            $query = "SELECT `value3`, `reading_time` FROM SensorData WHERE `sensor` = '$czujnik'";
            $result = $this->conn->query($query);

            if ($result === false) {
                echo "Błąd zapytania: " . $this->conn->error;
                return;
            }

            while ($row = $result->fetch_assoc()) {
                if ($czujnik == 'BMP280' && isset($row["value3"])) {
                    array_push($this->cisnienie, array("y" => $row["value3"], "label" => $row["reading_time"]));
                }
            }
        }
    }

    public function setCisnienieWithDate($fromDate, $toDate)
    {
        foreach ($this->czujniki as $czujnik) {
            $query = "SELECT `value3`, `reading_time` FROM SensorData WHERE `reading_time` BETWEEN '$fromDate' AND '$toDate' AND `sensor` = '$czujnik'";
            $result = $this->conn->query($query);

            if ($result === false) {
                echo "Błąd zapytania: " . $this->conn->error;
                return;
            }

            while ($row = $result->fetch_assoc()) {
                if ($czujnik == 'BMP280' && isset($row["value3"])) {
                    array_push($this->wilgotnosc, array("y" => $row["value3"], "label" => $row["reading_time"]));
                }
            }
        }
    }

    public function setObecnaTemperatura()
    {
        foreach ($this->czujniki as $czujnik) {
            $query = "SELECT `value1`, `reading_time`, `location` FROM SensorData WHERE `sensor` = '$czujnik' ORDER BY `reading_time` DESC LIMIT 1";
            $result = $this->conn->query($query);
    
            if ($result) {
                $row = $result->fetch_assoc();
    
                if ($row) {
                    if ($czujnik == 'AHT10') {
                        if ($row["location"] == "Room") {
                            $this->obecnaTemperaturaZewnetrzna = $row["value1"];
                        } elseif ($row["location"] == "Outdoor") {
                            $this->obecnaTemperaturaWewnetrzna = $row["value1"];
                        }
                    } elseif ($czujnik == 'BMP280') {
                        if ($row["location"] == "Outdoor") {
                            $this->obecnaTemperaturaZewnetrzna = $row["value1"];
                        }
                    }
                }
            }
        }
    }
    
    public function setObecnaWilgotnosc()
    {
        foreach ($this->czujniki as $czujnik) {
            $query = "SELECT `value2`, `reading_time`, `location` FROM SensorData WHERE `sensor` = '$czujnik' ORDER BY `reading_time` DESC LIMIT 1";
            $result = $this->conn->query($query);
    
            if ($result) {
                $row = $result->fetch_assoc();
    
                
                if ($row) {
                    if ($czujnik == 'AHT10') {
                        if ($row["location"] == "Room") {
                            $this->obecnaWilgotnoscZewnetrzna = $row["value2"];
                        } elseif ($row["location"] == "Outdoor") {
                            $this->obecnaWilgotnoscWewnetrzna = $row["value2"];
                        }
                    } elseif ($czujnik == 'BMP280') {
                        if ($row["location"] == "Outdoor") {
                            $this->obecnaWilgotnoscZewnetrzna = $row["value2"];
                        }
                    }
                }
            }
        }
    }
    
    public function setObecneCisnienie()
    {
        foreach ($this->czujniki as $czujnik) {
            $query = "SELECT `value3`, `reading_time`, `location` FROM SensorData WHERE `sensor` = '$czujnik' ORDER BY `reading_time` DESC LIMIT 1";
            $result = $this->conn->query($query);
    
            if ($result) {
                $row = $result->fetch_assoc();
    
                
                if ($row) {
                    if ($czujnik == 'BMP280' && $row["location"] == "Outdoor") {
                        $this->obecneCisnienieZewnetrzne = $row["value3"];
                    }
                }
            }
        }
    }


    public function setObecneDane()
    {
        foreach ($this->czujniki as $czujnik) {
            $query = "SELECT `value1`, `value2`, `value3`, `reading_time`, `location` 
                      FROM SensorData 
                      WHERE `sensor` = '$czujnik' 
                      ORDER BY `reading_time` DESC 
                      LIMIT 1";
    
            $result = $this->conn->query($query);
    
            if ($result) {
                $row = $result->fetch_assoc();
                if ($row) {

                    if ($czujnik == 'AHT10') {
                        if ($row["location"] == "Room") {
                            $this->obecnaTemperaturaWewnetrzna = $row["value1"];
                            $this->obecnaWilgotnoscWewnetrzna = $row["value2"];
                        } elseif ($row["location"] == "Outdoor") {
                            $this->obecnaTemperaturaZewnetrzna = $row["value1"];
                            $this->obecnaWilgotnoscZewnetrzna = $row["value2"];
                        }
                    } 
                    elseif ($czujnik == 'BMP280') {
                        if ($row["location"] == "Outdoor") {
                            $this->obecnaTemperaturaZewnetrzna = $row["value1"];
                            $this->obecnaWilgotnoscZewnetrzna = $row["value2"];
                            $this->obecneCisnienieZewnetrzne = $row["value3"];
                        }
                    }
                } else {
                    echo "Brak danych dla czujnika: $czujnik<br>";
                }
            } else {
                echo "Błąd w zapytaniu dla czujnika $czujnik: " . $this->conn->error . "<br>";
            }
        }
    }
    

    public function getWilgotnosc() { return $this->wilgotnosc; }
    public function getTemperatura() { return $this->temperatura; }
    public function getCisnienie() { return $this->cisnienie; }
    public function getObecnaTemperaturaZewnetrzna() { return $this->obecnaTemperaturaZewnetrzna; }
    public function getObecnaTemperaturaWewnetrzna() { return $this->obecnaTemperaturaWewnetrzna; }
    public function getObecnaWilgotnoscZewnetrzna() { return $this->obecnaWilgotnoscZewnetrzna; }
    public function getObecnaWilgotnoscWewnetrzna() { return $this->obecnaWilgotnoscWewnetrzna; }
    public function getObecneCisnienieZewnetrzne() { return $this->obecneCisnienieZewnetrzne; }

}
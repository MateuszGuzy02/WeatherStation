<?php

class Charts
{
    private $conn;
    private $obecnaTemperatura;
    private $obecnaWilgotnosc;
    private $temperatura = array();
    private $wilgotnosc = array();
    private $czujnik = ["AHT10"];

    public function __construct()
    {
        $this->conn = new mysqli("localhost:3306", "root", "root", "projektarm");
    }

    public function getWilgotnosc()
    {
        return $this->wilgotnosc;
    }

    public function getTemperatura()
    {
        return $this->temperatura;
    }

    public function setTemperature()
    {
        $query = "SELECT `value1`, `timestamp` FROM temperatura WHERE `sensor_name` LIKE '" . $this->czujnik[0] . "'";
        $result = $this->conn->query($query);

        for($i = 0; $i < $result->num_rows; $i++)
        {
            $row = $result->fetch_assoc();

            array_push($this->temperatura, array("y" => $row["value1"], "label" => $row["timestamp"]));
        }
    }

    public function setTemperaturaWithDate($fromDate, $toDate)
    {
        $query = "SELECT `value1`, `timestamp` FROM temperatura WHERE timestamp BETWEEN '$fromDate' AND '$toDate' AND `sensor_name` LIKE '" . $this->czujnik[0] . "'";
        $result = $this->conn->query($query);

        for($i = 0; $i < $result->num_rows; $i++)
        {
            $row = $result->fetch_assoc();

            array_push($this->temperatura, array("y" => $row["value1"], "label" => $row["timestamp"]));
        }
    }

    public function setWilgotnosc()
    {
        $query = "SELECT `value2`, `timestamp` FROM temperatura WHERE `sensor_name` LIKE '" . $this->czujnik[0] . "'";
        $result = $this->conn->query($query);

        for($i = 0; $i < $result->num_rows; $i++)
        {
            $row = $result->fetch_assoc();

            array_push($this->wilgotnosc, array("y" => $row["value2"], "label" => $row["timestamp"]));
        }
    }

    public function setWilgotnoscWithDate($fromDate, $toDate)
    {
        $query = "SELECT `value2`, `timestamp` FROM temperatura WHERE timestamp BETWEEN '$fromDate' AND '$toDate' AND `sensor_name` LIKE '" . $this->czujnik[0] . "'";
        $result = $this->conn->query($query);

        for($i = 0; $i < $result->num_rows; $i++)
        {
            $row = $result->fetch_assoc();

            array_push($this->wilgotnosc, array("y" => $row["value2"], "label" => $row["timestamp"]));
        }
    }

    public function setObecnaTemperatura()
    {
        $query = "SELECT `value1` FROM `temperatura` WHERE `sensor_name` LIKE '" . $this->czujnik[0] . "' ORDER BY `timestamp` DESC LIMIT 1";
        $result = $this->conn->query($query);

        $row = $result->fetch_assoc();

        $this->obecnaTemperatura = $row["value1"];
    }

    public function setObecnaWilgotnosc()
    {
        $query = "SELECT `value2` FROM `temperatura` WHERE `sensor_name` LIKE '" . $this->czujnik[0] . "' ORDER BY `timestamp` DESC LIMIT 1";
        $result = $this->conn->query($query);

        $row = $result->fetch_assoc();

        $this->obecnaWilgotnosc = $row["value2"];
    }

    public function getObecnaTemperatura()
    {
        return $this->obecnaTemperatura;
    }

    public function getObecnaWilgotnosc()
    {
        return $this->obecnaWilgotnosc;
    }
}
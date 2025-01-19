<?php
include_once "Charts.php";

$servername = "localhost";
$dbname = "esp_data";
$username = "root";
$password = "root";


$charts = new Charts($servername, $username, $password, $dbname);

$charts->setObecnaTemperatura();
$charts->setObecnaWilgotnosc();
$charts->setObecneCisnienie();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $location = isset($_POST['location']) ? $_POST['location'] : 'Outdoor';
    $dataType = isset($_POST['dataType']) ? $_POST['dataType'] : 'temperatura';
    $fromDate = isset($_POST['fromDate']) ? $_POST['fromDate'] : null;
    $toDate = isset($_POST['toDate']) ? $_POST['toDate'] : null;

    if ($fromDate && $toDate) {

        $charts->setDataWithDate($location, $dataType, $fromDate, $toDate);
    } else {
        // Ustaw dane bez zakresu dat
        $charts->setData($location, $dataType);
    }
}

?>
<html lang="pl">
    <head>
        <title>Stacja pogodowa</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body style="padding: 10px">

    <div class="container">
        <div class="row">
            <div class="col">
                <form method="POST" id="chartSelect" action="index.php">
                    <h5>Czujnik</h5>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="locationRoom" name="location" value="Room">
                        <label class="form-check-label" for="locationRoom">Wewnętrzny</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="locationOutdoor" name="location" value="Outdoor" checked>
                        <label class="form-check-label" for="locationOutdoor">Zewnętrzny</label>
                    </div>


                    <h5>Wykres</h5>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="chartType1" name="dataType" value="temperatura" checked>
                        <label for="chartType1" class="form-check-label">Temperatury</label>
                    </div>

                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="chartType2" name="dataType" value="wilgotnosc">
                        <label for="chartType2" class="form-check-label">Wilgotności</label>
                    </div>

                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="chartType3" name="dataType" value="cisnienie">
                        <label for="chartType3" class="form-check-label">Ciśnienia</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Pokaż wykres</button>
                </form>
            </div>
            <div class="col">
                <div id="chartContainer" style="height: 300px; width: 100%;"></div>

                <form method="POST" action="index.php">
                    <div class="mb-3">
                        <label for="fromDate">Od:</label>
                        <input name="fromDate" class="form-control" type="datetime-local" required><br>
                    </div>

                    <div class="mb-3">
                        <label for="toDate">Do:</label>
                        <input name="toDate" class="form-control" type="datetime-local" required><br>
                    </div>

                    <button type="submit" class="btn btn-primary">Wybierz zakres</button>
                </form>

            </div>
        </div>
        <div class="row">
            <div class="col">
                <table class="table table-bordered">
                    <tr>
                        <td>Temperatura zewnętrzna</td>
                        <td id="currentTempZew"></td>
                    </tr>
                    <tr>
                        <td>Temperatura wewnętrzna</td>
                        <td id="currentTempWew"></td>
                    </tr>
                    <tr>
                        <td>Wilgotność zewnętrzna</td>
                        <td id="currentWilgZew"></td>
                    </tr>
                    <tr>
                        <td>Wilgotność wewnętrzna</td>
                        <td id="currentWilgWew"></td>
                    </tr>
                    <tr>
                        <td>Ciśnienie zewnętrzne</td>
                        <td id="currentCisnienie"></td>
                    </tr>
                </table>
            </div>
        <div class="col"></div>
    </div>

    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
    <script>
        window.onload = (event) => {
            currentValues();
        };

        var unit = "";
        var yAxisTitle = "";
        var yValueFormatString = "";

        <?php if ($dataType == 'temperatura'): ?>
            unit = "°C";
            yAxisTitle = "Temperatura [°C]";
            yValueFormatString = "##.##'°C'";
        <?php elseif ($dataType == 'wilgotnosc'): ?>
            unit = "%";
            yAxisTitle = "Wilgotność [%]";
            yValueFormatString = "##.##'%'";
        <?php elseif ($dataType == 'cisnienie'): ?>
            unit = "hPa";
            yAxisTitle = "Ciśnienie [hPa]";
            yValueFormatString = "####.##'hPa'";
        <?php endif; ?>

            var chart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                title: {
                    text: "<?php echo ucfirst($dataType); ?> - <?php echo ucfirst($location); ?>"
                },
                axisY: {
                    title: yAxisTitle
                },
                data: [{
                    type: "spline",
                    markerSize: 5,
                    xValueFormatString: "YYYY-MM-DD HH:mm",
                    yValueFormatString: yValueFormatString, // Dynamiczny format wartości Y
                    xValueType: "dateTime",
                    dataPoints: <?php echo json_encode($charts->getData($dataType), JSON_NUMERIC_CHECK); ?>
                }]
            });
            chart.render();

        function currentValues() {
            $.ajax({
                url: "http://localhost/test/setCurrVal.php",
                dataType: 'json',
                method: 'get',
                timeout: 5000,
                data: {
                    'ajax': true
                },
                success: function (data) {
                    
                    document.getElementById("currentTempZew").innerHTML = data.temperaturaZew + "°C";
                    document.getElementById("currentTempWew").innerHTML = data.temperaturaWew ? data.temperaturaWew + "°C" : "Brak danych";
                    document.getElementById("currentWilgZew").innerHTML = data.wilgotnoscZew + "%";
                    document.getElementById("currentWilgWew").innerHTML = data.wilgotnoscWew ? data.wilgotnoscWew + "%" : "Brak danych";
                    document.getElementById("currentCisnienie").innerHTML = data.cisnienie + " hPa";
                },
                error: function (jqXHR, textStatus) {
                    if (textStatus === 'timeout') {
                        console.error("Żądanie AJAX przekroczyło limit czasu.");
                    } else {
                        console.error("Inny błąd AJAX:", textStatus);
                    }
                }
            });
        }

        setInterval(currentValues, 5000);
    </script>

    </body>
</html>
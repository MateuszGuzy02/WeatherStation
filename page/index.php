<?php
include_once "Charts.php";

$servername = "localhost";
$dbname = "esp_data";
$username = "root";
$password = "root";
$dataType = "";

$charts = new Charts($servername, $username, $password, $dbname);

$charts->setObecnaTemperatura();
$charts->setObecnaWilgotnosc();
$charts->setObecneCisnienie();
$charts->setObecneOswietlenie();

if(empty($_POST['location']))
{
    $location = "Room";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $location = $_POST['location'] ?? 'Outdoor';
    $dataType = $_POST['dataType'] ?? 'temperatura';
    $fromDate = $_POST['fromDate'] ?? null;
    $toDate = $_POST['toDate'] ?? null;

    if ($fromDate && $toDate) {
        $charts->setDataWithDate($location, $dataType, $fromDate, $toDate);
    } else {
        $charts->setData($location, $dataType);
    }
}

?>
<html lang="pl">
    <head>
        <title>Stacja pogodowa</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <link href= "./style.css" rel = "stylesheet">
        <script>
            function setupChartToggle() {
                if ($('#locationRoom').is(':checked')) {
                    $('#chartType3').prop('disabled', true);
                    $('#chartType4').prop('disabled', false);
                } else if ($('#locationOutdoor').is(':checked')) {
                    $('#chartType4').prop('disabled', true);
                    $('#chartType3').prop('disabled', false);
                }
            }
        </script>
    </head>

    <div class="container">
        <div class="row">
            <div class="dane">
                <form method="POST" id="chartSelect" action="index.php">
                    <h5>Czujnik</h5>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="locationRoom" name="location" value="Room" <?php if($location == "Room") { echo "checked"; } ?>>
                        <label class="form-check-label" for="locationRoom">Pokojowy</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="locationOutdoor" name="location" value="Outdoor" <?php if($location == "Outdoor") { echo "checked"; } ?>>
                        <label class="form-check-label" for="locationOutdoor">Otoczenie</label>
                    </div>


                    <h5>Wykres</h5>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="chartType1" name="dataType" value="temperatura" <?php if(isset($_POST['dataType']) && $_POST['dataType'] == "temperatura") { echo "checked"; } ?>>
                        <label for="chartType1" class="form-check-label">Temperatury</label>
                    </div>

                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="chartType2" name="dataType" value="wilgotnosc" <?php if(isset($_POST['dataType']) && $_POST['dataType'] == "wilgotnosc") { echo "checked"; } ?>>
                        <label for="chartType2" class="form-check-label">Wilgotności</label>
                    </div>

                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="chartType3" name="dataType" value="cisnienie" <?php if(isset($_POST['dataType']) && $_POST['dataType'] == "cisnienie") { echo "checked"; } ?>>
                        <label for="chartType3" class="form-check-label">Ciśnienia</label>
                    </div>

                    <div class="form-check" >
                        <input type="radio" class="form-check-input" id="chartType4" name="dataType" value="oswietlenie" <?php if(isset($_POST['dataType']) && $_POST['dataType'] == "oswietlenie") { echo "checked"; } ?>>
                        <label for="chartType3" class="form-check-label">Oświetlenie</label>
                    </div>

                    <div class="mb-3">
                        <label for="fromDate">Od:</label>
                        <input name="fromDate" class="form-control" type="datetime-local" value="<?php if(isset($_POST['fromDate'])) { echo $_POST['fromDate']; } ?>" ><br>
                    </div>

                    <div class="mb-3">
                        <label for="toDate">Do:</label>
                        <input name="toDate" class="form-control" type="datetime-local" value="<?php if(isset($_POST['toDate'])) { echo $_POST['toDate']; } ?>" ><br>
                    </div>
                    <button type="submit" class="btn btn-primary">Pokaż wykres</button>
                </form>
            </div>

            <div class="wykres">
                <div id="chartContainer" style="height: 300px; width: 100%;"></div>
            </div>
        </div>
        <div class="row">
            <div class="tabelka">
                <table class="table table-bordered">
                    <tr>
                        <td>Temperatura otoczenia</td>
                        <td id="currentTempZew"></td>
                    </tr>
                    <tr>
                        <td>Temperatura pokojowa</td>
                        <td id="currentTempWew"></td>
                    </tr>
                    <tr>
                        <td>Wilgotność otoczenia</td>
                        <td id="currentWilgZew"></td>
                    </tr>
                    <tr>
                        <td>Wilgotność pokojowa</td>
                        <td id="currentWilgWew"></td>
                    </tr>
                    <tr>
                        <td>Ciśnienie otoczenia</td>
                        <td id="currentCisnienie"></td>
                    </tr>
                    <tr>
                        <td>Oświetlenie pokojowe</td>
                        <td id="currentOswietlenie"></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <script>
       
        window.onload = (event) => {
            currentValues();
            setupChartToggle()
        };

        $('input[name="location"]').on('change', setupChartToggle);

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
        <?php elseif ($dataType == 'oswietlenie'): ?>
            unit = "lx";
            yAxisTitle = "Luks [lx]";
            yValueFormatString = "####'lx'";
        <?php endif; ?>

            var chart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                zoomEnabled: true,
                title: {
                    text: "<?php echo ucfirst($dataType); ?> - <?php echo ucfirst($location); ?>"
                },
                axisY: {
                    title: yAxisTitle,
                    valueFormatString: yValueFormatString
                },
                axisX: {
                    valueFormatString: "MM-DD HH-mm"
                },
                data: [{
                    type: "spline",
                    markerSize: 5,
                    xValueFormatString: "MM-DD HH:mm",
                    yValueFormatString: yValueFormatString, // Dynamiczny format wartości Y
                    xValueType: "dateTime",
                    dataPoints: <?php echo json_encode($charts->getData($dataType), JSON_NUMERIC_CHECK); ?>
                }]
            });
            chart.render();

        function currentValues() {
            $.ajax({
                url: "http://localhost/esp_data/setCurrVal.php",
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
                    document.getElementById("currentOswietlenie").innerHTML = data.oswietlenie + " lx";
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
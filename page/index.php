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

if(isset($_POST["fromDate"]) && isset($_POST["toDate"]))
{
    $charts->setTemperaturaWithDate($_POST["fromDate"], $_POST["toDate"]);
    $charts->setWilgotnoscWithDate($_POST["fromDate"], $_POST["toDate"]);
    $charts->setCisnienieWithDate($_POST["fromDate"], $_POST["toDate"]);
}
else
{
    $charts->setTemperature();
    $charts->setWilgotnosc();
    $charts->setCisnienie();
}

?>
<html lang="pl">
    <head>
        <title>Stacja pogodowa</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    </head>
    <body style="padding: 10px">

    <div class="container">
        <div class="row">
            <div class="col">
                <form id="chartSelect">
                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="chartType1" name="chartType" value="temp" checked>
                        <label for="chartType1" class="form-check-label">Wykres temperatury</label>
                    </div>

                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="chartType2" name="chartType" value="wilg">
                        <label for="chartType2" class="form-check-label">Wykres wilgotności</label>
                    </div>

                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="chartType3" name="chartType" value="cis">
                        <label for="chartType3" class="form-check-label">Wykres ciśnienia</label>
                    </div>
                    <button type="button" onclick="changeCharts()" class="btn btn-primary">Wybierz wykres</button>
                </form>
            </div>
            <div class="col">
                <div id="chartContainer" style="height: 300px; width: 100%;"></div>
                <form method="post" action="index.php">
                    <div class="mb-3">
                        <label for="fromDate">Od:</label>
                        <input name="fromDate" class="form-control" type="datetime-local"><br>
                    </div>

                    <div class="mb-3">
                        <label for="toDate">Do:</label>
                        <input name="toDate" class="form-control" type="datetime-local"><br>
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

    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <script>
        window.onload = (event) => {
            changeCharts();
            currentValues();
        };

        function changeCharts()
        {
            let elem = document.getElementsByName("chartType")

            for(let i = 0; i < elem.length; i++)
            {
                if(elem[i].checked)
                {
                    selectedCheck = elem[i].value
                }
            }

            if(selectedCheck == "temp")
            {
                var chart = new CanvasJS.Chart("chartContainer", {
                    animationEnabled: true,
                    title:{
                        text: "Temperatura"
                    },
                    axisY: {
                        title: "Temperatura [°C]",
                    },
                    data: [{
                        type: "spline",
                        markerSize: 5,
                        xValueFormatString: "YYYY",
                        yValueFormatString: "#,##0.##°C",
                        xValueType: "dateTime",
                        dataPoints: <?php echo json_encode($charts->getTemperatura(), JSON_NUMERIC_CHECK); ?>
                    }]
                });

                chart.render();
            }
            else if(selectedCheck == "wilg")
            {
                var chart = new CanvasJS.Chart("chartContainer", {
                    animationEnabled: true,
                    title:{
                        text: "Wilgotność"
                    },
                    axisY: {
                        title: "Wilgotność [%]",
                    },
                    data: [{
                        type: "spline",
                        markerSize: 5,
                        yValueFormatString: "##.##'%'",
                        xValueType: "dateTime",
                        dataPoints: <?php echo json_encode($charts->getWilgotnosc(), JSON_NUMERIC_CHECK); ?>
                    }]
                });

                chart.render();
            }

            else if(selectedCheck == "cis")
            {
                var chart = new CanvasJS.Chart("chartContainer", {
                    animationEnabled: true,
                    title:{
                        text: "Ciśnienie"
                    },
                    axisY: {
                        title: "Ciśnienie [hPa]",
                    },
                    data: [{
                        type: "spline",
                        markerSize: 5,
                        xValueFormatString: "YYYY",
                        yValueFormatString: "####.##'hPa'",
                        xValueType: "dateTime",
                        dataPoints: <?php echo json_encode($charts->getCisnienie(), JSON_NUMERIC_CHECK); ?>
                    }]
                });

                chart.render();
            }
        }

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

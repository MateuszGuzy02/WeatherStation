<?php
include_once "Charts.php";

$charts = new Charts;

$charts->setObecnaTemperatura();
$charts->setObecnaWilgotnosc();

if(isset($_POST["fromDate"]) && isset($_POST["toDate"]))
{
    $charts->setTemperaturaWithDate($_POST["fromDate"], $_POST["toDate"]);
    $charts->setWilgotnoscWithDate($_POST["fromDate"], $_POST["toDate"]);
}
else
{
    $charts->setTemperature();
    $charts->setWilgotnosc();
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
                        <td>Temperatura</td>
                        <td id="currentTemp"></td>
                    </tr>
                    <tr>
                        <td>Wilgotność</td>
                        <td id="currentWilg"></td>
                    </tr>
                </table>
            </div>
            <div class="col"></div>
        </div>
    </div>

    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <script>
        window.onload = (event) => {
            changeCharts()
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
                        suffix: "°C"
                    },
                    data: [{
                        type: "spline",
                        markerSize: 5,
                        xValueFormatString: "YYYY",
                        yValueFormatString: "$#,##0.##",
                        xValueType: "dateTime",
                        dataPoints: <?php echo json_encode($charts->getTemperatura(), JSON_NUMERIC_CHECK); ?>
                    }]
                });

                chart.render();
                console.log(document.getElementById("chartType").value)
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
                        suffix: "%"
                    },
                    data: [{
                        type: "spline",
                        markerSize: 5,
                        xValueFormatString: "YYYY",
                        yValueFormatString: "#.#%",
                        xValueType: "dateTime",
                        dataPoints: <?php echo json_encode($charts->getWilgotnosc(), JSON_NUMERIC_CHECK); ?>
                    }]
                });

                chart.render();


            }
        }

        function currentValues()
        {
            $.ajax({
                url: "http://localhost/projektArm/setCurrVal.php",
                dataType: 'json',
                method: 'get',
                data: {

                    'ajax': true
                },
                success: function(data) {
                    document.getElementById("currentTemp").innerHTML = data[0] + "°C"
                    document.getElementById("currentWilg").innerHTML = data[1] + "%"
                }
            });
        }

        setInterval(currentValues,3000);
    </script>

    </body>
</html>

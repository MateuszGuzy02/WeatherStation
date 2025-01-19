#include <WiFi.h>

#include <WiFiClientSecure.h>
#include <WebServer.h>
#include <HTTPClient.h>

const char* ssid = "Matys";
const char* password = "MateuszGuzy123";

const char* apSSID = "ESP32-AP";
const char* apPassword = "12345678";

const char* serverName = "http://192.168.0.106/test/post-esp-data.php";

String apiKeyValue = "tPmAT5Ab3j7F9";

WebServer server(80);

void handleRoot() 
{
  server.send(200, "text/plain", "Hello from ESP32!");
}

// Obsługa endpointu "/data"
void handleData() 
{
  if (server.hasArg("sensor") && server.hasArg("location")) {
    String sensor = server.arg("sensor");
    String location = server.arg("location");
    String value1 = ""; 
    String value2 = "";  
    String value3 = "";

    if (sensor == "AHT10") {
      if (server.hasArg("temperature") && server.hasArg("humidity") && server.hasArg("lux")) {
        value1 = server.arg("temperature"); 
        value2 = server.arg("humidity");
        value3 = server.arg("lux");   
      }
    } else if (sensor == "BMP280") {
      if (server.hasArg("temperature") && server.hasArg("humidity") && server.hasArg("pressure")) {
        value1 = server.arg("temperature");
        value2 = server.arg("humidity");  
        value3 = server.arg("pressure");
      }
    }

    Serial.print("Sensor: ");
    Serial.println(sensor);
    Serial.print("Location: ");
    Serial.println(location);
    Serial.print("Temperature: ");
    Serial.println(value1);
    Serial.print("Value2: ");
    Serial.println(value2);
    Serial.print("Value3: ");
    Serial.println(value3);

    sendDataToServer(sensor, location, value1, value2, value3);

    server.send(200, "text/plain", "Dane otrzymane i wysłane do serwera");
  } else {
    server.send(400, "text/plain", "Zły request: Brakujących parametrów");
  }
}

void sendDataToServer(String sensorName, String location, String value1, String value2, String value3) 
{
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;

    HTTPClient http;
    http.begin(client, serverName); 

    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String httpRequestData = "api_key=" + apiKeyValue 
                           + "&sensor=" + sensorName
                           + "&location=" + location
                           + "&value1=" + value1
                           + "&value2=" + value2
                           + "&value3=" + value3;

    Serial.print("httpRequestData: ");
    Serial.println(httpRequestData);

    int httpResponseCode = http.POST(httpRequestData);

    if (httpResponseCode > 0) {
      Serial.print("Kod odpowiedzi HTTP: ");
      Serial.println(httpResponseCode);
      Serial.println("Wysłano do bazy danych!");
    } else {
      Serial.print("Kod błędu: ");
      Serial.println(httpResponseCode);
    }

    http.end(); 
  } else {
    Serial.println("Brak połączenia Wi-Fi");
  }
}

void setup() 
{
  Serial.begin(9600);

  Serial.println("Łączenie z Wi-Fi...");
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  
  Serial.println("\nPołączono z Wi-Fi");
  Serial.print("Adres IP ESP32: ");
  Serial.println(WiFi.localIP());

  WiFi.softAP(apSSID, apPassword);
  Serial.print("Adres IP AP: ");
  Serial.println(WiFi.softAPIP());

  // Konfiguracja endpointów serwera HTTP
  server.on("/", handleRoot);
  server.on("/data", HTTP_POST, handleData);

  server.begin();
  Serial.println("Serwer HTTP uruchomiony");
}

void loop() 
{
  server.handleClient();

  // Sprawdzenie statusu połączenia Wi-Fi
  if (WiFi.status() != WL_CONNECTED) { 
    Serial.println("Brak połączenia z Wi-Fi! Ponowne łączenie..."); 
    WiFi.begin(ssid, password); 

    while (WiFi.status() != WL_CONNECTED) { 
      delay(500); 
      Serial.print("."); 
    } 

    Serial.println("\nPonownie połączono z Wi-Fi"); 
  }
}

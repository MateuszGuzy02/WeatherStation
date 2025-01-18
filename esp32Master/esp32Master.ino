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

// Obsługa endpointu głównego "/"
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
      if (server.hasArg("temperature") && server.hasArg("humidity")) {
        value1 = server.arg("temperature"); 
        value2 = server.arg("humidity");   
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

    server.send(200, "text/plain", "Data received and sent to server");
  } else {
    server.send(400, "text/plain", "Bad Request: Missing parameters");
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
      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);
      Serial.println("Wyslano do bazy danych!");
    } else {
      Serial.print("Error code: ");
      Serial.println(httpResponseCode);
    }

    http.end(); 
  } else {
    Serial.println("Wi-Fi not connected");
  }
}

void setup() 
{
  Serial.begin(9600);

  Serial.println("Connecting to Wi-Fi...");
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  
  Serial.println("\nConnected to Wi-Fi");
  Serial.print("ESP32 IP Address: ");
  Serial.println(WiFi.localIP());

  WiFi.softAP(apSSID, apPassword);
  Serial.print("AP IP address: ");
  Serial.println(WiFi.softAPIP());

  // Konfiguracja endpointów serwera HTTP
  server.on("/", handleRoot);
  server.on("/data", HTTP_POST, handleData);

  server.begin();
  Serial.println("HTTP server started");
}

void loop() 
{
  server.handleClient();

  // Check Wi-Fi connection status 
  if (WiFi.status() != WL_CONNECTED) { 
    Serial.println("Wi-Fi disconnected! Reconnecting..."); 
    WiFi.begin(ssid, password); 

    while (WiFi.status() != WL_CONNECTED) { 
      delay(500); 
      Serial.print("."); 
    } 

    Serial.println("\nReconnected to Wi-Fi"); 
  }
}
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_AHTX0.h>
#include <Adafruit_BMP280.h>

const char* apSSID = "ESP32-AP";
const char* apPassword = "12345678";

const char* serverName = "http://192.168.4.1/data";


Adafruit_AHTX0 aht;
Adafruit_BMP280 bmp;
WiFiClient client;

const String sensorName = "BMP280";
const String location = "Outdoor";

void setup() 
{
  Serial.begin(9600);

  Serial.println("Connecting to ESP32 AP...");
  WiFi.begin(apSSID, apPassword);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\nConnected to ESP32 AP");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());


  if (!aht.begin()) {
    Serial.println("Could not find a valid AHT10 sensor, check wiring!");
    while (1); 
  }

  if (!bmp.begin(0x76)) {
    Serial.println("Could not find a valid BMP280 sensor, check wiring!");
    while (1);
  }
}

void loop() 
{
  unsigned long freeMemoryBefore = ESP.getFreeHeap();
  Serial.println("Free memory before sending packet: " + String(freeMemoryBefore));

  sensors_event_t humidity, temperature;
  aht.getEvent(&humidity, &temperature);

  float hum = humidity.relative_humidity;
  float temp = bmp.readTemperature();
  float pressure = bmp.readPressure() / 100;

  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;

    http.begin(client, serverName);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String httpRequestData = "sensor=" + sensorName +
                            "&location=" + location +
                            "&temperature=" + String(temp) +
                            "&humidity=" + String(hum) + 
                            "&pressure=" + String(pressure);
                            

    int httpResponseCode = http.POST(httpRequestData);

    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);
      Serial.println("Response: " + response);
    } else {
      Serial.print("Error code: ");
      Serial.println(httpResponseCode);
    }

    http.end();
  } 
  else 
  {
    Serial.println("WiFi Disconnected! Reconnecting...");
    WiFi.begin(apSSID, apPassword); 
    while (WiFi.status() != WL_CONNECTED) { 
      delay(500); 
      Serial.print("."); 
    } 
    
    Serial.println("\nReconnected to ESP32 AP");
  }

  unsigned long freeMemoryAfter = ESP.getFreeHeap();
  Serial.println("Free memory after sending packet: " + String(freeMemoryAfter));
  delay(10000);
}
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_AHTX0.h>
#include <Adafruit_BMP280.h>
#include <Adafruit_SSD1306.h>
#include <Adafruit_GFX.h>

#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 32 
#define OLED_RESET    -1
#define SCREEN_ADDRESS 0x3C
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

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

  Serial.println("Łączenie z ESP32 AP...");
  WiFi.begin(apSSID, apPassword);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\nPołączono z ESP32 AP");
  Serial.print("Adres IP: ");
  Serial.println(WiFi.localIP());

  if (!aht.begin()) {
    Serial.println("Nie znaleziono czujnika AHT10!");
    while (1); 
  }

  if (!bmp.begin(0x76)) {
    Serial.println("Nie znaleziono czujnika BMP280!");
    while (1);
  }

  if(!display.begin(SSD1306_SWITCHCAPVCC, SCREEN_ADDRESS)) {
    Serial.println(F("Błąd przydzielania pamięci SSD1306"));
    for(;;);
  }

  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
}

void loop() 
{
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
      Serial.print("Kod odpowiedzi HTTP: ");
      Serial.println(httpResponseCode);
      Serial.println("Odpowiedź: " + response);
    } else {
      Serial.print("Kod błędu: ");
      Serial.println(httpResponseCode);
    }

    http.end();
  } 
  else 
  {
    Serial.println("Brak połączenia Wi-Fi! Ponowne łączenie...");
    WiFi.begin(apSSID, apPassword); 
    while (WiFi.status() != WL_CONNECTED) { 
      delay(500); 
      Serial.print("."); 
    } 
    
    Serial.println("\nPonownie połączono z ESP32 AP");
  }

  displayData(temp, hum, pressure);

  delay(10000);
}

void displayData(float temp, float hum, float pressure)
{
  display.clearDisplay();

  display.setCursor(37, 8);
  display.print(temp);
  display.print(" C");

  display.setCursor(37, 16); 
  display.print(hum);
  display.print(" %");

  display.setCursor(37, 24);
  display.print(pressure, 1);
  display.print(" hPa");

  display.display();
}

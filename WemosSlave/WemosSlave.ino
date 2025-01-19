#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_AHTX0.h>
#include <Adafruit_SSD1306.h>
#include <Adafruit_GFX.h>
#include <BH1750.h>

#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 32 
#define OLED_RESET    -1
#define SCREEN_ADDRESS 0x3C
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

const char* apSSID = "ESP32-AP";
const char* apPassword = "12345678";

const char* serverName = "http://192.168.4.1/data";

BH1750 lightMeter;
Adafruit_AHTX0 aht;
WiFiClient client;

const String sensorName = "AHT10";
const String location = "Room";

void setup() 
{
  Serial.begin(9600);
  delay(1000);

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

  if (!lightMeter.begin()) {
    Serial.println("Nie znaleziono czujnika BH1750!");
    while (1);
  }

  if(!display.begin(SSD1306_SWITCHCAPVCC, SCREEN_ADDRESS)) {
    Serial.println(F("Błąd alokacji SSD1306"));
    while (1);
  }

  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  display.display();
}

void loop() 
{
  sensors_event_t humidity, temperature;
  aht.getEvent(&humidity, &temperature);

  uint16_t lux = lightMeter.readLightLevel();
  float temp = temperature.temperature;
  float hum = humidity.relative_humidity;

  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;

    http.begin(client, serverName);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String httpRequestData = "sensor=" + sensorName +
                            "&location=" + location +
                            "&temperature=" + String(temp) + 
                            "&humidity=" + String(hum) +
                            "&lux=" + String(lux);

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
    Serial.println("Brak połączenia z WiFi! Ponowne łączenie...");
    WiFi.begin(apSSID, apPassword); 
    while (WiFi.status() != WL_CONNECTED) { 
      delay(500); 
      Serial.print("."); 
    } 
    
    Serial.println("\nPonownie połączono z ESP32 AP");
  }

  displayData(temp, hum, lux);
  
  delay(10000);
}

void displayData(float temp, float hum, uint16_t lux)
{
  display.clearDisplay();

  display.setCursor(37, 8);
  display.print(temp);
  display.print(" C");

  display.setCursor(37, 16); 
  display.print(hum);
  display.print(" %");

  display.setCursor(37, 24);
  display.print(lux, 1);
  display.print(" lx");

  display.display();
}

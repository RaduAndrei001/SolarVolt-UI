#include <WiFi.h>
#include <HTTPClient.h>
#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BMP280.h>

/* ===== Config Wi-Fi ===== */
const char* ssid     = "DIGI-39Eu";
const char* password = "hkbAQzyCHU";

/* ===== Server ===== */
const char* serverName = "http://192.168.1.129/ESP32_webserver/insert_meteo.php";

/* ===== Senzor BMP280 pe SPI ===== */
#define BMP_SCK  18   // GPIO18
#define BMP_MISO 19   // GPIO19
#define BMP_MOSI 23   // GPIO23
#define BMP_CS   5    // GPIO5

Adafruit_BMP280 bmp(BMP_CS); // Doar CS pentru SPI hardware

/* ===== Variabile ===== */
String condition = "senin"; // Poți schimba manual sau automat (ex: cu senzor de lumină)

/* ===== Prototipuri ===== */
void wifiConnect();

/* ====================== */
void setup() {
  Serial.begin(115200);

  // Inițializează SPI hardware pentru BMP280
  SPI.begin(BMP_SCK, BMP_MISO, BMP_MOSI, BMP_CS);
  bool bmpOk = bmp.begin(BMP_CS);
  if (bmpOk) {
    Serial.println("BMP280 detectat pe SPI!");
  } else {
    Serial.println("Nu s-a găsit niciun senzor BMP280 pe SPI!");
  }

  wifiConnect();
}

/* ====================== */
void loop() {
  wifiConnect();

  // Citire BMP280 doar dacă a fost detectat
  float bmp_temp = NAN, bmp_pres = NAN;
  if (bmp.sensorID() == 0x58) {
    bmp_temp = bmp.readTemperature();
    bmp_pres = bmp.readPressure() / 100.0F;
  }

  if (isnan(bmp_temp)) {
    Serial.println("Eroare citire temperatură!");
    delay(5000);
    return;
  }

  String condition = "senin";

  Serial.print("Temp: ");
  Serial.print(bmp_temp);
  Serial.print(" °C, Pressure: ");
  Serial.print(bmp_pres);
  Serial.println(" hPa");

  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverName);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String postData = "temperature=" + String(bmp_temp, 1) +
                      "&pressure=" + String(bmp_pres, 1) +
                      "&condition=" + condition;
    int code = http.POST(postData);

    if (code == HTTP_CODE_OK) {
      Serial.printf("Trimis meteo: T=%.1f°C, P=%.1f hPa, Cond=%s\n",
        bmp_temp, bmp_pres, condition.c_str());
    } else {
      Serial.printf("Eroare HTTP: %d\n", code);
    }
    http.end();
  } else {
    Serial.println("WiFi deconectat!");
  }

  delay(60000); // trimite la fiecare 60 secunde
}

/* ---------- funcţii ---------- */
void wifiConnect() {
  if (WiFi.status() == WL_CONNECTED) return;
  Serial.print("Conectare Wi-Fi");
  WiFi.begin(ssid, password);
  uint32_t t0 = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - t0 < 15000) {
    delay(500); Serial.print('.');
  }
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nConectat! IP: " + WiFi.localIP().toString());
  } else {
    Serial.println("\nConectare eșuată!");
  }
}

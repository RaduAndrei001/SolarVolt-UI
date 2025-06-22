/********************************************************************
   ESP32 – Datalogger panouri solare + Meteo (BMP280)
   - Măsoară lumină (fotorezistor), tensiune baterie, temperatură și presiune atmosferică
   - Trimite date panou la insert.php și date meteo la insert_meteo.php
   - Înregistrează automat panoul la pornire
 ********************************************************************/

#include <WiFi.h>
#include <HTTPClient.h>
#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BMP280.h>

/* ===== Config Wi-Fi ===== */
const char* ssid     = "DIGI-39Eu";
const char* password = "hkbAQzyCHU";

/* ===== Server ===== */
const char* serverPanel = "http://192.168.1.129/ESP32_webserver/insert.php";
const char* serverMeteo = "http://192.168.1.129/ESP32_webserver/insert_meteo.php";
const char* registerUrl = "http://192.168.1.129/ESP32_webserver/register_panel.php";

/* ===== Pini & ADC ===== */
const int PIN_LIGHT = 34;
const int PIN_BATT  = 35;
const int PIN_LED   = 2;
const int PIN_EXTRA = 32; // Adaugă pinul 32 pentru citire tensiune suplimentară

const float VREF       = 3.3f;
const int   ADC_RES    = 4095;
const float DIV_FACTOR = 2.98f;

/* ===== Senzor BMP280 pe SPI ===== */
#define BMP_SCK  18   // GPIO18
#define BMP_MISO 19   // GPIO19
#define BMP_MOSI 23   // GPIO23
#define BMP_CS   5    // GPIO5

Adafruit_BMP280 bmp(BMP_CS); // Doar CS pentru SPI hardware

/* ===== Variabile ===== */
int panelId = -1;
String panelName = "";
String lastIp = "";
String condition = "senin"; // Poți schimba manual sau automat

/* ==== prototipuri ==== */
float readVoltageSimple(int gpio);
float readVoltageDivided(int gpio);
void handleLed(float lightVoltage);
void wifiConnect();
bool registerPanel();
void sendPanelData(float vLight, float vBatt, bool isFaulty);
void sendMeteoData(float temp, float pres, String condition);

/* ====================== */
void setup()
{
  Serial.begin(115200);
  pinMode(PIN_LED, OUTPUT);
  digitalWrite(PIN_LED, HIGH);

  analogReadResolution(12);
  analogSetAttenuation(ADC_11db);

  // Inițializează SPI hardware pentru BMP280
  SPI.begin(BMP_SCK, BMP_MISO, BMP_MOSI, BMP_CS);
  bool bmpOk = bmp.begin(BMP_CS);
  if (bmpOk) {
    Serial.println("BMP280 detectat pe SPI!");
  } else {
    Serial.println("Nu s-a găsit niciun senzor BMP280 pe SPI!");
  }

  wifiConnect();

  // IMPORTANT: Nu continua dacă nu primești un ID valid!
  while (!registerPanel()) {
    Serial.println("Reîncercare înregistrare panou...");
    delay(3000);
  }
  lastIp = WiFi.localIP().toString();
}

void loop()
{
  wifiConnect();

  String currentIp = WiFi.localIP().toString();
  if (currentIp != lastIp) {
    Serial.println("IP schimbat, retrimit date către server...");
    while (!registerPanel()) {
      Serial.println("Reîncercare înregistrare panou după schimbare IP...");
      delay(3000);
    }
    lastIp = currentIp;
  }

  // Nu trimite date dacă nu ai un ID valid!
  if (panelId <= 0) {
    Serial.println("ID panou invalid, reîncercare înregistrare...");
    while (!registerPanel()) {
      delay(3000);
    }
    return;
  }

  // Citire panou solar
  float vLight = readVoltageSimple(PIN_LIGHT);
  float vBatt  = readVoltageDivided(PIN_BATT);
  bool isFaulty = (vLight < 1.0f || vBatt < 3.0f);
  handleLed(vLight);

  // Citire tensiune suplimentară pe pinul 32
  float vExtra = readVoltageSimple(PIN_EXTRA);
  Serial.printf("Tensiune suplimentară pe GPIO32: %.3f V\n", vExtra);

  // Citire meteo BMP280
  float bmp_temp = NAN, bmp_pres = NAN;
  if (bmp.sensorID() == 0x58) {
    bmp_temp = bmp.readTemperature();
    bmp_pres = bmp.readPressure() / 100.0F;
  }

  // Determină condition meteo (exemplu simplu, poți extinde)
  condition = "senin";
  if (!isnan(bmp_temp) && bmp_temp < 0) condition = "ceata";
  // (poți adăuga și alte reguli pe viitor)

  // Trimite date panou solar
  sendPanelData(vLight, vBatt, isFaulty);

  // Trimite date meteo dacă ai citire validă
  if (!isnan(bmp_temp) && !isnan(bmp_pres)) {
    sendMeteoData(bmp_temp, bmp_pres, condition);
  } else {
    Serial.println("Eroare citire BMP280, nu trimit date meteo.");
  }

  delay(60000); // trimite la fiecare 60 secunde
}

void sendPanelData(float vLight, float vBatt, bool isFaulty) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverPanel);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String postData = "panel_id=" + String(panelId) +
                      "&voltage1=" + String(vLight, 3) +
                      "&voltage2=" + String(vBatt, 3) +
                      "&faulty=" + String(isFaulty ? 1 : 0);
    int code = http.POST(postData);

    if (code == HTTP_CODE_OK) {
      Serial.printf("Trimis panou %d (%s): V_lumina=%.3f V  V_batt=%.3f V\n", panelId, panelName.c_str(), vLight, vBatt);
    } else {
      Serial.printf("Eroare HTTP panel: %d\n", code);
    }
    http.end();
  }
}

void sendMeteoData(float temp, float pres, String condition) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverMeteo);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String postData = "temperature=" + String(temp, 1) +
                      "&pressure=" + String(pres, 1) +
                      "&condition=" + condition;
    int code = http.POST(postData);

    if (code == HTTP_CODE_OK) {
      Serial.printf("Trimis meteo: T=%.1f°C, P=%.1f hPa, Cond=%s\n", temp, pres, condition.c_str());
    } else {
      Serial.printf("Eroare HTTP meteo: %d\n", code);
    }
    http.end();
  }
}

bool registerPanel()
{
  if (WiFi.status() != WL_CONNECTED) return false;

  HTTPClient http;
  http.begin(registerUrl);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  String mac = WiFi.macAddress();
  String ip  = WiFi.localIP().toString();
  String postData = "mac=" + mac + "&ip=" + ip;

  int code = http.POST(postData);
  String payload = http.getString();
  Serial.printf("HTTP code: %d, răspuns: [%s]\n", code, payload.c_str());
  if (code == HTTP_CODE_OK) {
    int sep = payload.indexOf('|');
    if (sep > 0) {
      panelId = payload.substring(0, sep).toInt();
      panelName = payload.substring(sep + 1);
      Serial.printf("Panou înregistrat: ID=%d, Nume=%s\n", panelId, panelName.c_str());
      http.end();
      return true;
    }
  }
  panelId = -1;
  panelName = "";
  http.end();
  return false;
}

float readVoltageSimple(int gpio) {
  return analogRead(gpio) * (VREF / ADC_RES);
}

float readVoltageDivided(int gpio) {
  return analogRead(gpio) * (VREF / ADC_RES) * DIV_FACTOR;
}

void handleLed(float lightVoltage) {
  digitalWrite(PIN_LED, (lightVoltage > 2.0f) ? LOW : HIGH);
}

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

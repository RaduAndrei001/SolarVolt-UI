/********************************************************************
   ESP32 – datalogger lumină & baterie
   • GPIO 34  – fotorezistor (fără divizor → conversie simplă)
   • GPIO 35  – tensiune baterie (divizor 10 kΩ / 5 kΩ → factor 2.96)
   • GPIO 2   – LED status (stins dacă V_lumină > 2 V)
   • trimite date prin POST la insert.php
 ********************************************************************/

#include <WiFi.h>
#include <HTTPClient.h>

/* ===== Wi-Fi ===== */
const char* ssid     = "DIGI-39Eu";
const char* password = "hkbAQzyCHU";

/* ===== Server ===== */
const char* serverName = "http://192.168.1.129/ESP32_webserver/insert.php";
const char* registerUrl = "http://192.168.1.129/ESP32_webserver/register_panel.php";

/* ===== Pini & ADC ===== */
const int PIN_LIGHT = 34;
const int PIN_BATT  = 35;
const int PIN_LED   = 2;

const float VREF       = 3.3f;
const int   ADC_RES    = 4095;
const float DIV_FACTOR = 2.98f;

/* ===== Variabile ===== */
int panelId = -1;
String panelName = "";
String lastIp = "";

/* ==== prototipuri ==== */
float readVoltageSimple(int gpio);
float readVoltageDivided(int gpio);
void handleLed(float lightVoltage);
void wifiConnect();
bool registerPanel();

/* ====================== */
void setup()
{
  Serial.begin(115200);
  pinMode(PIN_LED, OUTPUT);
  digitalWrite(PIN_LED, HIGH);

  analogReadResolution(12);
  analogSetAttenuation(ADC_11db);

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

  float vLight = readVoltageSimple(PIN_LIGHT);
  float vBatt  = readVoltageDivided(PIN_BATT);

  bool isFaulty = (vLight < 1.0f || vBatt < 3.0f);

  handleLed(vLight);

  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverName);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String postData = "panel_id=" + String(panelId) +
                      "&voltage1=" + String(vLight, 3) +
                      "&voltage2=" + String(vBatt, 3) +
                      "&faulty=" + String(isFaulty ? 1 : 0);
    int code = http.POST(postData);

    if (code == HTTP_CODE_OK) {
      Serial.printf("Trimis panou %d (%s): V_lumina=%.3f V  V_batt=%.3f V\n", panelId, panelName.c_str(), vLight, vBatt);
    } else {
      Serial.printf("Eroare HTTP: %d\n", code);
    }
    http.end();
  }

  delay(5000);
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
  // Dacă nu primești ID|Nume, resetează panelId și panelName
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

// Codul tău ESP32 este corect pentru fluxul de înregistrare automată a panoului și trimiterea datelor,
// cu următoarele observații și recomandări pentru robustețe:

// 1. Asigură-te că serverul PHP răspunde corect la POST și nu există spații/linii goale înainte de <?php.
// 2. Debug-ul Serial afișează clar codul HTTP și răspunsul serverului pentru register_panel.php.
// 3. MAC-ul este trimis corect ca parametru POST.
// 4. Dacă răspunsul nu conține separatorul "|", ESP32 va reîncerca la 3 secunde (comportament corect).
// 5. Dacă primești "HTTP code: 405, răspuns: [Utilizează metoda POST.]", problema este pe server (vezi register_panel.php).
// 6. Dacă primești "HTTP code: 200, răspuns: [ID|Nume]", ESP32 va folosi ID-ul și numele primit.
// 7. Pentru siguranță, poți adăuga și un timeout la reîncercare sau un număr maxim de încercări la înregistrare.

// Dacă pe Serial Monitor vezi "HTTP code: 200, răspuns: [1|ESP32_A]" sau similar, codul ESP32 funcționează corect.
// Dacă vezi altceva, problema este la server sau la rețeaua dintre ESP32 și server.

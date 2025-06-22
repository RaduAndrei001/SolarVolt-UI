# Proiect Monitorizare Panouri Solare cu ESP32

## Scopul Proiectului

Scopul acestui proiect este să creeze un sistem complet, ușor de folosit, pentru monitorizarea și analiza inteligentă a panourilor solare. Proiectul colectează date de la panouri (tensiune lumină, tensiune baterie), măsoară condițiile meteo (temperatură, presiune atmosferică), salvează totul într-o bază de date și folosește inteligența artificială pentru a prezice comportamentul sistemului și a detecta problemele automat. Datele sunt afișate într-o interfață web modernă.

---

## Contribuția Proiectului în Industrie (următorii 5 ani)

- **Automatizare și mentenanță predictivă:** Sistemul poate reduce costurile de întreținere, anticipând problemele înainte să apară defecțiuni majore.
- **Optimizare energetică:** Permite reglarea automată a consumului sau vânzarea surplusului de energie, crescând eficiența.
- **Scalabilitate și adaptabilitate:** Poate fi extins rapid pentru ferme solare mari sau pentru monitorizarea la distanță a sistemelor izolate.
- **Reducerea pierderilor:** Detectarea rapidă a scăderii randamentului sau a defecțiunilor duce la intervenții rapide și la creșterea duratei de viață a echipamentelor.
- **Integrare cu rețele inteligente:** Datele colectate pot fi folosite pentru optimizarea consumului la nivel de comunitate sau oraș.

---

## Legătura dintre Software și Hardware

- **Hardware-ul** (ESP32, senzori, panouri solare) colectează date din mediul fizic (tensiuni, temperatură, presiune).
- **Software-ul** (cod ESP32, server PHP) preia aceste date, le procesează, le stochează în baza de date și le afișează pe web.
- ESP32 trimite datele prin HTTP POST către serverul web, care le salvează și le folosește pentru predicții și vizualizare.

---

## Cum a fost construit software-ul proiectului

- **Programe folosite:** Arduino IDE (pentru ESP32), XAMPP (Apache+MySQL+PHP), Visual Studio Code, Python 3, phpMyAdmin.
- **Limbaje de programare:** C++ (ESP32), PHP (backend web), JavaScript (frontend), SQL (baza de date).
- **Platforme:** ESP32 (hardware), Windows/Linux (server web), browser web (vizualizare).
- **Tip hardware folosit:** ESP32-WROOM-32D, server PC/laptop, rețea Wi-Fi.
- **Structură software:**  
  - Cod ESP32 pentru colectare și trimitere date.
  - Backend PHP pentru primire, validare și stocare date.
  - Interfață web pentru vizualizare și administrare.

---

## Cum a fost construit hardware-ul proiectului

- **Programe folosite:** Arduino IDE pentru programarea ESP32.
- **Limbaje de programare:** C++ (Arduino/ESP32).
- **Platforme:** Placă de dezvoltare ESP32-WROOM-32D.
- **Componente hardware:** 
  - 1 panou solar 22V 0.8A
  - 2 panouri solare 5V 1A
  - 1 senzor BMP280 (temperatură și presiune)
  - 2 plăci ESP32-WROOM-32D
  - regulator voltaj LM7805
  - condensatori: 10uF 25V, 220uF 16V, 100nF ceramic
  - fotoresistor 5537 5MM
  - rezistori: 10kΩ, 410kΩ, (valoare lipsă completare)
- **Montaj:**  
  - Panourile solare conectate la ESP32 pentru măsurarea tensiunii.
  - Senzorul BMP280 conectat pe SPI pentru măsurarea temperaturii și presiunii.
  - Alimentare stabilizată cu LM7805 și condensatori pentru protecție.

---

## Cum a fost testat software-ul proiectului

- **Testare manuală:** Trimitere date de la ESP32, verificare răspuns server, vizualizare date în web.
- **Testare automată:** Scripturi de test pentru API (curl, Postman).
- **Instrumente:** Serial Monitor, browser, Postman, phpMyAdmin.
- **Rezultate:** Datele au fost colectate corect, statusul panourilor s-a actualizat.

---

## Protocoale de comunicare folosite și legătura dintre ele

- **HTTP/HTTPS:** ESP32 trimite datele către serverul PHP prin POST.
- **MySQL:** Serverul PHP salvează datele în baza de date.
- **SPI/I2C:** Pentru comunicarea ESP32 cu senzorii hardware.
- **Legătură:** Datele colectate hardware ajung în software prin HTTP, sunt procesate și stocate, apoi folosite pentru predicții și vizualizare.

---

## Cum a fost testat hardware-ul proiectului

- **Testare individuală:** Fiecare senzor a fost testat separat cu sketch-uri simple (ex: citire BMP280, citire fotoresistor).
- **Testare integrată:** Toate componentele conectate pe breadboard/prototip, verificare alimentare și semnal.
- **Validare:** Valorile citite au fost comparate cu instrumente de măsură externe (multimetru, termometru).

---

## Testarea software-ului: tipuri de teste, instrumente, rezultate

- **Tipuri de teste:** Testare funcțională (trimitere date, afișare web), testare de stres (trimitere rapidă de date).
- **Instrumente:** Serial Monitor, browser, Postman, phpMyAdmin.
- **Rezultate:** Sistemul a funcționat stabil, datele s-au salvat corect.

---

## Construcția bazei de date MySQL

- **Tabele principale:**
  - `solar_panels`: info panouri (id, nume, MAC, IP, status, last_update)
  - `solar_data`: date panouri (id, panel_id, light_voltage, battery_voltage, predicted_battery, timestamp)
  - `meteo_data`: date meteo (id, temperature, pressure, condition, predicted_temperature, timestamp)
  - Tabele dedicate pentru fiecare panou: `solar_data_panel_X`
- **Relații:** Fiecare panou are datele sale, meteo_data este independentă.
- **Instrumente:** phpMyAdmin, scripturi SQL.

---

## Hardware folosit în proiect

- 1 panou solar 22V 0.8A
- 2 panouri solare 5V 1A
- 1 BMP280 senzor de temperatură și presiune
- 2 plăci dezvoltare ESP32-WROOM-32D
- regulator de voltaj LM7805
- condensator electrolitic 10uF 25V
- condensator electrolitic 220uF 16V
- condensator ceramic 100nF
- fotoresistor 5537 5MM
- 1 rezistor 10,0 KΩ ± 1 %
- 1 rezistor (valoare lipsă completare)
- 1 rezistor 410 kΩ ± 1 %

---

## Pe scurt

Acest proiect îți arată cum să monitorizezi și să prezici comportamentul panourilor solare cu ESP32, PHP, MySQL folosind componente simple și software open-source. Datele sunt colectate, analizate și afișate ușor, iar sistemul poate fi extins pentru orice aplicație industrială sau casnică.

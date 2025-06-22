# Proiect Monitorizare și Predicție Panouri Solare cu ESP32

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
- **Software-ul** (cod ESP32, server PHP, AI Python) preia aceste date, le procesează, le stochează în baza de date și le afișează pe web.
- ESP32 trimite datele prin HTTP POST către serverul web, care le salvează și le folosește pentru predicții și vizualizare.
- AI-ul analizează datele și poate trimite alerte dacă detectează anomalii.

---

## Cum a fost construit software-ul proiectului

- **Programe folosite:** Arduino IDE (pentru ESP32), XAMPP (Apache+MySQL+PHP), Visual Studio Code, Python 3, phpMyAdmin.
- **Limbaje de programare:** C++ (ESP32), PHP (backend web), JavaScript (frontend), Python (AI), SQL (baza de date).
- **Platforme:** ESP32 (hardware), Windows/Linux (server web și AI), browser web (vizualizare).
- **Tip hardware folosit:** ESP32-WROOM-32D, server PC/laptop, rețea Wi-Fi.
- **Structură software:**  
  - Cod ESP32 pentru colectare și trimitere date.
  - Backend PHP pentru primire, validare și stocare date.
  - Scripturi Python pentru AI (predicție și detecție anomalii).
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
- **Testare AI:** Validare predicții cu date reale și simulate.
- **Instrumente:** Serial Monitor, browser, Postman, phpMyAdmin.
- **Rezultate:** Datele au fost colectate corect, statusul panourilor s-a actualizat, predicțiile AI au funcționat conform așteptărilor.

---

## Protocoale de comunicare folosite și legătura dintre ele

- **HTTP/HTTPS:** ESP32 trimite datele către serverul PHP prin POST.
- **MySQL:** Serverul PHP salvează datele în baza de date.
- **JSON:** Pentru comunicarea între PHP și serviciul AI Python.
- **SPI/I2C:** Pentru comunicarea ESP32 cu senzorii hardware.
- **Legătură:** Datele colectate hardware ajung în software prin HTTP, sunt procesate și stocate, apoi folosite pentru predicții și vizualizare.

---

## Cum a fost testat hardware-ul proiectului

- **Testare individuală:** Fiecare senzor a fost testat separat cu sketch-uri simple (ex: citire BMP280, citire fotoresistor).
- **Testare integrată:** Toate componentele conectate pe breadboard/prototip, verificare alimentare și semnal.
- **Validare:** Valorile citite au fost comparate cu instrumente de măsură externe (multimetru, termometru).

---

## Testarea software-ului: tipuri de teste, instrumente, rezultate

- **Tipuri de teste:** Testare funcțională (trimitere date, afișare web), testare de stres (trimitere rapidă de date), testare AI (validare predicții).
- **Instrumente:** Serial Monitor, browser, Postman, phpMyAdmin, scripturi Python.
- **Rezultate:** Sistemul a funcționat stabil, datele s-au salvat corect, AI-ul a prezis valori apropiate de realitate, anomaliile au fost detectate.

---

## Cum a fost construit sistemul de predicție AI și trainerul AI

### 1. Construcția sistemului de predicție AI

**Ce este și de ce e util:**  
Sistemul de predicție AI din acest proiect folosește algoritmi de inteligență artificială pentru a anticipa valorile viitoare ale temperaturii și ale tensiunii bateriei, dar și pentru a detecta automat dacă ceva nu este în regulă cu panourile (anomalie). Practic, AI-ul "învață" din datele reale colectate și apoi poate spune dacă valorile noi sunt normale sau dacă există o problemă.

**Cum funcționează, pas cu pas (explicație pe scurt și clar):**
1. **Colectare date:** ESP32 măsoară tensiunea de la panouri, temperatura și presiunea atmosferică, apoi trimite aceste date către server.
2. **Stocare date:** Serverul PHP primește datele și le salvează în baza de date MySQL.
3. **Predicție în timp real:** De fiecare dată când vine o nouă măsurătoare, serverul trimite datele către un serviciu Python (Flask API) care rulează modelul AI.
4. **Ce face AI-ul:**  
   - Privește la valorile primite (ex: tensiune lumină, tensiune baterie, temperatură, presiune, oră, zi, condiție meteo).
   - Folosește modelul RandomForestRegressor pentru a calcula ce valoare ar fi "normală" pentru acea situație.
   - Compară valoarea reală cu cea prezisă. Dacă diferența e mare sau modelul IsolationForest spune că e ceva suspect, marchează ca anomalie.
5. **Rezultat:**  
   - Dacă totul e ok, datele sunt afișate normal.
   - Dacă AI-ul detectează o anomalie, sistemul poate trimite o alertă (ex: email) și marchează panoul ca "faulty" (defect).

**Ce algoritmi și date se folosesc:**
- **RandomForestRegressor:** Un algoritm de tip "pădure de arbori de decizie" care poate învăța relații complexe între date (ex: cum influențează ora, temperatura și presiunea valoarea bateriei).
- **IsolationForest:** Un algoritm special pentru a detecta anomalii (valori care nu seamănă cu restul).
- **Date folosite:**  
  - Pentru panouri: tensiune lumină, tensiune baterie, oră, zi din săptămână, temperatură, presiune, condiție atmosferică, timestamp.
  - Pentru meteo: presiune atmosferică, oră, zi din săptămână, condiție atmosferică, timestamp.

**Platforme și hardware:**
- Python 3, scikit-learn, pandas, Flask (API), MySQL.
- Rulează pe un PC/laptop/server cu acces la baza de date.

**Fluxul complet:**
1. ESP32 → PHP → MySQL (salvare date)
2. PHP → Python Flask API (pentru predicție/anomalii)
3. Python returnează predicția și statusul (ok/anomalie)
4. PHP salvează rezultatul și îl afișează pe web

---

### 2. Construcția trainerului AI

**Ce este trainerul AI:**  
Este un script Python care "învață" AI-ul să facă predicții, folosind datele istorice colectate de la panouri și din mediul meteo.

**Cum funcționează, pe scurt:**
1. **Citește datele istorice** din MySQL (toate măsurătorile colectate până acum).
2. **Prelucrează datele:**  
   - Extrage ora și ziua din timestamp.
   - Transformă condiția atmosferică în cifre (ex: "senin" = 0, "innorat" = 1 etc).
3. **Antrenează modelele:**  
   - Un model RandomForest pentru predicția tensiunii bateriei.
   - Un model RandomForest pentru predicția temperaturii meteo.
   - Un model IsolationForest pentru detecția anomaliilor la panouri.
4. **Salvează modelele antrenate** pe disc (fișiere `.pkl`).
5. **Testare:**  
   - Modelele sunt testate cu date reale și simulate pentru a verifica dacă prezic corect și dacă detectează anomaliile.

**Ce face diferit față de alte modele:**  
- Folosește date reale din sistemul tău, nu date generice.
- Poate fi reantrenat oricând cu date noi, deci se adaptează la orice sistem de panouri.
- Detectează anomalii în timp real, nu doar face predicții.
- Poate fi extins ușor cu alți algoritmi sau date suplimentare.

---

## Exemplu concret de predicție AI (explicat pe scurt)

1. **Vine o nouă măsurătoare:**  
   - Tensiune lumină: 2.5V  
   - Tensiune baterie: 3.8V  
   - Temperatura: 25°C  
   - Presiune: 1012 hPa  
   - Condiție: "senin"  
   - Ora: 14:00, Zi: marți

2. **Serverul trimite aceste date la AI:**  
   - AI-ul calculează: "În mod normal, la ora 14:00, marți, cu senzorii aceștia, tensiunea bateriei ar trebui să fie 3.9V"
   - Dacă diferența între 3.8V (real) și 3.9V (prezis) este mică, totul e ok.
   - Dacă diferența e mare sau IsolationForest spune că e ceva ciudat, marchează ca anomalie.

3. **Ce se întâmplă mai departe:**  
   - Dacă e anomalie, sistemul trimite alertă și marchează panoul ca defect.
   - Dacă nu, datele sunt afișate normal pe site.

---

## Cum poți folosi/construi și tu un astfel de sistem pentru lucrarea de licență

- **Colectezi date reale** cu ESP32 și le salvezi în MySQL.
- **Rulezi scriptul de training** pentru AI pe PC/laptop.
- **Integrezi serviciul AI** cu PHP (prin API Flask).
- **Testezi sistemul** cu date reale și vezi cum AI-ul prezice și detectează probleme.
- **Documentezi fiecare pas** (colectare, training, predicție, alertare) pentru lucrarea de diplomă.
- **Poți extinde sistemul** cu noi senzori, noi algoritmi sau funcții (ex: predicție producție, optimizare consum).

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

Acest proiect îți arată cum să monitorizezi și să prezici comportamentul panourilor solare cu ESP32, PHP, MySQL și AI, folosind componente simple și software open-source. Datele sunt colectate, analizate și afișate ușor, iar sistemul poate fi extins pentru orice aplicație industrială sau casnică.

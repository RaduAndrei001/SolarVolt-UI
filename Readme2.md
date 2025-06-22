# Descriere pe scurt a fiecărei pagini PHP din proiect

- **index.php**  
  Redirecționează automat către pagina principală (homepage.php).

- **homepage.php**  
  Pagina publică cu prezentarea proiectului, istoria panourilor solare și idei de extindere.

- **login.php**  
  Pagina de autentificare pentru administratori. Verifică utilizatorul și parola pentru acces la zona de administrare.

- **logout.php**  
  Deconectează utilizatorul și îl redirecționează la login.

- **admin.php**  
  Pagina principală de administrare: afișează statusul panourilor, graficele de date și oferă acces la toate funcțiile de management.

- **add_panel.php**  
  Permite adăugarea sau editarea manuală a unui panou solar în baza de date.

- **status.php**  
  Listă cu toate panourile, statusul lor, opțiuni de editare/ștergere și ultimele actualizări.

- **register_panel.php**  
  Endpoint pentru ESP32: înregistrează automat un panou nou sau actualizează IP-ul unui panou existent pe baza MAC-ului.

- **insert.php**  
  Endpoint pentru ESP32: primește datele de la panouri (tensiuni, status), le salvează în tabela dedicată fiecărui panou și trimite la AI pentru predicție/anomalii.

- **insert_meteo.php**  
  Endpoint pentru ESP32: primește datele meteo (temperatură, presiune, condiție), le salvează și trimite la AI pentru predicție.

- **get_data.php**  
  Returnează datele pentru un anumit panou (din tabela solar_data_panel_X) în format JSON pentru graficele din admin.php.

- **get_meteo.php**  
  Returnează ultimele date meteo în format JSON pentru graficele din meteo.php.

- **solar_data_table.php**  
  Afișează tabelul cu ultimele 100 de măsurători pentru un panou selectat.

- **meteo.php**  
  Pagina cu grafic meteo (temperatură, presiune, predicție AI) și informații despre influența vremii asupra panourilor.

- **export.php**  
  Pagina de unde poți selecta panouri și exporta datele lor în format CSV.

- **export_csv.php**  
  Generează și descarcă fișierul CSV cu datele panourilor selectate.

- **history.php**  
  Afișează graficul cu istoricul multi-anual (medii zilnice) pentru un panou selectat.

- **energy_opt.php**  
  Pagina de optimizare energetică: arată starea bateriei și recomandă acțiuni pentru consumatori pe baza predicției AI.

- **about.php**  
  Pagina cu informații suplimentare despre proiect, istorie și idei de dezvoltare.

- **ping.php**  
  Script pentru verificarea online/offline a panourilor (ping IP) și actualizarea statusului în baza de date.


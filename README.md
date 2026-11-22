# Jugendfeuerwehr Zeltlager Anmeldung

Webanwendung zur Online-Anmeldung von Jugendfeuerwehren zu einem (Kreis-)Zeltlager.
Jede Jugendfeuerwehr meldet sich mit einer verantwortlichen Person und einer Teilnahmeliste an
und kann ihre Anmeldung bis zum Anmeldeschluss über einen persönlichen Link jederzeit ändern.
Die Anmeldungen werden als XML-Dateien gespeichert und lassen sich in die
[KZL-Verwaltungssoftware](https://github.com/KJFCux/jf-kzl) importieren.

![Screenshot](https://github.com/KJFCux/jf-bundeswettbewerb-anmeldung/assets/1337304/604af93d-c8a7-4ffd-80c5-c23592e646c6)

## Funktionen

- Anmeldung ohne Benutzerkonto, Zugriff über einen geheimen Link (`?anmeldung=<Schlüssel>`)
- Bestätigungs-E-Mail mit dem Link an die verantwortliche Person
- Interne Benachrichtigung bei jeder Anmeldung und Änderung, inklusive Link auf den damaligen Stand
- Teilnahmeliste mit Adresse, Geburtsdatum, Geschlecht, Status, Essgewohnheiten und Unverträglichkeiten
- Automatische Berechnung des Gesamtbetrags inklusive konfigurierbarem Geschwisterrabatt
  (Standard: 2. Geschwisterkind 75 %, jedes weitere 50 % des Beitrags)
- Anzahl der Personen und zu zahlender Betrag in den E-Mails
- Spam-Schutz durch ein für Menschen unsichtbares Köderfeld (Honeypot) und eine Begrenzung der Speichervorgänge pro IP-Adresse
- Anmeldeschluss: Danach ist die Anmeldung nur noch lesbar
- Versionierung: Jede Änderung wird zusätzlich als eigene Datei gespeichert

## Voraussetzungen

- Webserver mit PHP 7.4 oder neuer (empfohlen: PHP 8.x)
- PHP-Erweiterungen `dom`, `simplexml`, `json` und `mbstring`
- Funktionierender E-Mail-Versand über die PHP-Funktion `mail()`
- Schreibrechte des Webservers auf das Verzeichnis `xml/`
- Apache mit aktivierter `.htaccess`-Unterstützung (`AllowOverride`) oder eine entsprechende
  Konfiguration für andere Webserver (siehe [Datenschutz und Sicherheit](#datenschutz-und-sicherheit))

## Installation

1. Dateien auf den Webserver kopieren, z. B. in das Verzeichnis `/anmeldung/`.
2. `config.sample.php` nach `config.php` kopieren und die Werte anpassen (siehe [Konfiguration](#konfiguration)).
3. Sicherstellen, dass der Webserver in `xml/` schreiben darf.
4. Prüfen, dass `https://example.net/anmeldung/xml/` **nicht** abrufbar ist (erwartet: Fehler 403 oder 404).
5. Testanmeldung durchführen und den Empfang beider E-Mails prüfen. Die Testdateien danach aus `xml/` löschen.
6. Die URL der Seite an die Jugendfeuerwehren verteilen.

## Konfiguration

Alle Einstellungen stehen in der `config.php`:

| Schlüssel | Beschreibung |
|---|---|
| `title` | Titel im Browser-Tab |
| `logo` | Pfad oder URL zum Logo |
| `headline` | Überschrift der Seite, auch im Betreff der Bestätigungs-E-Mail |
| `description` | Beschreibungstext oberhalb der Teilnahmeliste (HTML erlaubt) |
| `organizationalunit` | Bezeichnung der Organisationseinheit, z. B. „Stadt, - Gemeinde“ |
| `organizationalunits` | Auswahlliste der Organisationseinheiten. Die Option „Extern“ mit Freitext gibt es immer zusätzlich. |
| `cost_pp` | Teilnahmebeitrag pro Person in Euro |
| `discounts` | Anteil am Beitrag je Status, z. B. `['2Geschwister' => 0.75, 'WeitereGeschwister' => 0.5]`. Nicht aufgeführte Status zahlen den vollen Beitrag, `0` bedeutet kostenlos. Mögliche Status: `1Geschwister` (Teilnehmender), `2Geschwister`, `WeitereGeschwister`, `Betreuer`, `Mitarbeiter` |
| `block_registration_at` | Anmeldeschluss im ISO-8601-Format, z. B. `2025-05-17T06:00:00+02:00` |
| `mailabsender` | Absenderadresse der E-Mails |
| `mailintern` | Empfänger der internen Benachrichtigungen (leer = keine interne E-Mail) |
| `url` | Öffentliche URL der Anwendung mit abschließendem `/`, wird für die Links in den E-Mails verwendet |
| `mailmessage` | Text der Bestätigungs-E-Mail an die verantwortliche Person (Platzhalter siehe unten) |
| `mailmessageintern` | Text der internen E-Mail (Platzhalter siehe unten) |
| `bankdetails` | Bankverbindung für die Überweisung (HTML erlaubt) |
| `max_submissions_per_hour` | Maximale Anzahl Speichervorgänge pro IP-Adresse und Stunde (Standard `20`, `0` = unbegrenzt) |

In den E-Mail-Texten stehen folgende Platzhalter zur Verfügung:

| Platzhalter | Inhalt |
|---|---|
| `{URL}` | Link zur Anmeldung (immer der aktuelle Stand) |
| `{URLINT}` | Link auf den Stand zum Zeitpunkt dieser Änderung |
| `{FEUERWEHR}` | Name der Jugendfeuerwehr |
| `{TEILNEHMENDE}` | Anzahl der angemeldeten Personen |
| `{BETRAG}` | Zu zahlender Gesamtbetrag inklusive Rabatten, z. B. `112,50 €` |

`description` und `bankdetails` werden ungefiltert als HTML ausgegeben. Dort nur vertrauenswürdige Inhalte eintragen.

Wird ein Logo von einer anderen Domain eingebunden, muss `img-src` in der Content-Security-Policy in der
`.htaccess` um diese Domain ergänzt werden, sonst blockiert der Browser das Bild.

## Ablauf und Datenablage

- Bei der ersten Anmeldung wird ein zufälliger 48-stelliger Schlüssel erzeugt. Die Daten liegen in `xml/<Schlüssel>.xml`.
- Bei jeder Speicherung wird zusätzlich `xml/<Schlüssel>_<Unix-Zeitstempel>.xml` angelegt.
  So lässt sich jeder frühere Stand nachvollziehen und über `?anmeldung=<Schlüssel>_<Zeitstempel>` aufrufen.
- Name der Jugendfeuerwehr und Organisationseinheit lassen sich nach der ersten Anmeldung nicht mehr ändern.
- Werte, die die KZL-Verwaltungssoftware in der XML-Datei pflegt (`GezahlterBeitrag`, `Einverstaendniserklaerung`,
  `Teilnehmerbeitrag`, `Zeltdorf` und die zugehörigen Zeitstempel), bleiben bei Änderungen durch die Jugendfeuerwehr erhalten.
- Die XML-Dateien sind UTF-16-kodiert.
- Nach dem Anmeldeschluss (`block_registration_at`) können Anmeldungen weiterhin angesehen, aber nicht mehr gespeichert werden.

## Import in die KZL-Verwaltungssoftware

Nach dem Anmeldeschluss die Dateien `xml/<Schlüssel>.xml` (ohne `_<Zeitstempel>`, das sind die älteren Stände) herunterladen
und in die [KZL-Verwaltungssoftware](https://github.com/KJFCux/jf-kzl) importieren. Dort werden alle Gruppen angelegt
und das Zeltlager geplant.

## Datenschutz und Sicherheit

Die Anwendung verarbeitet personenbezogene Daten von Kindern und Jugendlichen (Adresse, Geburtsdatum,
Unverträglichkeiten). Bitte beachte:

- **Das Verzeichnis `xml/` darf nicht über den Webserver abrufbar sein.** Für Apache liegt dafür eine
  `xml/.htaccess` bei. Bei nginx muss der Zugriff in der Serverkonfiguration gesperrt werden:

  ```nginx
  location ~ ^/anmeldung/(xml/|\.) {
      deny all;
  }
  ```

- Wer den Link einer Anmeldung kennt, kann sie einsehen und ändern. Die Links sollten daher nur an die
  jeweils Verantwortlichen weitergegeben werden. Die `.htaccess` setzt `Referrer-Policy: no-referrer`
  und `X-Robots-Tag: noindex`, damit die Links nicht an Dritte gelangen oder in Suchmaschinen erscheinen.
- Die Anwendung nur über HTTPS betreiben.
- Die XML-Dateien nach Abschluss der Veranstaltung gemäß eurem Löschkonzept entfernen.
- Die Dateien in `xml/` sind über `.gitignore` von der Versionsverwaltung ausgeschlossen und dürfen nie committet werden.
- Für die Begrenzung der Speichervorgänge wird `xml/.ratelimit.json` angelegt. Sie enthält nur gesalzene Hashwerte
  der IP-Adressen, Einträge werden nach einer Stunde verworfen. Diese Datei wird nicht in die KZL-Software importiert.
- Ein CSRF-Token ist nicht nötig: Die Anwendung hat keine Anmeldung per Cookie oder Session. Wer ändern will,
  braucht den geheimen Link, und mit diesem könnte er die Änderung ohnehin direkt abschicken.

## Lokale Entwicklung

```bash
cp config.sample.php config.php   # block_registration_at in die Zukunft setzen
php -S localhost:8000
```

Danach ist die Anwendung unter <http://localhost:8000> erreichbar. Der PHP-Entwicklungsserver wertet keine
`.htaccess` aus, `xml/` ist dort also direkt abrufbar. E-Mails werden lokal nur verschickt, wenn `sendmail` eingerichtet ist.

Verwendete Bibliotheken: [Bootstrap 5.3.9](https://getbootstrap.com/) (lokal in `css/` und `js/` eingebunden).
Zum Aktualisieren `bootstrap.min.css`, `bootstrap.bundle.min.js` und die zugehörigen `.map`-Dateien aus dem
`dist`-Verzeichnis eines neuen Bootstrap-Releases ersetzen.

## Lizenz

[GNU Affero General Public License v3.0](LICENSE)

<?php
// Kopiere diese Datei nach config.php und passe die Werte an.
$config = [];

// Titel im Browser-Tab
$config['title'] = 'Kreiszeltlager im Landkreis Cuxhaven';
// Pfad oder URL zum Logo (bei externer URL muss die Content-Security-Policy in .htaccess angepasst werden)
$config['logo'] = '/wp-content/uploads/2019/04/kjf_logo_web_transparent_mit_text_FUTURA.png';
// Überschrift der Seite, wird auch im Betreff der Bestätigungs-E-Mail verwendet
$config['headline'] = 'Kreiszeltlager 2025';
// Beschreibungstext oberhalb der Teilnahmeliste (HTML erlaubt)
$config['description'] = 'Kreiszeltlager in Otterndorf ... Beschreibung in Config';

// Bezeichnung und Auswahlliste der Organisationseinheiten. Zusätzlich gibt es immer die Option "Extern" mit Freitext.
$config['organizationalunit'] = 'Stadt, - Gemeinde';
$config['organizationalunits'] = ['Beverstedt', 'Börde Lamstedt', 'Cuxhaven', 'Geestland', 'Land Hadeln', 'Hagen', 'Hemmoor', 'Loxstedt', 'Schiffdorf', 'Wurster Nordseeküste'];

// Teilnahmebeitrag pro Person in Euro
$config['cost_pp'] = '50';
// Anteil am Beitrag je Status (1 = voller Beitrag, 0.75 = 75 %, 0 = kostenlos).
// Nicht aufgeführte Status zahlen den vollen Beitrag.
// Mögliche Status: 1Geschwister, 2Geschwister, WeitereGeschwister, Betreuer, Mitarbeiter
$config['discounts'] = [
    '2Geschwister' => 0.75,
    'WeitereGeschwister' => 0.5,
];

// Anmeldeschluss (ISO 8601). Danach ist die Seite nur noch lesbar, Änderungen werden abgelehnt.
$config['block_registration_at'] = '2025-05-17T06:00:00+02:00';

// Absenderadresse der E-Mails
$config['mailabsender'] = 'mail@example.net';
// Empfänger der internen Benachrichtigung bei jeder Anmeldung/Änderung (leer lassen zum Deaktivieren)
$config['mailintern'] = 'mail@example.net';
// Öffentliche URL der Anwendung (mit abschließendem Slash), wird für die Links in den E-Mails verwendet
$config['url'] = 'https://example.net/anmeldung/';

// E-Mail-Texte. Platzhalter: {URL} = Link zur Anmeldung, {URLINT} = Link auf den Stand dieser Änderung,
// {FEUERWEHR} = Name der Jugendfeuerwehr, {TEILNEHMENDE} = Anzahl Personen, {BETRAG} = zu zahlender Betrag
// Text der Bestätigungs-E-Mail an die verantwortliche Person
$config['mailmessage'] = "Hallo, du kannst deine Anmeldung unter dieser URL einsehen: \r\n\r\n{URL} \r\n\r\nAktuell angemeldet: {TEILNEHMENDE} Personen, Betrag: {BETRAG}\r\n\r\nViele Grüße";
// Text der internen E-Mail
$config['mailmessageintern'] = "{FEUERWEHR}: {TEILNEHMENDE} Personen, {BETRAG}\r\nAktuellste Anmeldung: {URL}\r\nAnmeldung zum Zeitpunkt der E-Mail: {URLINT}\r\nViele Grüße";

// Bankverbindung für die Überweisung (HTML erlaubt)
$config['bankdetails'] = "                Kontoinhaber: ABCD e.V.<br>
                IBAN: ABCD<br>
                BIC: ABCD<br>";

// Maximale Anzahl gespeicherter Anmeldungen/Änderungen pro IP-Adresse und Stunde (Schutz vor Spam, 0 = unbegrenzt)
$config['max_submissions_per_hour'] = 20;

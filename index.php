<?php

if (file_exists('config.php')) {
    include('config.php');
    if (!isset($config)) {
        die('Config not valid, please copy config.sample.php to config.php and reconfigure values');
    }
} else {
    die('Config not found, please copy config.sample.php to config.php and configure values');
}

// Auswahlmöglichkeiten (werden für Formular, Vorlage und serverseitige Prüfung genutzt)
const GESCHLECHT_OPTIONEN = ['D' => 'D', 'M' => 'M', 'W' => 'W'];
const STATUS_OPTIONEN = [
    '1Geschwister' => 'Teilnehmender',
    '2Geschwister' => '2. Geschwisterkind',
    'WeitereGeschwister' => 'Weiteres Geschwisterkind',
    'Betreuer' => 'Betreuer',
    'Mitarbeiter' => 'Mitarbeiter',
];
const ESSGEWOHNHEITEN_OPTIONEN = [
    'Alles' => 'Alles',
    'Vegetarisch' => 'Vegetarisch',
    'Vegan' => 'Vegan',
    'Sonstiges' => 'Sonstiges',
];
const UNVERTRAEGLICHKEITEN_OPTIONEN = [
    'keine' => 'Keine',
    'Erdnuesse' => 'Erdnüsse',
    'Gluten' => 'Gluten',
    'Laktose' => 'Laktose',
    'Schalenfruechte' => 'Schalenfrüchte',
    'Schalentiere' => 'Schalentiere',
    'Sellerie' => 'Sellerie',
    'Senf' => 'Senf',
    'Sesam' => 'Sesam',
    'Soja' => 'Soja',
    'Sulfite' => 'Sulfite',
    'Sonstiges' => 'Sonstiges',
];

function generateRandomString($length = 10): string
{
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Liefert skalare Werte als String, alles andere (z. B. Arrays aus
 * manipulierten Formulardaten oder verschachtelten XML-Elementen) als ''.
 */
function text($value): string
{
    return is_scalar($value) ? (string)$value : '';
}

function e($value): string
{
    return htmlspecialchars(text($value), ENT_QUOTES, 'UTF-8');
}

function echo_if_isset($array, $key, $echo = null): void
{
    if (isset($array[$key])) {
        if ($echo === null) {
            echo e($array[$key]);
        } else {
            echo $echo;
        }
    }
}

function if_isset_dt($array, $key): string
{
    $timestamp = strtotime(text($array[$key] ?? ''));
    return $timestamp === false ? '' : date('Y-m-d', $timestamp);
}

function input($string, $len = 64): string
{
    if (!is_string($string)) {
        return '';
    }
    return mb_strimwidth(trim($string), 0, $len);
}

/**
 * Zerlegt einen gespeicherten Auswahlwert wie "Gluten,Sonstiges:Kiwi"
 * in die gewählten Optionen und den Freitext für "Sonstiges".
 */
function parseAuswahl($raw): array
{
    $raw = is_string($raw) ? $raw : '';
    $sonstiges = '';
    $pos = strpos($raw, 'Sonstiges:');
    if ($pos !== false) {
        $sonstiges = substr($raw, $pos + strlen('Sonstiges:'));
        $raw = substr($raw, 0, $pos) . 'Sonstiges';
    }
    $values = $raw === '' ? [] : explode(',', $raw);
    return [$values, $sonstiges];
}

function renderOptions(array $options, array $selected): void
{
    foreach ($options as $value => $label) {
        $isSelected = in_array((string)$value, $selected, true) ? ' selected' : '';
        echo '<option value="' . e($value) . '"' . $isSelected . '>' . e($label) . '</option>';
    }
}

/**
 * Gibt eine Zeile der Teilnahmeliste aus. Wird sowohl für gespeicherte
 * Personen als auch für die <template>-Vorlage neuer Zeilen genutzt.
 */
function renderTeilnehmerRow(array $item, $index): void
{
    [$essgewohnheiten, $essgewohnheitenSonstiges] = parseAuswahl($item['Essgewohnheiten'] ?? 'Alles');
    [$unvertraeglichkeiten, $unvertraeglichkeitenSonstiges] = parseAuswahl($item['Unvertraeglichkeiten'] ?? 'keine');
    ?>
    <tr>
        <td>
            <input type="text" class="form-control" name="Teilnehmer[Vorname][]" placeholder="Vorname" required value="<?php echo_if_isset($item, 'Vorname'); ?>">
            <input type="text" class="form-control mb-2" name="Teilnehmer[Name][]" placeholder="Nachname" required value="<?php echo_if_isset($item, 'Name'); ?>">
        </td>
        <td>
            <input type="text" class="form-control mb-2" name="Teilnehmer[Strasse][]" placeholder="Straße/Hausnummer" required value="<?php echo_if_isset($item, 'Strasse'); ?>">
            <input type="text" class="form-control" name="Teilnehmer[PLZ][]" placeholder="PLZ" required inputmode="numeric" maxlength="5" value="<?php echo_if_isset($item, 'PLZ'); ?>">
            <input type="text" class="form-control" name="Teilnehmer[Ort][]" placeholder="Ort" required value="<?php echo_if_isset($item, 'Ort'); ?>">
        </td>
        <td>
            <input type="date" class="form-control mb-2" name="Teilnehmer[Geburtsdatum][]" required value="<?php echo if_isset_dt($item, 'Geburtsdatum'); ?>">
            <select name="Teilnehmer[Geschlecht][]" class="form-select" required>
                <option value="">Bitte wählen</option>
                <?php renderOptions(GESCHLECHT_OPTIONEN, [text($item['Geschlecht'] ?? '')]); ?>
            </select>
        </td>
        <td>
            <select name="Teilnehmer[Status][]" class="form-select mb-2 teilnehmer-status" required>
                <?php renderOptions(STATUS_OPTIONEN, [text($item['Status'] ?? '')]); ?>
            </select>
        </td>
        <td>
            <select name="Teilnehmer[Essgewohnheiten][]" class="form-select mb-2 has-sonstiges">
                <?php renderOptions(ESSGEWOHNHEITEN_OPTIONEN, $essgewohnheiten); ?>
            </select>
            <input type="text" class="form-control sonstiges-input" name="Teilnehmer[EssgewohnheitenSonstiges][]" placeholder="Sonstige Essgewohnheiten" value="<?php echo e($essgewohnheitenSonstiges); ?>" hidden>
        </td>
        <td>
            <select name="Teilnehmer[Unvertraeglichkeiten][<?php echo e($index); ?>][]" class="form-select mb-2 has-sonstiges" multiple>
                <?php renderOptions(UNVERTRAEGLICHKEITEN_OPTIONEN, $unvertraeglichkeiten); ?>
            </select>
            <input type="text" class="form-control sonstiges-input" name="Teilnehmer[UnvertraeglichkeitenSonstiges][]" placeholder="Sonstige Unverträglichkeiten" value="<?php echo e($unvertraeglichkeitenSonstiges); ?>" hidden>
        </td>
    </tr>
    <?php
}

/**
 * Erzeugt ein XML-Element mit Textinhalt. createElement($name, $value) escaped
 * "&" nicht, wodurch Werte wie "Musterdorf & Umgebung" verloren gingen.
 */
function xmlElement(DOMDocument $xml, string $name, $value = null): DOMElement
{
    $element = $xml->createElement($name);
    if ($value !== null && $value !== '') {
        $element->appendChild($xml->createTextNode((string)$value));
    }
    return $element;
}

function mailSubject(string $subject): string
{
    return mb_encode_mimeheader($subject, 'UTF-8', 'B');
}

function formatEuro(float $betrag): string
{
    return number_format($betrag, 2, ',', '.');
}

/**
 * Berechnet den Gesamtbeitrag. $faktoren enthält je Status den Anteil am
 * Beitrag pro Person (z. B. 0.75 für das 2. Geschwisterkind), sonst gilt 1.
 */
function berechneBeitrag(array $statusListe, float $beitragProPerson, array $faktoren): float
{
    $summe = 0.0;
    foreach ($statusListe as $status) {
        $summe += $beitragProPerson * (float)($faktoren[$status] ?? 1);
    }
    return $summe;
}

/**
 * Einfache Begrenzung der Speichervorgänge pro IP-Adresse und Stunde, um
 * massenhafte Anmeldungen und E-Mails durch Bots zu verhindern. IP-Adressen
 * werden nur gesalzen gehasht und nach einer Stunde verworfen.
 */
function rateLimitErreicht(string $datei, int $limit): bool
{
    if ($limit <= 0) {
        return false;
    }
    $fh = @fopen($datei, 'c+');
    if ($fh === false) {
        return false; // Im Zweifel niemanden aussperren
    }
    flock($fh, LOCK_EX);
    $data = json_decode((string)stream_get_contents($fh), true);
    if (!is_array($data) || empty($data['salt']) || !isset($data['hits']) || !is_array($data['hits'])) {
        $data = ['salt' => bin2hex(random_bytes(16)), 'hits' => []];
    }
    $jetzt = time();
    foreach ($data['hits'] as $key => $zeiten) {
        $zeiten = array_values(array_filter((array)$zeiten, function ($t) use ($jetzt) {
            return (int)$t > $jetzt - 3600;
        }));
        if ($zeiten === []) {
            unset($data['hits'][$key]);
        } else {
            $data['hits'][$key] = $zeiten;
        }
    }
    $key = hash('sha256', $data['salt'] . ($_SERVER['REMOTE_ADDR'] ?? ''));
    $erreicht = count($data['hits'][$key] ?? []) >= $limit;
    if (!$erreicht) {
        $data['hits'][$key][] = $jetzt;
    }
    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, json_encode($data));
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    return $erreicht;
}

// Beitrag und Geschwisterrabatt
$beitragProPerson = (float)str_replace(',', '.', (string)($config['cost_pp'] ?? 0));
$beitragsfaktoren = isset($config['discounts']) && is_array($config['discounts'])
    ? $config['discounts']
    : ['2Geschwister' => 0.75, 'WeitereGeschwister' => 0.5];

$registrationEnabled = true;
try {
    $blockRegistrationAt = new DateTimeImmutable($config['block_registration_at']);
    $registrationEnabled = $blockRegistrationAt > new DateTimeImmutable();
    // If the registration is blocked and the form is submitted, show an error message
    if (!$registrationEnabled && !empty($_POST)) {
        http_response_code(403);
        die('Anmeldung ist nicht mehr möglich. Der Anmeldeschluss war am ' . $blockRegistrationAt->format('d.m.Y H:i:s') . '.');
    }
} catch (Exception $e) {
    die('block_registration_at in config.php is not a valid date');
}

$XmlData = [];
$datafile = null;
$old_xml = null;
if (isset($_GET['anmeldung']) && is_string($_GET['anmeldung']) && preg_match('/^[a-zA-Z0-9_]+$/', $_GET['anmeldung']) && file_exists('./xml/' . $_GET['anmeldung'] . '.xml')) {
    $old_xml = simplexml_load_file('./xml/' . $_GET['anmeldung'] . '.xml');
    if ($old_xml) {
        //Convert SimpleXml Object to associative Array
        $XmlData = json_decode(str_replace(':{}', ':""', json_encode($old_xml)), true);
        $datafile = explode('_', $_GET['anmeldung'])[0];
    } else {
        $old_xml = null;
    }
}

if ($old_xml === null) {
    $XmlData = [
        "Feuerwehr" => null,
        "Organisationseinheit" => null,
        "Verantwortlicher" => [],
        "Persons" => [
            "Person" => [
                [
                    "Status" => "Betreuer",
                    "Essgewohnheiten" => "Alles",
                    "Unvertraeglichkeiten" => "keine",
                ]
            ],
        ],
    ];
}

// Bei nur einer Person liefert SimpleXML/JSON kein Array von Personen, sondern
// direkt die Person. Das wird hier vereinheitlicht.
$persons = $XmlData['Persons']['Person'] ?? [];
if (!is_array($persons) || $persons === []) {
    $persons = [[]];
} elseif (!isset($persons[0])) {
    $persons = [$persons];
}
$XmlData['Persons']['Person'] = $persons;
if (!isset($XmlData['Verantwortlicher']) || !is_array($XmlData['Verantwortlicher'])) {
    $XmlData['Verantwortlicher'] = [];
}

function addAppData($jf, $xml, $XmlData, $key)
{
    if (isset($XmlData[$key]) && !is_array($XmlData[$key])) {
        $jf->appendChild(xmlElement($xml, $key, $XmlData[$key]));
    }
}

if (isset($_POST['Feuerwehr'], $_POST['Organisationseinheit'], $_POST['Verantwortlicher'], $_POST['Teilnehmer'])
    && is_array($_POST['Verantwortlicher']) && is_array($_POST['Teilnehmer'])) {
    // Honeypot: Dieses Feld ist für Menschen unsichtbar und wird nur von Bots ausgefüllt
    if (!empty($_POST['homepage'])) {
        header('Location: ./');
        exit;
    }

    // XML-Daten aufbauen
    $xml = new DOMDocument("1.0", "utf-16");
    // Gruppe-Element erstellen
    $jf = $xml->createElement("Jugendfeuerwehr");
    $xml->appendChild($jf);

    $teilnehmer = $_POST["Teilnehmer"];
    $verantwortlicherPost = $_POST["Verantwortlicher"];

    if ($old_xml === null) {
        // Pflichtfelder auch serverseitig prüfen. Die Organisationseinheit ist bewusst
        // nicht auf die Liste beschränkt, da bei "Extern" ein Freitext erlaubt ist.
        if (input($_POST["Feuerwehr"]) === '' || input($_POST["Organisationseinheit"]) === '') {
            http_response_code(400);
            die('Bitte Jugendfeuerwehr und ' . e($config['organizationalunit']) . ' angeben. Über "Zurück" im Browser gelangst du zu deinen Eingaben.');
        }

        // Neue Daten
        $jf->appendChild(xmlElement($xml, "Feuerwehr", input($_POST["Feuerwehr"])));
        $jf->appendChild(xmlElement($xml, "Organisationseinheit", input($_POST["Organisationseinheit"])));
        $jf->appendChild(xmlElement($xml, "TimeStampAnmeldung", (new DateTime())->format("Y-m-d\TH:i:s")));
    } else {
        //Bei Update Feuerwehr, Organisationseinheit nicht ändern
        $jf->appendChild(xmlElement($xml, "Feuerwehr", $XmlData['Feuerwehr'] ?? ''));
        $jf->appendChild(xmlElement($xml, "Organisationseinheit", $XmlData["Organisationseinheit"] ?? ''));
        // Zeitpunkt der Erstanmeldung beibehalten
        addAppData($jf, $xml, $XmlData, "TimeStampAnmeldung");

        // Daten, die von der KZL-Verwaltungssoftware gepflegt werden, übernehmen
        addAppData($jf, $xml, $XmlData, "GezahlterBeitrag");
        addAppData($jf, $xml, $XmlData, "TimeStampGezahlterBeitrag");
        addAppData($jf, $xml, $XmlData, "Einverstaendniserklaerung");
        addAppData($jf, $xml, $XmlData, "TimeStampEinverstaendniserklaerung");
        addAppData($jf, $xml, $XmlData, "Teilnehmerbeitrag");
        addAppData($jf, $xml, $XmlData, "Zeltdorf");
        addAppData($jf, $xml, $XmlData, "TimeStampZeltdorf");
    }

    // Verantwortlicher-Element erstellen
    $verantwortlicher = $xml->createElement("Verantwortlicher");
    $jf->appendChild($verantwortlicher);
    foreach (['Vorname', 'Name', 'Strasse', 'PLZ', 'Ort', 'Funktion', 'Telefon', 'Email'] as $feld) {
        $verantwortlicher->appendChild(xmlElement($xml, $feld, input($verantwortlicherPost[$feld] ?? '')));
    }

    // Teilnehmer-Element erstellen
    $persons = $xml->createElement("Persons");
    $jf->appendChild($persons);

    // Teilnehmer hinzufügen
    $statusListe = [];
    $vornamen = is_array($teilnehmer["Vorname"] ?? null) ? $teilnehmer["Vorname"] : [];
    foreach ($vornamen as $index => $vorname) {
        $wert = function ($feld, $len = 64) use ($teilnehmer, $index) {
            return input($teilnehmer[$feld][$index] ?? '', $len);
        };

        // Leere Zeilen überspringen
        if ($wert('Vorname') === '' && $wert('Name') === '') {
            continue;
        }

        $person = $xml->createElement("Person");
        $persons->appendChild($person);

        $person->appendChild(xmlElement($xml, "Vorname", $wert('Vorname')));
        $person->appendChild(xmlElement($xml, "Name", $wert('Name')));
        $person->appendChild(xmlElement($xml, "Strasse", $wert('Strasse')));
        $person->appendChild(xmlElement($xml, "PLZ", $wert('PLZ', 5)));
        $person->appendChild(xmlElement($xml, "Ort", $wert('Ort')));

        // Geburtsdatum in das Format YYYY-MM-DD konvertieren
        $date = DateTime::createFromFormat('!Y-m-d', $wert('Geburtsdatum', 10));
        if ($date === false) {
            $date = new DateTime('1900-01-01');
        }
        $person->appendChild(xmlElement($xml, "Geburtsdatum", $date->format("Y-m-d\TH:i:s")));

        $geschlecht = $wert('Geschlecht', 1);
        $person->appendChild(xmlElement($xml, "Geschlecht", isset(GESCHLECHT_OPTIONEN[$geschlecht]) ? $geschlecht : ''));

        $status = $wert('Status');
        $status = isset(STATUS_OPTIONEN[$status]) ? $status : '1Geschwister';
        $statusListe[] = $status;
        $person->appendChild(xmlElement($xml, "Status", $status));

        $essgewohnheiten = $wert('Essgewohnheiten');
        if (!isset(ESSGEWOHNHEITEN_OPTIONEN[$essgewohnheiten])) {
            $essgewohnheiten = 'Alles';
        }
        if ($essgewohnheiten === 'Sonstiges') {
            $essgewohnheiten = 'Sonstiges:' . $wert('EssgewohnheitenSonstiges');
        }
        $person->appendChild(xmlElement($xml, "Essgewohnheiten", $essgewohnheiten));

        // Nur bekannte Werte übernehmen; ohne Auswahl gilt "keine"
        $auswahl = $teilnehmer["Unvertraeglichkeiten"][$index] ?? [];
        $auswahl = array_values(array_filter((array)$auswahl, function ($v) {
            return is_string($v) && isset(UNVERTRAEGLICHKEITEN_OPTIONEN[$v]);
        }));
        if (count($auswahl) > 1) {
            $auswahl = array_values(array_diff($auswahl, ['keine']));
        }
        if ($auswahl === []) {
            $auswahl = ['keine'];
        }
        $unvertraeglichkeiten = implode(',', $auswahl);
        if (in_array('Sonstiges', $auswahl, true)) {
            // "Sonstiges" steht immer am Ende, damit der Freitext beim Einlesen eindeutig ist
            $auswahl = array_values(array_diff($auswahl, ['Sonstiges']));
            $auswahl[] = 'Sonstiges:' . $wert('UnvertraeglichkeitenSonstiges');
            $unvertraeglichkeiten = implode(',', $auswahl);
        }
        $person->appendChild(xmlElement($xml, "Unvertraeglichkeiten", $unvertraeglichkeiten));
    }

    if ($statusListe === []) {
        http_response_code(400);
        die('Bitte mindestens eine Person in der Teilnahmeliste eintragen. Über "Zurück" im Browser gelangst du zu deinen Eingaben.');
    }

    // Nur gültige Speichervorgänge zählen für das Limit
    if (rateLimitErreicht('./xml/.ratelimit.json', (int)($config['max_submissions_per_hour'] ?? 20))) {
        http_response_code(429);
        die('Von deinem Anschluss wurden in der letzten Stunde zu viele Anmeldungen gespeichert. '
            . 'Bitte versuche es später erneut. Über "Zurück" im Browser gelangst du zu deinen Eingaben.');
    }

    // XML speichern
    if ($datafile === null) {
        $datafile = generateRandomString(48);
    }
    $url = $config['url'] . '?anmeldung=' . $datafile;
    $datafiledate = $datafile . '_' . time();
    $urlintern = $config['url'] . '?anmeldung=' . $datafiledate;

    $jf->appendChild(xmlElement($xml, "UrlderAnmeldung", $url));
    $jf->appendChild(xmlElement($xml, "TimeStampAenderung", (new DateTime())->format("Y-m-d\TH:i:s")));

    $xml->formatOutput = true;
    if ($xml->save('./xml/' . $datafile . '.xml') === false || $xml->save('./xml/' . $datafiledate . '.xml') === false) {
        http_response_code(500);
        die('Die Anmeldung konnte nicht gespeichert werden. Bitte wende dich an die Veranstaltungsleitung.');
    }

    $header = 'From: ' . $config['mailabsender'] . "\r\n" . 'Content-Type: text/plain; charset=utf-8' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    $feuerwehrName = $old_xml === null ? input($_POST["Feuerwehr"]) : text($XmlData['Feuerwehr'] ?? '');
    $platzhalter = [
        '{URL}' => $url,
        '{URLINT}' => $urlintern,
        '{FEUERWEHR}' => $feuerwehrName,
        '{TEILNEHMENDE}' => (string)count($statusListe),
        '{BETRAG}' => formatEuro(berechneBeitrag($statusListe, $beitragProPerson, $beitragsfaktoren)) . ' €',
    ];

    $email = input($verantwortlicherPost["Email"] ?? '', 254);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = strtr($config['mailmessage'], $platzhalter);
        mail($email, mailSubject('Anmeldung zum ' . $config['headline']), $message, $header);
    }
    if (!empty($config['mailintern'])) {
        $messageintern = strtr($config['mailmessageintern'], $platzhalter);
        mail($config['mailintern'], mailSubject('Anmeldung von ' . $feuerwehrName . ', Teilnehmende: ' . count($statusListe)), $messageintern, $header);
    }

    setcookie("saved", "1");
    header('Location: ./?anmeldung=' . $datafile);
    exit;
}
$saved = false;
if (!empty($_COOKIE['saved'])) {
    $saved = true;
    setcookie("saved", "", time() - 1000);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo $config['title']; ?></title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/KJFCux.css">
</head>
<body>
<div class="container">
    <header class="container">
        <div class="row align-items-center">
            <div class="col-sm-2">
                <img src="<?php echo $config['logo']; ?>" alt="Logo" width="150">
            </div>
            <div class="col-sm-10">
                <h1 class="mt-4 mb-4 text-center"><?php echo $config['headline']; ?></h1>
            </div>
        </div>
    </header>
    <?php
    if ($saved) {
        ?>
        <div class="card text-white bg-success">
            <div class="card-body">
                Die Daten wurden erfolgreich gespeichert. Du kannst sie jederzeit über die aktuelle Seite aktualisieren.
                Wir empfehlen daher diesen Link zu speichern, um später darauf zugreifen zu können.
            </div>
        </div>
        <?php
    }
    $existOU = false;
    $hasOU = isset($XmlData['Organisationseinheit']) && $XmlData['Organisationseinheit'] !== '';
    ?>
    <form method="POST" id="form">
        <div class="hp-feld" aria-hidden="true">
            <label for="homepage">Bitte leer lassen</label>
            <input type="text" name="homepage" id="homepage" tabindex="-1" autocomplete="off">
        </div>
        <div class="row mb-4">
            <div class="col-sm-3">
                <label for="Feuerwehr">Jugendfeuerwehr:</label>
                <input type="text" class="form-control" name="Feuerwehr" id="Feuerwehr"
                       placeholder="Musterdorf"
                       value="<?php echo_if_isset($XmlData, 'Feuerwehr'); ?>"
                       required <?php echo_if_isset($XmlData, 'Feuerwehr', 'readonly'); ?>>
            </div>
            <div class="col-sm-3">
                <label for="Organisationseinheit"><?php echo $config['organizationalunit']; ?></label>
                <select class="form-select" id="Organisationseinheit"
                        required <?php echo_if_isset($XmlData, 'Organisationseinheit', 'disabled'); ?>>
                    <option value="">Bitte wählen</option>
                    <?php foreach ($config['organizationalunits'] as $unit):
                        if(isset($XmlData['Organisationseinheit']) && $XmlData['Organisationseinheit'] == $unit){
                            $existOU = true;
                        }
                        ?>
                        <option value="<?php echo e($unit); ?>" <?php if ($hasOU && $XmlData['Organisationseinheit'] == $unit) { echo 'selected'; } ?>><?php echo e($unit); ?></option>
                    <?php endforeach; ?>
                    <option value="extern" <?php if ($hasOU && !$existOU) { echo 'selected'; } ?>>Extern</option>
                </select>
            </div>
            <div class="col-sm-3">
                <label for="OrganisationseinheitSonstige"></label>
                <input type="text" class="form-control" name="Organisationseinheit"
                       id="OrganisationseinheitSonstige" hidden required
                       placeholder="<?php echo $config['organizationalunit']; ?>"
                       value="<?php echo_if_isset($XmlData, 'Organisationseinheit'); ?>"
                    <?php echo_if_isset($XmlData, 'Organisationseinheit', 'readonly'); ?>>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-sm-4">
                <label for="VerantwortlicherName">Verantwortliche Person:</label>
                <input type="text" class="form-control mb-2" name="Verantwortlicher[Vorname]" id="VerantwortlicherName"
                       placeholder="Vorname" required value="<?php echo_if_isset($XmlData["Verantwortlicher"], 'Vorname'); ?>">
                <input type="text" class="form-control" name="Verantwortlicher[Name]" id="VerantwortlicherNachname" aria-label="Nachname"
                       placeholder="Nachname" required value="<?php echo_if_isset($XmlData["Verantwortlicher"], 'Name'); ?>">
            </div>
            <div class="col-sm-4">
                <div>
                    <label for="Adresse">Adresse:</label>
                    <input type="text" class="form-control mb-2" name="Verantwortlicher[Strasse]"
                           placeholder="Straße/Hausnummer" required id="Adresse" value="<?php echo_if_isset($XmlData["Verantwortlicher"], 'Strasse'); ?>">
                    <input type="text" class="form-control mb-2" name="Verantwortlicher[PLZ]" placeholder="PLZ" required
                           id="AdressePLZ" aria-label="PLZ" inputmode="numeric" value="<?php echo_if_isset($XmlData["Verantwortlicher"], 'PLZ'); ?>">
                    <input type="text" class="form-control" name="Verantwortlicher[Ort]" placeholder="Ort" required
                           id="AdresseOrt" aria-label="Ort" value="<?php echo_if_isset($XmlData["Verantwortlicher"], 'Ort'); ?>">
                </div>
            </div>
            <div class="col-sm-3">
                <label for="Funktion">Funktion:</label>
                <input type="text" class="form-control mb-2" name="Verantwortlicher[Funktion]" id="Funktion"
                       placeholder="z. B. Jugendwart" required value="<?php echo_if_isset($XmlData["Verantwortlicher"], 'Funktion'); ?>">
                <input type="text" class="form-control mb-2" name="Verantwortlicher[Telefon]" id="Telefon"
                       placeholder="Telefonnummer" required aria-label="Telefonnummer" autocomplete="tel" value="<?php echo_if_isset($XmlData["Verantwortlicher"], 'Telefon'); ?>">
                <input type="email" class="form-control" name="Verantwortlicher[Email]" id="Email"
                       placeholder="E-Mail Adresse" required aria-label="E-Mail Adresse" autocomplete="email" value="<?php echo_if_isset($XmlData["Verantwortlicher"], 'Email'); ?>">
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-sm-12">
                <p><?php echo $config['description']; ?></p>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-sm-12">
                <h4>Teilnahmeliste</h4>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-sm-12">
                <div id="teilnehmer-container" class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Persönliche Daten</th>
                            <th>Adresse</th>
                            <th>Alter/Geschlecht</th>
                            <th>Status</th>
                            <th>Essgewohnheiten</th>
                            <th>Unverträglichkeiten</th>
                        </tr>
                        </thead>
                        <tbody id="teilnehmer-list">
                        <?php
                        foreach ($XmlData["Persons"]["Person"] as $index => $item) {
                            renderTeilnehmerRow(is_array($item) ? $item : [], $index);
                        }
                        ?>
                        </tbody>
                    </table>
                    <template id="teilnehmer-template">
                        <?php renderTeilnehmerRow(['Status' => '1Geschwister', 'Essgewohnheiten' => 'Alles', 'Unvertraeglichkeiten' => 'keine'], '__INDEX__'); ?>
                    </template>
                    <button type="button" class="btn btn-secondary" id="add-teilnehmer">Teilnehmer hinzufügen</button>
                    <button type="button" class="btn btn-danger" id="delete-last-teilnehmer">Teilnehmer löschen</button>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-sm-12">
                <h5>Kosten</h5>
                <p>Pro Person: <span id="cost-pp" data-betrag="<?php echo e($beitragProPerson); ?>"><?php echo formatEuro($beitragProPerson); ?></span> €
                    <?php
                    $rabatte = [];
                    foreach ($beitragsfaktoren as $status => $faktor) {
                        if (isset(STATUS_OPTIONEN[$status]) && (float)$faktor != 1) {
                            $rabatte[] = e(STATUS_OPTIONEN[$status]) . ': ' . formatEuro($beitragProPerson * (float)$faktor) . ' €';
                        }
                    }
                    if ($rabatte) {
                        echo '<br><small class="text-body-secondary">' . implode(', ', $rabatte) . '</small>';
                    }
                    ?>
                </p>
                <p>Gesamtbetrag: <span id="total-cost" data-faktoren="<?php echo e(json_encode($beitragsfaktoren)); ?>">0</span> €</p>
                <p><strong>Bankverbindung</strong><br>
<?php echo $config['bankdetails']; ?>
                Verwendungszweck: <span id="jf-name"><?php echo_if_isset($XmlData, 'Feuerwehr'); ?></span>, <span id="participant-count">X</span> TN</p>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-sm-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="dsgvoConsent" name="dsgvoConsent" required>
                    <label class="form-check-label" for="dsgvoConsent">
                        Ich willige ein, dass die zur Durchführung des Zeltlagers relevanten Daten gespeichert werden.
                    </label>
                </div>
            </div>
        </div>
        <?php if($registrationEnabled){ ?>
        <div class="row">
            <div class="col-sm text-center mb-4">
                <button class="btn btn-primary" type="submit">Anmeldung übermitteln</button>
            </div>
        </div>
        <?php } else { ?>
        <div class="row">
            <div class="col-sm text-center mb-4">
                <button class="btn btn-secondary" type="button" disabled>Anmeldung deaktiviert</button>
            </div>
        </div>
        <?php } ?>
    </form>
</div>
<script src="./js/bootstrap.bundle.min.js"></script>
<script src="./js/KJFCux.js"></script>
</body>
</html>



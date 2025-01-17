<?php

if (file_exists('config.php')) {
    include('config.php');
    if (!isset($config)) {
        die('Config not valid, please copy config.sample.php to config.php and reconfigure values');
    }
} else {
    die('Config not found, please copy config.sample.php to config.php and configure values');
}

// Workaround for PHP < 8.0
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}

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

function echo_if_isset($array, $key, $echo = null): void
{
    if (isset($array[$key])) {
        if ($echo === null) {
            echo htmlspecialchars($array[$key]);
        } else {
            echo $echo;
        }
    }
}

function echo_if_check($array, $key, $value, $echo = null): void
{
    if (isset($array[$key]) && $array[$key] === $value) {
        if ($echo === null) {
            echo htmlspecialchars($array[$key]);
        } else {
            echo $echo;
        }
    }
}
function echo_sonstiges($array, $key): void
{
    if (isset($array[$key])) {

        $sonstiges = explode(':', $array[$key]);
        if(count($sonstiges) > 1){
            echo htmlspecialchars($sonstiges[1]);
        }
    }
}
function echo_if_contains($array, $key, $value, $echo = null): void
{
    if (isset($array[$key]) && str_contains($array[$key], $value)) {
        if ($echo === null) {
            echo htmlspecialchars($array[$key]);
        } else {
            echo $echo;
        }
    }
}

function if_isset_dt($array, $key): string
{
    if (isset($array[$key])) {
        return date('Y-m-d', strtotime($array[$key]));
    }
    return '';
}

function input($string, $len = 64): string
{
    return mb_strimwidth(trim($string), 0, $len);
}


$XmlData = [];
$datafile = null;
$old_xml = null;
if (isset($_GET['anmeldung']) && preg_match('/^[a-zA-Z0-9_]+$/', $_GET['anmeldung']) && file_exists('./xml/' . $_GET['anmeldung'] . '.xml')) {
    $old_xml = simplexml_load_file('./xml/' . $_GET['anmeldung'] . '.xml');
    if ($old_xml) {
        //Convert SimpleXml Object to associative Array
        $XmlData = json_decode(str_replace(':{}',':""',json_encode($old_xml)), TRUE);
        $datafile = explode('_', $_GET['anmeldung'])[0];
    }
}else{
    $XmlData = [
        "Feuerwehr" => null,
        "Organisationseinheit" => null,
        "Verantwortlicher" => [
            "Vorname" => null,
            "Name" => null,
            "Strasse" => null,
            "PLZ" => null,
            "Ort" => null,
            "Funktion" => null,
            "Telefon" => null,
            "Email" => null,
        ],
        "Persons" => [
            "Person" => [
                [
                    "Vorname" => null,
                    "Name" => null,
                    "Strasse" => null,
                    "PLZ" => null,
                    "Ort" => null,
                    "Geburtsdatum" => null,
                    "Geschlecht" => null,
                    "Status" => "Betreuer",
                    "Essgewohnheiten" => null,
                    "EssgewohnheitenSonstiges" => null,
                    "Unvertraeglichkeiten" => "keine",
                    "UnvertraeglichkeitenSonstiges" => null
                ]
            ],
        ],
    ];
}

if (isset($_POST['Feuerwehr']) && isset($_POST['Organisationseinheit']) && isset($_POST['Verantwortlicher']) && isset($_POST['Teilnehmer'])) {
    // XML-Daten aufbauen
    $xml = new DOMDocument("1.0", "utf-16");
    // Gruppe-Element erstellen
    $jf = $xml->createElement("Jugendfeuerwehr");
    $xml->appendChild($jf);

    if ($old_xml === null) {
        // Neue Daten
        $feuerwehr = $xml->createElement("Feuerwehr", input($_POST["Feuerwehr"]));
        $jf->appendChild($feuerwehr);

        if (!isset($_POST['Gruppe']["Organisationseinheit"])) {
            $_POST['Gruppe']["Organisationseinheit"] = '';
        }
        $organisationseinheit = $xml->createElement("Organisationseinheit", input($_POST["Organisationseinheit"]));
        $jf->appendChild($organisationseinheit);

        $timeStampAnmeldung = $xml->createElement("TimeStampAnmeldung", (new DateTime())->format("Y-m-d\TH:i:s"));
        $jf->appendChild($timeStampAnmeldung);
    } else {
        //Bei Update Feuerwehr, Organisationseinheit nicht ändern
        $feuerwehr = $xml->createElement("Feuerwehr", $XmlData['Feuerwehr']);
        $jf->appendChild($feuerwehr);

        $organisationseinheit = $xml->createElement("Organisationseinheit", $XmlData["Organisationseinheit"]);
        $jf->appendChild($organisationseinheit);

        $timeStampAnmeldung = $xml->createElement("TimeStampAnmeldung", $XmlData["TimeStampAnmeldung"]);
        $jf->appendChild($timeStampAnmeldung);
    }


    // Verantwortlicher-Element erstellen
    $verantwortlicher = $xml->createElement("Verantwortlicher");
    $jf->appendChild($verantwortlicher);

    $verantwortlicher->appendChild($xml->createElement("Vorname", input($_POST["Verantwortlicher"]["Vorname"])));
    $verantwortlicher->appendChild($xml->createElement("Name", input($_POST["Verantwortlicher"]["Name"])));
    $verantwortlicher->appendChild($xml->createElement("Strasse", input($_POST["Verantwortlicher"]["Strasse"])));
    $verantwortlicher->appendChild($xml->createElement("PLZ", input($_POST["Verantwortlicher"]["PLZ"])));
    $verantwortlicher->appendChild($xml->createElement("Ort", input($_POST["Verantwortlicher"]["Ort"])));
    $verantwortlicher->appendChild($xml->createElement("Funktion", input($_POST["Verantwortlicher"]["Funktion"])));
    $verantwortlicher->appendChild($xml->createElement("Telefon", input($_POST["Verantwortlicher"]["Telefon"])));
    $verantwortlicher->appendChild($xml->createElement("Email", input($_POST["Verantwortlicher"]["Email"])));

    // Teilnehmer-Element erstellen
    $persons = $xml->createElement("Persons");
    $jf->appendChild($persons);

    // Teilnehmer hinzufügen
    $teilnehmer = $_POST["Teilnehmer"];
    foreach ($teilnehmer["Vorname"] as $index => $vorname) {
        //If empty
        if ($teilnehmer["Vorname"][$index] == '' && $teilnehmer["Nachname"][$index] == '') {
            continue;
        }

        $person = $xml->createElement("Person");
        $persons->appendChild($person);

        $person->appendChild($xml->createElement("Vorname", input($teilnehmer["Vorname"][$index])));
        $person->appendChild($xml->createElement("Name", input($teilnehmer["Name"][$index])));
        $person->appendChild($xml->createElement("Strasse", input($teilnehmer["Strasse"][$index])));
        $person->appendChild($xml->createElement("PLZ", input($teilnehmer["PLZ"][$index], 5)));
        $person->appendChild($xml->createElement("Ort", input($teilnehmer["Ort"][$index])));

        $date = false;
        if (isset($teilnehmer["Geburtsdatum"][$index])) {
            // Geburtsdatum in das Format YYYY-MM-DD konvertieren
            $date = DateTime::createFromFormat("Y-m-d  H:i:s", input($teilnehmer["Geburtsdatum"][$index], 10) . ' 00:00:00');
        }
        $geburtsdatum = $xml->createElement("Geburtsdatum", $date->format("Y-m-d\TH:i:s"));
        $person->appendChild($geburtsdatum);

        $person->appendChild($xml->createElement("Geschlecht", input($teilnehmer["Geschlecht"][$index], 1)));
        $person->appendChild($xml->createElement("Status", input($teilnehmer["Status"][$index])));

        $essgewohnheiten = input($teilnehmer["Essgewohnheiten"][$index]);
        if(input($teilnehmer["Essgewohnheiten"][$index]) == "Sonstiges"){
            $essgewohnheiten = 'Sonstiges:'.input($teilnehmer["EssgewohnheitenSonstiges"][$index]);
        }
        $person->appendChild($xml->createElement("Essgewohnheiten", $essgewohnheiten));

        $unvertraeglichkeiten = implode(',', $teilnehmer["Unvertraeglichkeiten"][$index]);
        if(str_contains($unvertraeglichkeiten, 'Sonstiges')){
            $unvertraeglichkeiten = str_replace('Sonstiges', 'Sonstiges:'.input($teilnehmer["UnvertraeglichkeitenSonstiges"][$index]), $unvertraeglichkeiten);
        }
        $person->appendChild($xml->createElement("Unvertraeglichkeiten", $unvertraeglichkeiten));

    }

    // XML speichern
    if ($datafile === null) {
        $datafile = generateRandomString(48);
    }
    $url = $config['url'] . '?anmeldung=' . $datafile;
    $datafiledate = $datafile . '_' . time();
    $urlintern = $config['url'] . '?anmeldung=' . $datafiledate;

    $urlderAnmeldung = $xml->createElement("UrlderAnmeldung", $url);
    $jf->appendChild($urlderAnmeldung);

    $timeStampAenderung = $xml->createElement("TimeStampAenderung", (new DateTime())->format("Y-m-d\TH:i:s"));
    $jf->appendChild($timeStampAenderung);

    $xml->formatOutput = true;
    $xml->save('./xml/' . $datafile . '.xml');
    $xml->save('./xml/' . $datafiledate . '.xml');

    if (isset($_POST["Verantwortlicher"]["Email"]) && $_POST["Verantwortlicher"]["Email"] != '') {
        $message = str_replace('{URL}', $url, $config['mailmessage']);
        $messageintern = str_replace('{URLINT}', $urlintern, str_replace('{URL}', $url, $config['mailmessageintern']));
        $header = 'From: ' . $config['mailabsender'] . "\r\n" . 'Content-Type: text/plain; charset=utf-8' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        mail(trim($_POST["Verantwortlicher"]["Email"]), 'Anmeldung zum ' . $config['headline'], $message, $header);
        mail($config['mailintern'], 'Anmeldung von ' . input($_POST["Feuerwehr"]) . ', Teilnehmende: '.count($persons->childNodes), $messageintern, $header);
    }

    header('Location: ./?anmeldung=' . $datafile);
    setcookie("saved", true);
    exit;
}
$saved = false;
$invalid = false;
if (isset($_COOKIE['saved']) && $_COOKIE['saved']) {
    $saved = true;
    setcookie("saved", false, time() - 1000);
}
if (isset($_COOKIE['invalid']) && $_COOKIE['invalid']) {
    $invalid = true;
    setcookie("invalid", false, time() - 1000);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    if ($invalid) {
        ?>
        <div class="card text-white bg-danger">
            <div class="card-body">
                Achtung, Daten sind nicht valide. Bitte prüfe, ob alle Jahrgänge korrekt eingetragen sind.
            </div>
        </div>
        <?php
    }
    ?>
    <form method="POST" id="form">
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
                <select class="form-control" id="Organisationseinheit"
                        required <?php echo_if_isset($XmlData, 'Organisationseinheit', 'disabled'); ?>>
                    <option value="">Bitte wählen</option>
                    <?php foreach ($config['organizationalunits'] as $unit): ?>
                        <option value="<?php echo $unit; ?>"><?php echo $unit; ?></option>
                    <?php endforeach; ?>
                    <option value="extern" <?php echo_if_isset($XmlData, 'Organisationseinheit', 'selected'); ?>>Extern</option>
                </select>
            </div>
            <div class="col-sm-3">
                <label for="OrganisationseinheitSonstige"></label>
                <input type="text" class="form-control" name="Organisationseinheit"
                       id="OrganisationseinheitSonstige" style="display: none;" required
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
                <input type="text" class="form-control" name="Verantwortlicher[Name]" id="VerantwortlicherName"
                       placeholder="Nachname" required value="<?php echo_if_isset($XmlData["Verantwortlicher"], 'Name'); ?>">
            </div>
            <div class="col-sm-4">
                <div>
                    <label for="Adresse">Adresse:</label>
                    <input type="text" class="form-control mb-2" name="Verantwortlicher[Strasse]"
                           placeholder="Straße/Hausnummer" required id="Adresse" value="<?php echo_if_isset($XmlData["Verantwortlicher"], 'Strasse'); ?>">
                    <input type="text" class="form-control mb-2" name="Verantwortlicher[PLZ]" placeholder="PLZ" required
                           id="Adresse" value="<?php echo_if_isset($XmlData["Verantwortlicher"], 'PLZ'); ?>">
                    <input type="text" class="form-control" name="Verantwortlicher[Ort]" placeholder="Ort" required
                           id="Adresse" value="<?php echo_if_isset($XmlData["Verantwortlicher"], 'Ort'); ?>">
                </div>
            </div>
            <div class="col-sm-3">
                <label for="Funktion">Funktion:</label>
                <input type="text" class="form-control mb-2" name="Verantwortlicher[Funktion]" id="Funktion"
                       placeholder="z. B. Jugendwart" required value="<?php echo_if_isset($XmlData["Verantwortlicher"], 'Funktion'); ?>">
                <input type="text" class="form-control mb-2" name="Verantwortlicher[Telefon]" id="Telefon"
                       placeholder="Telefonnummer" required value="<?php echo_if_isset($XmlData["Verantwortlicher"], 'Telefon'); ?>">
                <input type="email" class="form-control" name="Verantwortlicher[Email]" id="Email"
                       placeholder="E-Mail Adresse" required value="<?php echo_if_isset($XmlData["Verantwortlicher"], 'Email'); ?>">
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
                        foreach ($XmlData["Persons"]["Person"] as $item) {
                        ?>
                        <tr>
                            <td>
                                <div>
                                    <input type="text" class="form-control" name="Teilnehmer[Vorname][]" placeholder="Vorname" required value="<?php echo_if_isset($item, 'Vorname'); ?>">
                                    <input type="text" class="form-control mb-2" name="Teilnehmer[Name][]" placeholder="Nachname" required value="<?php echo_if_isset($item, 'Name'); ?>">
                                </div>
                            </td>
                            <td>
                                <div>
                                    <input type="text" class="form-control mb-2" name="Teilnehmer[Strasse][]" placeholder="Straße/Hausnummer" required value="<?php echo_if_isset($item, 'Strasse'); ?>">
                                    <input type="text" class="form-control" name="Teilnehmer[PLZ][]" placeholder="PLZ" required value="<?php echo_if_isset($item, 'PLZ'); ?>">
                                    <input type="text" class="form-control" name="Teilnehmer[Ort][]" placeholder="Ort" required value="<?php echo_if_isset($item, 'Ort'); ?>">
                                </div>
                            </td>
                            <td>
                                <div>
                                    <input type="date" class="form-control mb-2" name="Teilnehmer[Geburtsdatum][]" required value="<?php echo if_isset_dt($item, 'Geburtsdatum'); ?>">
                                    <select name="Teilnehmer[Geschlecht][]" class="form-control" required>
                                        <option value="">Bitte wählen</option>
                                        <option <?php echo_if_check($item, 'Geschlecht', 'D', 'selected'); ?> value="D">D</option>
                                        <option <?php echo_if_check($item, 'Geschlecht', 'M', 'selected'); ?> value="M">M</option>
                                        <option <?php echo_if_check($item, 'Geschlecht', 'W', 'selected'); ?> value="W">W</option>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <select name="Teilnehmer[Status][]" class="form-control mb-2" required>
                                    <option <?php echo_if_check($item, 'Status', '1Geschwister', 'selected'); ?> value="1Geschwister">Teilnehmender</option>
                                    <option <?php echo_if_check($item, 'Status', '2Geschwister', 'selected'); ?> value="2Geschwister">2. Geschwisterkind</option>
                                    <option <?php echo_if_check($item, 'Status', 'WeitereGeschwister', 'selected'); ?> value="WeitereGeschwister">Weiteres Geschwisterkind</option>
                                    <option <?php echo_if_check($item, 'Status', 'Betreuer', 'selected'); ?> value="Betreuer">Betreuer</option>
                                    <option <?php echo_if_check($item, 'Status', 'Mitarbeiter', 'selected'); ?> value="Mitarbeiter">Mitarbeiter</option>
                                </select>
                            </td>
                            <td>
                                <div>
                                    <select name="Teilnehmer[Essgewohnheiten][]" class="form-control mb-2">
                                        <option <?php echo_if_contains($item, 'Essgewohnheiten', 'Alles', 'selected'); ?> value="Alles">Alles</option>
                                        <option <?php echo_if_contains($item, 'Essgewohnheiten', 'Vegetarisch', 'selected'); ?> value="Vegetarisch">Vegetarisch</option>
                                        <option <?php echo_if_contains($item, 'Essgewohnheiten', 'Vegan', 'selected'); ?> value="Vegan">Vegan</option>
                                        <option <?php echo_if_contains($item, 'Essgewohnheiten', 'Sonstiges', 'selected'); ?> value="Sonstiges">Sonstiges</option>
                                    </select>
                                    <input type="text" class="form-control" name="Teilnehmer[EssgewohnheitenSonstiges][]" placeholder="Sonstige Essgewohnheiten" value="<?php echo_sonstiges($item, 'Essgewohnheiten'); ?>" style="display: none;">
                                </div>
                            </td>
                            <td>
                                <div>
                                    <select name="Teilnehmer[Unvertraeglichkeiten][0][]" class="form-control mb-2" multiple>
                                        <option <?php echo_if_contains($item, 'Unvertraeglichkeiten', 'keine', 'selected'); ?> value="keine">Keine</option>
                                        <option <?php echo_if_contains($item, 'Unvertraeglichkeiten', 'Erdnuesse', 'selected'); ?> value="Erdnuesse">Erdnüsse</option>
                                        <option <?php echo_if_contains($item, 'Unvertraeglichkeiten', 'Gluten', 'selected'); ?> value="Gluten">Gluten</option>
                                        <option <?php echo_if_contains($item, 'Unvertraeglichkeiten', 'Laktose', 'selected'); ?> value="Laktose">Laktose</option>
                                        <option <?php echo_if_contains($item, 'Unvertraeglichkeiten', 'Schalenfruechte', 'selected'); ?> value="Schalenfruechte">Schalenfrüchte</option>
                                        <option <?php echo_if_contains($item, 'Unvertraeglichkeiten', 'Schalentiere', 'selected'); ?> value="Schalentiere">Schalentiere</option>
                                        <option <?php echo_if_contains($item, 'Unvertraeglichkeiten', 'Sellerie', 'selected'); ?> value="Sellerie">Sellerie</option>
                                        <option <?php echo_if_contains($item, 'Unvertraeglichkeiten', 'Senf', 'selected'); ?> value="Senf">Senf</option>
                                        <option <?php echo_if_contains($item, 'Unvertraeglichkeiten', 'Sesam', 'selected'); ?> value="Sesam">Sesam</option>
                                        <option <?php echo_if_contains($item, 'Unvertraeglichkeiten', 'Soja', 'selected'); ?> value="Soja">Soja</option>
                                        <option <?php echo_if_contains($item, 'Unvertraeglichkeiten', 'Sulfite', 'selected'); ?> value="Sulfite">Sulfite</option>
                                        <option <?php echo_if_contains($item, 'Unvertraeglichkeiten', 'Sonstiges', 'selected'); ?> value="Sonstiges">Sonstiges</option>
                                    </select>
                                    <input type="text" class="form-control" name="Teilnehmer[UnvertraeglichkeitenSonstiges][]" placeholder="Sonstige Unverträglichkeiten" value="<?php echo_sonstiges($item, 'Unvertraeglichkeiten'); ?>" style="display: none;">
                                </div>
                            </td>
                        </tr>
                        <?php
                        }
                        ?>

                        </tbody>
                    </table>
                    <button type="button" class="btn btn-secondary" id="add-teilnehmer">Teilnehmer hinzufügen</button>
                    <button type="button" class="btn btn-danger" id="delete-last-teilnehmer">Teilnehmer löschen</button>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-sm-12">
                <h5>Kosten</h5>
                <p>Pro Person: <span id="cost-pp"><?php echo $config['cost_pp'];?></span>€</p>
                <p>Gesamtbetrag: <span id="total-cost">0</span>€</p>
                <p><strong>Bankverbindung</strong><br>
<?php echo $config['bankdetails']; ?>
                Verwendungszweck: <span id="jf-name"><?php echo_if_isset($XmlData, 'Feuerwehr'); ?></span>, <span id="participant-count">X</span> TN</p>
            </div>
        </div>

        <div class="row">
            <div class="col-sm text-center">
                <button class="btn btn-primary" type="submit">Anmeldung übermitteln</button>
            </div>
        </div>
</div>
<script src="./js/bootstrap.bundle.min.js"></script>
<script src="./js/KJFCux.js"></script>
</body>
</html>



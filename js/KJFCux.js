let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
let tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
})


document.getElementById('add-teilnehmer').addEventListener('click', function () {
    const container = document.getElementById('teilnehmer-list');
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>
            <div>
                <input type="text" class="form-control" name="Teilnehmer[Vorname][]" placeholder="Vorname" required>
                <input type="text" class="form-control mb-2" name="Teilnehmer[Name][]" placeholder="Nachname" required>
            </div>
        </td>
        <td>
            <div>
                <input type="text" class="form-control mb-2" name="Teilnehmer[Strasse][]" placeholder="Straße/Hausnummer" required>
                <input type="text" class="form-control" name="Teilnehmer[PLZ][]" placeholder="PLZ" required>
                <input type="text" class="form-control" name="Teilnehmer[Ort][]" placeholder="Ort" required>
            </div>
        </td>
        <td>
            <div>
                <input type="date" class="form-control mb-2" name="Teilnehmer[Geburtsdatum][]" required>
                <select name="Teilnehmer[Geschlecht][]" class="form-control" required>
                    <option value="">Bitte wählen</option>
                    <option value="D">D</option>
                    <option value="M">M</option>
                    <option value="W">W</option>
                </select>
            </div>
        </td>
        <td>
            <select name="Teilnehmer[Status][]" class="form-control mb-2" required>
                <option value="1Geschwister">Teilnehmender</option>
                <option value="2Geschwister">2. Geschwisterkind</option>
                <option value="WeitereGeschwister">Weitere Geschwisterkinder</option>
                <option value="Betreuer">Betreuer</option>
                <option value="Mitarbeiter">Mitarbeiter</option>
            </select>
        </td>
        <td>
            <div>
                <select name="Teilnehmer[Essgewohnheiten][]" class="form-control mb-2">
                    <option selected value="Alles">Alles</option>
                    <option value="Vegetarisch">Vegetarisch</option>
                    <option value="Vegan">Vegan</option>
                    <option value="Sonstiges">Sonstiges</option>
                </select>
                <input type="text" class="form-control" name="Teilnehmer[EssgewohnheitenSonstiges][]" placeholder="Sonstige Essgewohnheiten" style="display: none;">
            </div>
        </td>
        <td>
            <div>
                <select name="Teilnehmer[Unvertraeglichkeiten][0][]" class="form-control mb-2" multiple>
                    <option selected value="keine">Keine</option>
                    <option value="Erdnuesse">Erdnüsse</option>
                    <option value="Gluten">Gluten</option>
                    <option value="Laktose">Laktose</option>
                    <option value="Schalenfruechte">Schalenfrüchte</option>
                    <option value="Schalentiere">Schalentiere</option>
                    <option value="Sellerie">Sellerie</option>
                    <option value="Senf">Senf</option>
                    <option value="Sesam">Sesam</option>
                    <option value="Soja">Soja</option>
                    <option value="Sulfite">Sulfite</option>
                    <option value="Sonstiges">Sonstiges</option>
                </select>
                <input type="text" class="form-control" name="Teilnehmer[UnvertraeglichkeitenSonstiges][]" placeholder="Sonstige Unverträglichkeiten" style="display: none;">
            </div>
        </td>
    `;

    // Neuen Index berechnen
    const newIndex = container.querySelectorAll('tr').length;

    // Zur Tabelle hinzufügen
    container.appendChild(newRow);
    addEventListenersToRow(newRow, newIndex);

    // Update participant count and total cost
    updateParticipantCountAndCost();
});

// Event-Listener für bestehende Felder hinzufügen
function addEventListenersToRow(row, index) {
    // Aktualisiere die Namen der Felder mit dem richtigen Index
    row.querySelectorAll('[name]').forEach(function (input) {
        const originalName = input.getAttribute('name');
        input.setAttribute('name', originalName.replace(/\[\d+\]/, `[${index}]`));
    });
}

function updateParticipantCountAndCost() {
    const rows = document.querySelectorAll('#teilnehmer-list tr').length;
    const costPerPerson = parseInt(document.getElementById('cost-pp').textContent);
    document.getElementById('participant-count').textContent = rows;
    document.getElementById('total-cost').textContent = rows * costPerPerson;
}

document.addEventListener('DOMContentLoaded', function () {

    const verantwortlicherFields = {
        vorname: document.querySelector('input[name="Verantwortlicher[Vorname]"]'),
        name: document.querySelector('input[name="Verantwortlicher[Name]"]'),
        strasse: document.querySelector('input[name="Verantwortlicher[Strasse]"]'),
        plz: document.querySelector('input[name="Verantwortlicher[PLZ]"]'),
        ort: document.querySelector('input[name="Verantwortlicher[Ort]"]')
    };

    const teilnehmerFields = {
        vorname: document.querySelector('input[name="Teilnehmer[Vorname][]"]'),
        name: document.querySelector('input[name="Teilnehmer[Name][]"]'),
        strasse: document.querySelector('input[name="Teilnehmer[Strasse][]"]'),
        plz: document.querySelector('input[name="Teilnehmer[PLZ][]"]'),
        ort: document.querySelector('input[name="Teilnehmer[Ort][]"]')
    };

    function syncFields(source, target) {
        source.addEventListener('input', function () {
            target.value = source.value;
        });
    }

    // Synchronize Verantwortlicher fields with the first Teilnehmer fields
    syncFields(verantwortlicherFields.vorname, teilnehmerFields.vorname);
    syncFields(verantwortlicherFields.name, teilnehmerFields.name);
    syncFields(verantwortlicherFields.strasse, teilnehmerFields.strasse);
    syncFields(verantwortlicherFields.plz, teilnehmerFields.plz);
    syncFields(verantwortlicherFields.ort, teilnehmerFields.ort);

    // Synchronize the first Teilnehmer fields with Verantwortlicher fields
    syncFields(teilnehmerFields.vorname, verantwortlicherFields.vorname);
    syncFields(teilnehmerFields.name, verantwortlicherFields.name);
    syncFields(teilnehmerFields.strasse, verantwortlicherFields.strasse);
    syncFields(teilnehmerFields.plz, verantwortlicherFields.plz);
    syncFields(teilnehmerFields.ort, verantwortlicherFields.ort);

    const input_jfname = document.getElementById("Feuerwehr");
    const display_jfname = document.getElementById("jf-name");
    input_jfname.addEventListener('input', function () {
        display_jfname.textContent = input_jfname.value;
    });

    // Update participant count and total cost
    updateParticipantCountAndCost();

    // Funktion zur Steuerung der Anzeige des Freitextfeldes für OU
    const selectOU = document.getElementById("Organisationseinheit");
    selectOU.addEventListener('change', function () {
        const textbox = document.getElementById("OrganisationseinheitSonstige");
        if (this.value === "extern") {
            textbox.value = '';
            textbox.style.display = 'block';
        } else {
            textbox.style.display = 'none';
            textbox.value = this.value;
        }
    });

    // Funktion zur Steuerung der Anzeige des Freitextfeldes für Essgewohnheiten
    document.querySelectorAll('select[name="Teilnehmer[Essgewohnheiten][]"]').forEach(function (select) {
        toggleTextbox(select, 'EssgewohnheitenSonstiges');
        select.addEventListener('change', function () {
            toggleTextbox(this, 'EssgewohnheitenSonstiges');
        });
    });

    // Funktion zur Steuerung der Anzeige des Freitextfeldes für Unverträglichkeiten
    document.querySelectorAll('select[name^="Teilnehmer[Unvertraeglichkeiten]"]').forEach(function (select) {
        toggleTextbox(select, 'UnvertraeglichkeitenSonstiges');
        select.addEventListener('change', function () {
            toggleTextbox(this, 'UnvertraeglichkeitenSonstiges');
        })
    });

    // Funktion, um das passende Freitextfeld ein- oder auszublenden
    function toggleTextbox(selectElement, textboxName) {
        const row = selectElement.closest('tr');
        const textbox = row.querySelector(`input[name^="Teilnehmer[${textboxName}]"]`);
        if (selectElement.value === "Sonstiges" || Array.from(selectElement.selectedOptions).some(option => option.value === "Sonstiges")) {
            textbox.style.display = 'block';
        } else {
            textbox.style.display = 'none';
            textbox.value = ''; // Textbox leeren, wenn sie ausgeblendet wird
        }
    }

    // Initiale Event-Listener hinzufügen
    document.querySelectorAll('#teilnehmer-list tr').forEach(function (row, index) {
        addEventListenersToRow(row, index);
    });

    // Funktion, um die Logik auch bei neu hinzugefügten Teilnehmern anzuwenden
    document.getElementById('add-teilnehmer').addEventListener('click', function () {
        const lastRow = document.querySelector('#teilnehmer-list tr:last-child');
        const essgewohnheitenSelect = lastRow.querySelector('select[name^="Teilnehmer[Essgewohnheiten]"]');
        const unvertraeglichkeitenSelect = lastRow.querySelector('select[name^="Teilnehmer[Unvertraeglichkeiten]"]');


        // Event-Listener für die neuen Dropdowns hinzufügen
        toggleTextbox(essgewohnheitenSelect, 'EssgewohnheitenSonstiges');
        essgewohnheitenSelect.addEventListener('change', function () {
            toggleTextbox(this, 'EssgewohnheitenSonstiges');
        });

        toggleTextbox(unvertraeglichkeitenSelect, 'UnvertraeglichkeitenSonstiges');
        unvertraeglichkeitenSelect.addEventListener('change', function () {
            toggleTextbox(this, 'UnvertraeglichkeitenSonstiges');
        });
    });

    document.getElementById('delete-last-teilnehmer').addEventListener('click', function () {
        const teilnehmerList = document.getElementById('teilnehmer-list');
        const rows = teilnehmerList.getElementsByTagName('tr');
        if (rows.length > 1) {
            teilnehmerList.removeChild(rows[rows.length - 1]);
        } else {
            alert('Der erste Teilnehmer kann nicht gelöscht werden.');
        }
        // Update participant count and total cost
        updateParticipantCountAndCost();
    });
});

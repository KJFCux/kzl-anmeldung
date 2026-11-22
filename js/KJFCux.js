// Rabatt-Faktoren für Geschwisterkinder (Anteil vom Beitrag pro Person)
const BEITRAGSFAKTOR = {
    '2Geschwister': 0.75,
    'WeitereGeschwister': 0.5
};

// Felder, die zwischen verantwortlicher Person und erstem Teilnehmenden synchronisiert werden
const SYNC_FELDER = ['Vorname', 'Name', 'Strasse', 'PLZ', 'Ort'];

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });

    const teilnehmerList = document.getElementById('teilnehmer-list');
    const template = document.getElementById('teilnehmer-template');
    const costPerPerson = parseFloat(document.getElementById('cost-pp').textContent.replace(',', '.')) || 0;

    function formatEuro(betrag) {
        return betrag.toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Freitextfeld "Sonstiges" passend zur Auswahl ein- oder ausblenden
    function toggleSonstiges(select) {
        const textbox = select.parentElement.querySelector('.sonstiges-input');
        const show = Array.from(select.selectedOptions).some(function (option) {
            return option.value === 'Sonstiges';
        });
        textbox.hidden = !show;
        textbox.required = show;
        if (!show) {
            textbox.value = '';
        }
    }

    function updateParticipantCountAndCost(report) {
        const statusDropdowns = teilnehmerList.querySelectorAll('.teilnehmer-status');
        let totalCost = 0;
        let countTeilnehmender = 0;
        let count2Geschwister = 0;

        statusDropdowns.forEach(function (dropdown) {
            const status = dropdown.value;
            totalCost += costPerPerson * (BEITRAGSFAKTOR[status] || 1);
            if (status === '1Geschwister') {
                countTeilnehmender++;
            } else if (status === '2Geschwister') {
                count2Geschwister++;
            }
        });

        document.getElementById('participant-count').textContent = statusDropdowns.length;
        document.getElementById('total-cost').textContent = formatEuro(totalCost);

        // Geschwisterrabatt nur, wenn auch ein (2.) Geschwisterkind angemeldet ist
        statusDropdowns.forEach(function (dropdown) {
            if (dropdown.value === '2Geschwister' && countTeilnehmender < 1) {
                dropdown.setCustomValidity('Es muss ein Teilnehmer vorhanden sein.');
            } else if (dropdown.value === 'WeitereGeschwister' && count2Geschwister < 1) {
                dropdown.setCustomValidity('Es muss ein 2. Geschwisterkind vorhanden sein.');
            } else {
                dropdown.setCustomValidity('');
            }
            if (report === true) {
                dropdown.reportValidity();
            }
        });
    }

    function initRow(row) {
        row.querySelectorAll('select.has-sonstiges').forEach(function (select) {
            toggleSonstiges(select);
            select.addEventListener('change', function () {
                toggleSonstiges(select);
            });
        });
        row.querySelector('.teilnehmer-status').addEventListener('change', function () {
            updateParticipantCountAndCost(true);
        });
    }

    teilnehmerList.querySelectorAll('tr').forEach(initRow);

    document.getElementById('add-teilnehmer').addEventListener('click', function () {
        const index = teilnehmerList.querySelectorAll('tr').length;
        const row = template.content.firstElementChild.cloneNode(true);
        row.querySelectorAll('[name*="__INDEX__"]').forEach(function (input) {
            input.name = input.name.replace('__INDEX__', index);
        });
        teilnehmerList.appendChild(row);
        initRow(row);
        updateParticipantCountAndCost();
        row.querySelector('input').focus();
    });

    document.getElementById('delete-last-teilnehmer').addEventListener('click', function () {
        const rows = teilnehmerList.querySelectorAll('tr');
        if (rows.length <= 1) {
            alert('Der erste Teilnehmer kann nicht gelöscht werden.');
            return;
        }
        const lastRow = rows[rows.length - 1];
        const hatDaten = Array.from(lastRow.querySelectorAll('input[type="text"], input[type="date"]')).some(function (input) {
            return input.value.trim() !== '';
        });
        if (hatDaten && !confirm('Den letzten Teilnehmer wirklich löschen?')) {
            return;
        }
        lastRow.remove();
        updateParticipantCountAndCost();
    });

    // Verantwortliche Person und erster Teilnehmender werden gegenseitig synchronisiert
    const ersteZeile = teilnehmerList.querySelector('tr');
    SYNC_FELDER.forEach(function (feld) {
        const verantwortlicher = document.querySelector('input[name="Verantwortlicher[' + feld + ']"]');
        const teilnehmer = ersteZeile.querySelector('input[name="Teilnehmer[' + feld + '][]"]');
        verantwortlicher.addEventListener('input', function () {
            teilnehmer.value = verantwortlicher.value;
        });
        teilnehmer.addEventListener('input', function () {
            verantwortlicher.value = teilnehmer.value;
        });
    });

    // Name der Jugendfeuerwehr im Verwendungszweck anzeigen
    const inputJfName = document.getElementById('Feuerwehr');
    const displayJfName = document.getElementById('jf-name');
    inputJfName.addEventListener('input', function () {
        displayJfName.textContent = inputJfName.value;
    });

    // Freitextfeld für externe Organisationseinheiten
    const selectOU = document.getElementById('Organisationseinheit');
    const textboxOU = document.getElementById('OrganisationseinheitSonstige');
    textboxOU.hidden = selectOU.value !== 'extern';
    selectOU.addEventListener('change', function () {
        if (selectOU.value === 'extern') {
            textboxOU.value = '';
            textboxOU.hidden = false;
            textboxOU.focus();
        } else {
            textboxOU.hidden = true;
            textboxOU.value = selectOU.value;
        }
    });

    updateParticipantCountAndCost();
});

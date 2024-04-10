function getEventDate(eventDate){
    let dateParts = eventDate.split('.');
    let day = parseInt(dateParts[0]);
    let month = parseInt(dateParts[1]) - 1;
    let year = parseInt(dateParts[2]);

    return new Date(year, month, day);
}

$(document).ready(function () {
    const validationMessage = $('#age-validation-message')
    const birthday = $('.Geburtsdatum')
    const average = $('.average')
    const totalAge = $('#total-age');
    const totalAverage = $('#average');
    const eventDate =  getEventDate($('#eventdate').text());

    function validate() {
        // Date validation
        validationMessage.text('');
        birthday.each(function () {
            calculateAgeForRow($(this));
        });
        calculateTotalAge();
    }

    validate();

    // Date validation
    birthday.change(function () {
        validate();
    });

    $('#form').submit(function (event) {
        validate();
        if (validationMessage.text() !== '') {
            alert(validationMessage.text());
            return false;
        }
    });

    function calculateTotalAge() {
        // Update total age
        let age = 0;
        let count = 0;
        average.each(function () {
            if ($(this).text() !== "") {
                count++;
                age += Number($(this).text());
            }
        });

        if (count !== 0) {
            totalAge.text(age);
            totalAverage.text(Math.floor(age / count));
        }

    }

    function calculateAgeForRow(element) {
        element.removeClass('error');
        let inputDate = element.val();
        if (inputDate === '') return;
        let dateRegex = /^(0?[1-9]|[12][0-9]|3[01])[.](0?[1-9]|1[012])[.]((19|20)\d\d)$/;
        if (!dateRegex.test(inputDate)) {
            element.addClass('error');
            validationMessage.text('Bitte geben Sie ein gültiges Geburtsdatum im Format DD.MM.YYYY ein.');
            return;
        }
        let dateParts = inputDate.split('.');
        let day = parseInt(dateParts[0]);
        let month = parseInt(dateParts[1]) - 1;
        let year = parseInt(dateParts[2]);

        //Mindestalter 10 Jahre
        let allowage = new Date(year + 10, month, day);

        let age = eventDate.getFullYear() - year;

        element.closest('tr').find('td:last').text(age);

        if (age < 10 || age > 18) {
            element.addClass('error');
            validationMessage.text('Das Alter muss zwischen 10 und 18 Jahren liegen.');
            return;
        }

        if (age === 10 && eventDate < allowage) {
            element.addClass('error');
            validationMessage.text('Das Mindestalter beträgt 10 Jahre');
            return;
        }


    }
});
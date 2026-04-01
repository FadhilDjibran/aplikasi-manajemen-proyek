function moneyFormat(val) {
    if (!val && val !== 0) return '';

    let str = val.toString().replace(/\./g, '');
    let clean = str.replace(/[^0-9,]/g, '');

    let parts = clean.split(',');
    let bulat = parts[0];
    let desimal = parts[1];

    bulat = bulat.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

    if (desimal !== undefined) {
        return bulat + ',' + desimal.substring(0, 2);
    }
    return bulat;
}

function cleanToSql(val) {
    if (!val) return '0';
    return val.toString().replace(/\./g, '').replace(',', '.');
}

document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.money-format');

    inputs.forEach(input => {
        const hiddenInput = input.parentElement.querySelector('input[type="hidden"]');

        const syncValue = (value) => {
            let formatted = moneyFormat(value);
            input.value = formatted;
            if (hiddenInput) {
                hiddenInput.value = cleanToSql(formatted);
            }
        };

        if (input.value) {
            let initial = input.value.replace('.', ',');
            syncValue(initial);
        }

        input.addEventListener('input', function () {
            let cursorPosition = this.selectionStart;
            let oldLength = this.value.length;

            syncValue(this.value);

            let newLength = this.value.length;
            cursorPosition = cursorPosition + (newLength - oldLength);
            this.setSelectionRange(cursorPosition, cursorPosition);
        });
    });
});

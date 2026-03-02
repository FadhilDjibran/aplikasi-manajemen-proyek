function moneyFormat(angka) {
    if (!angka) return '';
    let numberString = angka.replace(/[^0-9]/g, '').toString();
    let sisa = numberString.length % 3;
    let rupiah = numberString.substr(0, sisa);
    let ribuan = numberString.substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }
    return rupiah;
}

document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.money-format');

    inputs.forEach(input => {
        if (input.value) {
            input.value = moneyFormat(input.value);
        }

        input.addEventListener('input', function () {
            let rawValue = this.value.replace(/\./g, '');

            let hiddenInput = this.parentElement.querySelector('input[type="hidden"]');
            if (hiddenInput) {
                hiddenInput.value = rawValue;
            }

            this.value = moneyFormat(this.value);
        });
    });
});

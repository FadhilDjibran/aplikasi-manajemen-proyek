document.addEventListener('DOMContentLoaded', function () {
    const elTanggal = document.getElementById('tanggal_transaksi');
    const elInput = document.getElementById('tipe_input');
    const containerSaldo = document.getElementById('saldo-container');
    const valSaldo = document.getElementById('saldo-value');

    if (!elTanggal || !elInput || !containerSaldo || !valSaldo) return;

    function fetchSaldo() {
        const tanggal = elTanggal.value;
        const tipe = elInput.value;

        if (!tanggal || !tipe) {
            containerSaldo.style.display = 'none';
            return;
        }

        const baseUrl = elInput.getAttribute('data-saldo-url');

        if (!baseUrl) {
            console.error('URL rute get-saldo tidak ditemukan di atribut data-saldo-url');
            return;
        }

        containerSaldo.style.display = 'block';
        valSaldo.textContent = 'Memuat...';

        const url = `${baseUrl}?tanggal=${encodeURIComponent(tanggal)}&tipe_input=${encodeURIComponent(tipe)}`;

        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(async response => {
                if (!response.ok) {
                    const textError = await response.text();
                    throw new Error(textError);
                }
                return response.json();
            })
            .then(data => {
                const formattedSaldo = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR'
                }).format(data.saldo);

                valSaldo.textContent = formattedSaldo;
            })
            .catch(error => {
                console.error("Error dari Server:", error);
                valSaldo.textContent = 'Terjadi Kesalahan Server';
            });
    }

    elTanggal.addEventListener('change', fetchSaldo);
    elInput.addEventListener('change', fetchSaldo);

    if (elTanggal.value && elInput.value) {
        fetchSaldo();
    }
});

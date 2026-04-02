document.addEventListener('DOMContentLoaded', function () {

    window.initTomSelectCoa = function (element) {
        if (element.tomselect) return;
        if (typeof TomSelect !== 'undefined') {
            new TomSelect(element, {
                create: false,
                sortField: { field: "text", direction: "asc" },
                maxOptions: 1000,
                dropdownParent: 'body'
            });
        }
    };

    document.querySelectorAll('.coa-select-dynamic').forEach(el => initTomSelectCoa(el));

    document.body.addEventListener('change', function (e) {

        if (e.target.classList.contains('date-trigger-coa')) {
            const dateInput = e.target;
            const dateVal = dateInput.value;
            if (!dateVal) return;

            const container = dateInput.closest('.transaction-container');
            if (!container) return;

            const selectEl = container.querySelector('.coa-select-dynamic');
            if (!selectEl) return;

            const apiUrl = selectEl.getAttribute('data-coa-url');
            if (!apiUrl) {
                console.error("Atribut data-coa-url tidak ditemukan pada elemen select!");
                return;
            }

            if (selectEl.tomselect) {
                const ts = selectEl.tomselect;

                ts.clearOptions();
                ts.addOption({ value: '', text: 'Memuat data akun...' });

                fetch(`${apiUrl}?tanggal=${dateVal}`)
                    .then(response => response.json())
                    .then(data => {
                        const currentValue = ts.getValue();

                        ts.clear();
                        ts.clearOptions();
                        ts.clearOptionGroups();

                        for (const [kategori, accounts] of Object.entries(data)) {
                            ts.addOptionGroup(kategori, { label: kategori });

                            accounts.forEach(acc => {
                                ts.addOption({
                                    value: acc.no_akun,
                                    text: `${acc.no_akun} - ${acc.nama_akun}`,
                                    optgroup: kategori
                                });
                            });
                        }

                        if (currentValue) {
                            ts.setValue(currentValue);
                        }
                    })
                    .catch(error => {
                        console.error('Gagal mengambil data CoA:', error);
                        ts.clearOptions();
                        ts.addOption({ value: '', text: 'Gagal memuat data.' });
                    });
            }
        }
    });
});

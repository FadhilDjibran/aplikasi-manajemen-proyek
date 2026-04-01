document.addEventListener('DOMContentLoaded', function () {

    function initTomSelect(element) {
        if (element.tomselect) return;
        if (typeof TomSelect !== 'undefined') {
            new TomSelect(element, {
                create: false,
                sortField: { field: "text", direction: "asc" },
                maxOptions: 1000,
                dropdownParent: 'body'
            });
        }
    }

    document.querySelectorAll('.coa-select').forEach(el => initTomSelect(el));

    // SMART PARSER JS: Menghindari penghapusan titik jika itu adalah desimal
    function cleanMoney(val) {
        if (!val) return 0;
        let str = val.toString();

        if (str.includes(',')) {
            // Format IDR: 1.200,50 -> 1200.50
            str = str.replace(/\./g, '').replace(',', '.');
        } else if (str.split('.').length > 2) {
            // Format Ribuan: 1.000.000 -> 1000000
            str = str.replace(/\./g, '');
        }

        let parsed = parseFloat(str);
        return isNaN(parsed) ? 0 : parsed;
    }

    function formatToRupiah(num) {
        return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function calculateBalance() {
        let baseDebit = 0;
        let baseKredit = 0;

        document.querySelectorAll('.merge-checkbox:checked').forEach(cb => {
            let db = parseFloat(cb.getAttribute('data-debit'));
            let kr = parseFloat(cb.getAttribute('data-kredit'));
            baseDebit += isNaN(db) ? 0 : db;
            baseKredit += isNaN(kr) ? 0 : kr;
        });

        let totalFormDebit = 0;
        let totalFormKredit = 0;

        document.querySelectorAll('.input-debit').forEach(input => {
            totalFormDebit += cleanMoney(input.value);
        });
        document.querySelectorAll('.input-kredit').forEach(input => {
            totalFormKredit += cleanMoney(input.value);
        });

        let totalDebit = Number(baseDebit) + Number(totalFormDebit);
        let totalKredit = Number(baseKredit) + Number(totalFormKredit);

        // Atasi Javascript Floating Point Error
        let selisih = Math.abs(totalDebit - totalKredit);

        const calcDebitEl = document.getElementById('calc-debit');
        const calcKreditEl = document.getElementById('calc-kredit');
        const calcSelisihEl = document.getElementById('calc-selisih');

        if (calcDebitEl) calcDebitEl.textContent = 'Rp ' + formatToRupiah(totalDebit);
        if (calcKreditEl) calcKreditEl.textContent = 'Rp ' + formatToRupiah(totalKredit);
        if (calcSelisihEl) calcSelisihEl.textContent = 'Rp ' + formatToRupiah(selisih);

        const badge = document.getElementById('badge-status');
        const btnSubmit = document.getElementById('btn-submit');

        if (!badge || !btnSubmit) return;

        // Validasi tombol dengan toleransi 0.01 (untuk sen)
        if (totalDebit === 0 && totalKredit === 0) {
            badge.textContent = "KOSONG";
            badge.style.background = "#e2e8f0";
            badge.style.color = "#475569";
            if (calcSelisihEl) calcSelisihEl.style.color = "#64748b";

            btnSubmit.disabled = true;
            btnSubmit.style.opacity = "0.5";

        } else if (selisih < 0.01) { // Ganti selisih === 0 menjadi selisih < 0.01
            badge.textContent = "SEIMBANG (READY)";
            badge.style.background = "#d1fae5";
            badge.style.color = "#065f46";
            if (calcSelisihEl) calcSelisihEl.style.color = "#10b981";

            btnSubmit.disabled = false;
            btnSubmit.style.opacity = "1";

        } else {
            badge.textContent = "TIDAK SEIMBANG";
            badge.style.background = "#fee2e2";
            badge.style.color = "#991b1b";
            if (calcSelisihEl) calcSelisihEl.style.color = "#dc2626";

            btnSubmit.disabled = true;
            btnSubmit.style.opacity = "0.5";
        }
    }

    document.body.addEventListener('input', function (e) {
        if (e.target.classList.contains('input-debit') || e.target.classList.contains('input-kredit')) {
            if (typeof moneyFormat === 'function') {
                let cursorPosition = e.target.selectionStart;
                let originalLength = e.target.value.length;

                e.target.value = moneyFormat(e.target.value);

                let newLength = e.target.value.length;
                cursorPosition = cursorPosition + (newLength - originalLength);
                e.target.setSelectionRange(cursorPosition, cursorPosition);

                // Sinkronkan hidden input jika ada di dalam table (opsional pengaman)
                let hiddenInput = e.target.parentElement.querySelector('input[type="hidden"]');
                if (hiddenInput) {
                    hiddenInput.value = cleanMoney(e.target.value);
                }
            }
            calculateBalance();
        }
    });

    document.body.addEventListener('change', function (e) {
        if (e.target.classList.contains('merge-checkbox')) {
            const card = e.target.closest('.merge-card');
            if (card) {
                if (e.target.checked) {
                    card.style.borderColor = '#3b82f6';
                    card.style.backgroundColor = '#eff6ff';
                } else {
                    card.style.borderColor = '#cbd5e1';
                    card.style.backgroundColor = '#fff';
                }
            }
            calculateBalance();
        }
    });

    document.body.addEventListener('click', function (e) {
        const btnRemove = e.target.closest('.btn-remove-row');
        if (btnRemove) {
            const rowCount = document.querySelectorAll('.jurnal-row').length;
            if (rowCount > 1) {
                btnRemove.closest('tr').remove();
                calculateBalance();
            } else {
                alert("Jurnal minimal harus memiliki 1 baris transaksi.");
            }
        }
    });

    const btnAddRow = document.getElementById('btn-add-row');
    if (btnAddRow) {
        btnAddRow.addEventListener('click', function () {
            const template = document.getElementById('row-template');
            if (!template) return;

            const clone = template.content.cloneNode(true);
            const body = document.getElementById('jurnal-body');

            const rows = body.querySelectorAll('.jurnal-row');
            if (rows.length > 0) {
                const lastRow = rows[rows.length - 1];
                const prevTanggal = lastRow.querySelector('input[name="tanggal_array[]"]')?.value;
                const prevPenggunaan = lastRow.querySelector('input[name="jenis_penggunaan_array[]"]')?.value;
                const prevKeterangan = lastRow.querySelector('input[name="keterangan_array[]"]')?.value;

                if (prevTanggal) clone.querySelector('input[name="tanggal_array[]"]').value = prevTanggal;
                if (prevPenggunaan) clone.querySelector('input[name="jenis_penggunaan_array[]"]').value = prevPenggunaan;
                if (prevKeterangan) clone.querySelector('input[name="keterangan_array[]"]').value = prevKeterangan;
            }

            const newSelect = clone.querySelector('.coa-select-new');
            if (newSelect) {
                newSelect.classList.remove('coa-select-new');
                newSelect.classList.add('coa-select');
            }

            body.appendChild(clone);

            const addedSelect = body.lastElementChild.querySelector('.coa-select');
            if (addedSelect) {
                initTomSelect(addedSelect);
            }

            calculateBalance();
        });
    }

    setTimeout(calculateBalance, 100);
});

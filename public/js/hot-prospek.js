

document.addEventListener('DOMContentLoaded', function () {

    let currentSelectElement = null;

    window.openTransaksiModal = function (id, name) {
        const modal = document.getElementById('transaksiModal');
        const leadIdInput = document.getElementById('modalLeadId');
        const leadNameLabel = document.getElementById('modalLeadName');

        if (modal && leadIdInput && leadNameLabel) {
            leadIdInput.value = id;
            leadNameLabel.innerText = name;
            modal.style.display = 'flex';
        } else {
            console.error('Modal tidak ditemukan.');
        }
    };

    window.closeTransaksiModal = function () {
        const modal = document.getElementById('transaksiModal');
        if (modal) modal.style.display = 'none';
    };


    window.checkStatus = function (selectElement, idLead, leadName) {
        const selectedValue = selectElement.value;

        if (selectedValue === 'Gagal Closing') {
            currentSelectElement = selectElement;

            const modal = document.getElementById('gagalModal');
            const form = document.getElementById('gagalForm');
            const nameLabel = document.getElementById('gagalLeadName');

            if (modal && form && nameLabel) {
                nameLabel.innerText = leadName;

                if (window.appRoutes && window.appRoutes.updateLead) {
                    const newAction = window.appRoutes.updateLead.replace(':id', idLead);
                    form.action = newAction;
                }

                modal.style.display = 'flex';
            }
        }
        else if (selectedValue !== 'Hot Prospek') {
            const form = document.getElementById('form-status-' + idLead);
            if (form) form.submit();
        }
    };

    window.closeGagalModal = function () {
        const modal = document.getElementById('gagalModal');
        if (modal) {
            modal.style.display = 'none';
        }

        if (currentSelectElement) {
            currentSelectElement.value = 'Hot Prospek';
            currentSelectElement = null;
        }
    };


    window.onclick = function (event) {
        const trModal = document.getElementById('transaksiModal');
        const flModal = document.getElementById('gagalModal');

        if (event.target === trModal) {
            window.closeTransaksiModal();
        }
        if (event.target === flModal) {
            window.closeGagalModal();
        }
    };

    const nominalInput = document.getElementById('inputNominal');

    if (nominalInput) {
        nominalInput.addEventListener('keyup', function (e) {
            let value = this.value.replace(/[^,\d]/g, '').toString();

            let split = value.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;

            this.value = rupiah;
        });
    }

    const transaksiForm = document.querySelector('form[action*="store_transaksi"]');

    if (transaksiForm) {
        transaksiForm.addEventListener('submit', function (e) {
            if (nominalInput) {
                let cleanValue = nominalInput.value.replace(/\./g, '');
                nominalInput.value = cleanValue;
            }
        });
    }
});

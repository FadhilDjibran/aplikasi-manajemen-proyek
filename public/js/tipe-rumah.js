
window.openModal = function (mode, data = null) {

    const modal = document.getElementById('tipeModal');
    const form = document.getElementById('tipeForm');
    const title = document.getElementById('modalTitle');
    const methodField = document.getElementById('methodField');
    const namaInput = document.getElementById('nama_tipe');

    if (!modal) {
        console.error('Modal tidak ditemukan!');
        return;
    }

    modal.style.display = 'flex';

    if (mode === 'create') {
        title.innerText = 'Tambah Tipe Rumah';

        if (window.appRoutes && window.appRoutes.store) {
            form.action = window.appRoutes.store;
        }

        methodField.innerHTML = '';
        form.reset();

        if (namaInput) namaInput.value = '';

    } else {
        title.innerText = 'Edit Tipe Rumah';

        if (window.appRoutes && window.appRoutes.update) {
            if (data) {
                let updateUrl = window.appRoutes.update.replace(':id', data.id_tipe);
                form.action = updateUrl;
                if (namaInput) namaInput.value = data.nama_tipe;
            }
        }

        methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';
    }
};

window.closeModal = function () {
    const modal = document.getElementById('tipeModal');
    if (modal) modal.style.display = 'none';
};

window.onclick = function (event) {
    const modal = document.getElementById('tipeModal');
    if (event.target == modal) {
        window.closeModal();
    }
};


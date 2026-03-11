function toggleKpiInput(mode) {
    const roleSelect = document.getElementById(mode + 'Role');
    if (!roleSelect) return;

    const kpiWrapper = document.getElementById(mode + 'KpiWrapper');
    const kpiInput = mode === 'edit' ? document.getElementById('editKpi') : document.querySelector('input[name="kpi_target"]');

    if (roleSelect.value === 'Marketing' || roleSelect.value === 'Admin') {
        if (kpiWrapper) kpiWrapper.classList.add('active');
        if (kpiInput) kpiInput.required = true;
    } else {
        if (kpiWrapper) kpiWrapper.classList.remove('active');
        if (kpiInput) kpiInput.required = false;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('createRole')) {
        toggleKpiInput('create');
    }
});

function openEditModal(user, kpiValue) {
    const currentUserRole = window.userData ? window.userData.currentRole : null;
    const currentUserId = window.userData ? window.userData.currentId : null;
    const updateUrlTemplate = window.userData ? window.userData.updateUrl : '';

    if (!currentUserRole) {
        alert("Sistem sedang memuat data, silakan refresh halaman.");
        return;
    }

    if (currentUserRole === 'Admin') {
        if ((user.role === 'Super_Admin' || user.role === 'Admin') && user.id !== currentUserId) {
            alert("Anda tidak dapat mengedit Super Admin atau Admin lain.");
            return;
        }
    }

    document.getElementById('editName').value = user.name;
    document.getElementById('editEmail').value = user.email;

    const roleSelect = document.getElementById('editRole');
    if (roleSelect) {
        roleSelect.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = "";
        placeholder.text = "--Pilih Role--";
        placeholder.disabled = true;
        placeholder.selected = true;
        roleSelect.appendChild(placeholder);

        if (currentUserRole === 'Super_Admin') {
            const roles = ['Super_Admin', 'Admin', 'Marketing'];
            roles.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r;
                opt.text = r.replace('_', ' ');
                roleSelect.appendChild(opt);
            });
        } else if (currentUserRole === 'Admin') {
            if (user.id === currentUserId) {
                const opt = document.createElement('option');
                opt.value = 'Admin';
                opt.text = 'Admin';
                roleSelect.appendChild(opt);
            } else {
                const opt = document.createElement('option');
                opt.value = 'Marketing';
                opt.text = 'Marketing';
                roleSelect.appendChild(opt);
            }
        }

        if (user.role) {
            roleSelect.value = user.role;
        } else {
            roleSelect.value = "";
        }
    }

    const projectSelect = document.getElementById('editProject');
    if (projectSelect) {
        projectSelect.value = user.project_id || "";
    }

    const kpiInput = document.getElementById('editKpi');
    if (kpiInput) {
        kpiInput.value = kpiValue || "";
    }

    toggleKpiInput('edit');

    let url = updateUrlTemplate.replace('999', user.id);
    document.getElementById('editForm').action = url;
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

window.onclick = function (event) {
    let modal = document.getElementById('editModal');
    if (event.target == modal) {
        closeEditModal();
    }
}

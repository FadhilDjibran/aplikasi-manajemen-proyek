function toggleKpiInput(mode) {
    const roleSelect = document.getElementById(mode + 'Role');
    if (!roleSelect) return;

    const kpiWrapper = document.getElementById(mode + 'KpiWrapper');
    const kpiInput = mode === 'edit' ? document.getElementById('editKpi') : document.querySelector('input[name="kpi_target"]');

    if (roleSelect.value === 'Marketing' || roleSelect.value === 'Admin_Marketing') {
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

    if (currentUserRole === 'Admin_Marketing' || currentUserRole === 'Admin_Keuangan') {
        const adminRoles = ['Super_Admin', 'Admin_Marketing', 'Admin_Keuangan'];
        if (adminRoles.includes(user.role) && user.id !== currentUserId) {
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
        roleSelect.appendChild(placeholder);

        if (currentUserRole === 'Super_Admin') {
            const roles = ['Super_Admin', 'Admin_Marketing', 'Admin_Keuangan', 'Marketing', 'Keuangan'];
            roles.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r;
                opt.text = r.replace('_', ' ');
                roleSelect.appendChild(opt);
            });
        }
        else if (currentUserRole === 'Admin_Marketing') {
            if (user.id === currentUserId) {
                const opt = document.createElement('option');
                opt.value = 'Admin_Marketing';
                opt.text = 'Admin Marketing';
                roleSelect.appendChild(opt);
            } else {
                const roles = ['Marketing', 'Keuangan'];
                roles.forEach(r => {
                    const opt = document.createElement('option');
                    opt.value = r;
                    opt.text = r;
                    roleSelect.appendChild(opt);
                });
            }
        }
        else if (currentUserRole === 'Admin_Keuangan') {
            if (user.id === currentUserId) {
                const opt = document.createElement('option');
                opt.value = 'Admin_Keuangan';
                opt.text = 'Admin Keuangan';
                roleSelect.appendChild(opt);
            } else {
                const roles = ['Keuangan'];
                roles.forEach(r => {
                    const opt = document.createElement('option');
                    opt.value = r;
                    opt.text = r;
                    roleSelect.appendChild(opt);
                });
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

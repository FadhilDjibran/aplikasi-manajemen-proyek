
const currentUserRole = window.userData.currentRole;
const currentUserId = window.userData.currentId;
const updateUrlTemplate = window.userData.updateUrl;

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
    document.getElementById('editName').value = user.name;
    document.getElementById('editEmail').value = user.email;

    const roleSelect = document.getElementById('editRole');
    roleSelect.innerHTML = '';

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

    roleSelect.value = user.role;

    const projectSelect = document.getElementById('editProject');
    if (projectSelect) {
        projectSelect.value = user.project_id || "";
    }

    document.getElementById('editKpi').value = kpiValue || "";

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

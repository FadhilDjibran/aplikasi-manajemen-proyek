@php
    $layout = session('active_project_id') ? 'layouts.app' : 'layouts.plain';
@endphp

@extends($layout)

@section('title', 'Manajemen User')

@section('content')

    @if (!session('active_project_id'))
        <nav class="navbar" style="padding: 1.5rem 2rem; margin-bottom: 2rem;">
            <div class="nav-brand" style="color: var(--accent); font-size: 1.5rem; font-weight: 800;">
                <i class="fas fa-users-cog"></i> Manajemen User
            </div>
            <a href="{{ route('projects.index') }}" class="btn-logout"
                style="border: 1px solid var(--accent); padding: 5px 15px; border-radius: 6px; color: white; text-decoration: none; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </nav>
    @endif

    <div class="container" style="max-width: 1000px;">

        @if (session('success'))
            <div class="alert-danger"
                style="background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; padding: 1rem; margin-bottom: 1rem; border-radius: 8px;">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert-danger"
                style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; padding: 1rem; margin-bottom: 1rem; border-radius: 8px;">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-danger"
                style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; padding: 1rem; margin-bottom: 1rem; border-radius: 8px;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (auth()->check() && in_array(auth()->user()->role, ['Super_Admin', 'Admin']))
            <div class="card" style="padding: 2rem; margin-bottom: 2rem; border: none;">
                <h4 style="margin-bottom: 1rem; color: var(--bg-main);">Tambah User Baru</h4>
                <form action="{{ route('store') }}" method="POST">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Role</label>
                            <select name="role" id="createRole" class="form-control" required
                                onchange="toggleKpiInput('create')">
                                @if (Auth::user()->role === 'Super_Admin')
                                    <option value="Super_Admin">Super Admin</option>
                                    <option value="Admin">Admin</option>
                                @endif
                                <option value="Marketing" selected>Marketing</option>
                                <option value="Keuangan">Keuangan</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Assign ke Proyek <small>(Opsional)</small></label>
                            @if (auth()->user()->role === 'Admin')
                                <input type="hidden" name="project_id" value="{{ session('active_project_id') }}">
                                <select class="form-control" disabled
                                    style="background-color: #f8fafc; cursor: not-allowed;">
                                    <option value="{{ session('active_project_id') }}" selected>
                                        {{ session('active_project_name') ?? 'Proyek Aktif' }}
                                    </option>
                                </select>
                                <small style="color: #64748b;">Otomatis dimasukkan ke proyek Anda.</small>
                            @else
                                <select name="project_id" class="form-control">
                                    <option value="">-</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->nama_proyek }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div class="form-group kpi-wrapper active" id="createKpiWrapper">
                            <label class="form-label">KPI <small style="color:#ef4444;">*</small></label>
                            <div style="position: relative; display: flex; align-items: center;">
                                <div style="position: absolute; left: 12px; color: #94a3b8;">
                                    <i class="fas fa-bullseye"></i>
                                </div>
                                <input type="number" name="kpi_target" class="form-control" placeholder="Contoh: 50"
                                    style="padding-left: 35px; padding-right: 60px;">
                                <span
                                    style="position: absolute; right: 12px; color: #64748b; font-size: 0.85rem; font-weight: 600; pointer-events: none;">
                                    Leads
                                </span>
                            </div>
                            <small style="color: #64748b;">Target jumlah konversi per PIC</small>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">+ Simpan</button>
                </form>
            </div>
        @endif
        <div class="table-container" style="border: none; box-shadow: 0 10px 15px rgba(0,0,0,0.2);">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="Width: 120px;">Nama</th>
                        <th>Email</th>
                        <th style="min-width: 200px; text-align: center;">Proyek</th>
                        <th>Hak Akses</th>
                        <th>KPI</th>
                        <th style="text-align: center;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="{{ empty($user->role) ? 'user-tanpa-role' : '' }}"
                            style="{{ empty($user->role) ? 'display: none;' : '' }}">
                            <td style="font-weight: 700; font-size: 0.9rem">{{ $user->name }}</td>
                            <td style="font-size: 0.8rem">{{ $user->email }}</td>
                            <td style="text-align: center;">
                                @if (!empty($user->project))
                                    @if ($user->project->nama_proyek === 'Safira Regency')
                                        <span class="badge"
                                            style="background: var(--bg-main); color: var(--accent); border: 1px solid var(--accent);">
                                            {{ $user->project->nama_proyek }}
                                        </span>
                                    @else
                                        <span class="badge"
                                            style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                                            {{ $user->project->nama_proyek }}
                                        </span>
                                    @endif
                                @else
                                    <span style="color: #94a3b8;">-</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if (!empty($user->role))
                                    <span class="nav-badge"
                                        style="background: rgba(7, 64, 58, 0.1); color: var(--bg-main); border: 1px solid var(--bg-main);">
                                        {{ str_replace('_', ' ', $user->role) }}
                                    </span>
                                @else
                                    <span style="color: #94a3b8;">-</span>
                                @endif
                            </td>
                            <td>
                                @if (in_array($user->role, ['Marketing', 'Admin']) && $user->picMarketing)
                                    <span style="color: #166534; font-weight: 600;">
                                        {{ number_format($user->picMarketing->kpi_target, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span style="color: #cbd5e1;">-</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 10px; justify-content: center;">
                                    @php
                                        $isSelf = Auth::id() === $user->id;
                                        $isSuperAdmin = Auth::user()->role === 'Super_Admin';
                                        $isAdmin = Auth::user()->role === 'Admin';

                                        $isTargetProtected = in_array($user->role, ['Super_Admin', 'Admin']);

                                        $canEdit = $isSuperAdmin || $isSelf || ($isAdmin && !$isTargetProtected);

                                        $canDelete =
                                            ($isSuperAdmin && !$isSelf) ||
                                            ($isAdmin && !$isTargetProtected && !$isSelf);
                                    @endphp

                                    @if ($canEdit)
                                        <button type="button"
                                            onclick='openEditModal(@json($user), "{{ $user->picMarketing->kpi_target ?? '' }}")'
                                            style="background: #fef9c3; border: 1px solid #fde047; color: #854d0e; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    @else
                                        <span style="color: #94a3b8; font-size: 0.8rem; padding: 5px;"><i
                                                class="fas fa-lock"></i></span>
                                    @endif

                                    @if ($canDelete)
                                        <form action="{{ route('destroy', $user->id) }}" method="POST"
                                            onsubmit="return confirm('Hapus user ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                style="background: #fef2f2; border: 1px solid #fecaca; color: #ef4444; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                                <i class="fas fa-trash-alt"></i> Hapus
                                            </button>
                                        </form>
                                    @elseif($isSelf)
                                        <span style="font-size: 0.8rem; color: #94a3b8; padding: 5px;">(Akun
                                            Anda)</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if (in_array(auth()->user()->role, ['Super_Admin', 'Admin']))
            <div style="margin-top: 1rem; margin-bottom: 2rem; display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" onclick="toggleUserTanpaRole()" class="btn btn-primary"
                    style="width: 260px; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; white-space: nowrap;">
                    <i class="fas fa-eye" id="icon-toggle-role"></i>
                    <span id="text-toggle-role">Tampilkan User Tanpa Role</span>
                </button>
                <form action="{{ route('trigger_cleanup') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-primary"
                        style="width: 180px; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; white-space: nowrap;"
                        onclick="return confirm('PERINGATAN: Proses ini akan menghapus permanen semua akun yang tidak memiliki Role selama lebih dari 7 hari. Lanjutkan?')">
                        <i class="fas fa-user-slash"></i> Bersihkan User
                    </button>
                </form>
            </div>
        @endif

    </div>
    <div id="editModal" class="modal compact-modal">
        <div class="modal-content compact-content">

            <div class="modal-header">
                <h3><i class="fas fa-user-edit"></i> Edit User</h3>
                <button type="button" onclick="closeEditModal()" class="btn-close-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <form id="editForm" method="POST">
                    @csrf @method('PUT')

                    <div class="compact-grid">
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="editEmail" class="form-control" required>
                        </div>

                        @if (in_array(auth()->user()->role, ['Admin', 'Super_Admin']))
                            <div class="form-group">
                                <label class="form-label">Role</label>
                                <select name="role" id="editRole" class="form-control" required
                                    onchange="toggleKpiInput('edit')">
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Assign ke Proyek</label>
                                @if (auth()->user()->role === 'Admin')
                                    <input type="hidden" name="project_id" value="{{ session('active_project_id') }}">
                                    <select class="form-control disabled-select" disabled>
                                        <option value="{{ session('active_project_id') }}" selected>
                                            {{ session('active_project_name') }}
                                        </option>
                                    </select>
                                @else
                                    <select name="project_id" id="editProject" class="form-control">
                                        <option value="">- Tanpa Proyek -</option>
                                        @foreach ($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->nama_proyek }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        @endif

                        <div class="form-group kpi-wrapper" id="editKpiWrapper">
                            <label class="form-label">Target KPI <small style="color:#ef4444;">*</small></label>
                            <input type="number" name="kpi_target" id="editKpi" class="form-control"
                                placeholder="0">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="password" class="form-control" placeholder="Kosongi jika sama">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" onclick="closeEditModal()" class="btn-cancel">Batal</button>
                        <button type="submit" class="btn-save"><i class="fas fa-check"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        window.userData = {
            currentRole: "{{ Auth::user()->role }}",
            currentId: {{ Auth::id() }},
            updateUrl: "{{ url('/users/999') }}"
        };
    </script>
    <script>
        function toggleUserTanpaRole() {
            const rows = document.querySelectorAll('.user-tanpa-role');
            const textSpan = document.getElementById('text-toggle-role');
            const icon = document.getElementById('icon-toggle-role');

            if (rows.length === 0) {
                alert('Saat ini tidak ada user tanpa role.');
                return;
            }

            let isHidden = rows[0].style.display === 'none';

            rows.forEach(row => {
                row.style.display = isHidden ? 'table-row' : 'none';
            });

            textSpan.innerText = isHidden ? 'Sembunyikan User Tanpa Role' : 'Tampilkan User Tanpa Role';
            icon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
        }
    </script>
    <script src="{{ asset('js/users-page.js?v=' . time()) }}"></script>
@endpush

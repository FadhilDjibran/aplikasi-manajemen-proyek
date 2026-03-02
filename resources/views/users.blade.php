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
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Assign ke Proyek <small>(Opsional)</small></label>
                        @if (auth()->user()->role === 'Admin')
                            <input type="hidden" name="project_id" value="{{ session('active_project_id') }}">
                            <select class="form-control" disabled style="background-color: #f8fafc; cursor: not-allowed;">
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
                        <small style="color: #64748b;">Target jumlah Leads per PIC</small>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">+ Simpan</button>
            </form>
        </div>

        <div class="table-container" style="border: none; box-shadow: 0 10px 15px rgba(0,0,0,0.2);">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="Width: 120px;">Nama</th>
                        <th>Email</th>
                        <th style="Width: 180px;">Proyek</th>
                        <th>Hak Akses</th>
                        <th>KPI</th>
                        <th style="text-align: center;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td style="font-weight: 700;">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if ($user->project)
                                    <span class="badge"
                                        style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                                        {{ $user->project->nama_proyek }}
                                    </span>
                                @else
                                    <span style="color: #94a3b8;">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="nav-badge"
                                    style="background: rgba(7, 64, 58, 0.1); color: var(--bg-main); border: 1px solid var(--bg-main);">
                                    {{ $user->role }}
                                </span>
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
                                        $isTargetMarketing = $user->role === 'Marketing';
                                        $canEdit = $isSuperAdmin || $isSelf || ($isAdmin && $isTargetMarketing);
                                        $canDelete = ($isSuperAdmin && !$isSelf) || ($isAdmin && $isTargetMarketing);
                                    @endphp

                                    @if ($canEdit)
                                        <button type="button"
                                            onclick="openEditModal({{ $user }}, '{{ $user->picMarketing->kpi_target ?? '' }}')"
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
                                        <span style="font-size: 0.8rem; color: #94a3b8; padding: 5px;">(Akun Anda)</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3 style="margin-bottom: 1.5rem; color: var(--bg-main);">Edit User</h3>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" id="editName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="editEmail" class="form-control" required>
                </div>
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
                        <select class="form-control" disabled style="background-color: #f1f5f9; cursor: not-allowed;">
                            <option value="{{ session('active_project_id') }}" selected>
                                {{ session('active_project_name') }} (Proyek Aktif Anda)
                            </option>
                        </select>
                    @else
                        <select name="project_id" id="editProject" class="form-control">
                            <option value="">-</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->nama_proyek }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="form-group kpi-wrapper" id="editKpiWrapper">
                    <label class="form-label">Target KPI Bulanan <small style="color:#ef4444;">*</small></label>
                    <div style="position: relative;">
                        <input type="number" name="kpi_target" id="editKpi" class="form-control" placeholder="0">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password Baru <small style="color: #64748b; font-weight: normal;">(Biarkan
                            kosong jika tidak diganti)</small></label>
                    <input type="password" name="password" class="form-control" placeholder="******">
                </div>
                <div style="display: flex; gap: 10px; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <button type="button" onclick="closeEditModal()" class="btn"
                        style="background: #e2e8f0; color: #334155;">Batal</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        window.userData = {
            currentRole: "{{ Auth::user()->role }}",
            currentId: {{ Auth::id() }},
            updateUrl: "{{ route('update', '999') }}"
        };
    </script>

    <script src="{{ asset('js/users-page.js') }}"></script>
@endpush

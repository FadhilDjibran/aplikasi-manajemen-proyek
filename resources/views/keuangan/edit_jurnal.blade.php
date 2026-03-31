@extends('layouts.app')
@section('title', 'Edit Jurnal Umum')

@section('content')
    <div class="mb-4" style="display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ url()->previous() }}" class="btn"
            style="background: #f1f5f9; color: #475569; padding: 0.6rem 1.2rem; text-decoration: none; font-weight: 600; border-radius: 8px;">
            &larr; Kembali ke Daftar
        </a>
    </div>

    <div class="card"
        style="width: 100%; padding: 2.5rem; max-width: 965px; margin: 0 auto; border: none; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); overflow: visible;">

        @if (session('error'))
            <div
                style="background-color: #fef2f2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #fecaca;">
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            </div>
        @endif

        <form action="{{ route('jurnal.update', $ref->id) }}" method="POST" enctype="multipart/form-data" id="form-jurnal">
            @csrf
            @method('PUT')
            <input type="hidden" name="input" value="Jurnal">
            <input type="hidden" name="keterangan_lama" value="{{ $ref->keterangan }}">

            <h4 class="mb-4"
                style="border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; color: #1e293b; font-weight: 700; font-size: 1.1rem;">
                Edit Informasi Jurnal Umum
            </h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Tanggal Transaksi <span style="color:red">*</span>
                    </label>
                    <input type="date" name="tanggal" class="form-control"
                        value="{{ old('tanggal', date('Y-m-d', strtotime($ref->tanggal))) }}"
                        style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem;" required>
                </div>

                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Upload Bukti Baru (Opsional)
                    </label>
                    <input type="file" name="bukti" class="form-control" accept="image/*,.pdf"
                        style="border-radius: 6px; border: 1px dashed #cbd5e1; padding: 0.5rem 0.75rem; background: #f8fafc;">
                    @if ($ref->bukti)
                        <small style="color: #059669; margin-top: 5px; display: block;">File bukti sebelumnya sudah
                            tersimpan. Upload jika ingin mengganti.</small>
                    @endif
                </div>
            </div>

            <h4 class="mb-3"
                style="border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; color: #1e293b; font-weight: 700; font-size: 1.1rem;">
                Detail Entri Jurnal
            </h4>

            <div style="width: 100%; margin-bottom: 1rem;">
                <table style="width: 100%; border-collapse: collapse; table-layout: fixed;" id="jurnal-table">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1;">
                            <th style="padding: 10px; text-align: left; color: #475569; font-weight: 700; width: 22%;">Akun
                                (CoA) <span style="color:red">*</span></th>
                            <th style="padding: 10px; text-align: left; color: #475569; font-weight: 700; width: 18%;">Jenis
                                Penggunaan</th>
                            <th style="padding: 10px; text-align: left; color: #475569; font-weight: 700; width: 22%;">
                                Keterangan <span style="color:red">*</span></th>
                            <th style="padding: 10px; text-align: right; color: #475569; font-weight: 700; width: 16%;">
                                Debit (Rp)</th>
                            <th style="padding: 10px; text-align: right; color: #475569; font-weight: 700; width: 16%;">
                                Kredit (Rp)</th>
                            <th style="padding: 10px; text-align: center; color: #475569; font-weight: 700; width: 6%;">#
                            </th>
                        </tr>
                    </thead>
                    <tbody id="jurnal-body">
                        @foreach ($jurnalRows as $row)
                            <tr class="jurnal-row" style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 8px;">
                                    <select name="no_akun_array[]" class="form-control coa-select" required>
                                        <option value="">-- Pilih Akun --</option>
                                        @php $groupedCoa = isset($coa) ? $coa->unique('no_akun')->groupBy('kategori_akun') : collect(); @endphp
                                        @foreach ($groupedCoa as $kategori => $akunList)
                                            <optgroup label="{{ $kategori }}">
                                                @foreach ($akunList as $item)
                                                    <option value="{{ $item->no_akun }}"
                                                        {{ $row->no_akun == $item->no_akun ? 'selected' : '' }}>
                                                        {{ $item->no_akun }} - {{ $item->nama_akun }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="padding: 8px;">
                                    <input type="text" name="jenis_penggunaan_array[]" class="form-control"
                                        value="{{ $row->jenis_penggunaan }}" placeholder="Contoh: Operasional"
                                        style="border-radius: 4px; border: 1px solid #cbd5e1; padding: 0.4rem; width: 100%; font-size: 0.85rem;">
                                </td>
                                <td style="padding: 8px;">
                                    <input type="text" name="keterangan_array[]" class="form-control"
                                        value="{{ $row->keterangan }}" placeholder="Keterangan..."
                                        style="border-radius: 4px; border: 1px solid #cbd5e1; padding: 0.4rem; width: 100%; font-size: 0.85rem;"
                                        required>
                                </td>
                                <td style="padding: 8px;">
                                    <input type="text" name="mutasi_debit_array[]" class="form-control input-debit"
                                        value="{{ number_format($row->mutasi_masuk, 0, '', '.') }}"
                                        style="border-radius: 4px; border: 1px solid #cbd5e1; padding: 0.4rem; width: 100%; text-align: right; color: #059669; font-weight: 600; font-size: 0.9rem;"
                                        required>
                                </td>
                                <td style="padding: 8px;">
                                    <input type="text" name="mutasi_kredit_array[]" class="form-control input-kredit"
                                        value="{{ number_format($row->mutasi_keluar, 0, '', '.') }}"
                                        style="border-radius: 4px; border: 1px solid #cbd5e1; padding: 0.4rem; width: 100%; text-align: right; color: #dc2626; font-weight: 600; font-size: 0.9rem;"
                                        required>
                                </td>
                                <td style="padding: 8px; text-align: center;">
                                    <button type="button" class="btn-remove-row"
                                        style="background: #fee2e2; color: #dc2626; border: none; border-radius: 4px; padding: 0.3rem 0.6rem; cursor: pointer; font-weight: bold;">×</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-bottom: 2rem; display: flex; justify-content: flex-end;">
                <button type="button" id="btn-add-row" class="btn"
                    style="background: #e0e7ff; max-width: 200px; color: #0369a1; font-size: 0.85rem; font-weight: 700; padding: 0.5rem 1rem; border-radius: 6px; border: 1px dashed #a5b4fc; width: 100%;">
                    + Tambah Baris Transaksi
                </button>
            </div>

            <div
                style="position: relative; z-index: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
                <div style="display: flex; justify-content: flex-end; gap: 3rem; align-items: center;">
                    <div style="text-align: right;">
                        <span style="display: block; font-size: 0.85rem; font-weight: 700; color: #64748b;">TOTAL
                            DEBIT</span>
                        <span id="calc-debit" style="font-size: 1.2rem; font-weight: 800; color: #059669;">Rp 0</span>
                    </div>
                    <div style="text-align: right;">
                        <span style="display: block; font-size: 0.85rem; font-weight: 700; color: #64748b;">TOTAL
                            KREDIT</span>
                        <span id="calc-kredit" style="font-size: 1.2rem; font-weight: 800; color: #dc2626;">Rp 0</span>
                    </div>
                    <div style="text-align: right; border-left: 2px solid #cbd5e1; padding-left: 3rem;">
                        <span style="display: block; font-size: 0.85rem; font-weight: 700; color: #64748b;">SELISIH
                            (BALANCE)</span>
                        <span id="calc-selisih" style="font-size: 1.4rem; font-weight: 800; color: #10b981;">Rp 0</span>
                        <div id="badge-status"
                            style="margin-top: 5px; font-size: 0.75rem; font-weight: 700; background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 12px; display: inline-block;">
                            SEIMBANG
                        </div>
                    </div>
                </div>
            </div>

            <div
                style="display: flex; justify-content: flex-end; gap: 1rem; border-top: 1px solid #f3f4f6; padding-top: 1.5rem;">
                <button type="submit" id="btn-submit" class="btn btn-primary"
                    style="width: auto; max-width: fit-content; display: inline-flex; align-items: center; justify-content: center; padding: 0.8rem 3rem; font-weight: 600; border-radius: 8px;">
                    <i class="fas fa-save" style="margin-right: 5px;"></i> Perbarui Jurnal Umum
                </button>
            </div>

        </form>
    </div>

    <template id="row-template">
        <tr class="jurnal-row" style="border-bottom: 1px solid #e2e8f0;">
            <td style="padding: 8px;">
                <select name="no_akun_array[]" class="form-control coa-select-new" required>
                    <option value="">-- Pilih Akun --</option>
                    @foreach ($groupedCoa as $kategori => $akunList)
                        <optgroup label="{{ $kategori }}">
                            @foreach ($akunList as $item)
                                <option value="{{ $item->no_akun }}">{{ $item->no_akun }} - {{ $item->nama_akun }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </td>
            <td style="padding: 8px;">
                <input type="text" name="jenis_penggunaan_array[]" class="form-control"
                    placeholder="Contoh: Operasional"
                    style="border-radius: 4px; border: 1px solid #cbd5e1; padding: 0.4rem; width: 100%; font-size: 0.85rem;">
            </td>
            <td style="padding: 8px;">
                <input type="text" name="keterangan_array[]" class="form-control" placeholder="Keterangan..."
                    style="border-radius: 4px; border: 1px solid #cbd5e1; padding: 0.4rem; width: 100%; font-size: 0.85rem;"
                    required>
            </td>
            <td style="padding: 8px;">
                <input type="text" name="mutasi_debit_array[]" class="form-control input-debit" value="0"
                    style="border-radius: 4px; border: 1px solid #cbd5e1; padding: 0.4rem; width: 100%; text-align: right; color: #059669; font-weight: 600; font-size: 0.9rem;"
                    required>
            </td>
            <td style="padding: 8px;">
                <input type="text" name="mutasi_kredit_array[]" class="form-control input-kredit" value="0"
                    style="border-radius: 4px; border: 1px solid #cbd5e1; padding: 0.4rem; width: 100%; text-align: right; color: #dc2626; font-weight: 600; font-size: 0.9rem;"
                    required>
            </td>
            <td style="padding: 8px; text-align: center;">
                <button type="button" class="btn-remove-row"
                    style="background: #fee2e2; color: #dc2626; border: none; border-radius: 4px; padding: 0.3rem 0.6rem; cursor: pointer; font-weight: bold;">×</button>
            </td>
        </tr>
    </template>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function initTomSelect(element) {
                if (element.tomselect) return;
                new TomSelect(element, {
                    create: false,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    },
                    maxOptions: 1000,
                    dropdownParent: 'body'
                });
            }

            document.querySelectorAll('.coa-select').forEach(el => initTomSelect(el));

            function formatMoney(num) {
                let str = num.toString().replace(/[^,\d]/g, '');
                let split = str.split(',');
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }
                return rupiah || '0';
            }

            function cleanMoney(str) {
                if (!str) return 0;
                return parseFloat(str.replace(/\./g, '')) || 0;
            }

            function calculateBalance() {
                let totalDebit = 0;
                let totalKredit = 0;
                document.querySelectorAll('.input-debit').forEach(input => {
                    totalDebit += cleanMoney(input.value);
                });
                document.querySelectorAll('.input-kredit').forEach(input => {
                    totalKredit += cleanMoney(input.value);
                });
                let selisih = Math.abs(totalDebit - totalKredit);

                document.getElementById('calc-debit').textContent = 'Rp ' + formatMoney(totalDebit);
                document.getElementById('calc-kredit').textContent = 'Rp ' + formatMoney(totalKredit);
                document.getElementById('calc-selisih').textContent = 'Rp ' + formatMoney(selisih);

                const badge = document.getElementById('badge-status');
                const btnSubmit = document.getElementById('btn-submit');
                const selisihEl = document.getElementById('calc-selisih');

                if (totalDebit === 0 && totalKredit === 0) {
                    badge.textContent = "KOSONG";
                    badge.style.background = "#e2e8f0";
                    badge.style.color = "#475569";
                    selisihEl.style.color = "#64748b";
                    btnSubmit.disabled = true;
                    btnSubmit.style.opacity = "0.5";
                } else if (selisih === 0) {
                    badge.textContent = "SEIMBANG";
                    badge.style.background = "#d1fae5";
                    badge.style.color = "#065f46";
                    selisihEl.style.color = "#10b981";
                    btnSubmit.disabled = false;
                    btnSubmit.style.opacity = "1";
                    btnSubmit.innerHTML =
                        '<i class="fas fa-save" style="margin-right: 5px;"></i> Perbarui Jurnal Umum';
                } else {
                    badge.textContent = "TIDAK SEIMBANG";
                    badge.style.background = "#fee2e2";
                    badge.style.color = "#991b1b";
                    selisihEl.style.color = "#dc2626";
                    btnSubmit.disabled = true;
                    btnSubmit.style.opacity = "0.5";
                    btnSubmit.innerHTML =
                        '<i class="fas fa-lock" style="margin-right: 5px;"></i> Balance Harus Rp 0';
                }
            }

            document.getElementById('jurnal-body').addEventListener('input', function(e) {
                if (e.target.classList.contains('input-debit') || e.target.classList.contains(
                        'input-kredit')) {
                    e.target.value = formatMoney(e.target.value);
                    calculateBalance();
                }
            });

            document.getElementById('btn-add-row').addEventListener('click', function() {
                const template = document.getElementById('row-template');
                const clone = template.content.cloneNode(true);
                const body = document.getElementById('jurnal-body');

                const rows = body.querySelectorAll('.jurnal-row');
                if (rows.length > 0) {
                    const lastRow = rows[rows.length - 1];
                    const prevPenggunaan = lastRow.querySelector('input[name="jenis_penggunaan_array[]"]')
                        .value;
                    const prevKeterangan = lastRow.querySelector('input[name="keterangan_array[]"]').value;

                    clone.querySelector('input[name="jenis_penggunaan_array[]"]').value = prevPenggunaan;
                    clone.querySelector('input[name="keterangan_array[]"]').value = prevKeterangan;
                }

                body.appendChild(clone);
                const newSelect = body.lastElementChild.querySelector('.coa-select-new');
                if (newSelect) {
                    newSelect.classList.remove('coa-select-new');
                    newSelect.classList.add('coa-select');
                    initTomSelect(newSelect);
                }
            });

            document.getElementById('jurnal-body').addEventListener('click', function(e) {
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

            calculateBalance();
        });
    </script>
@endsection

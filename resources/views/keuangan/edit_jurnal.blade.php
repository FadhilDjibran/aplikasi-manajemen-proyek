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
        style="width: 100%; padding: 2.5rem; max-width: 1100px; margin: 0 auto; border: none; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); overflow: visible;">

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
            <input type="hidden" name="jurnal_ref" value="{{ $ref->jurnal_ref }}">

            <h4 class="mb-4"
                style="border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; color: #1e293b; font-weight: 700; font-size: 1.1rem; display: flex; justify-content: space-between; align-items: center;">
                <span>Edit Informasi Jurnal Umum</span>
                <span id="label-ref"
                    style="font-size: 0.85rem; background: #e2e8f0; color: #475569; padding: 4px 10px; border-radius: 6px; font-weight: 600;">
                    Ref: #{{ $ref->jurnal_ref }}
                </span>
            </h4>

            <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 2rem;">
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
                            <th style="padding: 10px; text-align: left; color: #475569; font-weight: 700; width: 14%;">
                                Tanggal <span style="color:red">*</span></th>
                            <th style="padding: 10px; text-align: left; color: #475569; font-weight: 700; width: 18%;">Akun
                                (CoA) <span style="color:red">*</span></th>
                            <th style="padding: 10px; text-align: left; color: #475569; font-weight: 700; width: 14%;">Jenis
                                Penggunaan</th>
                            <th style="padding: 10px; text-align: left; color: #475569; font-weight: 700; width: 18%;">
                                Keterangan <span style="color:red">*</span></th>
                            <th style="padding: 10px; text-align: right; color: #475569; font-weight: 700; width: 15%;">
                                Debit (Rp)</th>
                            <th style="padding: 10px; text-align: right; color: #475569; font-weight: 700; width: 15%;">
                                Kredit (Rp)</th>
                            <th style="padding: 10px; text-align: center; color: #475569; font-weight: 700; width: 6%;">#
                            </th>
                        </tr>
                    </thead>
                    <tbody id="jurnal-body">
                        @foreach ($jurnalRows as $row)
                            <tr class="jurnal-row" style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 8px;">
                                    <input type="date" name="tanggal_array[]" class="form-control"
                                        value="{{ old('tanggal_array.' . $loop->index, \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d')) }}"
                                        style="border-radius: 4px; border: 1px solid #cbd5e1; padding: 0.4rem; width: 100%; font-size: 0.85rem;"
                                        required>
                                </td>
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
                                        value="{{ $row->jenis_penggunaan }}" placeholder="Opsional..."
                                        style="border-radius: 4px; border: 1px solid #cbd5e1; padding: 0.4rem; width: 100%; font-size: 0.85rem;">
                                </td>
                                <td style="padding: 8px;">
                                    <input type="text" name="keterangan_array[]" class="form-control"
                                        value="{{ $row->keterangan }}" placeholder="Keterangan..."
                                        style="border-radius: 4px; border: 1px solid #cbd5e1; padding: 0.4rem; width: 100%; font-size: 0.85rem;"
                                        required>
                                </td>
                                <td style="padding: 8px;">
                                    <input type="text" name="mutasi_debit_array[]"
                                        class="form-control input-debit money-format"
                                        value="{{ $row->mutasi_masuk != 0 ? number_format($row->mutasi_masuk, 2, ',', '.') : '' }}"
                                        style="border-radius: 4px; border: 1px solid #cbd5e1; padding: 0.4rem; width: 100%; text-align: right; color: #059669; font-weight: 600; font-size: 0.9rem;"
                                        required>
                                </td>
                                <td style="padding: 8px;">
                                    <input type="text" name="mutasi_kredit_array[]"
                                        class="form-control input-kredit money-format"
                                        value="{{ $row->mutasi_keluar != 0 ? number_format($row->mutasi_keluar, 2, ',', '.') : '' }}"
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
                <input type="date" name="tanggal_array[]" class="form-control"
                    style="border-radius: 4px; border: 1px solid #cbd5e1; padding: 0.4rem; width: 100%; font-size: 0.85rem;"
                    required>
            </td>
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
                <input type="text" name="jenis_penggunaan_array[]" class="form-control" placeholder="Opsional..."
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
    <script src="{{ asset('js/money-format.js') }}"></script>
    <script src="{{ asset('js/jurnal-handler.js') }}"></script>
@endsection

@extends('layouts.app')

<link rel="stylesheet" href="{{ url('assets/css/catatan_perilaku.css') }}">

<!-- Tambahan CSS untuk animasi dropdown, perbaikan tampilan "Pembinaan:" dan animasi isi tabel -->
<style>
  /* Animasi transisi untuk container collapse (dropdown) */
  .collapse {
    overflow: hidden;
    transition: max-height 0.5s ease;
  }
  .collapse.show {
    max-height: 1000px; /* Sesuaikan nilai sesuai kebutuhan */
  }
  .collapse:not(.show) {
    max-height: 0;
  }

  /* Animasi untuk isi konten tabel (fade in dan slide down) */
  .collapse .p-3 {
    opacity: 0;
    transform: translateY(-10px);
    transition: opacity 0.5s ease, transform 0.5s ease;
  }
  .collapse.show .p-3 {
    opacity: 1;
    transform: translateY(0);
  }

  /* Perbaikan tampilan header "Pembinaan:" */
  .pembinaan-header {
    transition: all 0.3s ease;
    min-height: 50px;
    display: flex;
    align-items: center;
    font-weight: bold;
    margin-bottom: 15px;
  }

  /* Style untuk dropdown icon dengan animasi rotasi */
  .dropdown-icon i {
    transition: transform 0.3s ease;
    display: inline-block;
  }
  .dropdown-icon i.open {
    transform: rotate(-90deg);
  }
</style>

@section('content')
<!-- Header -->
<div class="d-flex align-items-center mb-4 border-bottom">
  <h3 class="me-auto">
    <a>
      <i class="fas fa-user-edit"></i> Catatan Perilaku /
    </a>
    <a>
      Detail / {{ $studentNim }}
    </a>
  </h3>
</div>

<div class="container mt-4">
  <div class="position-relative mb-4">
    <div class="text-center">
      <div class="d-inline-block">
        <p class="mb-0">Detail Pelanggaran Mahasiswa ({{ $studentNim }})</p>
        <hr style="margin: 0 auto; width: 100%;">
      </div>
    </div>
    <a href="javascript:window.history.back()"
       class="position-absolute"
       style="left: 0; top: 50%; transform: translateY(-50%);">
      <i class="fas fa-arrow-left fs-4"></i>
    </a>
  </div>
  
  <!-- Main Table for Nilai Perilaku -->
  <table class="table">
    <thead>
      <tr>
        <th style="width: 5%;">No</th>
        <th>TA</th>
        <th>Semester</th>
        <th>Skor Awal</th>
        <th>Akumulasi Skor</th>
        <th>Nilai Huruf</th>
      </tr>
    </thead>
    <tbody>
      @php $index = 1; @endphp
      @forelse ($nilaiPerilaku as $key => $perilaku)
      <tr>
        <td style="width: 5%;">{{ $index++ }}</td>
        <td>{{ $perilaku['ta'] ?? '-' }}</td>
        <td>{{ $perilaku['semester'] ?? '-' }}</td>
        <td>{{ $perilaku['akumulasi_skor_awal'] ?? 0 }}</td>
        <td>{{ $perilaku['akumulasi_skor'] ?? 0 }}</td>
        <td class="nilai-huruf-cell">
          {{ $perilaku['nilai_huruf'] ?? '-' }}
          <a href="#" class="dropdown-icon" data-bs-toggle="collapse" data-bs-target="#details{{ $key }}">
            <i class="fas fa-chevron-left"></i>
          </a>
        </td>
      </tr>
      
      <!-- Collapsible Detail Row -->
      <tr class="collapse" id="details{{ $key }}">
        <td colspan="7">
          <div class="p-3">
            <!-- Header Pembinaan dengan styling -->
            <div class="pembinaan-header">
              <span>Pembinaan:</span>
            </div>
            <!-- Tabs untuk Pelanggaran dan Perbuatan Baik -->
            <div class="custom-box-container mb-3">
              <div id="pelanggaranBox{{ $key }}" class="custom-box active"
                onclick="showTable('pelanggaranTable{{ $key }}', 'pelanggaranBox{{ $key }}', 'perbuatanBaikBox{{ $key }}')">
                Pelanggaran ({{ count($perilaku['pelanggaran'] ?? []) }})
              </div>
              <div id="perbuatanBaikBox{{ $key }}" class="custom-box"
                onclick="showTable('perbuatanBaikTable{{ $key }}', 'perbuatanBaikBox{{ $key }}', 'pelanggaranBox{{ $key }}')">
                Perbuatan Baik ({{ count($perilaku['perbuatan_baik'] ?? []) }})
              </div>
            </div>
            
            <!-- Pelanggaran Section -->
            @php
              $hasPelanggaran = count($perilaku['pelanggaran'] ?? []) > 0;
              $pelanggaranColspan = $hasPelanggaran ? 7 : 6;
            @endphp
            <div id="pelanggaranTable{{ $key }}">
              <table class="table table-bordered table-striped" style="margin-top: 0; border-top: none;">
                <thead>
                  <tr>
                    <th style="width: 0.7%;">#</th>
                    <th>Pelanggaran</th>
                    <th style="width: 115px;">Unit</th>
                    <th>Tanggal</th>
                    <th style="width: 70px;">Poin</th>
                    <th>Tindakan</th>
                    @if($hasPelanggaran)
                      <th>Aksi</th>
                    @endif
                  </tr>
                </thead>
                <tbody>
                  @php $pelanggaranIndex = 1; @endphp
@forelse ($perilaku['pelanggaran'] ?? [] as $pelanggaran)
    <!-- Display Row -->
    <tr id="displayRowPelanggaran{{ $pelanggaran['id'] }}">
        <td style="width: 5%;">{{ $pelanggaranIndex++ }}</td>
        <td>{{ $pelanggaran['pelanggaran'] ?? '-' }}</td>
        <td style="width: 115px;">{{ $pelanggaran['unit'] ?? '-' }}</td>
        <td>{{ $pelanggaran['tanggal'] ?? '-' }}</td>
        <td style="width: 70px;">{{ $pelanggaran['poin'] ?? 0 }}</td>
        <td>{{ $pelanggaran['tindakan'] ?? '-' }}</td>
        <td>
            @if (isset($pelanggaran['id']))
                <!-- Tombol Edit -->
                <button type="button" class="btn btn-outline-primary btn-sm" title="Edit" onclick="toggleEditForm('Pelanggaran', {{ $pelanggaran['id'] }})">
                    <i class="fas fa-pencil-alt"></i>
                </button>
                <!-- Tombol Delete -->
                <button type="button" class="btn btn-outline-danger btn-sm delete-btn" 
                    data-id="{{ $pelanggaran['id'] }}" 
                    data-url="{{ route('student_behaviors.destroy', $pelanggaran['id']) }}" 
                    data-type="pelanggaran" 
                    title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            @endif
        </td>
    </tr>

    <!-- Inline Edit Form Row (hidden secara default) -->
    <tr id="editFormPelanggaran{{ $pelanggaran['id'] }}" style="display: none;">
        <form action="{{ route('student_behaviors.update', $pelanggaran['id']) }}" method="POST">
            @csrf
            <td style="width: 5%;">{{ $pelanggaranIndex - 1 }}</td>
            <td>
                <textarea name="pelanggaran" class="form-control" required>{{ old('pelanggaran', $pelanggaran['pelanggaran']) }}</textarea>
            </td>
            <td style="width: 115px;">
                <input type="text" name="unit" class="form-control" value="{{ old('unit', $pelanggaran['unit']) }}">
            </td>
            <td>
              <input type="date" name="tanggal" class="form-control tanggal" value="{{ old('tanggal', $pelanggaran['tanggal']) }}">
            </td>
            <td style="width: 70px;">
                <input type="number" name="poin" class="form-control" value="{{ old('poin', $pelanggaran['poin']) }}" min="1" max="100">
            </td>
            <td>
                <textarea name="tindakan" class="form-control">{{ old('tindakan', $pelanggaran['tindakan']) }}</textarea>
            </td>
            <td>
                <button type="submit" class="btn btn-success btn-sm" title="Simpan"><i class="fas fa-save"></i></button>
                <button type="button" class="btn btn-secondary btn-sm" title="Batal" onclick="toggleEditForm('Pelanggaran', {{ $pelanggaran['id'] }})"><i class="fas fa-times"></i></button>
            </td>
        </form>
    </tr>
@empty
    <tr>
        <td colspan="{{ $pelanggaranColspan }}" class="no-results">No results found.</td>
    </tr>
@endforelse
                  
                  <!-- Plus Sign Row untuk Pelanggaran -->
                  <tr id="plusRow{{ $key }}">
                    <td colspan="{{ $pelanggaranColspan }}" class="text-end">
                      <button type="button" class="btn btn-primary btn-sm"
                        onclick="toggleForm('pelanggaranForm{{ $key }}', 'plusRow{{ $key }}')">
                        <i class="fas fa-plus"></i>
                      </button>
                    </td>
                  </tr>
                  
                  <!-- Inline Form untuk Menambahkan Pelanggaran -->
                  <form method="POST" action="{{ route('student_behaviors.store') }}" class="validate-form">
                    @csrf
                    <input type="hidden" name="student_nim" value="{{ $studentNim }}">
                    <input type="hidden" name="ta" value="{{ $perilaku['ta'] }}">
                    <input type="hidden" name="semester" value="{{ $perilaku['sem_ta'] }}">
                    <input type="hidden" name="type" value="pelanggaran">
                    <tr id="pelanggaranForm{{ $key }}" style="display: none;">
                      <td>#</td>
                      <td>
                        <textarea name="pelanggaran" class="form-control auto-expand" placeholder="Pelanggaran" rows="1" required></textarea>
                      </td>
                      <td>
                        <input type="text" name="unit" class="form-control text-center" value="Keasramaan" style="width: 115px;" readonly>
                      </td>
                      <td>
                        <input type="date" name="tanggal" class="form-control tanggal" required>
                      </td>
                      <td style="width: 70px;">
                        <input type="number" name="poin" class="form-control text-center" placeholder="Poin" 
                          style="width: 70px;" min="1" max="99" required 
                          oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,2);" 
                          onblur="if(this.value < 1){this.value = 1;} else if(this.value > 99){this.value = 99;}">
                      </td>

                      <td>
                        <textarea name="tindakan" class="form-control auto-expand" placeholder="Tindakan" rows="1" required></textarea>
                      </td>
                      @if($hasPelanggaran)
                        <td></td>
                      @endif
                    </tr>
                    <tr id="buttonRow{{ $key }}" style="display: none;">
                      <td colspan="{{ $pelanggaranColspan }}" class="text-end">
                        <button type="submit" class="btn btn-success btn-sm me-2">Tambah</button>
                        <button type="button" class="btn btn-secondary btn-sm"
                          onclick="toggleForm('pelanggaranForm{{ $key }}', 'plusRow{{ $key }}')">
                          Batalkan
                        </button>
                      </td>
                    </tr>
                  </form>
                </tbody>
              </table>
            </div>
            
            <!-- Perbuatan Baik Section -->
            @php
              $hasPerbuatanBaik = count($perilaku['perbuatan_baik'] ?? []) > 0;
              $perbuatanBaikColspan = $hasPerbuatanBaik ? 7 : 6;
            @endphp
            <div id="perbuatanBaikTable{{ $key }}" style="display: none;">
              <table class="table table-bordered table-striped" style="margin-top: 0; border-top: none;">
                <thead>
                  <tr>
                    <th style="width: 0.7%;">#</th>
                    <th>Perbuatan Baik</th>
                    <th>Deskripsi / Keterangan</th>
                    <th style="width: 50px;">Poin</th>
                    <th style="width: 115px;">Unit</th>
                    <th>Tanggal</th>
                    @if($hasPerbuatanBaik)
                      <th>Aksi</th>
                    @endif
                  </tr>
                </thead>
                <tbody>
                  @php $perbuatanBaikIndex = 1; @endphp
@forelse ($perilaku['perbuatan_baik'] ?? [] as $perbuatan)
    <!-- Display Row -->
    <tr id="displayRowPerbuatanBaik{{ $perbuatan['id'] }}">
        <td style="width: 5%;">{{ $perbuatanBaikIndex++ }}</td>
        <td>{{ $perbuatan['perbuatan_baik'] ?? '-' }}</td>
        <td>{{ $perbuatan['tindakan'] ?? '-' }}</td>
        <td style="width: 50px;">{{ $perbuatan['poin'] ?? 0 }}</td>
        <td style="width: 115px;">{{ $perbuatan['unit'] ?? '-' }}</td>
        <td>{{ $perbuatan['tanggal'] ?? '-' }}</td>
        <td>
            @if (isset($perbuatan['id']))
                <button type="button" class="btn btn-outline-primary btn-sm" title="Edit" onclick="toggleEditForm('PerbuatanBaik', {{ $perbuatan['id'] }})">
                    <i class="fas fa-pencil-alt"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm delete-btn" 
                    data-id="{{ $perbuatan['id'] }}" 
                    data-url="{{ route('student_behaviors.destroy', $perbuatan['id']) }}" 
                    data-type="perbuatan_baik" 
                    title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            @endif
        </td>
    </tr>

    <!-- Inline Edit Form Row (hidden secara default) -->
    <tr id="editFormPerbuatanBaik{{ $perbuatan['id'] }}" style="display: none;">
        <form action="{{ route('student_behaviors.update', $perbuatan['id']) }}" method="POST">
            @csrf
            <td style="width: 5%;">{{ $perbuatanBaikIndex - 1 }}</td>
            <td>
                <textarea name="perbuatan_baik" class="form-control" required>{{ old('perbuatan_baik', $perbuatan['perbuatan_baik']) }}</textarea>
            </td>
            <td>
                <textarea name="keterangan" class="form-control">{{ old('keterangan', $perbuatan['keterangan'] ?? '') }}</textarea>
            </td>
            <td style="width: 50px;">
                <input type="number" name="kredit_poin" class="form-control" value="{{ old('kredit_poin', $perbuatan['poin']) }}" min="1" max="100">
            </td>
            <td style="width: 115px;">
                <input type="text" name="unit" class="form-control" value="{{ old('unit', $perbuatan['unit']) }}">
            </td>
            <td>
              <input type="date" name="tanggal" class="form-control tanggal" value="{{ old('tanggal', $perbuatan['tanggal']) }}">
            </td>
            <td>
                <button type="submit" class="btn btn-success btn-sm" title="Simpan"><i class="fas fa-save"></i></button>
                <button type="button" class="btn btn-secondary btn-sm" title="Batal" onclick="toggleEditForm('PerbuatanBaik', {{ $perbuatan['id'] }})"><i class="fas fa-times"></i></button>
            </td>
        </form>
    </tr>
@empty
    <tr>
        <td colspan="{{ $perbuatanBaikColspan }}" class="no-results">No results found.</td>
    </tr>
@endforelse

                  
                  <!-- Plus Sign Row untuk Perbuatan Baik -->
                  <tr id="plusRowPB{{ $key }}">
                    <td colspan="{{ $perbuatanBaikColspan }}" class="text-end">
                      <button type="button" class="btn btn-primary btn-sm"
                              onclick="toggleForm('perbuatanBaikForm{{ $key }}', 'plusRowPB{{ $key }}')">
                        <i class="fas fa-plus"></i>
                      </button>
                    </td>
                  </tr>
                  
                  <!-- Inline Form untuk Menambahkan Perbuatan Baik -->
                  <form method="POST" action="{{ route('student_behaviors.store') }}" class="validate-form">
                    @csrf
                    <input type="hidden" name="student_nim" value="{{ $studentNim }}">
                    <input type="hidden" name="ta" value="{{ $perilaku['ta'] }}">
                    <input type="hidden" name="semester" value="{{ $perilaku['sem_ta'] }}">
                    <input type="hidden" name="type" value="perbuatan_baik">
                    <tr id="perbuatanBaikForm{{ $key }}" style="display: none;">
                      <td>#</td>
                      <td>
                        <textarea name="perbuatan_baik" class="form-control auto-expand" placeholder="Perbuatan Baik" rows="1"></textarea>
                      </td>
                      <td>
                        <textarea name="keterangan" class="form-control auto-expand" placeholder="Keterangan" rows="1"></textarea>
                      </td>
                      <td style="width: 70px;">
                        <input type="number" name="poin" class="form-control text-center" placeholder="Poin" 
                          style="width: 70px;" min="1" max="99" required 
                          oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,2);" 
                          onblur="if(this.value < 1){this.value = 1;} else if(this.value > 99){this.value = 99;}">
                      </td>
                      <td>
                        <input type="text" name="unit" class="form-control" value="Keasramaan" style="width: 115px;" readonly>
                      </td>
                      <td>
                        <input type="date" name="tanggal" class="form-control tanggal" required>
                      </td>

                      @if($hasPerbuatanBaik)
                        <td></td>
                      @endif
                    </tr>
                    <tr id="buttonRowPB{{ $key }}" style="display: none;">
                      <td colspan="{{ $perbuatanBaikColspan }}" class="text-end">
                        <button type="submit" class="btn btn-success btn-sm me-2">Tambah</button>
                        <button type="button" class="btn btn-secondary btn-sm"
                                onclick="toggleForm('perbuatanBaikForm{{ $key }}', 'plusRowPB{{ $key }}')">
                          Batalkan
                        </button>
                      </td>
                    </tr>
                  </form>
                </tbody>
              </table>
            </div>
            
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" class="text-center">Tidak ada data perilaku.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

<!-- Auto-expand script untuk textarea -->
<script>
  function autoExpand(field) {
    field.style.height = 'inherit';
    const computed = window.getComputedStyle(field);
    const height = parseInt(computed.getPropertyValue('border-top-width'), 10)
                 + field.scrollHeight
                 + parseInt(computed.getPropertyValue('border-bottom-width'), 10);
    field.style.height = height + 'px';
  }
  document.querySelectorAll('.auto-expand').forEach(function(textarea) {
    textarea.addEventListener('input', function() {
      autoExpand(textarea);
    });
  });
</script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    let today = new Date().toISOString().split("T")[0];

    document.querySelectorAll(".tanggal").forEach(function (input) {
      input.setAttribute("max", today);
    });
  });
</script>

<!-- Include SweetAlert2 dari CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function toggleForm(formRowId, plusRowId) {
  const formRow = document.getElementById(formRowId);
  const plusRow = document.getElementById(plusRowId);
  if (formRow.style.display === 'none' || formRow.style.display === '') {
    formRow.style.display = 'table-row';
    let buttonRow = document.getElementById("buttonRow" + formRowId.replace("pelanggaranForm", "").replace("perbuatanBaikForm", "PB"));
    if (buttonRow) {
      buttonRow.style.display = 'table-row';
    }
    if (plusRow) {
      plusRow.style.display = 'none';
    }
  } else {
    formRow.style.display = 'none';
    let buttonRow = document.getElementById("buttonRow" + formRowId.replace("pelanggaranForm", "").replace("perbuatanBaikForm", "PB"));
    if (buttonRow) {
      buttonRow.style.display = 'none';
    }
    if (plusRow) {
      plusRow.style.display = 'table-row';
    }
  }
}

// Fungsi untuk menampilkan/menghilangkan tabel detail dan mengatur animasi arrow
function showTable(tableId, activeBoxId, inactiveBoxId, dropdownIcon) {
  // Sembunyikan semua tabel detail terlebih dahulu
  document.querySelectorAll('[id^="pelanggaranTable"], [id^="perbuatanBaikTable"]').forEach(function(table) {
    table.style.display = 'none';
  });
  // Tampilkan tabel yang dipilih
  if (tableId) {
    document.getElementById(tableId).style.display = 'block';
  }
  // Atur active/inactive box (tab)
  if (activeBoxId) {
    document.getElementById(activeBoxId).classList.add('active');
  }
  if (inactiveBoxId) {
    document.getElementById(inactiveBoxId).classList.remove('active');
  }
  
  // Jika dropdownIcon (arrow) disediakan, toggle kelas untuk animasi rotasi
  if (dropdownIcon) {
    dropdownIcon.classList.toggle('open');
  }
}

// Event listener untuk dropdown icon (jika tidak menggunakan event Bootstrap)
document.querySelectorAll('.dropdown-icon').forEach(function(iconWrapper) {
  iconWrapper.addEventListener('click', function(e) {
    const arrowIcon = this.querySelector('i');
    arrowIcon.classList.toggle('open');
  });
});

// Jika menggunakan Bootstrap collapse, atur event untuk rotasi arrow
var collapseElements = document.querySelectorAll('.collapse');
collapseElements.forEach(function(collapseEl) {
  collapseEl.addEventListener('shown.bs.collapse', function () {
    const icon = document.querySelector('[data-bs-target="#' + collapseEl.id + '"] .dropdown-icon i');
    if (icon) {
      icon.classList.add('open');
    }
  });
  collapseEl.addEventListener('hidden.bs.collapse', function () {
    const icon = document.querySelector('[data-bs-target="#' + collapseEl.id + '"] .dropdown-icon i');
    if (icon) {
      icon.classList.remove('open');
    }
  });
});

// Delete button handler menggunakan full form submission
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.delete-btn').forEach(function(button) {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      const url = this.dataset.url;
      Swal.fire({
        title: 'Yakin ingin menghapus data ini?',
        text: "Data akan dihapus secara permanen.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Tidak, batalkan!',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = url;
          const tokenInput = document.createElement('input');
          tokenInput.type = 'hidden';
          tokenInput.name = '_token';
          tokenInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          form.appendChild(tokenInput);
          const methodInput = document.createElement('input');
          methodInput.type = 'hidden';
          methodInput.name = '_method';
          methodInput.value = 'DELETE';
          form.appendChild(methodInput);
          document.body.appendChild(form);
          form.submit();
        }
      });
    });
  });
});
</script>

<!-- Alert on successful addition atau delete -->
@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function() {
  Swal.fire({
    title: 'Sukses!',
    text: '{{ session("success") }}',
    icon: 'success',
    timer: 1500,
    showConfirmButton: false
  });
});
</script>
@endif

<script>
function toggleEditForm(prefix, id) {
    var displayRow = document.getElementById('displayRow' + prefix + id);
    var editRow = document.getElementById('editForm' + prefix + id);
    if (editRow.style.display === 'none' || editRow.style.display === '') {
        editRow.style.display = 'table-row';
        displayRow.style.display = 'none';
    } else {
        editRow.style.display = 'none';
        displayRow.style.display = 'table-row';
    }
}
</script>


@endsection
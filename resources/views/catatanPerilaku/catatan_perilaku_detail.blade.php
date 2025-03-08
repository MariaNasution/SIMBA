@extends('layouts.app')

<link rel="stylesheet" href="{{ url('assets/css/catatan_perilaku.css') }}">


@section('content')
<!-- Header -->
<div class="d-flex align-items-center mb-4 border-bottom">
  <h3 class="me-auto">
    <a href="{{ route('pelanggaran_keasramaan') }}">
      <i class="fas fa-user-edit"></i> Catatan Perilaku /
    </a>
    <a href="{{ route('catatan_perilaku_detail', ['studentNim' => $studentNim]) }}">
      Detail / {{ $studentNim }}
    </a>
  </h3>
  <a href="#" onclick="confirmLogout()">
    <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
  </a>
</div>

<div class="container mt-4">
  <div class="position-relative mb-4">
    <div class="text-center">
      <div class="d-inline-block">
        <p class="mb-0">
          Detail Pelanggaran Mahasiswa ({{ $studentNim }})
        </p>
        <hr style="margin: 0 auto; width: 100%;">
      </div>
    </div>
    <a href="javascript:window.history.back()" 
       class="position-absolute" 
       style="left: 0; top: 50%; transform: translateY(-50%);">
      <i class="fas fa-arrow-left fs-4"></i>
    </a>
  </div>
  
  <!-- Tabel -->
  <table class="table">
    <thead>
      <tr>
        <th style="width: 5%">No</th>
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
        <td style="width: 5%">{{ $index++ }}</td>
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

      <!-- Collapsible row -->
      <tr class="collapse" id="details{{ $key }}">
        <td colspan="7">
          <div class="p-3">
            <h5>Pembinaan:</h5>

            <!-- Container for Pelanggaran and Perbuatan Baik tabs -->
            <div class="custom-box-container">
              <!-- Pelanggaran Tab -->
              <div id="pelanggaranBox{{ $key }}" class="custom-box active"
                onclick="showTable('pelanggaranTable{{ $key }}', 'pelanggaranBox{{ $key }}', 'perbuatanBaikBox{{ $key }}')">
                Pelanggaran ({{ count($perilaku['pelanggaran'] ?? []) }})
              </div>

              <!-- Perbuatan Baik Tab -->
              <div id="perbuatanBaikBox{{ $key }}" class="custom-box"
                onclick="showTable('perbuatanBaikTable{{ $key }}', 'perbuatanBaikBox{{ $key }}', 'pelanggaranBox{{ $key }}')">
                Perbuatan Baik Baru ({{ count($perilaku['perbuatan_baik'] ?? []) }})
              </div>
            </div>

            <!-- Pelanggaran Table -->
            <div id="pelanggaranTable{{ $key }}">
              <table class="table table-bordered table-striped" style="margin-top: 0; border-top: none;">
                <thead>
                  <tr>
                    <th style="width: 0.7%">#</th>
                    <th>Pelanggaran</th>
                    <th>Unit</th>
                    <th>Tanggal</th>
                    <th>Poin</th>
                    <th>Tindakan</th>
                  </tr>
                </thead>
                <tbody>
                  @php $pelanggaranIndex = 1; @endphp
                  @forelse ($perilaku['pelanggaran'] ?? [] as $pelanggaran)
    <tr>
        <td style="width: 5%">{{ $pelanggaranIndex++ }}</td>
        <td>{{ $pelanggaran['pelanggaran'] ?? '-' }}</td>
        <td>{{ $pelanggaran['unit'] ?? '-' }}</td>
        <td>{{ $pelanggaran['tanggal'] ?? '-' }}</td>
        <td>{{ $pelanggaran['poin'] ?? 0 }}</td>
        <td>
            {{ $pelanggaran['tindakan'] ?? '-' }}
        </td>
        <td>
            {{-- Check if this is a local record --}}
            @if (isset($pelanggaran['local_id']))
                <!-- Edit Button -->
                <a href="{{ route('student_behaviors.edit', $pelanggaran['local_id']) }}"
                   class="btn btn-outline-primary btn-sm"
                   title="Edit">
                    <i class="fas fa-pencil-alt"></i>
                </a>

                <!-- Delete Form -->
                <form action="{{ route('student_behaviors.destroy', $pelanggaran['local_id']) }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="no-results">No results found.</td>
    </tr>
@endforelse

                  <!-- Plus Sign Row (Pelanggaran) -->
                  <tr id="plusRow{{ $key }}">
                    <td colspan="7" class="text-end">
                      <button type="button" class="btn btn-primary btn-sm" 
                              onclick="toggleForm('pelanggaranForm{{ $key }}', 'plusRow{{ $key }}')">
                        <i class="fas fa-plus"></i>
                      </button>
                    </td>
                  </tr>

                  <!-- Hidden Inline Form Row (Pelanggaran) -->
                  <!-- Form Input -->
<tr id="pelanggaranForm{{ $key }}" style="display: none;">
  <td>#</td>
  <td><input type="text" name="pelanggaran" class="form-control" placeholder="Pelanggaran"></td>
  <td><input type="text" name="unit" class="form-control" value="Keasramaan" readonly></td>
  <td><input type="date" name="tanggal" class="form-control"></td>
  <td><input type="number" name="poin" class="form-control" placeholder="Poin"></td>
  <td><input type="text" name="tindakan" class="form-control" placeholder="Tindakan"></td>
</tr>

<!-- Tombol Tambah & Batalkan (Baris Baru) -->
<tr id="buttonRow{{ $key }}" style="display: none;">
  <td colspan="6" class="text-end">
    <form method="POST" action="{{ route('student_behaviors.store') }}">
      @csrf
      <input type="hidden" name="student_nim" value="{{ $studentNim }}">
      <input type="hidden" name="ta" value="{{ $perilaku['ta'] }}">
      <input type="hidden" name="semester" value="{{ $perilaku['sem_ta'] }}">
      <input type="hidden" name="type" value="pelanggaran">
      
      <button type="submit" class="btn btn-success btn-sm me-2">Tambah</button>
      <button type="button" class="btn btn-secondary btn-sm"
              onclick="toggleForm('pelanggaranForm{{ $key }}', 'plusRow{{ $key }}')">
        Batalkan
      </button>
    </form>
  </td>
</tr>

                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Perbuatan Baik Table -->
            <div id="perbuatanBaikTable{{ $key }}" style="display: none;">
              <table class="table table-bordered table-striped" style="margin-top: 0; border-top: none;">
                <thead>
                  <tr>
                    <th style="width: 0.7%">#</th>
                    <th>Perbuatan Baik</th>
                    <th>Keterangan</th>
                    <th>Kredit Kebaikan Poin</th>
                    <th>Unit</th>
                    <th>Tanggal</th>
                  </tr>
                </thead>
                <tbody>
                  @php $perbuatanBaikIndex = 1; @endphp
                  @forelse ($perilaku['perbuatan_baik'] ?? [] as $perbuatan)
                    <tr>
                      <td style="width: 5%">{{ $perbuatanBaikIndex++ }}</td>
                      <td>{{ $perbuatan['perbuatan_baik'] ?? '-' }}</td>
                      <td>{{ $perbuatan['keterangan'] ?? '-' }}</td>
                      <td style="width: 15%">{{ $perbuatan['kredit_poin'] ?? 0 }}</td>
                      <td>{{ $perbuatan['unit'] ?? '-' }}</td>
                      <td>{{ $perbuatan['tanggal'] ?? '-' }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="no-results">No results found.</td>
                    </tr>
                  @endforelse

                  <!-- Plus Sign Row (Perbuatan Baik) -->
                  <tr id="plusRowPB{{ $key }}">
                    <td colspan="6" class="text-end">
                      <button type="button" class="btn btn-primary btn-sm" 
                              onclick="toggleForm('perbuatanBaikForm{{ $key }}', 'plusRowPB{{ $key }}')">
                        <i class="fas fa-plus"></i>
                      </button>
                    </td>
                  </tr>
<!-- Hidden Inline Form Row (Perbuatan Baik) -->
<tr id="perbuatanBaikForm{{ $key }}" style="display: none;">
  <td>#</td>
  <td><input type="text" name="perbuatan_baik" class="form-control" placeholder="Perbuatan Baik"></td>
  <td><input type="text" name="keterangan" class="form-control" placeholder="Keterangan"></td>
  <td><input type="number" name="kredit_poin" class="form-control" placeholder="Kredit Poin"></td>
  <td><input type="text" name="unit" class="form-control" value="Keasramaan" readonly></td>
  <td><input type="date" name="tanggal" class="form-control"></td>
</tr>

<!-- Tombol Tambah & Batalkan (Baris Baru) -->
<tr id="buttonRowPB{{ $key }}" style="display: none;">
  <td colspan="6" class="text-end">
    <form method="POST" action="{{ route('student_behaviors.store') }}">
      @csrf
      <input type="hidden" name="student_nim" value="{{ $studentNim }}">
      <input type="hidden" name="ta" value="{{ $perilaku['ta'] }}">
      <input type="hidden" name="semester" value="{{ $perilaku['sem_ta'] }}">
      <input type="hidden" name="type" value="perbuatan_baik">
      
      <button type="submit" class="btn btn-success btn-sm me-2">Tambah</button>
      <button type="button" class="btn btn-secondary btn-sm"
              onclick="toggleForm('perbuatanBaikForm{{ $key }}', 'plusRowPB{{ $key }}')">
        Batalkan
      </button>
    </form>
  </td>
</tr>

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

<script>
function toggleForm(formRowId, plusRowId) {
  const formRow = document.getElementById(formRowId);
  const plusRow = document.getElementById(plusRowId);
  if (formRow.style.display === 'none' || formRow.style.display === '') {
      formRow.style.display = 'table-row';
      if (plusRow) {
          plusRow.style.display = 'none';
      }
  } else {
      formRow.style.display = 'none';
      if (plusRow) {
          plusRow.style.display = 'table-row';
      }
  }
}

// Function to toggle form and buttons
function toggleForm(formId, plusRowId) {
  let formRow = document.getElementById(formId);
  let buttonRow = document.getElementById("buttonRow" + formId.replace("pelanggaranForm", "").replace("perbuatanBaikForm", "PB"));
  let plusRow = document.getElementById(plusRowId);

  if (formRow.style.display === "none" || formRow.style.display === "") {
    formRow.style.display = "table-row";
    buttonRow.style.display = "table-row"; // Tampilkan tombol
    plusRow.style.display = "none"; // Sembunyikan tombol "+"
  } else {
    formRow.style.display = "none";
    buttonRow.style.display = "none"; // Sembunyikan tombol
    plusRow.style.display = "table-row"; // Tampilkan tombol "+"
  }
}


// Function to show the corresponding table (pelanggaran or perbuatan baik)
function showTable(tableId, activeBoxId, inactiveBoxId) {
  document.querySelectorAll('[id^="pelanggaranTable"], [id^="perbuatanBaikTable"]').forEach(function(table) {
    table.style.display = 'none';
  });
  if (tableId) {
    document.getElementById(tableId).style.display = 'block';
  }
  if (activeBoxId) {
    document.getElementById(activeBoxId).classList.add('active');
  }
  if (inactiveBoxId) {
    document.getElementById(inactiveBoxId).classList.remove('active');
  }
}

// Toggle dropdown icon on collapse
function toggleDropdownIcon(element, isOpen) {
  const icon = element.querySelector('i');
  if (isOpen) {
    icon.classList.remove('fa-chevron-left');
    icon.classList.add('fa-chevron-down');
  } else {
    icon.classList.remove('fa-chevron-down');
    icon.classList.add('fa-chevron-left');
  }
}

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.dropdown-icon').forEach(function(dropdown) {
    const target = dropdown.getAttribute('data-bs-target');
    const collapseElement = document.querySelector(target);

    collapseElement.addEventListener('show.bs.collapse', function() {
      toggleDropdownIcon(dropdown, true);
      const key = target.replace('#details', '');
      showTable(`pelanggaranTable${key}`, `pelanggaranBox${key}`, `perbuatanBaikBox${key}`);
    });

    collapseElement.addEventListener('hide.bs.collapse', function() {
      toggleDropdownIcon(dropdown, false);
    });
  });
});
</script>

@endsection
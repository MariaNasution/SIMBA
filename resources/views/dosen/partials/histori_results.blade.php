<div class="histori-column">
  <div class="histori-title">
    <h2>Semester Baru</h2>
  </div>
  <div class="histori-items">
    @forelse ($semesterBaru as $item)
      <div class="histori-item">
        <a href="{{ route('dosen.histori.detailed', $item->ID_Perwalian) }}">
          {{ \Carbon\Carbon::parse($item->Tanggal)->translatedFormat('l, d F Y') }} ({{ $item->kelas }})
        </a>
      </div>
    @empty
      <div class="histori-item">Tidak ada data</div>
    @endforelse
  </div>
</div>

<div class="histori-column">
  <div class="histori-title">
    <h2>Sebelum UTS</h2>
  </div>
  <div class="histori-items">
    @forelse ($sebelumUts as $item)
      <div class="histori-item">
        <a href="{{ route('dosen.histori.detailed', $item->ID_Perwalian) }}">
          {{ \Carbon\Carbon::parse($item->Tanggal)->translatedFormat('l, d F Y') }} ({{ $item->kelas }})
        </a>
      </div>
    @empty
      <div class="histori-item">Tidak ada data</div>
    @endforelse
  </div>
</div>

<div class="histori-column">
  <div class="histori-title">
    <h2>Sebelum UAS</h2>
  </div>
  <div class="histori-items">
    @forelse ($sebelumUas as $item)
      <div class="histori-item">
        <a href="{{ route('dosen.histori.detailed', $item->ID_Perwalian) }}">
          {{ \Carbon\Carbon::parse($item->Tanggal)->translatedFormat('l, d F Y') }} ({{ $item->kelas }})
        </a>
      </div>
    @empty
      <div class="histori-item">Tidak ada data</div>
    @endforelse
  </div>
</div>

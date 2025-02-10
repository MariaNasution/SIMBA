@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <h3 class="me-auto">
            <a href="{{ route('beranda') }}">Home</a> /
            <a href="{{ route('bursar') }}">Bursar</a> /
            Detail
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>

    <!-- Detail Tagihan Header -->

    <div class="card-header bg-white d-flex justify-content-between">
        <div>
            <h5>User Payment: <strong>{{ $bursarDetail['periode'] ?? 'N/A' }}</strong></h5>
        </div>
        <div class="text-end">
            <p>VA ({{ $VAData['bank'] ?? 'N/A' }}): {{ $VAData['virtual_account'] ?? 'N/A' }}</p>
        </div>
    </div>


    <!-- Detail Section -->

    <div class="card-body">
        <h5 class="mb-4 text-start">Detail Tagihan</h5>

        <table class="table table-bordered mb-4">
            <thead class="table-light">
                <tr>
                    <th>Fee</th>
                    <th>Payment Detail</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bursarDetail['detail'] as $index => $detail)
                    <tr>
                        <td>
                            <strong>{{ $detail['nama_biaya'] ?? '-' }}</strong>
                        </td>
                        <td>
                            <div><strong>Total Fee:</strong> Rp.
                                {{ number_format($detail['nominal_dibayarkan'] ?? 0, 2, ',', '.') }}</div>
                            <div><strong>Minimum Payment:</strong> Rp.
                                {{ number_format($detail['nominal_minimum'] ?? 0, 2, ',', '.') }}</div>
                            <div><strong>Voucher:</strong> Rp. {{ number_format($detail['voucher'] ?? 0, 2, ',', '.') }}
                            </div>
                        </td>
                        <td>{{ $bursarDetail['status'] ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada detail biaya.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-between">
            <h5>Total Pembayaran:</h5>
            <h5 class="text-success">Rp. {{ number_format($bursarDetail['nominal_dibayarkan'] ?? 0, 2, ',', '.') }}
            </h5>
        </div>
        <div class="d-flex justify-content-between">
            <h5>Total Pembayaran Virtual Account:</h5>
            <h5 class="text-success">Rp. {{ number_format($bursarDetail['nominal_dibayarkan'] ?? 0, 2, ',', '.') }}
            </h5>
        </div>
    </div>
@endsection

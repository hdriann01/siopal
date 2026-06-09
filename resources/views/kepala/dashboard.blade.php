@extends('layouts.kepala') {{-- Pastikan kamu sudah membuat file layout ini --}}

@section('content')
    <div class="space-y-8">

        <!-- Page Header -->
        <div>
            <h2 class="text-2xl md:text-3xl font-display font-bold text-gray-800 tracking-tight">Dashboard Kepala Apotek</h2>
            <p class="text-gray-500 mt-2 text-sm">Ringkasan analitik persediaan dan daftar tunggu persetujuan.</p>
        </div>

        <!-- Bento Grid: Early Warning Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Card 1: Verifikasi Faktur -->
            <div
                class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-2 bg-teal-50 rounded-lg">
                        <!-- Heroicons: Clipboard Document Check -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6 text-teal-600">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" />
                        </svg>
                    </div>
                    @if ($menungguMasuk > 0)
                        <span class="text-xs font-medium text-amber-700 bg-amber-100 px-2 py-1 rounded-full">Urgent</span>
                    @endif
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Menunggu Verifikasi (Draft)</p>
                    <h3 class="text-3xl font-bold text-gray-800 mb-6">{{ $menungguMasuk }} Faktur</h3>
                </div>
                <button
                    class="w-full bg-teal-600 hover:bg-teal-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                    Cek Faktur
                </button>
            </div>

            <!-- Card 2: Otorisasi Keluar -->
            <div
                class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-2 bg-blue-50 rounded-lg">
                        <!-- Heroicons: Archive Box Arrow Down -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6 text-blue-600">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20.25 14.15v4.25c0 1.084-.907 1.92-2.022 1.92H5.772c-1.115 0-2.022-.836-2.022-1.92v-4.25m16.5 0a2.18 2.18 0 00-.75-1.661l-3.7-3.21a2.25 2.25 0 00-1.48-.569h-3.64l-3.7 3.21a2.18 2.18 0 00-.75 1.661m16.5 0a2.18 2.18 0 01-.75 1.661l-3.7 3.21a2.25 2.25 0 01-1.48.569h-3.64l-3.7-3.21a2.18 2.18 0 01-.75-1.661M12 12.75v-9m0 0l-3 3m3-3l3 3" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Otorisasi Permintaan/Keluar</p>
                    <h3 class="text-3xl font-bold text-gray-800 mb-6">{{ $menungguKeluar }} Transaksi</h3>
                </div>
                <button
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                    Tinjau Permintaan
                </button>
            </div>

            <!-- Card 3: Alert Stok Kritis -->
            <div
                class="bg-red-50 rounded-xl border border-red-200 p-6 shadow-sm flex flex-col justify-between relative overflow-hidden">
                <div class="flex items-start justify-between mb-4 relative z-10">
                    <div class="p-2 bg-white rounded-lg">
                        <!-- Heroicons: Exclamation Triangle -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6 text-red-600">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <div class="relative z-10">
                    <p class="text-sm text-red-700 font-medium mb-1">Peringatan Stok Tersedia</p>
                    <h3 class="text-3xl font-bold text-red-700 mb-6">{{ $stokKritis }} Obat Kritis</h3>
                </div>
                <button
                    class="w-full bg-white hover:bg-red-100 text-red-700 py-2 px-4 rounded-lg text-sm font-bold border border-red-300 transition-colors relative z-10">
                    Lihat Rincian Stok
                </button>
            </div>
        </div>

        <!-- Bagian Grafik dan Tabel -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Tabel Tugas Menunggu Persetujuan -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm flex flex-col">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800">Menunggu Verifikasi Faktur</h3>
                    <span
                        class="bg-teal-100 text-teal-800 font-medium text-xs px-2.5 py-1 rounded-full">{{ $menungguMasuk }}
                        Tugas</span>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 font-medium" scope="col">No. Faktur</th>
                                <th class="px-6 py-4 font-medium" scope="col">Tanggal</th>
                                <th class="px-6 py-4 font-medium text-right" scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fakturPending as $faktur)
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-800">
                                        Faktur #{{ $faktur->no_faktur }} <br>
                                        <span class="text-xs text-gray-500 font-normal">{{ $faktur->nama_supplier }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ \Carbon\Carbon::parse($faktur->tanggal_masuk)->format('d M Y') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <button
                                            class="text-teal-600 hover:text-teal-800 font-medium text-sm">Tinjau</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                        Tidak ada faktur yang menunggu verifikasi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Grafik Peta Kedaluwarsa Obat -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col">
                <h3 class="text-lg font-bold text-gray-800 mb-1">Peta Kedaluwarsa Obat (Defecta)</h3>
                <p class="text-sm text-gray-500 mb-4">Pemantauan masa kedaluwarsa (FEFO) seluruh batch di inventaris.</p>

                <div id="chartDefecta" class="w-full flex-1"></div>
            </div>
        </div>

        <!-- Skrip ApexCharts -->
        <!-- Skrip ApexCharts untuk Bar Chart Defecta -->
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            // Menangkap data dari controller
            const dataDefecta = @json($dataDefecta);

            const options = {
                series: [{
                    name: 'Jumlah Batch',
                    data: dataDefecta
                }],
                chart: {
                    type: 'bar',
                    height: 280,
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'Inter, sans-serif'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: true, // Mengubah grafik menjadi mendatar
                        distributed: true, // Membuat setiap batang memiliki warna berbeda
                        dataLabels: {
                            position: 'bottom'
                        }
                    }
                },
                colors: [
                    '#10B981', // Hijau (Aman)
                    '#F59E0B', // Kuning/Amber (Peringatan)
                    '#F97316', // Oranye (Kritis)
                    '#EF4444' // Merah (Kedaluwarsa)
                ],
                dataLabels: {
                    enabled: true,
                    textAnchor: 'start',
                    style: {
                        colors: ['#fff'],
                        fontSize: '12px',
                        fontWeight: 'bold'
                    },
                    formatter: function(val, opt) {
                        return val + " Batch";
                    },
                    offsetX: 0,
                    dropShadow: {
                        enabled: true,
                        top: 1,
                        left: 1,
                        blur: 1,
                        color: '#000',
                        opacity: 0.45
                    }
                },
                xaxis: {
                    // Kategori sumbu Y (karena horizontal)
                    categories: ['Aman (> 6 Bulan)', 'Peringatan (3-6 Bulan)', 'Kritis (< 3 Bulan)', 'Kedaluwarsa'],
                    labels: {
                        formatter: function(val) {
                            return Math.round(val); // Membulatkan angka agar tidak ada desimal
                        }
                    }
                },
                legend: {
                    show: false // Disembunyikan karena label sudah ada di sebelah kiri batang
                },
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: function(val) {
                            return val + " Batch Obat";
                        }
                    }
                }
            };

            const chart = new ApexCharts(document.querySelector("#chartDefecta"), options);
            chart.render();
        </script>
    </div>
@endsection
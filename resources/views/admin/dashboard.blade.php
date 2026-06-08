@extends('layouts.admin')

@section('content')
    <div class="space-y-8">

        <div>
            <h2 class="text-2xl md:text-3xl font-display font-bold text-on-surface tracking-tight">Dashboard Administrator</h2>
            <p class="text-on-surface-variant mt-2 text-sm">Pantau ringkasan data sistem SIOPAL hari ini.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            <div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant ambient-shadow flex flex-col justify-between hover:bg-surface-container-low transition-colors">
                <div class="flex justify-between items-start mb-4">
                    <div class="bg-primary-container/20 p-2 rounded-lg">
                        <span class="material-symbols-outlined text-primary">group</span>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-on-surface-variant">Total Pengguna</p>
                    <p class="text-2xl font-bold text-on-surface mt-1">{{ $totalPengguna }}</p>
                    <p class="text-xs text-on-surface-variant mt-2">Akun Terdaftar</p>
                </div>
            </div>

            <div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant ambient-shadow flex flex-col justify-between hover:bg-surface-container-low transition-colors">
                <div class="flex justify-between items-start mb-4">
                    <div class="bg-secondary-container/30 p-2 rounded-lg">
                        <span class="material-symbols-outlined text-secondary">medication</span>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-on-surface-variant">Total Jenis Obat</p>
                    <p class="text-2xl font-bold text-on-surface mt-1">{{ $totalObat }}</p>
                    <p class="text-xs text-on-surface-variant mt-2">Item di Inventaris</p>
                </div>
            </div>

            <div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant ambient-shadow flex flex-col justify-between hover:bg-surface-container-low transition-colors">
                <div class="flex justify-between items-start mb-4">
                    <div class="bg-primary-container/20 p-2 rounded-lg">
                        <span class="material-symbols-outlined text-primary">input</span>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-on-surface-variant">Obat Masuk</p>
                    <p class="text-2xl font-bold text-on-surface mt-1">{{ $totalMasuk }}</p>
                    <p class="text-xs text-primary font-medium mt-2">Faktur Penerimaan</p>
                </div>
            </div>

            <div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant ambient-shadow flex flex-col justify-between hover:bg-surface-container-low transition-colors">
                <div class="flex justify-between items-start mb-4">
                    <div class="bg-error-container p-2 rounded-lg">
                        <span class="material-symbols-outlined text-error">output</span>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-on-surface-variant">Obat Keluar</p>
                    <p class="text-2xl font-bold text-on-surface mt-1">{{ $totalKeluar }}</p>
                    <p class="text-xs text-on-surface-variant mt-2">Transaksi Selesai</p>
                </div>
            </div>

        </div> <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm mt-6">
            <h3 class="text-lg font-bold text-gray-800 mb-1">Tren Pergerakan Obat</h3>
            <p class="text-sm text-gray-500 mb-4">Statistik data barang masuk dan keluar dalam 7 hari terakhir.</p>

            <div id="chartTrenObat" class="w-full h-80"></div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

        <script>
            // Menangkap data array dari PHP (Controller) dan mengubahnya menjadi format JSON yang dipahami JavaScript
            const labelTanggal = @json($labelTanggal);
            const dataMasuk = @json($dataMasuk);
            const dataKeluar = @json($dataKeluar);

            // Konfigurasi tampilan grafik Area
            const options = {
                series: [{
                    name: 'Obat Masuk',
                    data: dataMasuk
                }, {
                    name: 'Obat Keluar',
                    data: dataKeluar 
                }],
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: { show: false }, // Menyembunyikan ikon menu bawaan grafik agar lebih bersih
                    fontFamily: 'Inter, sans-serif' // Menyesuaikan dengan font SIOPAL kamu
                },
                colors: ['#0D9488', '#F59E0B'], // Hijau Teal (Masuk) dan Kuning Amber (Keluar)
                fill: {
                    type: 'gradient', // Membuat warna bawah garis bergradasi transparan
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.05,
                        stops: [0, 100]
                    }
                },
                dataLabels: {
                    enabled: false // Menonaktifkan angka langsung di atas garis agar tidak semrawut
                },
                stroke: {
                    curve: 'smooth', // Garisnya melengkung halus, tidak patah-patah (zigzag)
                    width: 3
                },
                xaxis: {
                    categories: labelTanggal, // Memasukkan data PHP label tanggal ke sumbu X
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        formatter: function (val) {
                            return Math.round(val); // Membulatkan angka sumbu Y agar tidak ada desimal koma
                        }
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    theme: 'light'
                }
            };

            // Mengeksekusi gambar grafik ke dalam elemen div 'chartTrenObat'
            const chart = new ApexCharts(document.querySelector("#chartTrenObat"), options);
            chart.render();
        </script>

    </div>
@endsection

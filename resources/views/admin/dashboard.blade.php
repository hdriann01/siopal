@extends('layouts.admin')

@section('content')
    <div class="space-y-8">

        <div>
            <h2 class="text-2xl md:text-3xl font-display font-bold text-on-surface tracking-tight">Dashboard Administrator</h2>
            <p class="text-on-surface-variant mt-2 text-sm">Pantau ringkasan data sistem SIOPAL hari ini.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            <div
                class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant ambient-shadow flex flex-col justify-between hover:bg-surface-container-low transition-colors">
                <div class="flex justify-between items-start mb-4">
                    <div class="bg-primary-container/20 p-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6 text-primary">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-on-surface-variant">Total Pengguna</p>
                    <p class="text-2xl font-bold text-on-surface mt-1">{{ $totalPengguna }}</p>
                    <p class="text-xs text-on-surface-variant mt-2">Akun Terdaftar</p>
                </div>
            </div>

            <div
                class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant ambient-shadow flex flex-col justify-between hover:bg-surface-container-low transition-colors">
                <div class="flex justify-between items-start mb-4">
                    <div class="bg-secondary-container/30 p-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6 text-secondary">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-on-surface-variant">Total Jenis Obat</p>
                    <p class="text-2xl font-bold text-on-surface mt-1">{{ $totalObat }}</p>
                    <p class="text-xs text-on-surface-variant mt-2">Item di Inventaris</p>
                </div>
            </div>

            <div
                class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant ambient-shadow flex flex-col justify-between hover:bg-surface-container-low transition-colors">
                <div class="flex justify-between items-start mb-4">
                    <div class="bg-primary-container/20 p-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6 text-primary">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-on-surface-variant">Obat Masuk</p>
                    <p class="text-2xl font-bold text-on-surface mt-1">{{ $totalMasuk }}</p>
                    <p class="text-xs text-primary font-medium mt-2">Faktur Penerimaan</p>
                </div>
            </div>

            <div
                class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant ambient-shadow flex flex-col justify-between hover:bg-surface-container-low transition-colors">
                <div class="flex justify-between items-start mb-4">
                    <div class="bg-error-container p-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6 text-error">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-on-surface-variant">Obat Keluar</p>
                    <p class="text-2xl font-bold text-on-surface mt-1">{{ $totalKeluar }}</p>
                    <p class="text-xs text-on-surface-variant mt-2">Transaksi Selesai</p>
                </div>
            </div>

        </div>
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm mt-6">
            <h3 class="text-lg font-bold text-gray-800 mb-1">Tren Pergerakan Obat</h3>
            <p class="text-sm text-gray-500 mb-4">Statistik data barang masuk dan keluar dalam 7 hari terakhir.</p>

            <div id="chartTrenObat" class="w-full h-80"></div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

        <script>
            // Menangkap data array dari PHP
            const labelTanggal = @json($labelTanggal);
            const dataMasuk = @json($dataMasuk);
            const dataKeluar = @json($dataKeluar);

            // Konfigurasi ApexCharts
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
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'Inter, sans-serif'
                },
                colors: ['#0D9488', '#F59E0B'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.05,
                        stops: [0, 100]
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                xaxis: {
                    categories: labelTanggal,
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function(val) {
                            return Math.round(val);
                        }
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    theme: 'light'
                }
            };

            const chart = new ApexCharts(document.querySelector("#chartTrenObat"), options);
            chart.render();
        </script>

    </div>
@endsection
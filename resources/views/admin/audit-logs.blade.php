@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Log Audit Sistem</h2>
            <p class="mt-1 text-sm text-gray-500 font-medium">Pantau seluruh rekaman aktivitas dan perubahan data dalam
                sistem secara kronologis.</p>
        </div>

        <div
            class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col sm:flex-row gap-4 items-center justify-between">

            <div class="w-full sm:w-auto">
                <form action="{{ route('admin.audit-logs') }}" method="GET" class="relative w-full sm:w-auto">
                    <select name="role" onchange="this.form.submit()"
                        class="bg-none block w-full sm:w-48 pl-3 pr-10 py-2 border border-gray-200 rounded-lg text-sm bg-white text-gray-700 focus:ring-2 focus:ring-primary focus:border-primary appearance-none cursor-pointer">
                        <option value="">Semua Peran</option>
                        <option value="Administrator" {{ request('role') == 'Administrator' ? 'selected' : '' }}>
                            Administrator</option>
                        <option value="Petugas Apotek" {{ request('role') == 'Petugas Apotek' ? 'selected' : '' }}>Petugas
                            Farmasi</option>
                        <option value="Kepala Apotek" {{ request('role') == 'Kepala Apotek' ? 'selected' : '' }}>Kepala
                            Apotek</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-gray-500">
                        <span class="material-symbols-outlined text-sm">expand_more</span>
                    </div>
                </form>
            </div>

            <div class="w-full sm:w-auto flex justify-end">
                <a href="{{ route('admin.audit-logs.pdf', ['role' => request('role')]) }}" target="_blank"
                    class="flex items-center gap-2 px-4 py-2 border border-gray-200 text-gray-700 font-medium text-sm rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap">
                    <span class="material-symbols-outlined text-sm">description</span> PDF
                </a>
            </div>

        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Waktu</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Pengguna</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Aktivitas</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Alamat IP</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-800 font-medium">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, H:i') }}
                                </td>

                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $log->pengguna->nama_lengkap ?? 'Sistem' }}</div>
                                    <div class="text-xs text-primary font-medium mt-0.5">
                                        {{ $log->pengguna->peran ?? 'Unknown' }}</div>
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex items-start gap-2">
                                        <span
                                            class="material-symbols-outlined text-gray-400 text-base mt-0.5">history</span>
                                        <span class="text-sm text-gray-700">{{ $log->aktivitas }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500 font-mono">
                                    {{ $log->alamat_ip ?? '-' }}
                                </td>

                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="w-2.5 h-2.5 rounded-full {{ $log->status == 'Success' ? 'bg-green-600' : 'bg-red-600' }}"></span>
                                        <span class="text-sm text-gray-800 font-medium">{{ $log->status }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    @if (request('role'))
                                        Belum ada aktivitas yang direkam oleh peran <b>{{ request('role') }}</b>.
                                    @else
                                        Belum ada riwayat aktivitas yang terekam.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

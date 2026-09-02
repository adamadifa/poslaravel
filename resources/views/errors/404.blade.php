@extends('layouts.auth')

@section('content')
<div class="bg-white border border-slate-200/90 rounded-3xl p-8 shadow-xl text-center space-y-6 transition-colors duration-200">
    
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-slate-100 border border-slate-200 text-slate-500">
        <i data-lucide="file-question" class="w-8 h-8"></i>
    </div>

    <div class="space-y-2">
        <h2 class="text-3xl font-black text-slate-900 tracking-tight">404</h2>
        <h3 class="text-base font-bold text-slate-800">Halaman Tidak Ditemukan</h3>
        <p class="text-xs text-slate-500 max-w-sm mx-auto">
            Halaman atau dokumen yang Anda cari mungkin telah dipindahkan, dihapus, atau URL tidak valid.
        </p>
    </div>

    <div class="pt-2">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 text-white font-bold text-xs shadow-md shadow-brand-500/25 hover:from-brand-600 hover:to-amber-600 transition">
            <i data-lucide="home" class="w-4 h-4"></i>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

</div>
@endsection

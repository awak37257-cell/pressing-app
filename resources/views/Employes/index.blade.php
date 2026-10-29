@extends('layouts.app')

@section('title', 'Gestion du personnel')
@section('page-title', 'Comptes employés')
@section('page-subtitle', 'Gérez les accès et les rôles de votre équipe')

@section('content')

<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pulseRing { 0% { transform: scale(1); opacity: 0.6; } 70% { transform: scale(1.8); opacity: 0; } 100% { transform: scale(1.8); opacity: 0; } }
    .stat-card { animation: fadeInUp 0.5s ease-out backwards; }
    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.10s; }
    .stat-card:nth-child(3) { animation-delay: 0.15s; }
    .table-row { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .table-row:hover { background: linear-gradient(90deg, #faf5ff 0%, #ffffff 40%, #ffffff 100%); }
    .table-row:hover .row-avatar { transform: scale(1.1) rotate(-3deg); }
    .table-row:hover .action-btn { opacity: 1; transform: translateX(0); }
    .action-btn { opacity: 0.5; transform: translateX(4px); transition: all 0.25s ease; }
    .gradient-border { position: relative; background: white; }
    .gradient-border::before {
        content: ''; position: absolute; inset: 0; padding: 1px; border-radius: inherit;
        background: linear-gradient(135deg, rgba(139,92,246,0.3), rgba(168,85,247,0.1), rgba(139,92,246,0.3));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none;
    }
    .ring-pulse::before { content: ''; position: absolute; inset: 0; border-radius: 9999px; background: currentColor; animation: pulseRing 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
</style>

{{-- ══════ HERO ══════ --}}
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-500/30">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Personnel & Utilisateurs</h1>
            <p class="text-xs text-slate-500 font-medium">Liste des comptes actifs sur votre établissement</p>
        </div>
    </div>
    <a href="{{ route('employes.create') }}"
       class="group inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 via-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold px-5 py-2.5 rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-xl text-sm transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
        <svg class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nouveau compte
    </a>
</div>

{{-- ══════ STATS ══════ --}}
@php
    $totalEmployes  = $employes->count();
    $nbAdmin        = $employes->where('role', 'admin')->count();
    $nbOperations   = $employes->whereIn('role', ['gerant', 'caissier', 'atelier'])->count();
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    {{-- Total employés --}}
    <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-600 via-violet-600 to-purple-700 p-6 text-white shadow-xl shadow-violet-600/20">
        <div class="absolute top-0 right-0 w-40 h-40 bg-fuchsia-400/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-fuchsia-200 bg-fuchsia-500/20 px-2 py-1 rounded-md tracking-wider uppercase">Total</span>
            </div>
            <p class="text-[11px] font-semibold text-white/70 uppercase tracking-[0.1em] mb-1">Comptes employés</p>
            <p class="text-3xl font-extrabold tracking-tight">{{ $totalEmployes }}</p>
            <p class="text-xs text-white/80 mt-2 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-fuchsia-300 ring-pulse relative"></span>
                Équipe active
            </p>
        </div>
    </div>

    {{-- Admins --}}
    <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6 text-white shadow-xl shadow-slate-900/20">
        <div class="absolute top-0 right-0 w-40 h-40 bg-purple-500/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-purple-300 bg-purple-500/20 px-2 py-1 rounded-md tracking-wider uppercase">Admin</span>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-[0.1em] mb-1">Administrateurs</p>
            <p class="text-3xl font-extrabold tracking-tight">{{ $nbAdmin }}</p>
            <p class="text-xs text-slate-400 mt-2 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
                Accès complet
            </p>
        </div>
    </div>

    {{-- Opérations --}}
    <div class="stat-card relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 via-emerald-600 to-teal-700 p-6 text-white shadow-xl shadow-emerald-600/20">
        <div class="absolute top-0 right-0 w-40 h-40 bg-teal-400/25 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-sm border border-white/25 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="text-[10px] font-bold bg-white/20 px-2 py-1 rounded-md tracking-wider uppercase">Ops</span>
            </div>
            <p class="text-[11px] font-semibold text-white/80 uppercase tracking-[0.1em] mb-1">Équipe d'opérations</p>
            <p class="text-3xl font-extrabold tracking-tight">{{ $nbOperations }}</p>
            <p class="text-xs text-white/80 mt-2 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-teal-300"></span>
                Gérants · Caissiers · Atelier
            </p>
        </div>
    </div>
</div>

{{-- ══════ TABLEAU ══════ --}}
<div class="gradient-border rounded-2xl bg-white shadow-lg shadow-slate-200/50 overflow-hidden">

    <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white shadow-md shadow-violet-500/25">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
        </div>
        <div>
            <h3 class="font-bold text-slate-800 text-sm">Liste des employés</h3>
            <p class="text-[11px] text-slate-500">{{ $totalEmployes }} compte(s) enregistré(s)</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/70 border-b border-slate-200/60">
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Employé</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Rôle</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em]">Contact</th>
                    <th class="py-3.5 px-6 text-[10.5px] font-bold text-slate-500 uppercase tracking-[0.08em] text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($employes as $emp)
                    @php
                        $roleConfig = match($emp->role) {
                            'admin'    => ['label' => 'Admin',    'bg' => 'from-purple-500 to-violet-600', 'badge' => 'bg-purple-100 text-purple-700 border-purple-200/70', 'dot' => 'bg-purple-500'],
                            'gerant'   => ['label' => 'Gérant',   'bg' => 'from-blue-500 to-indigo-600',   'badge' => 'bg-blue-100 text-blue-700 border-blue-200/70',       'dot' => 'bg-blue-500'],
                            'caissier' => ['label' => 'Caissier', 'bg' => 'from-emerald-500 to-teal-600',  'badge' => 'bg-emerald-100 text-emerald-700 border-emerald-200/70', 'dot' => 'bg-emerald-500'],
                            'atelier'  => ['label' => 'Atelier',  'bg' => 'from-amber-500 to-orange-600',  'badge' => 'bg-amber-100 text-amber-700 border-amber-200/70',     'dot' => 'bg-amber-500'],
                            default    => ['label' => ucfirst($emp->role), 'bg' => 'from-slate-500 to-slate-600', 'badge' => 'bg-slate-100 text-slate-600 border-slate-200/70', 'dot' => 'bg-slate-400'],
                        };
                        $isMe = $emp->id === auth()->id();
                    @endphp
                    <tr class="table-row group">
                        <td class="py-3.5 px-6">
                            <div class="flex items-center gap-3">
                                <div class="row-avatar w-10 h-10 rounded-xl bg-gradient-to-br {{ $roleConfig['bg'] }} flex items-center justify-center text-xs font-extrabold text-white shadow-md transition-all duration-300">
                                    {{ mb_substr($emp->nom, 0, 2) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900 truncate">{{ $emp->nom }}</span>
                                        @if($isMe)
                                            <span class="text-[9px] font-extrabold bg-amber-100 text-amber-700 border border-amber-200/70 px-1.5 py-0.5 rounded-md uppercase tracking-wider">
                                                Moi
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400 truncate">{{ $emp->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-6">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $roleConfig['badge'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $roleConfig['dot'] }}"></span>
                                {{ $roleConfig['label'] }}
                            </span>
                        </td>
                        <td class="py-3.5 px-6">
                            @if($emp->telephone)
                                <div class="flex items-center gap-1.5 text-slate-600">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <span class="font-mono text-xs font-semibold">{{ $emp->telephone }}</span>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">Non renseigné</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-6 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('employes.edit', $emp) }}"
                                   class="action-btn p-2 rounded-lg bg-amber-50 hover:bg-amber-500 text-amber-600 hover:text-white transition-all duration-200 hover:scale-110"
                                   title="Modifier le compte">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @if(! $isMe)
                                    <form method="POST" action="{{ route('employes.destroy', $emp) }}" class="inline"
                                          onsubmit="return confirm('Supprimer définitivement le compte de {{ $emp->nom }} ?');">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="action-btn p-2 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white transition-all duration-200 hover:scale-110"
                                                title="Supprimer le compte">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="relative mb-5">
                                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-400 rotate-6">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                    </div>
                                    <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-amber-100 border-2 border-white flex items-center justify-center">
                                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </div>
                                </div>
                                <p class="font-bold text-slate-700 text-base">Aucun employé enregistré</p>
                                <p class="text-xs text-slate-400 mt-1 max-w-xs">Commencez par ajouter votre premier collaborateur.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
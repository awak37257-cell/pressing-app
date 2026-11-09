<div class="line-item group relative rounded-xl border border-slate-200/80 bg-gradient-to-br from-slate-50/70 to-white p-4 transition-all duration-200 hover:border-amber-300 hover:shadow-md" data-index="{{ $index }}">
    <div class="flex items-center gap-3 mb-3">
        <div class="line-number w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-xs font-extrabold text-slate-500 transition-all duration-200">1</div>
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Article</span>
    </div>
    <div class="grid md:grid-cols-12 gap-3">
        <div class="md:col-span-5">
            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Type d'article <span class="text-rose-500">*</span></label>
            <div class="relative">
                <select name="articles[{{ $index }}][type_article_id]" class="appearance-none w-full pl-3 pr-8 py-2.5 rounded-lg border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer" required>
                    <option value="">— Sélectionner —</option>
                    @foreach($typesArticles as $type)
                        <option value="{{ $type->id }}" @selected(($data['type_article_id'] ?? '') == $type->id)>{{ $type->nom }}</option>
                    @endforeach
                </select>
                <svg class="w-3.5 h-3.5 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </div>
            @if($errorTypeArticle)
                <p class="mt-2 text-[11px] font-bold text-rose-700 bg-rose-50 border border-rose-200/60 p-2 rounded-lg">{{ $errorTypeArticle }}</p>
            @endif
        </div>
        <div class="md:col-span-4">
            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Service <span class="text-rose-500">*</span></label>
            <div class="relative">
                <select name="articles[{{ $index }}][service_id]" class="appearance-none w-full pl-3 pr-8 py-2.5 rounded-lg border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all cursor-pointer" required>
                    <option value="">— Sélectionner —</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" @selected(($data['service_id'] ?? '') == $service->id)>{{ $service->nom }}</option>
                    @endforeach
                </select>
                <svg class="w-3.5 h-3.5 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </div>
            @if($errorService)
                <p class="mt-2 text-[11px] font-bold text-rose-700 bg-rose-50 border border-rose-200/60 p-2 rounded-lg">{{ $errorService }}</p>
            @endif
        </div>
        <div class="md:col-span-3">
            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Quantité <span class="text-rose-500">*</span></label>
            <input type="number" name="articles[{{ $index }}][quantite]" min="1" max="999" value="{{ $data['quantite'] ?? 1 }}" inputmode="numeric"
                   class="w-full px-3 py-2.5 rounded-lg border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all" required>
            @if($errorQuantite)
                <p class="mt-2 text-[11px] font-bold text-rose-700 bg-rose-50 border border-rose-200/60 p-2 rounded-lg">{{ $errorQuantite }}</p>
            @endif
        </div>
    </div>
    <div class="mt-3 pt-3 flex items-center justify-between gap-3 text-xs border-t border-slate-200/60">
        <p class="text-slate-500 font-medium">Prix : <span class="prix font-extrabold text-slate-800">—</span> <span class="text-[10px] text-slate-400">FCFA</span></p>
        <div class="flex items-center gap-3">
            <p class="text-slate-500 font-medium">Sous-total : <span class="st font-extrabold text-slate-900 text-sm">0</span> <span class="text-[10px] text-slate-400">FCFA</span></p>
            <button type="button" class="sup inline-flex items-center justify-center w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white font-bold transition-all duration-200" aria-label="Supprimer">✕</button>
        </div>
    </div>
    <p class="ligne-hint hidden mt-2 text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-200/60 p-2 rounded-lg">⚠ Aucun tarif défini pour cette combinaison article / service.</p>
    <p class="ligne-erreur hidden mt-2 text-[11px] font-bold text-rose-700 bg-rose-50 border border-rose-200/60 p-2 rounded-lg"></p>
</div>

<!-- MUTASI AKUN / REKENING KORAN MODAL -->
<div id="accountMutationsModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-4xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="history" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 id="mutationModalTitle" class="font-extrabold text-base text-slate-900 tracking-tight">Buku Mutasi Rekening</h3>
                    <p id="mutationModalSubtitle" class="text-xs text-slate-400">Riwayat transaksi masuk & keluar saldo akun</p>
                </div>
            </div>
            <button onclick="closeModal('accountMutationsModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Info Card & Quick Stats -->
        <div class="px-6 py-3.5 bg-gradient-to-r from-slate-50 to-white border-b border-slate-100 grid grid-cols-1 sm:grid-cols-3 gap-3 shrink-0">
            <div class="p-3 rounded-xl bg-white border border-slate-200/80 shadow-2xs">
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Saldo Saat Ini</span>
                <span id="mutationCurrentBalance" class="text-lg font-black font-mono-num text-slate-900 mt-0.5 block">Rp 0</span>
            </div>
            <div class="p-3 rounded-xl bg-white border border-slate-200/80 shadow-2xs">
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Tipe / Kategori</span>
                <span id="mutationAccountTypeBadge" class="mt-1 inline-block">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">-</span>
                </span>
            </div>
            <div class="p-3 rounded-xl bg-white border border-slate-200/80 shadow-2xs">
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">No. Rekening / Provider</span>
                <span id="mutationAccountNumber" class="text-xs font-bold font-mono text-slate-700 mt-1 block">-</span>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="px-6 py-3 border-b border-slate-100 bg-white flex flex-wrap items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500">Filter:</span>
                <select id="mutationTypeFilter" onchange="loadAccountMutations(currentActiveAccountId, 1)" class="text-xs font-semibold rounded-lg border border-slate-200 px-2.5 py-1.5 bg-slate-50 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value="">Semua Mutasi</option>
                    <option value="debit">Pengeluaran / Terpotong (- Debit)</option>
                    <option value="credit">Pemasukan / Top Up (+ Kredit)</option>
                </select>
            </div>
            <div class="text-[11px] text-slate-400 font-medium">
                Menampilkan transaksi terbaru
            </div>
        </div>

        <!-- Table Body -->
        <div class="p-6 overflow-y-auto flex-1">
            <div class="rounded-xl border border-slate-200/90 overflow-hidden bg-white shadow-2xs">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-slate-600 font-bold text-[11px] border-b border-slate-200 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Jenis</th>
                            <th class="py-3 px-4 text-right">Nominal</th>
                            <th class="py-3 px-4 text-right">Saldo Sesudah</th>
                            <th class="py-3 px-4">Keterangan / Ref</th>
                            <th class="py-3 px-4">User</th>
                        </tr>
                    </thead>
                    <tbody id="mutationTableBody" class="divide-y divide-slate-100">
                        <!-- Loaded by JS -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination Container -->
            <div id="mutationPagination" class="mt-4 flex items-center justify-between text-xs text-slate-500">
                <!-- Loaded by JS -->
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-end shrink-0">
            <button type="button" onclick="closeModal('accountMutationsModal')" class="px-5 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-200/70 transition cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    let currentActiveAccountId = null;

    function openAccountMutationsModal(accountId) {
        currentActiveAccountId = accountId;
        const modal = document.getElementById('accountMutationsModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        // Reset filter
        const filterEl = document.getElementById('mutationTypeFilter');
        if (filterEl) filterEl.value = '';

        loadAccountMutations(accountId, 1);
    }

    function loadAccountMutations(accountId, page = 1) {
        if (!accountId) return;

        const tbody = document.getElementById('mutationTableBody');
        const pagination = document.getElementById('mutationPagination');
        const type = document.getElementById('mutationTypeFilter')?.value || '';

        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="py-8 text-center text-slate-400">
                    <div class="inline-block animate-spin w-5 h-5 border-2 border-brand-500 border-t-transparent rounded-full mb-2"></div>
                    <p class="font-medium">Memuat data mutasi rekening...</p>
                </td>
            </tr>
        `;

        let url = `/accounts/${accountId}/mutations?page=${page}`;
        if (type) url += `&type=${type}`;

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const acc = res.account;
                const data = res.mutations;

                // Update Header info
                document.getElementById('mutationModalTitle').innerText = `Buku Mutasi: ${acc.name}`;
                document.getElementById('mutationModalSubtitle').innerText = `Kode Akun: ${acc.account_code}`;
                document.getElementById('mutationCurrentBalance').innerText = `Rp ${Math.round(parseFloat(acc.current_balance || 0)).toLocaleString('id-ID')}`;
                
                const typeMap = {
                    'cash': '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Kas Fisik</span>',
                    'bank': '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Bank Ops</span>',
                    'bank_agent': '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">Agen Bank</span>',
                    'ppob_provider': '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Server PPOB</span>',
                    'other': '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">Lainnya</span>',
                };
                document.getElementById('mutationAccountTypeBadge').innerHTML = typeMap[acc.type] || '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">Akun</span>';
                document.getElementById('mutationAccountNumber').innerText = acc.account_number ? `${acc.bank_name || ''} - ${acc.account_number}` : (acc.bank_name || 'Kas Internal');

                // Render Table
                if (!data.data || data.data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">
                                <i data-lucide="receipt-text" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                <p class="font-bold text-slate-600">Belum Ada Riwayat Mutasi</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Transaksi masuk dan keluar pada akun ini akan otomatis tercatat di sini.</p>
                            </td>
                        </tr>
                    `;
                    pagination.innerHTML = '';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    return;
                }

                let html = '';
                data.data.forEach(m => {
                    const isDebit = m.mutation_type === 'debit';
                    const amountFormatted = Math.round(parseFloat(m.amount)).toLocaleString('id-ID');
                    const balanceAfterFormatted = Math.round(parseFloat(m.balance_after)).toLocaleString('id-ID');
                    const dateFormatted = new Date(m.created_at).toLocaleString('id-ID', {
                        day: '2-digit', month: 'short', year: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                    });

                    html += `
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 whitespace-nowrap text-slate-500 font-mono text-[11px]">
                                ${dateFormatted}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                ${isDebit 
                                    ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-rose-50 text-rose-600 border border-rose-200/80"><i data-lucide="arrow-down-right" class="w-3 h-3"></i> KELUAR (DB)</span>'
                                    : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-50 text-emerald-600 border border-emerald-200/80"><i data-lucide="arrow-up-right" class="w-3 h-3"></i> MASUK (CR)</span>'
                                }
                            </td>
                            <td class="py-3 px-4 text-right font-black font-mono-num whitespace-nowrap ${isDebit ? 'text-rose-600' : 'text-emerald-600'}">
                                ${isDebit ? '-' : '+'}Rp ${amountFormatted}
                            </td>
                            <td class="py-3 px-4 text-right font-bold font-mono-num whitespace-nowrap text-slate-800">
                                Rp ${balanceAfterFormatted}
                            </td>
                            <td class="py-3 px-4 text-slate-700 min-w-[220px]">
                                <div class="font-medium">${m.description || '-'}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-slate-500 text-[11px]">
                                ${m.user ? m.user.name : '-'}
                            </td>
                        </tr>
                    `;
                });

                tbody.innerHTML = html;

                // Render simple pagination
                let pagHtml = `<div>Menampilkan ${data.from || 0} - ${data.to || 0} dari total ${data.total || 0} mutasi</div><div class="flex items-center gap-1.5">`;
                if (data.prev_page_url) {
                    pagHtml += `<button type="button" onclick="loadAccountMutations(${accountId}, ${data.current_page - 1})" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Prev</button>`;
                }
                if (data.next_page_url) {
                    pagHtml += `<button type="button" onclick="loadAccountMutations(${accountId}, ${data.current_page + 1})" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Next</button>`;
                }
                pagHtml += `</div>`;
                pagination.innerHTML = pagHtml;

                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = `<tr><td colspan="6" class="py-6 text-center text-rose-500 font-bold">Gagal mengambil data mutasi rekening.</td></tr>`;
        });
    }
</script>

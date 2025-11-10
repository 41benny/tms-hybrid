@extends('layouts.app', ['title' => 'AI Assistant'])

@section('content')
    <x-card title="AI Assistant — Analisa TMS & Keuangan">
        <div id="chat" class="h-80 overflow-y-auto space-y-3">
            <div class="text-sm text-slate-500">Mulai percakapan. Contoh: "Tunjukkan invoice yang jatuh tempo minggu ini".</div>
        </div>
        <form id="chat-form" class="mt-4 flex items-center gap-2">
            <input type="text" id="question" name="question" placeholder="Ketik pertanyaan Anda..." class="flex-1 rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2" />
            <button class="px-4 py-2 rounded bg-indigo-600 text-white">Kirim</button>
        </form>
        <div class="mt-3 flex flex-wrap gap-2">
            <button class="px-2 py-1 rounded bg-slate-200 dark:bg-slate-800 text-sm quick">Tunjukkan daftar invoice yang sudah jatuh tempo minggu ini</button>
            <button class="px-2 py-1 rounded bg-slate-200 dark:bg-slate-800 text-sm quick">Berapa laba rugi bulan ini?</button>
            <button class="px-2 py-1 rounded bg-slate-200 dark:bg-slate-800 text-sm quick">Customer mana yang paling besar piutangnya?</button>
            <button class="px-2 py-1 rounded bg-slate-200 dark:bg-slate-800 text-sm quick">Vendor mana yang paling sering terlambat?</button>
        </div>
    </x-card>

    <script>
    const chat = document.getElementById('chat');
    const form = document.getElementById('chat-form');
    const question = document.getElementById('question');
    function addBubble(text, who){
        const el = document.createElement('div');
        el.className = `max-w-[80%] px-3 py-2 rounded ${who==='me' ? 'ml-auto bg-indigo-600 text-white' : 'bg-slate-200 dark:bg-slate-800'}`;
        el.textContent = text;
        chat.appendChild(el); chat.scrollTop = chat.scrollHeight;
    }
    async function ask(q){
        if(!q) return; addBubble(q,'me'); question.value='';
        const wait = document.createElement('div'); wait.className='text-xs text-slate-500'; wait.textContent='Memproses...'; chat.appendChild(wait); chat.scrollTop=chat.scrollHeight;
        try {
            const res = await fetch("{{ route('ai-assistant.ask') }}", {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: JSON.stringify({question:q})});
            const data = await res.json();
            wait.remove();
            addBubble(data.answer || 'Tidak ada jawaban','ai');
        } catch(e){ wait.remove(); addBubble('Gagal memproses pertanyaan.','ai'); }
    }
    form.addEventListener('submit', (e)=>{ e.preventDefault(); ask(question.value); });
    document.querySelectorAll('.quick').forEach(btn=> btn.addEventListener('click', ()=> ask(btn.textContent)));
    </script>
@endsection


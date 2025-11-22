@extends('layouts.app', ['title' => 'AI Assistant'])

@section('content')
<div class="h-[calc(100vh-8rem)] flex flex-col bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800 relative">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white/80 dark:bg-slate-900/80 backdrop-blur-md z-10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2 2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Z"/><path d="m4.93 10.93 1.41 1.41"/><path d="M2 12h2"/><path d="m4.93 13.07 1.41-1.41"/><path d="m19.07 10.93-1.41 1.41"/><path d="M20 12h2"/><path d="m19.07 13.07-1.41-1.41"/><path d="M12 22v-2"/><path d="m17.66 17.66-1.41-1.41"/><path d="m6.34 17.66 1.41-1.41"/><path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/></svg>
            </div>
            <div>
                <h2 class="font-bold text-slate-800 dark:text-white text-lg">Gemini Assistant</h2>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs text-slate-500 font-medium">Online & Ready to Help</span>
                </div>
            </div>
        </div>
        <button onclick="window.location.reload()" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full transition-colors text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
        </button>
    </div>

    <!-- Chat Area -->
    <div id="chat-container" class="flex-1 overflow-y-auto p-6 space-y-6 scroll-smooth bg-slate-50 dark:bg-slate-950/50">
        <!-- Welcome Message -->
        <div class="flex gap-4 max-w-3xl mx-auto">
            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex-shrink-0 flex items-center justify-center text-white text-xs">AI</div>
            <div class="flex-1 space-y-2">
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl rounded-tl-none shadow-sm border border-slate-100 dark:border-slate-700 text-slate-700 dark:text-slate-200 leading-relaxed">
                    <p>Halo! Saya adalah asisten AI Anda yang telah terintegrasi dengan sistem TMS.</p>
                    <p class="mt-2">Saya dapat membantu Anda dengan:</p>
                    <ul class="list-disc list-inside mt-1 space-y-1 text-sm opacity-80">
                        <li>Menganalisa data keuangan (Piutang, Hutang, Laba Rugi)</li>
                        <li>Memantau status Job Order dan Armada</li>
                        <li>Mengecek performa Vendor dan Driver</li>
                        <li>Melihat aktivitas terbaru dalam sistem</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Input Area -->
    <div class="p-4 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
        <div class="max-w-3xl mx-auto space-y-4">
            <!-- Quick Actions -->
            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide" id="quick-actions">
                <button class="quick-btn whitespace-nowrap px-3 py-1.5 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 text-xs font-medium border border-indigo-100 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">
                    📊 Laba rugi bulan ini
                </button>
                <button class="quick-btn whitespace-nowrap px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-300 text-xs font-medium border border-emerald-100 dark:border-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors">
                    💰 Top Customer Piutang
                </button>
                <button class="quick-btn whitespace-nowrap px-3 py-1.5 rounded-full bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-300 text-xs font-medium border border-rose-100 dark:border-rose-800 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors">
                    ⚠️ Cek Piutang Macet
                </button>
                <button class="quick-btn whitespace-nowrap px-3 py-1.5 rounded-full bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-300 text-xs font-medium border border-purple-100 dark:border-purple-800 hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors">
                    📈 Analisa Margin
                </button>
                <button class="quick-btn whitespace-nowrap px-3 py-1.5 rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-300 text-xs font-medium border border-amber-100 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-colors">
                    🚚 Status Armada
                </button>
                <button class="quick-btn whitespace-nowrap px-3 py-1.5 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 text-xs font-medium border border-blue-100 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                    🕒 Aktivitas Terbaru
                </button>
            </div>

            <form id="chat-form" class="relative flex items-end gap-2">
                <div class="relative flex-1">
                    <textarea 
                        id="question" 
                        name="question" 
                        rows="1"
                        placeholder="Tanyakan sesuatu atau gunakan perintah suara..." 
                        class="w-full rounded-xl bg-slate-100 dark:bg-slate-800 border-0 focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white px-4 py-3 pr-12 resize-none scrollbar-hide"
                        style="min-height: 48px; max-height: 120px;"
                    ></textarea>
                    <button type="button" id="mic-btn" class="absolute right-3 bottom-2.5 p-1.5 text-slate-400 hover:text-indigo-600 transition-colors rounded-full hover:bg-slate-200 dark:hover:bg-slate-700">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><path d="M12 19v4"/><path d="M8 23h8"/></svg>
                    </button>
                </div>
                <button type="submit" class="h-12 w-12 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center shadow-lg shadow-indigo-500/30 transition-all hover:scale-105 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                </button>
            </form>
            <div class="text-center">
                <p class="text-[10px] text-slate-400">AI dapat membuat kesalahan. Mohon verifikasi informasi penting.</p>
            </div>
        </div>
    </div>
</div>

<!-- Markdown Parser -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const chatContainer = document.getElementById('chat-container');
    const form = document.getElementById('chat-form');
    const input = document.getElementById('question');
    const submitBtn = form.querySelector('button[type="submit"]');
    const micBtn = document.getElementById('mic-btn');

    // Speech Recognition Setup
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (SpeechRecognition) {
        const recognition = new SpeechRecognition();
        recognition.lang = 'id-ID'; // Indonesian
        recognition.continuous = false;
        recognition.interimResults = false;

        micBtn.addEventListener('click', () => {
            if (micBtn.classList.contains('listening')) {
                recognition.stop();
            } else {
                recognition.start();
            }
        });

        recognition.onstart = () => {
            micBtn.classList.add('listening', 'text-red-500', 'animate-pulse');
            micBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><line x1="1" y1="1" x2="23" y2="23"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><path d="M12 19v4"/><path d="M8 23h8"/></svg>'; // Change to stop icon or similar if needed, or keep mic but red
        };

        recognition.onend = () => {
            micBtn.classList.remove('listening', 'text-red-500', 'animate-pulse');
            micBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><path d="M12 19v4"/><path d="M8 23h8"/></svg>';
        };

        recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            input.value = transcript;
            // Auto submit for "Agentic" feel
            setTimeout(() => ask(transcript), 500);
        };

        recognition.onerror = (event) => {
            console.error('Speech recognition error', event.error);
            micBtn.classList.remove('listening');
        };
    } else {
        micBtn.style.display = 'none'; // Hide if not supported
    }

    // Auto-resize textarea
    input.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Handle Quick Actions
    document.querySelectorAll('.quick-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const text = btn.textContent.trim().replace(/^[^\w\s]+/, '').trim(); // Remove emoji
            ask(text);
        });
    });

    // Handle Form Submit
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const q = input.value.trim();
        if (q) ask(q);
    });

    // Handle Enter key (Shift+Enter for new line)
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
        }
    });

    async function ask(q) {
        if (!q) return;
        
        // Disable input
        input.value = '';
        input.style.height = 'auto';
        input.disabled = true;
        submitBtn.disabled = true;

        // Add User Bubble
        addBubble(q, 'me');

        // Add Loading Bubble
        const loadingId = addLoadingBubble();

        try {
            const res = await fetch("{{ route('ai-assistant.ask') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ question: q })
            });

            const data = await res.json();
            
            // Remove loading
            document.getElementById(loadingId).remove();

            // Add AI Response
            addBubble(data.answer || 'Maaf, saya tidak dapat menemukan jawaban untuk pertanyaan tersebut.', 'ai');

            // Handle Action if present
            if (data.action && data.action.type === 'navigate') {
                setTimeout(() => {
                    window.location.href = data.action.url;
                }, 1500); // Wait 1.5s so user can read the message
            }

        } catch (e) {
            console.error(e);
            document.getElementById(loadingId).remove();
            addBubble('Terjadi kesalahan saat memproses permintaan Anda. Silakan coba lagi.', 'ai');
        } finally {
            input.disabled = false;
            submitBtn.disabled = false;
            input.focus();
        }
    }

    function addBubble(text, who) {
        const wrapper = document.createElement('div');
        wrapper.className = 'flex gap-4 max-w-3xl mx-auto animate-fade-in-up';
        
        if (who === 'me') {
            wrapper.classList.add('flex-row-reverse');
            wrapper.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex-shrink-0 flex items-center justify-center text-slate-600 dark:text-slate-300 text-xs font-bold">ME</div>
                <div class="max-w-[80%] space-y-1">
                    <div class="bg-indigo-600 text-white px-4 py-3 rounded-2xl rounded-tr-none shadow-md text-sm leading-relaxed">
                        ${escapeHtml(text)}
                    </div>
                </div>
            `;
        } else {
            // Parse Markdown for AI response
            const htmlContent = marked.parse(text);
            
            wrapper.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex-shrink-0 flex items-center justify-center text-white text-xs font-bold">AI</div>
                <div class="flex-1 space-y-1">
                    <div class="bg-white dark:bg-slate-800 px-5 py-4 rounded-2xl rounded-tl-none shadow-sm border border-slate-100 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm leading-relaxed prose dark:prose-invert max-w-none">
                        ${htmlContent}
                    </div>
                </div>
            `;
        }

        chatContainer.appendChild(wrapper);
        scrollToBottom();
    }

    function addLoadingBubble() {
        const id = 'loading-' + Date.now();
        const wrapper = document.createElement('div');
        wrapper.id = id;
        wrapper.className = 'flex gap-4 max-w-3xl mx-auto animate-pulse';
        wrapper.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex-shrink-0 flex items-center justify-center text-white text-xs">AI</div>
            <div class="flex-1 space-y-2">
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl rounded-tl-none shadow-sm border border-slate-100 dark:border-slate-700 flex items-center gap-2">
                    <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                    <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                </div>
            </div>
        `;
        chatContainer.appendChild(wrapper);
        scrollToBottom();
        return id;
    }

    function scrollToBottom() {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>

<style>
    /* Custom Scrollbar for Chat */
    #chat-container::-webkit-scrollbar {
        width: 6px;
    }
    #chat-container::-webkit-scrollbar-track {
        background: transparent;
    }
    #chat-container::-webkit-scrollbar-thumb {
        background-color: rgba(156, 163, 175, 0.3);
        border-radius: 20px;
    }
    
    /* Hide scrollbar for quick actions but allow scroll */
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fade-in-up 0.3s ease-out forwards;
    }
</style>
@endsection

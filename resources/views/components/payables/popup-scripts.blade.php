<script>
if(!window._payablesPopup){
    window._payablesPopup = true;
    window.showPayablesPopup = function(id, url, renderer){
        const popup = document.getElementById(id);
        const content = document.getElementById(id+'-content');
        if(!popup || !content) return;
        popup.classList.remove('hidden');
        content.innerHTML = '<div class="flex items-center justify-center py-12 text-slate-500 dark:text-slate-400"><svg class="animate-spin h-6 w-6" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';
        fetch(url)
            .then(r=>r.json())
            .then(data=>{
                try {
                    content.innerHTML = renderer(data);
                } catch(e){
                    content.innerHTML = '<div class="text-center py-12 text-rose-500 text-sm">Gagal memproses data</div>';
                }
            })
            .catch(()=>{
                content.innerHTML = '<div class="text-center py-12 text-rose-500 text-sm">Gagal memuat data</div>';
            });
    };
    window.closePayablesPopup = function(id){
        const popup = document.getElementById(id);
        if(popup){ popup.classList.add('hidden'); }
    };
}
</script>

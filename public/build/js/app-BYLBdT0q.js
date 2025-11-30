import{a as k}from"./vendor-B9ygI19o.js";window.axios=k;window.axios.defaults.headers.common["X-Requested-With"]="XMLHttpRequest";const x="tms-theme";document.readyState==="loading"?document.addEventListener("DOMContentLoaded",()=>{m(),u()}):(m(),u());function m(){const i=document.getElementById("theme-toggle");if(!i){console.warn("Theme toggle button not found");return}console.log("Theme toggle initialized"),i.addEventListener("click",()=>{const n=document.documentElement.classList.toggle("dark");localStorage.setItem(x,n?"dark":"light"),console.log("Theme toggled to:",n?"dark":"light")})}function u(){const i=document.getElementById("notification-button"),o=document.getElementById("notification-badge"),n=document.getElementById("notification-list"),d=document.getElementById("mark-all-read");if(!i||!o||!n)return;function a(){fetch("/notifications/count",{headers:{"X-Requested-With":"XMLHttpRequest",Accept:"application/json"},credentials:"same-origin"}).then(e=>e.json()).then(e=>{const t=e.count||0;t>0?(o.textContent=t>99?"99+":t,o.classList.remove("hidden")):o.classList.add("hidden")}).catch(e=>{console.error("Error fetching notification count:",e)})}function r(){fetch("/notifications",{headers:{"X-Requested-With":"XMLHttpRequest",Accept:"application/json"},credentials:"same-origin"}).then(e=>e.json()).then(e=>{const t=e.notifications||[];if(t.length===0){n.innerHTML='<div class="p-4 text-center text-sm text-slate-500 dark:text-slate-400">Tidak ada notifikasi</div>';return}n.innerHTML=t.map(c=>{const l=c.read_at!==null,s=c.data||{},f=s.payment_request_id,h=s.request_number||"N/A",g=s.amount?new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR"}).format(s.amount):"N/A",p=s.requested_by_name||"Unknown";return`
            <div class="p-3 border-b border-slate-200 dark:border-[#2d2d2d] hover:bg-slate-50 dark:hover:bg-[#2d2d2d] ${l?"":"bg-indigo-50 dark:bg-indigo-900/20"}">
              <a href="/payment-requests/${f}" class="block" onclick="markNotificationAsRead('${c.id}')">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                      Pengajuan Pembayaran Baru
                    </p>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                      ${h} - ${g}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">
                      Oleh: ${p}
                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                      ${c.created_at}
                    </p>
                  </div>
                  ${l?"":'<div class="w-2 h-2 bg-indigo-600 rounded-full mt-1"></div>'}
                </div>
              </a>
            </div>
          `}).join("")}).catch(e=>{console.error("Error fetching notifications:",e),n.innerHTML='<div class="p-4 text-center text-sm text-red-500">Error memuat notifikasi</div>'})}window.markNotificationAsRead=function(e){fetch(`/notifications/${e}/read`,{method:"POST",headers:{"X-Requested-With":"XMLHttpRequest",Accept:"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},credentials:"same-origin"}).then(t=>t.json()).then(t=>{t.success&&(a(),r())}).catch(t=>{console.error("Error marking notification as read:",t)})},d&&d.addEventListener("click",function(e){e.preventDefault(),fetch("/notifications/read-all",{method:"POST",headers:{"X-Requested-With":"XMLHttpRequest",Accept:"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},credentials:"same-origin"}).then(t=>t.json()).then(t=>{t.success&&(a(),r())}).catch(t=>{console.error("Error marking all notifications as read:",t)})}),i.addEventListener("click",function(){r()}),a(),setInterval(a,6e4),document.addEventListener("visibilitychange",()=>{document.hidden||a()})}

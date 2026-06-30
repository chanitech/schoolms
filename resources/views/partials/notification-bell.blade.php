{{-- Notification Bell — include in @section('content_top_nav_right') --}}
<li class="nav-item dropdown" id="notif-bell-li">
    <a class="nav-link" href="#" data-toggle="dropdown" id="notif-bell-toggle">
        <i class="far fa-bell"></i>
        @if($_unreadNotifCount > 0)
        <span class="badge badge-danger navbar-badge" id="notif-badge">{{ $_unreadNotifCount > 99 ? '99+' : $_unreadNotifCount }}</span>
        @else
        <span class="badge badge-danger navbar-badge d-none" id="notif-badge"></span>
        @endif
    </a>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="min-width:320px">
        <div class="dropdown-item d-flex justify-content-between align-items-center py-2 border-bottom">
            <strong style="font-size:.85rem">Notifications</strong>
            <button class="btn btn-link btn-sm p-0 text-muted" id="notif-mark-all" style="font-size:.75rem">Mark all read</button>
        </div>
        <div id="notif-list" style="max-height:340px;overflow-y:auto">
            <div class="text-center py-3 text-muted" style="font-size:.82rem">
                <i class="fas fa-spinner fa-spin"></i> Loading…
            </div>
        </div>
        <div class="dropdown-divider mb-0"></div>
        <a href="{{ route('notifications.index') }}" class="dropdown-item text-center py-2" style="font-size:.8rem;color:#4e73df">
            <i class="fas fa-list mr-1"></i>View all notifications
        </a>
    </div>
</li>

@once
@push('js')
<script>
(function () {
    const RECENT_URL   = "{{ route('notifications.recent') }}";
    const READ_URL     = "{{ url('notifications') }}";
    const MARK_ALL_URL = "{{ route('notifications.mark-all-read') }}";
    const CSRF         = "{{ csrf_token() }}";

    function colorClass(c) {
        const map = { success:'#28a745', danger:'#dc3545', warning:'#f59e0b', info:'#17a2b8', secondary:'#6c757d' };
        return map[c] || '#4e73df';
    }

    function renderNotif(n) {
        const unreadStyle = n.read ? '' : 'background:#f0f4ff;';
        return `<a href="${n.url}" class="dropdown-item notif-item border-bottom py-2"
                   data-id="${n.id}" style="${unreadStyle}font-size:.8rem">
            <div class="d-flex align-items-start gap-2" style="gap:.6rem">
                <div style="width:30px;height:30px;border-radius:50%;background:${colorClass(n.color)};
                    display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="${n.icon} text-white" style="font-size:.65rem"></i>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-weight:600;color:#1a1a2e">${n.title}</div>
                    <div class="text-muted" style="white-space:normal;line-height:1.3">${n.message}</div>
                    <div class="text-muted" style="font-size:.7rem;margin-top:2px">${n.time}</div>
                </div>
                ${n.read ? '' : '<div style="width:7px;height:7px;border-radius:50%;background:#4e73df;margin-top:4px;flex-shrink:0"></div>'}
            </div>
        </a>`;
    }

    function loadNotifs() {
        fetch(RECENT_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                const list   = document.getElementById('notif-list');
                const badge  = document.getElementById('notif-badge');
                const count  = data.unread_count;

                if (data.notifications.length === 0) {
                    list.innerHTML = '<div class="text-center py-3 text-muted" style="font-size:.82rem"><i class="far fa-bell-slash"></i><br>No notifications</div>';
                } else {
                    list.innerHTML = data.notifications.map(renderNotif).join('');
                }

                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.classList.remove('d-none');
                } else {
                    badge.textContent = '';
                    badge.classList.add('d-none');
                }
            })
            .catch(() => {});
    }

    // Load on dropdown open
    document.getElementById('notif-bell-toggle').addEventListener('click', loadNotifs);

    // Mark individual as read when clicking
    document.getElementById('notif-list').addEventListener('click', function (e) {
        const item = e.target.closest('.notif-item');
        if (!item) return;
        const id = item.dataset.id;
        fetch(`${READ_URL}/${id}/read`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
        }).catch(() => {});
    });

    // Mark all read
    document.getElementById('notif-mark-all').addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        fetch(MARK_ALL_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
        }).then(() => loadNotifs()).catch(() => {});
    });

    // Poll for new notifications every 60 seconds
    setInterval(function () {
        fetch("{{ route('notifications.count') }}", { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('notif-badge');
                if (data.count > 0) {
                    badge.textContent = data.count > 99 ? '99+' : data.count;
                    badge.classList.remove('d-none');
                } else {
                    badge.textContent = '';
                    badge.classList.add('d-none');
                }
            }).catch(() => {});
    }, 60000);
})();
</script>
@endpush
@endonce

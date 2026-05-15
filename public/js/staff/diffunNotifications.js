/*
  Injects a notification dropdown into any page with a `.top-bar` element
  and polls the server for new users and pending Diffun appointments.
*/
(function(){
  const POLL_INTERVAL = 15000; // ms
  const ENDPOINT = '/diffun-staff/notifications';
  const STORAGE_KEY = 'diffun_last_seen';

  function isoNowMinus(minutes){
    return new Date(Date.now() - minutes*60000).toISOString();
  }

  function createDropdownHtml(){
    return `
      <div class="notification-container" id="diffun-notification-container" style="position:relative;margin-left:12px">
        <button id="diffunNotificationBtn" class="notification-btn btn btn-light" style="position:relative">
          <i class="fas fa-bell"></i>
          <span id="diffunNotificationBadge" class="badge" style="display:none;position:absolute;top:-6px;right:-6px;background:#ff4757;color:#fff;padding:2px 6px;border-radius:12px;font-size:11px">0</span>
        </button>
        <div id="diffunNotificationDropdown" class="notification-dropdown" style="display:none;position:absolute;right:0;top:40px;z-index:9999;width:360px;background:#fff;border:1px solid #e6e6e6;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,0.08);overflow:hidden">
          <div class="notification-header" style="padding:8px 12px;border-bottom:1px solid #f0f0f0;background:#fafafa;display:flex;justify-content:space-between;align-items:center">
            <strong>Notifications</strong>
            <div style="display:flex;align-items:center;gap:8px">
              <button id="diffunMarkAllBtn" class="btn btn-sm btn-outline-secondary" style="font-size:11px;padding:3px 8px">Mark all as read</button>
              <small id="diffunNotificationTime" style="color:#888;font-size:12px"></small>
            </div>
          </div>
          <div id="diffunNotificationList" class="notification-list" style="max-height:320px;overflow:auto;padding:8px"></div>
          <div style="padding:8px;border-top:1px solid #f0f0f0;background:#fafafa;text-align:center;font-size:13px;color:#666">
            <a href="/StaffClientstbl" style="text-decoration:none">View all</a>
          </div>
        </div>
      </div>
    `;
  }

  function injectIntoTopBar(){
    const top = document.querySelector('.top-bar');
    if (!top) return false;

    let targetContainer = top.querySelector('#diffun-notification-container') || top.querySelector('.notification-container');
    if (targetContainer && targetContainer.id !== 'diffun-notification-container') {
      targetContainer.id = 'diffun-notification-container';
      const existingDropdown = targetContainer.querySelector('.notification-dropdown');
      if (!existingDropdown) {
        targetContainer.innerHTML = createDropdownHtml();
      } else {
        let markBtn = existingDropdown.querySelector('#markAllReadBtn') || existingDropdown.querySelector('#diffunMarkAllBtn');
        if (!markBtn) {
          const header = existingDropdown.querySelector('.notification-header');
          if (header) {
            const wrapper = document.createElement('div');
            wrapper.style.display = 'inline-flex';
            wrapper.style.gap = '8px';
            wrapper.innerHTML = `<button id="diffunMarkAllBtn" class="btn btn-sm btn-link">Mark all as read</button>`;
            const actions = header.querySelector('.notification-actions');
            if (actions) actions.insertBefore(wrapper, actions.firstChild);
            else header.appendChild(wrapper);
          }
        }
      }
    }

    if (!targetContainer) {
      const wrapper = document.createElement('div');
      wrapper.innerHTML = createDropdownHtml();

      const logoutBtn = top.querySelector('.logout-btn');
      try {
        if (logoutBtn && logoutBtn.parentNode) {
          logoutBtn.parentNode.insertBefore(wrapper, logoutBtn);
        } else {
          top.appendChild(wrapper);
        }
      } catch (e) {
        top.appendChild(wrapper);
      }
      targetContainer = document.getElementById('diffun-notification-container');
    }

    const btn = document.getElementById('diffunNotificationBtn');
    const dropdown = document.getElementById('diffunNotificationDropdown');
    if (btn) {
      btn.addEventListener('click', function(e){
        e.stopPropagation();
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        if (dropdown.style.display === 'block') {
          localStorage.setItem(STORAGE_KEY, new Date().toISOString());
          const b = document.getElementById('diffunNotificationBadge');
          if (b) b.style.display = 'none';
        }
      });
    }

    const markAllBtn = document.getElementById('diffunMarkAllBtn') || document.getElementById('markAllReadBtn');
    if (markAllBtn) {
      markAllBtn.addEventListener('click', function(e){
        e.stopPropagation();
        markAllNotifications().then(() => {
          const container = document.getElementById('diffun-notification-container');
          const badge = document.getElementById('diffunNotificationBadge');
          const listEl = document.getElementById('diffunNotificationList') || document.getElementById('notificationList');
          if (listEl) listEl.innerHTML = '<div style="padding:12px;color:#666">No new notifications</div>';
          if (container) container.style.display = '';
          if (badge) badge.style.display = 'none';
        });
      });
      try { markAllBtn.style.display = ''; } catch (e) {}
    }

    document.addEventListener('click', function(){
      const dd = document.getElementById('diffunNotificationDropdown');
      if (dd) dd.style.display = 'none';
    });
    return true;
  }

  async function pollNotifications(){
    const last = localStorage.getItem(STORAGE_KEY) || isoNowMinus(60);
    const url = ENDPOINT + '?since=' + encodeURIComponent(last);
    try {
      const res = await fetch(url, {headers: {'Accept':'application/json'}});
      if (!res.ok) return;
      const data = await res.json();
      if (!data.success) return;

      const rawApps = data.new_appointments || [];
      const rawStaff = data.staff_notifications || [];
      const apps = [];

      rawApps.forEach(a => {
        const s = (a.status || a.appointment_status || a.app_status || a.appointment_approval || '').toString().toLowerCase();
        if (s === 'pending' || a.is_pending === true || a.pending === true || s === 'awaiting') {
          apps.push(Object.assign({}, a, {notif_type: 'appointment', notif_id: a.id}));
        }
      });

      rawStaff.forEach(n => {
        if (n.appointment) {
          apps.push({
            id: n.appointment.id,
            fullname: n.appointment.fullname || '',
            selected_date: n.appointment.selected_date || '',
            selected_time: n.appointment.selected_time || '',
            created_at: n.created_at || n.appointment.created_at,
            title: n.title || 'Notification',
            message: n.message || '',
            type: n.type || 'staff',
            notif_type: 'staff',
            notif_id: n.notification_id
          });
        } else {
          apps.push({
            id: null,
            fullname: '',
            selected_date: '',
            selected_time: '',
            created_at: n.created_at,
            title: n.title || 'Notification',
            message: n.message || '',
            type: n.type || 'staff',
            notif_type: 'staff',
            notif_id: n.notification_id
          });
        }
      });

      const byKey = new Map();
      apps.forEach(item => {
        const key = item.id ? `a:${item.id}` : `n:${item.notif_id}`;
        if (!byKey.has(key)) {
          byKey.set(key, item);
        } else {
          const existing = byKey.get(key);
          if (existing.notif_type === 'appointment' && item.notif_type === 'staff') {
            byKey.set(key, item);
          } else if (existing.notif_type === 'staff' && item.notif_type === 'staff') {
            try {
              if (new Date(item.created_at) > new Date(existing.created_at)) byKey.set(key, item);
            } catch (e) {}
          }
        }
      });

      const renderList = Array.from(byKey.values()).sort((x, y) => new Date(y.created_at) - new Date(x.created_at));
      const total = renderList.length || 0;

      const badge = document.getElementById('diffunNotificationBadge');
      const list = document.getElementById('diffunNotificationList');
      const time = document.getElementById('diffunNotificationTime');
      const container = document.getElementById('diffun-notification-container');
      if (!badge || !list) return;

      if (total > 0) {
        if (container) container.style.display = '';
        badge.textContent = total;
        badge.style.display = 'inline-flex';
      } else {
        if (container) container.style.display = '';
        badge.style.display = 'none';
        list.innerHTML = '<div style="padding:12px;color:#666">No new notifications</div>';
        if (time) time.textContent = new Date().toLocaleTimeString();
        return;
      }

      list.innerHTML = '';
      renderList.forEach(a => {
        const el = document.createElement('div');
        el.className = 'notification-item';
        el.style.padding = '8px';
        el.style.borderBottom = '1px solid #f5f5f5';

        const nid = escapeHtml(a.notif_id || a.id || '');
        const ntype = escapeHtml(a.notif_type || (a.notif_id ? 'staff' : 'appointment'));
        const isAppointment = a.notif_type === 'appointment' || (!!a.id && !!a.fullname && (!a.type || a.type === 'pending_request'));
        const title = isAppointment
          ? `Pending appointment: ${escapeHtml(a.fullname)}`
          : escapeHtml(a.title || 'Notification');
        const meta = isAppointment
          ? `Date: ${escapeHtml(a.selected_date || '')} ${escapeHtml(a.selected_time || '')} | ${new Date(a.created_at).toLocaleString()}`
          : `${escapeHtml(a.message || '')} | ${new Date(a.created_at).toLocaleString()}`;

        el.innerHTML = `<div style="display:flex;justify-content:space-between;align-items:flex-start"><div><div style="font-weight:600">${title}</div><div style="font-size:12px;color:#666">${meta}</div></div><div style="margin-left:8px;"><button data-notif-id="${nid}" data-notif-type="${ntype}" class="btn btn-sm btn-outline-primary diffun-mark-read" style="font-size:11px;padding:4px 6px">Mark read</button></div></div>`;

        if (!isAppointment) {
          el.style.cursor = 'pointer';
          el.addEventListener('click', function(e){
            if (e.target && e.target.closest('.diffun-mark-read')) return;
            window.location.href = '/staff/message-inquiries';
          });
        }

        list.appendChild(el);
      });

      list.querySelectorAll('.diffun-mark-read').forEach(btn => {
        btn.addEventListener('click', function(e){
          e.stopPropagation();
          const id = btn.getAttribute('data-notif-id');
          const type = btn.getAttribute('data-notif-type') || 'appointment';
          const item = btn.closest('.notification-item');
          if (!id) {
            if (item) item.remove();
            if (list.children.length === 0) {
              const container = document.getElementById('diffun-notification-container');
              const badge = document.getElementById('diffunNotificationBadge');
              const listEl = document.getElementById('diffunNotificationList');
              if (listEl) listEl.innerHTML = '<div style="padding:12px;color:#666">No new notifications</div>';
              if (container) container.style.display = '';
              if (badge) badge.style.display = 'none';
            }
            return;
          }

          markNotification(id, type).then(() => {
            try { localStorage.setItem(STORAGE_KEY, new Date().toISOString()); } catch(e){}
            if (item) item.remove();
            try {
              const remaining = list.querySelectorAll('.notification-item').length;
              const badge = document.getElementById('diffunNotificationBadge');
              const listEl = document.getElementById('diffunNotificationList');
              if (remaining === 0) {
                if (listEl) listEl.innerHTML = '<div style="padding:12px;color:#666">No new notifications</div>';
                if (badge) badge.style.display = 'none';
              } else if (badge) {
                badge.textContent = remaining;
                badge.style.display = 'inline-flex';
              }
            } catch(e){}
          }).catch(() => {
            if (item) item.remove();
            if (list.children.length === 0) {
              const badge = document.getElementById('diffunNotificationBadge');
              const listEl = document.getElementById('diffunNotificationList');
              if (listEl) listEl.innerHTML = '<div style="padding:12px;color:#666">No new notifications</div>';
              if (badge) badge.style.display = 'none';
            }
          });
        });
      });

      if (renderList.length > 0) {
        document.title = `(${renderList.length}) New notifications`;
        setTimeout(() => { document.title = document.title.replace(/\(\d+\) /,''); }, 3000);
      }

      if (time) time.textContent = new Date().toLocaleTimeString();
    } catch (err) {
      console.error('Diffun notifications poll error', err);
    }
  }

  async function markNotification(notificationId, type = 'appointment'){
    try {
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const res = await fetch('/diffun-staff/notifications/mark-read', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},
        body: JSON.stringify({id: notificationId, type: type})
      });
      return res.ok;
    } catch (e) {
      console.error('markNotification error', e);
      return false;
    }
  }

  async function markAllNotifications(){
    try {
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const res = await fetch('/diffun-staff/notifications/mark-all-read', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},
        body: JSON.stringify({})
      });
      const list = document.getElementById('diffunNotificationList');
      const badge = document.getElementById('diffunNotificationBadge');
      const container = document.getElementById('diffun-notification-container');
      if (list) list.innerHTML = '<div style="padding:12px;color:#666">No new notifications</div>';
      if (badge) badge.style.display = 'none';
      if (container) container.style.display = '';
      localStorage.setItem(STORAGE_KEY, new Date().toISOString());
      return res.ok;
    } catch (e) {
      console.error('markAllNotifications error', e);
      return false;
    }
  }

  function escapeHtml(text){
    if (!text) return '';
    return String(text).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
  }

  document.addEventListener('DOMContentLoaded', function(){
    const injected = injectIntoTopBar();
    if (!injected) return;
    if (!localStorage.getItem(STORAGE_KEY)) localStorage.setItem(STORAGE_KEY, isoNowMinus(60));
    pollNotifications();
    setInterval(pollNotifications, POLL_INTERVAL);
  });
})();

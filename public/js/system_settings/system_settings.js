document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('ss-search-input');
    const searchForm = document.getElementById('ss-search-form');
    const modalEl = document.getElementById('ss-update-modal');
    // Note: we'll re-query dynamic field at use-time to avoid stale references
    const saveBtn = document.getElementById('ss-save-btn');

    if (searchInput && searchForm) {
        let t;
        searchInput.addEventListener('input', function() {
            clearTimeout(t);
            t = setTimeout(() => {
                searchForm.submit();
            }, 400);
        });
    }

    // Open Update modal
    document.body.addEventListener('click', function(e) {
        const btn = e.target.closest('.ss-update');
        if (!btn) return;
        const id = btn.getAttribute('data-id');
        // Fetch the latest data from server to ensure current values
        fetch(`/systemsettings/get/${id}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Failed to load');
            const rec = data.data || {};
            const key = rec.key || '';
            const type = String(rec.type || '').toLowerCase();
            const description = rec.description || '';

            const idEl = document.getElementById('ss-id');
            const typeEl = document.getElementById('ss-type');
            const keyEl = document.getElementById('ss-key');
            if (idEl) idEl.value = rec.id;
            if (typeEl) typeEl.value = type;
            if (keyEl) keyEl.value = key;

            const dyn = document.getElementById('ss-dynamic-field');
            if (dyn) {
                if (type === 'image') {
                    dyn.innerHTML = `
                        <div>
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" id="description_file" name="description_file" accept="image/*">
                            ${description ? `<div class=\"mt-2\"><img src=\"/${description}\" alt=\"current\" class=\"h-16 rounded\" onerror=\"this.style.display='none'\"/></div>` : ''}
                        </div>
                    `;
                } else {
                    dyn.innerHTML = `
                        <div>
                            <label class="form-label">Description</label>
                            <input type="text" class="form-control" id="description_text" name="description_text" value="${escapeHtml(description)}">
                        </div>
                    `;
                }
            }
        })
        .catch(err => {
            console.error('Failed to load current setting:', err);
        });

        // Show modal (follow events.js approach first)
        const trigger = document.createElement('div');
        trigger.style.display = 'none';
        trigger.setAttribute('data-tw-toggle', 'modal');
        trigger.setAttribute('data-tw-target', '#ss-update-modal');
        document.body.appendChild(trigger);
        trigger.click();
        setTimeout(() => document.body.removeChild(trigger), 100);

        // Fallbacks
        setTimeout(() => {
            const isVisible = modalEl && (modalEl.classList.contains('show') || modalEl.style.display === 'flex');
            if (isVisible) return;
            if (typeof modal_show !== 'undefined') {
                modal_show('ss-update-modal');
                return;
            }
            if (modalEl && typeof tailwind !== 'undefined' && tailwind.Modal) {
                tailwind.Modal.getOrCreateInstance(modalEl).show();
                return;
            }
            if (modalEl) {
                modalEl.classList.remove('hidden');
                modalEl.style.display = 'flex';
                modalEl.style.alignItems = 'center';
                modalEl.style.justifyContent = 'center';
                if (!document.querySelector('.modal-backdrop')) {
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop';
                    backdrop.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1040;';
                    document.body.appendChild(backdrop);
                }
            }
        }, 50);
    });

    // Save
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            const form = document.getElementById('ss-update-form');
            const type = document.getElementById('ss-type').value;
            const fd = new FormData(form);
            // CSRF
            const tokenEl = document.querySelector('meta[name="csrf-token"]');
            if (tokenEl) fd.append('_token', tokenEl.getAttribute('content'));

            fetch('/systemsettings/update', {
                method: 'POST',
                body: fd,
            })
            .then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Update failed');
                // Show success toast (same helpers used in Events)
                if (typeof showSuccess === 'function') {
                    showSuccess('Success', data.message || 'Setting updated successfully.');
                }
                // Update row description text
                const row = document.querySelector(`.ss-update[data-id="${data.data.id}"]`)?.closest('.ss-row');
                if (row) {
                    const descCell = row.querySelector('.ss-description');
                    if (descCell) {
                        if ((data.data.type || '').toLowerCase() === 'image') {
                            const raw = data.data.description || '';
                            const friendly = /^https?:\/\//.test(raw) || raw.startsWith('/') ? raw : '/' + raw.replace(/^\/+/, '');
                            const fallback = /^https?:\/\//.test(raw) || raw.startsWith('/') ? raw : (window.Laravel && Laravel.asset ? Laravel.asset(raw) : raw);
                            descCell.innerHTML = `<img src="${friendly}" onerror="this.onerror=null;this.src='${fallback}'" alt="${(data.data.key || 'image')}" class="h-10 w-auto rounded" />`;
                            descCell.setAttribute('data-value', (raw || '').toLowerCase());
                        } else {
                            descCell.textContent = data.data.description;
                            descCell.setAttribute('data-value', (data.data.description || '').toLowerCase());
                        }
                    }
                }
                // Close modal
                if (typeof modal_hide !== 'undefined') {
                    modal_hide('ss-update-modal');
                } else if (modalEl && typeof tailwind !== 'undefined' && tailwind.Modal) {
                    tailwind.Modal.getOrCreateInstance(modalEl).hide();
                } else if (modalEl) {
                    modalEl.classList.add('hidden');
                    modalEl.style.display = 'none';
                }
            })
            .catch(err => {
                console.error('Update failed:', err);
                if (typeof showError === 'function') {
                    showError('Error', err.message || 'Update failed');
                } else {
                    alert('Update failed: ' + err.message);
                }
            });
        });
    }

    function escapeHtml(str) {
        return (str || '').replace(/[&<>"]/g, function(c){
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c] || c;
        });
    }
});

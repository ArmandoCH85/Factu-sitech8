<div class="modal fade" id="activityModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="activityModalForm" action="{{ route('tenant.crm.activities.store') }}" method="POST" class="modal-content" data-ajax-submit="1">
            @csrf
            <input type="hidden" name="sale_opportunity_id" value="{{ $opportunity->id }}" />
            <input type="hidden" name="type" id="activityModalType" value="note" />
            <div class="modal-header">
                <h5 class="modal-title">Agregar actividad</h5>
            </div>
            <div class="modal-body">
                <div id="activityModalErrors" class="alert alert-danger" style="display:none;"></div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="description" class="form-control" required maxlength="65535"></textarea>
                </div>
                <div class="form-group" id="activityDueDateGroup" style="display:none;">
                    <label>Fecha de vencimiento (tareas)</label>
                    <input type="datetime-local" name="due_date" class="form-control" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Set the type when a trigger button is clicked
    document.querySelectorAll('[data-bs-target="#activityModal"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var t = btn.getAttribute('data-type') || 'note';
            document.getElementById('activityModalType').value = t;
            document.getElementById('activityDueDateGroup').style.display = t === 'task' ? 'block' : 'none';
            document.getElementById('activityModalErrors').style.display = 'none';
        });
    });

    // Quotation file upload — separate handler using FormData with file
    var qForm = document.getElementById('quotationFileForm');
    if (qForm) {
        qForm.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var submitBtn = qForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin fa-fw me-1"></i>Subiendo...';

            var errBox = document.getElementById('quotationFileErrors');
            errBox.textContent = '';
            errBox.style.display = 'none';

            var fd = new FormData(qForm);
            fd.delete('_token');
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

            // Use URL from form's action attribute (set in the form template)
            var actionUrl = qForm.action;

            fetch(actionUrl, {
                method: 'POST',
                body: fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                credentials: 'same-origin'
            }).then(function (resp) {
                return resp.json().then(function (data) {
                    if (resp.ok && data && data.success) {
                        var modalEl = document.getElementById('quotationFileModal');
                        if (modalEl) {
                            modalEl.classList.remove('show');
                            modalEl.style.display = 'none';
                            modalEl.setAttribute('aria-hidden', 'true');
                            document.body.classList.remove('modal-open');
                            var backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) backdrop.remove();
                        }
                        window.location.reload();
                    } else {
                        var msg;
                        if (data && data.errors && data.errors.quotation_file) {
                            msg = Array.isArray(data.errors.quotation_file)
                                ? data.errors.quotation_file.join(' ')
                                : String(data.errors.quotation_file);
                        } else if (data && data.message && typeof data.message === 'object') {
                            // Laravel 422: errors can come nested inside message as {field: [msgs]}
                            var parts = [];
                            Object.keys(data.message).forEach(function (k) {
                                var v = data.message[k];
                                if (Array.isArray(v)) parts.push(v.join(' '));
                                else parts.push(String(v));
                            });
                            msg = parts.join(' ');
                        } else if (data && data.message) {
                            msg = String(data.message);
                        } else {
                            msg = 'Error ' + resp.status + ': ' + JSON.stringify(data);
                        }
                        errBox.textContent = msg;
                        errBox.style.display = 'block';
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-upload fa-fw me-1"></i>Subir';
                    }
                });
            }).catch(function (err) {
                errBox.textContent = 'Error de red: ' + String(err.message || err);
                errBox.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-upload fa-fw me-1"></i>Subir';
            });
        }, true);
    }

    // Stage change dropdown — POST to /crm/opportunities/{id}/stage
    document.querySelectorAll('.crm-stage-change').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var oppId = link.getAttribute('data-opp-id');
            var stageCode = link.getAttribute('data-stage');
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

            // Visual feedback: show a brief loading state
            var origHtml = link.innerHTML;
            link.style.pointerEvents = 'none';
            link.style.opacity = '0.5';

            fetch('/crm/opportunities/' + oppId + '/stage', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf
                },
                credentials: 'same-origin',
                body: JSON.stringify({ stage_code: stageCode })
            }).then(function (resp) {
                return resp.json().then(function (data) {
                    if (resp.ok && data && data.success) {
                        // Reload page to show new stage + auto-logged activity
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data && data.message ? data.message : 'Status ' + resp.status));
                        link.innerHTML = origHtml;
                        link.style.pointerEvents = '';
                        link.style.opacity = '';
                    }
                });
            }).catch(function (err) {
                alert('Error de red: ' + String(err.message || err));
                link.innerHTML = origHtml;
                link.style.pointerEvents = '';
                link.style.opacity = '';
            });
        }, true);
    });

    // Intercept form submit and use fetch (bypasses Vue router interception)
    var form = document.getElementById('activityModalForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            // Always intercept; submit via fetch and reload on success
            e.preventDefault();
            e.stopPropagation();

            var submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Guardando...';

            var errBox = document.getElementById('activityModalErrors');
            // Always reset error display
            errBox.textContent = '';
            errBox.style.display = 'none';

            var fd = new FormData(form);
            // Remove _token from FormData since we send it in header
            fd.delete('_token');
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

            // Debug: log what's being sent
            console.log('[CRM DEBUG] Submitting form. Action:', form.action);
            console.log('[CRM DEBUG] FormData entries:');
            fd.forEach(function (v, k) { console.log('  ' + k + ' = ' + v); });
            console.log('[CRM DEBUG] CSRF token:', csrf);

            fetch(form.action, {
                method: 'POST',
                body: fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                credentials: 'same-origin',
                redirect: 'manual'
            }).then(function (resp) {
                console.log('[CRM DEBUG] Response status:', resp.status, 'type:', resp.type);
                var contentType = resp.headers.get('content-type') || '';
                console.log('[CRM DEBUG] Content-Type:', contentType);

                // Handle opaque redirect responses (type 'opaqueredirect')
                if (resp.type === 'opaqueredirect') {
                    errBox.textContent = 'Redirigido a otra URL. Probable error de autenticación o sesión expirada. Status: ' + resp.status;
                    errBox.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Guardar';
                    return;
                }

                // Try to read response body
                var readBody = contentType.indexOf('application/json') !== -1
                    ? resp.json()
                    : resp.text();

                return readBody.then(function (data) {
                    console.log('[CRM DEBUG] Response data:', data);

                    // Success check
                    if (resp.ok && data && typeof data === 'object' && data.success) {
                        // Close modal manually (Bootstrap may not be loaded as window.bootstrap)
                        var modalEl = document.getElementById('activityModal');
                        if (modalEl) {
                            modalEl.classList.remove('show');
                            modalEl.style.display = 'none';
                            modalEl.setAttribute('aria-hidden', 'true');
                            document.body.classList.remove('modal-open');
                            var backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) backdrop.remove();
                        }
                        window.location.reload();
                        return;
                    }

                    // Error display — always use JSON.stringify for safety
                    var displayText;
                    if (typeof data === 'string') {
                        displayText = 'Status ' + resp.status + ':\n\n' + data.substring(0, 2000);
                    } else {
                        // data is object or null — stringify it safely
                        try {
                            displayText = 'Status ' + resp.status + ':\n\n' + JSON.stringify(data, null, 2);
                        } catch (e) {
                            displayText = 'Status ' + resp.status + ' (could not stringify response)';
                        }
                    }
                    errBox.textContent = displayText;
                    errBox.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Guardar';
                });
            }).catch(function (err) {
                console.error('[CRM DEBUG] Fetch error:', err);
                errBox.textContent = 'Error de red: ' + String(err.message || err);
                errBox.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Guardar';
            });
        }, true); // capture phase: intercept BEFORE any other listener
    }
});
</script>
@endpush

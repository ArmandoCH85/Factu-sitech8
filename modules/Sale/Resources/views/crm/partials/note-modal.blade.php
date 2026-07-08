<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="noteForm"
              action="{{ route('tenant.crm.activities.store') }}"
              method="POST"
              class="modal-content"
              data-ajax-submit="1">
            @csrf
            <input type="hidden" name="sale_opportunity_id" value="{{ $opportunity->id }}" />
            <input type="hidden" name="type" value="note" />
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-sticky-note fa-fw me-1"></i>Agregar nota
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="noteErrors" class="alert alert-danger" style="display:none;"></div>
                <div class="form-group">
                    <label for="noteDescription">Descripción <span class="text-danger">*</span></label>
                    <textarea name="description"
                              id="noteDescription"
                              class="form-control"
                              required
                              maxlength="65535"
                              rows="5"
                              placeholder="Escribí la nota..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save fa-fw me-1"></i>Guardar nota
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// ponytail: handler genérico para formularios con data-ajax-submit.
// Usado por los 3 modales (nota, llamada, tarea).
function bindCrmAjaxForm(formId, modalId, errorBoxId, successLabel) {
    var form = document.getElementById(formId);
    if (!form || form.__crmBound) return;
    form.__crmBound = true;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var submitBtn = form.querySelector('button[type="submit"]');
        var origHtml  = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin fa-fw me-1"></i>Guardando...';

        var errBox = document.getElementById(errorBoxId);
        errBox.textContent = '';
        errBox.style.display = 'none';

        var fd = new FormData(form);
        fd.delete('_token');
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

        fetch(form.action, {
            method: 'POST',
            body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            credentials: 'same-origin'
        }).then(function (resp) {
            var contentType = resp.headers.get('content-type') || '';
            var readBody = contentType.indexOf('application/json') !== -1
                ? resp.json()
                : resp.text();
            return readBody.then(function (data) {
                if (resp.ok && data && typeof data === 'object' && data.success) {
                    var modalEl = document.getElementById(modalId);
                    if (modalEl) {
                        modalEl.classList.remove('show');
                        modalEl.style.display = 'none';
                        modalEl.setAttribute('aria-hidden', 'true');
                        document.body.classList.remove('modal-open');
                        var bd = document.querySelector('.modal-backdrop');
                        if (bd) bd.remove();
                    }
                    window.location.reload();
                    return;
                }
                var msg;
                if (data && data.errors) {
                    var parts = [];
                    Object.keys(data.errors).forEach(function (k) {
                        var v = data.errors[k];
                        if (Array.isArray(v)) parts.push(v.join(' '));
                        else parts.push(String(v));
                    });
                    msg = parts.join(' ');
                } else if (data && data.message) {
                    msg = typeof data.message === 'string'
                        ? data.message
                        : JSON.stringify(data.message);
                } else if (typeof data === 'string') {
                    msg = data.substring(0, 1000);
                } else {
                    msg = 'Error ' + resp.status;
                }
                errBox.textContent = msg;
                errBox.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = origHtml;
            });
        }).catch(function (err) {
            errBox.textContent = 'Error de red: ' + String(err.message || err);
            errBox.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = origHtml;
        });
    }, true);
}

document.addEventListener('DOMContentLoaded', function () {
    bindCrmAjaxForm('noteForm',  'noteModal',  'noteErrors',  'Nota');
    bindCrmAjaxForm('callForm',  'callModal',  'callErrors',  'Llamada');
    bindCrmAjaxForm('taskForm',  'taskModal',  'taskErrors',  'Tarea');
});
</script>
@endpush
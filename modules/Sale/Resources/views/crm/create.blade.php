@extends('tenant.layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Nuevo Lead</h2>
        <a href="{{ route('tenant.crm.leads') }}" class="btn btn-secondary">← Volver a Leads</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Revisá los errores:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('tenant.crm.leads.store') }}" class="card" id="crmLeadForm">
        @csrf
        <div class="card-body">

            {{-- ============================================== --}}
            {{-- CAMPO CLIENTE: typeahead + crear nuevo            --}}
            {{-- ============================================== --}}
            <div class="form-group mb-3" id="customerFieldGroup">
                <label class="customer-field-label" for="customerSearchInput">Cliente <span class="text-danger">*</span></label>
                <div class="customer-field-wrapper" @error('customer_id') data-has-error="1" @enderror>
                    <i class="fas fa-search customer-field-icon-left" aria-hidden="true"></i>
                    <input type="text"
                           id="customerSearchInput"
                           class="customer-field-input @error('customer_id') is-invalid @enderror"
                           placeholder="Escriba el nombre o número de documento del cliente"
                           autocomplete="off"
                           value="{{ old('customer_search_display', optional($customers->firstWhere('id', old('customer_id')))->name) }}"
                           @if(old('customer_id')) data-preselected="1" @endif />
                    <button type="button"
                            class="customer-field-add"
                            id="newCustomerBtn"
                            title="Crear un nuevo cliente">
                        <i class="fas fa-user-plus" aria-hidden="true"></i>
                    </button>
                    <ul class="customer-field-results" id="customerSearchResults" role="listbox"></ul>
                </div>
                <input type="hidden" name="customer_id" id="customerIdInput" value="{{ old('customer_id') }}" />
                <small class="text-danger d-none" id="customerError">Seleccione o registre un cliente.</small>
                @error('customer_id')
                    <small class="text-danger d-block">{{ $message }}</small>
                @enderror
            </div>

            {{-- ponytail: el modal Vue existente se monta aquí DENTRO de
                 main-wrapper (donde sí compila el Vue principal con store,
                 axios, eventHub). :external="true" hace que emita reloadDataPersons(id). --}}
            <div id="crmPersonModalRoot" style="display:none;">
                <tenant-person-form
                    type="customers"
                    :external="true"
                ></tenant-person-form>
            </div>

            <hr class="my-3">

            {{-- ============================================== --}}
            {{-- CAMPOS DEL LEAD                                      --}}
            {{-- ============================================== --}}
            <input type="hidden" name="detail" id="detailField" value="{{ old('detail') }}" />
            {{-- ponytail: multi-select de productos/servicios del sistema --}}
            <div class="form-group">
                <label for="itemsSearchInput">Productos / Servicios de interés</label>
                <div class="customer-field-wrapper">
                    <i class="fas fa-box customer-field-icon-left" aria-hidden="true"></i>
                    <input type="text"
                           id="itemsSearchInput"
                           class="customer-field-input"
                           placeholder="Buscar productos o servicios del sistema..."
                           autocomplete="off" />
                    <ul class="customer-field-results" id="itemsSearchResults" role="listbox"></ul>
                </div>
                <ul id="itemsSelected" class="items-selected-chips"></ul>
                {{-- ponytail: los IDs seleccionados se envían como array --}}
                <select name="item_ids[]" id="itemIdsInput" multiple style="display:none;">
                    @if(is_array(old('item_ids')))
                        @foreach(old('item_ids') as $oid)
                            <option value="{{ $oid }}" selected></option>
                        @endforeach
                    @endif
                </select>
                @error('item_ids')
                    <small class="text-danger d-block">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="total">Monto esperado (S/)</label>
                    <input type="number" name="total" id="total" step="0.01" min="0"
                           class="form-control @error('total') is-invalid @enderror"
                           value="{{ old('total') }}"
                           placeholder="0.00" />
                    @error('total')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group col-md-4">
                    <label for="crm_source">Fuente</label>
                    <select name="crm_source" id="crm_source" class="form-control @error('crm_source') is-invalid @enderror">
                        <option value="web"       {{ old('crm_source', 'web') === 'web' ? 'selected' : '' }}>Web</option>
                        <option value="referral"  {{ old('crm_source') === 'referral' ? 'selected' : '' }}>Referido</option>
                        <option value="cold_call" {{ old('crm_source') === 'cold_call' ? 'selected' : '' }}>Llamada en frío</option>
                        <option value="import"    {{ old('crm_source') === 'import' ? 'selected' : '' }}>Importación</option>
                        <option value="other"     {{ old('crm_source') === 'other' ? 'selected' : '' }}>Otro</option>
                    </select>
                    @error('crm_source')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group col-md-4">
                    <label for="expected_close_date">Fecha cierre estimada</label>
                    <input type="date" name="expected_close_date" id="expected_close_date"
                           class="form-control @error('expected_close_date') is-invalid @enderror"
                           value="{{ old('expected_close_date') }}"
                           min="{{ date('Y-m-d') }}" />
                    @error('expected_close_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <p class="text-muted small mb-0">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                El lead se crea en estado <strong>Nuevo</strong>. Después podés cambiarlo desde el detalle.
            </p>
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('tenant.crm.leads') }}" class="btn btn-link">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus" aria-hidden="true"></i> Crear Lead
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<style>
/* ponytail: estilos del campo Cliente typeahead (sin dependencias externas) */
.customer-field-label {
    display: block;
    font-size: 13px;
    color: #2c3e50;
    font-weight: 500;
    margin-bottom: 6px;
}
.customer-field-wrapper {
    position: relative;
}
.customer-field-input {
    width: 100%;
    height: 40px;
    padding: 0 44px 0 38px;
    background-color: #f4f6f9;
    border: 1px solid #e1e5eb;
    border-radius: 8px;
    font-size: 14px;
    color: #2c3e50;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.customer-field-input::placeholder {
    color: #a3aab5;
}
.customer-field-input:focus {
    outline: none;
    border-color: #4a90e2;
    background-color: #fff;
    box-shadow: 0 0 0 3px rgba(74,144,226,0.12);
}
.customer-field-input.is-invalid {
    border-color: #e74c3c;
}
.customer-field-icon-left {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #a3aab5;
    font-size: 13px;
    pointer-events: none;
}
.customer-field-add {
    position: absolute;
    right: 6px;
    top: 50%;
    transform: translateY(-50%);
    width: 30px;
    height: 30px;
    border: none;
    background: transparent;
    color: #4a90e2;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.15s ease;
}
.customer-field-add:hover { background: #eaf2fb; }
.customer-field-results {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #e1e5eb;
    border-radius: 8px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    max-height: 240px;
    overflow-y: auto;
    list-style: none;
    margin: 0;
    padding: 4px 0;
    z-index: 50;
    display: none;
}
.customer-field-results.is-open { display: block; }
.customer-field-results li {
    padding: 8px 14px;
    cursor: pointer;
    font-size: 14px;
    color: #2c3e50;
}
.customer-field-results li:hover,
.customer-field-results li.is-active {
    background: #f4f6f9;
}
.customer-field-results li small {
    display: block;
    color: #95a5a6;
    font-size: 12px;
}
.customer-field-wrapper[data-has-error="1"] .customer-field-input {
    border-color: #e74c3c;
}
.items-selected-chips {
    list-style: none;
    padding: 0;
    margin: 8px 0 0 0;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.items-selected-chips:empty { display: none; }
.items-selected-chips li {
    background: #eaf2fb;
    color: #2c3e50;
    padding: 4px 10px;
    border-radius: 14px;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.items-selected-chips li button {
    background: none;
    border: none;
    color: #95a5a6;
    cursor: pointer;
    padding: 0;
    line-height: 1;
    font-size: 14px;
}
.items-selected-chips li button:hover { color: #e74c3c; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ponytail: <tenant-person-form> ya está montado por el Vue de #main-wrapper.
    // Como lo abrimos mutando dlg.visible directamente, también tenemos que
    // escuchar los eventos de cierre (save, cancelar, X, backdrop) y forzar
    // dlg.visible = false para que se cierre.
    var root = document.getElementById('main-wrapper');
    var main = root && root.__vue__;
    if (!main) return;

    function findByTag(vm, tag, depth) {
        if (!vm || depth > 60) return null;
        var t = vm.$options && (vm.$options._componentTag || vm.$options.name);
        if (t === tag) return vm;
        var ch = vm.$children || [];
        for (var i = 0; i < ch.length; i++) {
            var hit = findByTag(ch[i], tag, depth + 1);
            if (hit) return hit;
        }
        return null;
    }

    function getFormAndDialog() {
        var f = findByTag(main, 'tenant-person-form', 0);
        if (!f) return null;
        var d = findByTag(f, 'el-dialog', 0);
        return { form: f, dialog: d };
    }

    function openPersonModal() {
        var p = getFormAndDialog();
        if (!p || !p.form) {
            console.warn('[crm] tenant-person-form no encontrado');
            return;
        }
        p.form.recordId = null;
        if (p.dialog) p.dialog.visible = true;
    }

    function closePersonModal() {
        var p = getFormAndDialog();
        if (p && p.dialog) p.dialog.visible = false;
    }

    window.addEventListener('open-person-form', openPersonModal);
    window.addEventListener('close-person-form', closePersonModal);

    // ponytail: escuchar el $on del form para detectar cuando cierra (save,
    // cancelar, X). form.vue emite 'update:showDialog' sin .sync del padre,
    // así que tenemos que forzarlo nosotros.
    setTimeout(function () {
        var p = getFormAndDialog();
        if (!p || !p.dialog) return;
        p.dialog.$on('close', closePersonModal);
        p.dialog.$on('update:visible', function (val) {
            if (!val) closePersonModal();
        });
    }, 500);

    var hub = main.$root && main.$root.constructor && main.$root.constructor.prototype.$eventHub;
    if (hub) {
        hub.$on('reloadDataPersons', function (id) {
            if (!id) return;
            var hidden = document.getElementById('customerIdInput');
            var inp    = document.getElementById('customerSearchInput');
            if (hidden) hidden.value = id;
            if (inp) inp.value = '(Cliente #' + id + ')';
            fetch('/persons/search-data/customers?id=' + encodeURIComponent(id), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            }).then(function (r) { return r.ok ? r.json() : []; }).then(function (data) {
                var item = Array.isArray(data) ? data[0] : (data && data.data && data.data[0]);
                if (item && inp) inp.value = item.search_full_name || item.name || '';
                var err = document.getElementById('customerError');
                if (err) err.classList.add('d-none');
                if (inp) inp.classList.remove('is-invalid');
                closePersonModal();
            });
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var input       = document.getElementById('customerSearchInput');
    var hidden      = document.getElementById('customerIdInput');
    var list        = document.getElementById('customerSearchResults');
    var newBtn      = document.getElementById('newCustomerBtn');
    var errorMsg    = document.getElementById('customerError');
    var form        = document.getElementById('crmLeadForm');
    var preselected = input && input.dataset.preselected === '1';
    var debounceTimer = null;
    var lastQuery = null;  // ponytail: null inicial para que la 1ª carga no se corte por dedupe
    var activeIdx = -1;
    var lastResults = [];

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>"']/g, function (c) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c];
        });
    }

    function renderResults(items) {
        lastResults = items || [];
        activeIdx = -1;
        if (!lastResults.length) {
            list.innerHTML = '<li class="text-muted px-3 py-2" style="cursor:default;">Sin coincidencias. Usá el botón "+" para crear el cliente.</li>';
            list.classList.add('is-open');
            return;
        }
        list.innerHTML = lastResults.map(function (it, i) {
            var label = it.search_full_name || it.name || it.text || '';
            return '<li data-idx="' + i + '" data-id="' + escapeHtml(it.id) + '">'
                 +     escapeHtml(label)
                 + '</li>';
        }).join('');
        list.classList.add('is-open');
    }

    function closeList() {
        list.classList.remove('is-open');
        activeIdx = -1;
    }

    function pickResult(item) {
        if (!item) return;
        hidden.value = item.id;
        input.value = item.search_full_name || item.name || '';
        input.dataset.preselected = '1';
        closeList();
        clearError();
    }

    function clearError() {
        if (errorMsg) errorMsg.classList.add('d-none');
        input.classList.remove('is-invalid');
    }

    function showError() {
        if (errorMsg) errorMsg.classList.remove('d-none');
        input.classList.add('is-invalid');
    }

    function searchCustomers(query) {
        if (query === lastQuery) return;
        lastQuery = query;

        // ponytail: sin query pedimos la lista inicial (servidor devuelve hasta
        // getConfigMinItemsSelect()) — así la lista aparece al cargar/focus.
        var url = query && query.length >= 2
            ? '/persons/search-data/customers?input=' + encodeURIComponent(query)
            : '/persons/search-data/customers';

        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(function (resp) {
            if (!resp.ok) throw new Error('Non-OK response');
            return resp.json();
        })
        .then(function (data) {
            var items = Array.isArray(data) ? data
                       : (data && Array.isArray(data.data)) ? data.data
                       : (data && Array.isArray(data.records)) ? data.records
                       : [];
            renderResults(items);
            // Si no hay query y hay items, abrimos el dropdown automáticamente
            if ((!query || query.length < 2) && items.length) {
                list.classList.add('is-open');
            }
        })
        .catch(function () {
            closeList();
        });
    }

    // ponytail: carga inicial para que la lista se vea al abrir la página,
    // sin necesidad de tipear primero.
    searchCustomers('');

    if (input) {
        input.addEventListener('input', function () {
            // Si edita el texto, la selección previa deja de ser válida
            if (hidden.value) {
                hidden.value = '';
                input.dataset.preselected = '';
            }
            clearError();
            var q = input.value.trim();
            if (debounceTimer) clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () { searchCustomers(q); }, 250);
        });

        input.addEventListener('focus', function () {
            // Reabre la lista si ya hay resultados cacheados
            if (lastResults.length) {
                list.classList.add('is-open');
            } else {
                searchCustomers('');
            }
        });

        input.addEventListener('keydown', function (e) {
            var items = list.querySelectorAll('li[data-idx]');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIdx = Math.min(activeIdx + 1, items.length - 1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIdx = Math.max(activeIdx - 1, 0);
            } else if (e.key === 'Enter') {
                if (activeIdx >= 0 && items[activeIdx]) {
                    e.preventDefault();
                    pickResult(lastResults[activeIdx]);
                }
                return;
            } else if (e.key === 'Escape') {
                closeList();
                return;
            } else {
                return;
            }
            items.forEach(function (li, i) {
                li.classList.toggle('is-active', i === activeIdx);
            });
            if (items[activeIdx]) items[activeIdx].scrollIntoView({ block: 'nearest' });
        });
    }

    if (list) {
        list.addEventListener('mousedown', function (e) {
            // mousedown para que se dispare antes del blur del input
            var li = e.target.closest('li[data-idx]');
            if (!li) return;
            e.preventDefault();
            pickResult(lastResults[parseInt(li.dataset.idx, 10)]);
        });
    }

    document.addEventListener('click', function (e) {
        var wrapper = input && input.closest('.customer-field-wrapper');
        if (wrapper && wrapper.contains(e.target)) return; // clicks dentro del campo no cierran
        if (list) closeList();
    });

    if (newBtn) {
        newBtn.addEventListener('click', function () {
            window.dispatchEvent(new CustomEvent('open-person-form', {
                detail: { type: 'customers' }
            }));
        });
    }

    // Validación en submit: cliente obligatorio
    if (form) {
        form.addEventListener('submit', function (e) {
            if (!hidden.value) {
                e.preventDefault();
                showError();
                input.focus();
                return false;
            }
            // ponytail: si el campo detail quedó vacío, lo rellenamos con los
            // productos/servicios seleccionados para que el lead tenga descripción.
            var detailField = document.getElementById('detailField');
            if (detailField && !detailField.value.trim()) {
                var descs = Object.keys(selectedItems).map(function (id) {
                    return selectedItems[id].description;
                }).filter(Boolean);
                if (descs.length) detailField.value = descs.join(', ');
            }
        });
    }

    // ====================================================================
    // Multi-select de productos/servicios
    // ====================================================================
    var itemsInput = document.getElementById('itemsSearchInput');
    var itemsList  = document.getElementById('itemsSearchResults');
    var itemsSel   = document.getElementById('itemsSelected');
    var itemIds    = document.getElementById('itemIdsInput');
    var itemDebounce = null;
    var itemResults = [];
    var itemActive = -1;
    var selectedItems = {};

    // Reconstruir selectedItems desde old() (errores de validación)
    if (itemIds && itemIds.options) {
        Array.prototype.forEach.call(itemIds.options, function (opt) {
            if (opt.value) {
                selectedItems[opt.value] = { id: opt.value, description: opt.textContent || ('#' + opt.value) };
            }
        });
    }

    function renderItemResults(items) {
        itemResults = items || [];
        itemActive = -1;
        if (!itemResults.length) {
            itemsList.innerHTML = '<li class="text-muted px-3 py-2" style="cursor:default;">Sin coincidencias.</li>';
            itemsList.classList.add('is-open');
            return;
        }
        itemsList.innerHTML = itemResults.map(function (it, i) {
            var label = it.description || it.name || '';
            return '<li data-idx="' + i + '" data-id="' + escapeHtml(it.id) + '">'
                 +     escapeHtml(label)
                 +     (it.internal_id ? ' <small>(' + escapeHtml(it.internal_id) + ')</small>' : '')
                 + '</li>';
        }).join('');
        itemsList.classList.add('is-open');
    }

    function renderSelectedChips() {
        if (!itemsSel) return;
        var html = '';
        Object.keys(selectedItems).forEach(function (id) {
            html += '<li data-id="' + escapeHtml(id) + '">'
                  +     escapeHtml(selectedItems[id].description)
                  +     ' <button type="button" data-remove="' + escapeHtml(id) + '" title="Quitar">&times;</button>'
                  + '</li>';
        });
        itemsSel.innerHTML = html;
        // sync hidden multi-select
        itemIds.innerHTML = '';
        Object.keys(selectedItems).forEach(function (id) {
            var o = document.createElement('option');
            o.value = id;
            o.selected = true;
            o.textContent = selectedItems[id].description;
            itemIds.appendChild(o);
        });
    }

    function searchItems(query) {
        var url = query && query.length >= 2
            ? '/items/search-items?input=' + encodeURIComponent(query)
            : '/items/records?page=1';
        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) {
            var arr = [];
            if (data) {
                if (Array.isArray(data)) arr = data;
                else if (Array.isArray(data.items)) arr = data.items;
                else if (data.data && Array.isArray(data.data)) arr = data.data;
            }
            renderItemResults(arr);
        })
        .catch(function () {});
    }

    if (itemsInput) {
        itemsInput.addEventListener('input', function () {
            var q = itemsInput.value.trim();
            if (itemDebounce) clearTimeout(itemDebounce);
            itemDebounce = setTimeout(function () { searchItems(q); }, 250);
        });
        itemsInput.addEventListener('focus', function () {
            if (!itemResults.length) searchItems('');
        });
        itemsInput.addEventListener('keydown', function (e) {
            var items = itemsList.querySelectorAll('li[data-idx]');
            if (e.key === 'ArrowDown') { e.preventDefault(); itemActive = Math.min(itemActive + 1, items.length - 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); itemActive = Math.max(itemActive - 1, 0); }
            else if (e.key === 'Enter') {
                if (itemActive >= 0 && items[itemActive]) {
                    e.preventDefault();
                    var it = itemResults[itemActive];
                    selectedItems[String(it.id)] = { id: it.id, description: it.description || it.name || ('#' + it.id) };
                    renderSelectedChips();
                    itemsInput.value = '';
                    itemResults = []; itemActive = -1;
                    itemsList.classList.remove('is-open');
                }
                return;
            } else if (e.key === 'Escape') { itemsList.classList.remove('is-open'); return; }
            else { return; }
            items.forEach(function (li, i) { li.classList.toggle('is-active', i === itemActive); });
            if (items[itemActive]) items[itemActive].scrollIntoView({ block: 'nearest' });
        });
    }

    if (itemsList) {
        itemsList.addEventListener('mousedown', function (e) {
            var li = e.target.closest('li[data-idx]');
            if (!li) return;
            e.preventDefault();
            var it = itemResults[parseInt(li.dataset.idx, 10)];
            if (!it) return;
            selectedItems[String(it.id)] = { id: it.id, description: it.description || it.name || ('#' + it.id) };
            renderSelectedChips();
            if (itemsInput) itemsInput.value = '';
            itemResults = []; itemActive = -1;
            itemsList.classList.remove('is-open');
        });
    }

    document.addEventListener('click', function (e) {
        var wrapper = itemsInput && itemsInput.closest('.customer-field-wrapper');
        if (wrapper && wrapper.contains(e.target)) return;
        if (itemsList) itemsList.classList.remove('is-open');
    });

    if (itemsSel) {
        itemsSel.addEventListener('click', function (e) {
            var btn = e.target.closest('button[data-remove]');
            if (!btn) return;
            delete selectedItems[btn.dataset.remove];
            renderSelectedChips();
        });
    }

    renderSelectedChips();
});
</script>
@endpush
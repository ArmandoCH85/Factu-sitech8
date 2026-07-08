<div class="modal fade" id="callModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="callForm"
              action="{{ route('tenant.crm.activities.store') }}"
              method="POST"
              class="modal-content"
              data-ajax-submit="1">
            @csrf
            <input type="hidden" name="sale_opportunity_id" value="{{ $opportunity->id }}" />
            <input type="hidden" name="type" value="call" />
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-phone fa-fw me-1"></i>Registrar llamada
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="callErrors" class="alert alert-danger" style="display:none;"></div>
                <div class="form-group mb-3">
                    <label for="callResult">Resultado <span class="text-danger">*</span></label>
                    <select name="payload[result]" id="callResult" class="form-control" required>
                        <option value="">-- Seleccionar --</option>
                        <option value="contestada">Contestada</option>
                        <option value="no_contestada">No contestada</option>
                        <option value="mensaje">Dejó mensaje</option>
                        <option value="numero_invalido">Número inválido</option>
                        <option value="reprogramar">Volver a llamar</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="callDescription">Notas / Comentarios <span class="text-danger">*</span></label>
                    <textarea name="description"
                              id="callDescription"
                              class="form-control"
                              required
                              maxlength="65535"
                              rows="4"
                              placeholder="Detalles de la conversación..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save fa-fw me-1"></i>Guardar llamada
                </button>
            </div>
        </form>
    </div>
</div>
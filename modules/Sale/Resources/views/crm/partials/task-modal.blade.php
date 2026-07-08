<div class="modal fade" id="taskModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="taskForm"
              action="{{ route('tenant.crm.activities.store') }}"
              method="POST"
              class="modal-content"
              data-ajax-submit="1">
            @csrf
            <input type="hidden" name="sale_opportunity_id" value="{{ $opportunity->id }}" />
            <input type="hidden" name="type" value="task" />
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-square fa-fw me-1"></i>Crear tarea
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="taskErrors" class="alert alert-danger" style="display:none;"></div>

                @if($pendingTasksCount > 0)
                    <div class="alert alert-warning d-flex align-items-start gap-2" role="alert">
                        <i class="fas fa-exclamation-triangle fa-fw mt-1"></i>
                        <div>
                            <strong>Tienes {{ $pendingTasksCount }} tarea{{ $pendingTasksCount > 1 ? 's' : '' }} pendiente{{ $pendingTasksCount > 1 ? 's' : '' }}</strong>
                            en esta oportunidad. No olvides completarlas para mantener el seguimiento al día.
                        </div>
                    </div>
                @endif

                <div class="form-group mb-3">
                    <label for="taskDueDate">Fecha de vencimiento <span class="text-danger">*</span></label>
                    <input type="datetime-local"
                           name="due_date"
                           id="taskDueDate"
                           class="form-control"
                           required
                           min="{{ now()->format('Y-m-d\TH:i') }}" />
                </div>
                <div class="form-group">
                    <label for="taskDescription">Descripción <span class="text-danger">*</span></label>
                    <textarea name="description"
                              id="taskDescription"
                              class="form-control"
                              required
                              maxlength="65535"
                              rows="4"
                              placeholder="Qué hay que hacer..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save fa-fw me-1"></i>Guardar tarea
                </button>
            </div>
        </form>
    </div>
</div>
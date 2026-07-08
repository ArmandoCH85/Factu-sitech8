<?php

namespace Modules\Sale\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Sale\Models\SaleOpportunity;
use App\Models\Tenant\Person;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\CrmActivity;
use App\Models\Tenant\CrmPipelineStage;
use Modules\Sale\Services\CrmActivityService;
use Modules\Sale\Services\CrmCommercialService;
use Modules\Sale\Http\Requests\StoreCrmActivityRequest;
use Modules\Sale\Http\Requests\ChangeStageRequest;
use Modules\Sale\Http\Requests\MarkLostRequest;
use Modules\Sale\Http\Requests\StoreLeadRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class CrmCommercialController
 *
 * HTTP layer for the KISS CRM (crm-comercial-kiss). All write paths delegate
 * to services; the controller is intentionally thin and only cares about
 * lifecycle HTTP concerns (validation, redirect, view rendering).
 */
class CrmCommercialController extends Controller
{
    public function __construct(
        protected CrmActivityService $activityService,
        protected CrmCommercialService $commercialService
    ) {
    }

    /**
     * FR-1 — Dashboard with six KPI cards.
     */
    public function indexDashboard(Request $request)
    {
        $user = auth()->user();
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $leadStageIds = CrmPipelineStage::leads()->pluck('id')->all();
        $dealStageIds = CrmPipelineStage::deals()->pluck('id')->all();
        $wonStage = CrmPipelineStage::where('code', 'won')->first();
        $lostStage = CrmPipelineStage::where('code', 'lost')->first();

        $base = SaleOpportunity::query();
        // Apply existing scope so sellers only see their own opportunities.
        if (method_exists($base, 'scopeWhereTypeUser')) {
            $base->whereTypeUser();
        }

        $kpiLeadsNew = (clone $base)->whereIn('crm_stage_id', $leadStageIds)->count();

        $baseDeals = SaleOpportunity::query();
        if (method_exists($baseDeals, 'scopeWhereTypeUser')) {
            $baseDeals->whereTypeUser();
        }
        $kpiDealsOpen = $baseDeals->whereIn('crm_stage_id', $dealStageIds)->count();

        $kpiOverdueTasks = CrmActivity::query()
            ->where('user_id', $user->id)
            ->where('type', 'task')
            ->where('status', 'pending')
            ->where('due_date', '<', $now)
            ->count();

        $kpiWonMonth = $wonStage
            ? (clone $base)->where('crm_stage_id', $wonStage->id)
                ->whereBetween('won_at', [$monthStart, $monthEnd])
                ->count()
            : 0;

        $kpiLostMonth = $lostStage
            ? (clone $base)->where('crm_stage_id', $lostStage->id)
                ->whereBetween('lost_at', [$monthStart, $monthEnd])
                ->count()
            : 0;

        $kpiPendingQuotations = Quotation::query()
            ->whereNotNull('sale_opportunity_id')
            ->whereIn('state_type_id', ['01', '05', '09', '11'])
            ->count();

        return view('sale::crm.dashboard', compact(
            'kpiLeadsNew',
            'kpiDealsOpen',
            'kpiOverdueTasks',
            'kpiWonMonth',
            'kpiLostMonth',
            'kpiPendingQuotations'
        ));
    }

    /**
     * FR-2 — Leads list: opportunities in lead stages (new/contacted/interested).
     */
    public function leads(Request $request)
    {
        $leadStageIds = CrmPipelineStage::leads()->pluck('id')->all();

        $opportunities = SaleOpportunity::query()
            ->whereIn('crm_stage_id', $leadStageIds)
            ->whereTypeUser()
            ->when($request->filled('customer_name'), function ($q) use ($request) {
                $q->whereHas('person', function ($pq) use ($request) {
                    $pq->where('name', 'like', "%{$request->customer_name}%");
                });
            })
            ->when($request->filled('sort'), function ($q) use ($request) {
                $sort = ltrim($request->sort, '-');
                $direction = str_starts_with($request->sort, '-') ? 'desc' : 'asc';
                $q->orderBy($sort, $direction);
            }, fn($q) => $q->orderByDesc('total'))
            ->with('person')
            ->paginate(config('tenant.items_per_page', 20));

        $stages = CrmPipelineStage::orderBy('position')->get();

        return view('sale::crm.leads', compact('opportunities', 'stages'));
    }

    /**
     * FR-3 — Deals list: opportunities in deal/terminal stages.
     */
    public function opportunities(Request $request)
    {
        $dealStageIds = CrmPipelineStage::whereIn('type', ['deal', 'terminal'])->pluck('id')->all();

        $opportunities = SaleOpportunity::query()
            ->whereIn('crm_stage_id', $dealStageIds)
            ->whereTypeUser()
            ->when($request->filled('stage'), function ($q) use ($request) {
                $stage = CrmPipelineStage::where('code', $request->stage)->first();
                if ($stage) {
                    $q->where('crm_stage_id', $stage->id);
                }
            })
            ->when($request->filled('current_month'), function ($q) {
                $q->whereMonth('date_of_issue', Carbon::now()->month)
                  ->whereYear('date_of_issue', Carbon::now()->year);
            })
            ->with('person')
            ->orderByDesc('date_of_issue')
            ->paginate(config('tenant.items_per_page', 20));

        $stages = CrmPipelineStage::orderBy('position')->get();

        return view('sale::crm.opportunities', compact('opportunities', 'stages'));
    }

    /**
     * FR-4 — Detail view: timeline, customer data, items, files, related quotation.
     */
    public function showOpportunity(int $id)
    {
        /** @var SaleOpportunity $opportunity */
        $opportunity = SaleOpportunity::with([
            'person',
            'crmActivities.user',
            'crmStage',
            'items',
            'files',
            'quotation',
        ])->findOrFail($id);

        $activities = $opportunity->crmActivities()->orderByDesc('created_at')->get();
        // ponytail: timeline solo muestra cambios de etapa; las notas/llamadas/tareas
        // se siguen guardando pero no aparecen en el timeline.
        $timelineActivities = $activities->where('type', 'status_change');
        $pendingTasksCount  = $activities->where('type', 'task')->where('status', 'pending')->count();
        $stages = CrmPipelineStage::orderBy('position')->get();

        return view('sale::crm.show', compact('opportunity', 'activities', 'timelineActivities', 'pendingTasksCount', 'stages'));
    }

    /**
     * FR-5 — Persist a new activity (note/call/task/...).
     */
    public function storeActivity(StoreCrmActivityRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        try {
            $activity = $this->activityService->store($data);
        } catch (\Throwable $e) {
            \Log::error('[CRM] storeActivity FAILED', ['error' => $e->getMessage()]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            throw $e;
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'activity_id' => $activity->id,
                'type' => $activity->type,
                'message' => 'Actividad registrada.',
            ]);
        }

        return redirect()
            ->route('tenant.crm.show', ['id' => $data['sale_opportunity_id']])
            ->with('success', 'Actividad registrada.');
    }

    /**
     * FR-6 — Move opportunity to a different pipeline stage.
     */
    public function changeStage(int $id, ChangeStageRequest $request)
    {
        $this->commercialService->changeStage(
            $id,
            $request->input('stage_code'),
            $request->input('lost_reason')
        );

        // AJAX request: return JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Etapa actualizada correctamente.',
            ]);
        }

        return redirect()->back()->with('success', 'Etapa actualizada.');
    }

    /**
     * FR-7 — Mark opportunity as Won.
     */
    public function markWon(int $id)
    {
        $this->commercialService->markWon($id);

        return redirect()->back()->with('success', 'Marcada como ganada.');
    }

    /**
     * FR-7 — Mark opportunity as Lost (lost_reason required).
     */
    public function markLost(int $id, MarkLostRequest $request)
    {
        $this->commercialService->markLost($id, $request->input('lost_reason'));

        return redirect()->back()->with('success', 'Marcada como perdida.');
    }

    /**
     * FR-9 — Complete an activity (sets status=done).
     */
    public function completeActivity(int $id)
    {
        $this->activityService->complete($id);

        return redirect()->back()->with('success', 'Actividad completada.');
    }

    /**
     * FR-9 — Delete an activity. Policy gates ownership / admin.
     */
    public function destroyActivity(int $id)
    {
        /** @var CrmActivity $activity */
        $activity = CrmActivity::findOrFail($id);
        $this->authorize('delete', $activity);
        $activity->delete();

        return response()->noContent();
    }

    /**
     * FR-8 — Log "intent to create quotation" + redirect to quotation form.
     */
    public function createQuotationFromOpportunity(int $id)
    {
        /** @var SaleOpportunity $opportunity */
        $opportunity = SaleOpportunity::findOrFail($id);

        DB::connection('tenant')->transaction(function () use ($opportunity) {
            $this->activityService->logQuotationIntent($opportunity);
        });

        return redirect()->route('tenant.quotations.create', [
            'id' => $opportunity->id,
            'type' => 'sale_opportunity',
        ]);
    }

    /**
     * Fase 2 — Show the "New Lead" form (GET /crm/leads/create).
     * Renders create.blade.php with a list of customers (persons where type=customers)
     * for the dropdown.
     */
    public function createLead()
    {
        $customers = Person::whereType('customers')
            ->where('enabled', true)
            ->orderBy('name')
            ->get(['id', 'name', 'number']);

        return view('sale::crm.create', compact('customers'));
    }

    /**
     * Fase 2 — Store a new lead (POST /crm/leads).
     * Creates a SaleOpportunity with crm_stage_id = 'new' (id 1) by default,
     * plus an automatic crm_activity of type=note logging the creation.
     */
    public function storeLead(StoreLeadRequest $request)
    {
        $user = auth()->user();
        $stage = CrmPipelineStage::where('code', 'new')->firstOrFail();

        $opportunity = DB::connection('tenant')->transaction(function () use ($request, $user, $stage) {
            $customer = Person::findOrFail($request->input('customer_id'));

            $opportunity = SaleOpportunity::create([
                'external_id' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => $user->id,
                'establishment_id' => 1,
                'establishment' => ['id' => 1, 'code' => '0000', 'description' => 'Oficina Principal'],
                'customer_id' => $customer->id,
                'soap_type_id' => '01',
                'state_type_id' => '01',
                'prefix' => 'OPP',
                'date_of_issue' => now()->format('Y-m-d'),
                'time_of_issue' => now()->format('H:i:s'),
                'customer' => [
                    'name' => $customer->name,
                    'number' => $customer->number,
                ],
                'currency_type_id' => 'PEN',
                'exchange_rate_sale' => 1.000,
                'total_exportation' => 0,
                'total_free' => 0,
                'total_taxed' => (float) ($request->input('total') ?? 0),
                'total_unaffected' => 0,
                'total_exonerated' => 0,
                'total' => (float) ($request->input('total') ?? 0),
                'total_igv' => round(((float) ($request->input('total') ?? 0)) * 0.18, 2),
                'total_taxes' => round(((float) ($request->input('total') ?? 0)) * 0.18, 2),
                'total_value' => round(((float) ($request->input('total') ?? 0)) / 1.18, 2),
                'detail' => $request->input('detail'),
                'observation' => '',
                'crm_stage_id' => $stage->id,
                'crm_source' => $request->input('crm_source') ?? 'web',
                'expected_close_date' => $request->input('expected_close_date'),
                'last_activity_at' => now(),
            ]);

            // Auto-log a note saying the lead was created.
            CrmActivity::create([
                'type' => 'note',
                'user_id' => $user->id,
                'sale_opportunity_id' => $opportunity->id,
                'description' => 'Lead creado.',
            ]);

            // ponytail: guarda los productos/servicios seleccionados (item_ids[])
            $itemIds = $request->input('item_ids', []);
            if (is_array($itemIds) && count($itemIds)) {
                $itemsById = \App\Models\Tenant\Item::whereIn('id', $itemIds)
                    ->get()
                    ->keyBy('id');
                foreach (array_unique(array_filter($itemIds)) as $itemId) {
                    $item = $itemsById->get((int) $itemId);
                    $opportunity->items()->create([
                        'item_id' => (int) $itemId,
                        'item' => $item ? [
                            'id' => $item->id,
                            'description' => $item->description,
                            'internal_id' => $item->internal_id,
                            'unit_type_id' => $item->unit_type_id,
                            'sale_affectation_igv_type_id' => $item->sale_affectation_igv_type_id,
                        ] : null,
                        'quantity' => 1,
                        'unit_value' => 0,
                        'affectation_igv_type_id' => '10',
                        'total_base_igv' => 0,
                        'percentage_igv' => 18,
                        'total_igv' => 0,
                        'total_taxes' => 0,
                        'price_type_id' => '01',
                        'unit_price' => 0,
                        'total_value' => 0,
                        'total_charge' => 0,
                        'total_discount' => 0,
                        'total' => 0,
                    ]);
                }
            }

            return $opportunity;
        });

        return redirect()
            ->route('tenant.crm.leads')
            ->with('success', "Lead #{$opportunity->id} creado correctamente.");
    }

    /**
     * Fase 2 — Upload a quotation file (PDF/DOCX/XLSX) attached to an opportunity.
     * Stores the file in tenant storage and creates a crm_activity of type=note
     * with description "Cotización adjunta: filename" so the timeline shows it.
     */
    public function uploadQuotationFile(Request $request, int $id)
    {
        $request->validate([
            'quotation_file' => 'required|file|max:10240',
        ], [
            'quotation_file.required' => 'Seleccioná un archivo.',
            'quotation_file.max' => 'El archivo no puede pesar más de 10 MB.',
        ]);

        // Laravel 9 mimes: validates by detected MIME, which rejects valid .docx/.xlsx
        // whose content doesn't match the expected MIME. Validate by extension instead.
        $ext = strtolower($request->file('quotation_file')->getClientOriginalExtension());
        if (!in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solo PDF, Word o Excel.',
                'errors' => ['quotation_file' => ['Solo PDF, Word o Excel.']],
            ], 422);
        }

        $opportunity = SaleOpportunity::findOrFail($id);
        $user = auth()->user();

        $file = $request->file('quotation_file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $safeBase = Str::slug($baseName);
        $storedName = $safeBase . '-' . $opportunity->id . '-' . substr(uniqid(), -6) . '.' . $extension;

        // Save to tenant disk under sale_opportunity_files/
        Storage::disk('tenant')->put(
            'sale_opportunity_files' . DIRECTORY_SEPARATOR . $storedName,
            file_get_contents($file->getRealPath())
        );

        // Create the file record (relationship from SaleOpportunity->files())
        $opportunity->files()->create([
            'filename' => $storedName,
        ]);

        // Log a note activity so the timeline shows the attachment.
        CrmActivity::create([
            'type' => 'note',
            'user_id' => $user->id,
            'sale_opportunity_id' => $opportunity->id,
            'description' => 'Cotización adjunta: ' . $originalName,
        ]);

        return response()->json([
            'success' => true,
            'filename' => $storedName,
            'original_name' => $originalName,
            'message' => 'Cotización adjuntada correctamente.',
        ]);
    }

    /**
     * Download a quotation file attached to an opportunity.
     */
    public function downloadQuotationFile(Request $request, int $id, string $filename)
    {
        $opportunity = SaleOpportunity::findOrFail($id);

        // Verify the file actually belongs to this opportunity.
        $fileRecord = $opportunity->files()->where('filename', $filename)->first();
        if (!$fileRecord) {
            abort(404, 'Archivo no encontrado.');
        }

        $path = 'sale_opportunity_files' . DIRECTORY_SEPARATOR . $filename;
        if (!Storage::disk('tenant')->exists($path)) {
            abort(404, 'Archivo físico no encontrado.');
        }

        return Storage::disk('tenant')->download($path);
    }
}

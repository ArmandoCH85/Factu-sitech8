<?php

    namespace Modules\FullSuscription\Http\Controllers;

    use Illuminate\Contracts\View\Factory;
    use Illuminate\Database\Query\Builder;
    use Illuminate\Foundation\Application;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\View\View;
    use Modules\FullSuscription\Http\Requests\PlanFullSuscriptionRequest;
    use Modules\FullSuscription\Http\Resources\SuscriptionPlansCollection;
    use Modules\FullSuscription\Http\Resources\SuscriptionPlansResource;
    use Modules\FullSuscription\Models\Tenant\CatPeriod;
    use Modules\FullSuscription\Models\Tenant\SuscriptionPlan;

    class PlansFullSuscriptionController extends FullSuscriptionController
    {
        public function Columns()
        {
            return [
                'cat_period_id' => 'Periodos',
                'name' => 'Descripción',
                'description' => 'Nombre',
            ];

        }

        public function Record(Request $request)
        {

            return new SuscriptionPlansResource(SuscriptionPlan::findOrFail($request->person));
        }

        public function Records(Request $request)
        {
            $records = SuscriptionPlan::query();

            // Búsqueda por nombre o descripción
            if ($request->filled('q')) {
                $q = $request->q;
                $records->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            }

            // Filtro por frecuencia (período)
            if ($request->filled('frequency') && $request->frequency !== 'all') {
                $records->where('cat_period_id', $request->frequency);
            }

            // Filtro por estado
            if ($request->filled('status') && $request->status !== 'all') {
                $records->where('status', (bool)($request->status));
            }

            // Filtro por período de prueba
            if ($request->filled('trial') && $request->trial !== 'all') {
                if ($request->trial === 'with') {
                    $records->whereNotNull('trial_days')->where('trial_days', '>', 0);
                } else {
                    $records->whereNull('trial_days')->orWhere('trial_days', '<=', 0);
                }
            }

            /** @var Builder $records */
            $records->orderBy('name');

            return new SuscriptionPlansCollection($records->paginate(config('tenant.items_per_page')));
        }

        public function Tables()
        {

            $periods = CatPeriod::where('active', 1)->get();
            $establishment_id = auth()->user()->establishment_id;

            return compact(
                'periods',
                'establishment_id'
            );

        }

        public function destroy($id)
        {

            $record = SuscriptionPlan::findOrFail($id);
            $record->delete();

            return [
                'success' => true,
                'message' => 'Plan eliminado con éxito'
            ];

        }

        /**
         * Actualiza el estado `status` de un plan.
         */
        public function updateStatus(Request $request, $id)
        {
            $plan = SuscriptionPlan::findOrFail($id);
            if ($request->has('status')) {
                $plan->status = (bool)$request->status;
                $plan->save();
            }

            return [
                'success' => true,
                'status' => (bool)$plan->status
            ];
        }

        /**
         * Show the form for editing the specified resource.
         *
         * @param int $id
         *
         * @return Factory|Application|Response|View
         */
        public function edit($id)
        {
            return view('full_suscription::edit');
        }

        /**
         * Display a listing of the resource.
         *
         * @return Factory|Application|Response|View
         */
        public function index()
        {
            return view('full_suscription::plans.index');
        }

        /**
         * Show the specified resource.
         *
         * @param int $id
         *
         * @return Factory|Application|Response|View
         */
        public function show($id)
        {
            return view('full_suscription::show');
        }


        /**
         * Store a newly created resource in storage.
         *
         * @param PlanFullSuscriptionRequest $request
         *
         * @return SuscriptionPlansResource
         */
        public function store(PlanFullSuscriptionRequest $request)
        {
            $id = null;
            $requestItems = $request->items;
            if ($request->has('id')) $id = (int)$request->id;
            $period = CatPeriod::where('period', 'Y')->first();
            if ($request->has('periods')) {
                if (CatPeriod::where('period', $request->periods)->first() != null) {
                    $period = CatPeriod::where('period', $request->periods)->first();
                }
            }
            $plan = SuscriptionPlan::firstOrNew(['id' => $id], $request->all());

            $plan->fill($request->all());
            $plan->setName($request->name)
                ->setDescription($request->description)
                ->setCatPeriod($period)
                ->push();
            foreach ($plan->items as $i) {
                $i->delete();
            }
            // Elimina todos los items anteriores
            // Inserta todos los nuevos items
            foreach ($requestItems as $item) {
                $plan->items()->create($item);

            }


            return new SuscriptionPlansResource($plan);

        }

    }

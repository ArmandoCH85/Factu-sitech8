<?php

namespace Modules\Restaurant\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Restaurant\Models\RestaurantConfiguration;
use Modules\Restaurant\Models\RestaurantRole;
use Modules\Restaurant\Models\RestaurantTable;
use Modules\Restaurant\Models\RestaurantTableEnv;
use App\Models\Tenant\User;
use App\Models\Tenant\Company;
use Modules\Restaurant\Models\RestaurantItemOrderStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Tenant\UserResource;
use Illuminate\Support\Str;


class RestaurantConfigurationController extends Controller
{
    /**
     * muestra vista para utilizar en mozo
     */
    public function configuration()
    {
        return view('restaurant::configuration.index');
    }

    /**
     * obtiene configuración para utilizar en mozo
     */
    public function record()
    {
        $configurations = RestaurantConfiguration::first();
        $company = Company::query()->first();
        $user = auth()->user();

        return [
            'success' => true,
            'data' => $configurations->getCollectionData(),
            'info' => ['ruc' => $company->number, 'userEmail' => $user->email, 'socketServer' => config('tenant.socket_server') ?? 'http://localhost:8070'],
        ];
    }

    public function tablesAndEnv()
    {
        $tables = RestaurantTable::whereNotNull('environment')->get()->transform(function ($row) {

            // Verificar si es la mesa principal del grupo
            $isMainTable = false;
            if ($row->group_id) {
                $group = \Modules\Restaurant\Models\RestaurantTableGroup::find($row->group_id);
                $isMainTable = $group && $group->main_table_id === $row->id;
            }

            return (object)[
                'id' => $row->id,
                'status' => $row->status,
                'products' => (array)$row->products,
                'total' => (float)$row->total,
                'personas' => $row->personas,
                'cliente' => $row->cliente,
                'label' => $row->label,
                'shape' => $row->shape,
                'environment' => $row->environment,
                'waiter' => $row->waiter,
                'open' => false,
                'close' => false,
                'group_id' => $row->group_id,
                'is_main_table' => $isMainTable, // Agregar la lógica de verificación antes
                'is_active' => (bool)$row->is_active,
                'quantityOrders' => (count((array)$row->products)>0)?$this->getQuantityOrdersByTable((array)$row->products):0,
                'timeOpening' => ($row->opening_date)?$this->getTimeByDateOpening($row->opening_date):null,
                'order_status' => $row->order_status,
            ];
        });

        $configuration = RestaurantConfiguration::first();

        return [
            'tables' => $tables,
            'enabled_environment_1' => (object)['active' => (bool)$configuration->enabled_environment_1, 'tablesQuantity' => $configuration->tables_quantity,'name'=> RestaurantTableEnv::where('id', 1)->pluck('name')->first()],
            'enabled_environment_2' => (object)['active' => (bool)$configuration->enabled_environment_2, 'tablesQuantity' => $configuration->tables_quantity_environment_2,'name'=> RestaurantTableEnv::where('id', 2)->pluck('name')->first()],
            'enabled_environment_3' => (object)['active' => (bool)$configuration->enabled_environment_3, 'tablesQuantity' => $configuration->tables_quantity_environment_3,'name'=> RestaurantTableEnv::where('id', 3)->pluck('name')->first()],
            'enabled_environment_4' => (object)['active' => (bool)$configuration->enabled_environment_4, 'tablesQuantity' => $configuration->tables_quantity_environment_4,'name'=> RestaurantTableEnv::where('id', 4)->pluck('name')->first()],
            'environments' => RestaurantTableEnv::where('active', true)->get()
        ];
    }

    private function getTimeByDateOpening($date_opening = null)
    {
        if($date_opening){
            $date = $date_opening;
            $dateOpening = Carbon::parse($date);
            $now = Carbon::now();

            $diffHours = $dateOpening->diffInHours($now);
            $diffMinutes = $dateOpening->diffInMinutes($now) % 60;

            if ($diffHours > 0) {
                $result = $diffHours.' h'. ($diffHours > 1 ? 's' : '').' y '.$diffMinutes.' min';
            } else {
                $result = $diffMinutes.' min';
            }

            return $result;
        }
        return null ;
    }


    private function getQuantityOrdersByTable($items)
    {
        $quantityOrders = collect($items)->sum('quantity');
        return $quantityOrders;
    }

    /**
     * guarda cada nueva configuración para utilizar en mozo
     */
    public function setConfiguration(Request $request)
    {
        $configuration = RestaurantConfiguration::first();
        $configuration->fill($request->all());
        if (!$configuration->menu_pos && !$configuration->menu_order && !$configuration->menu_tables) {
            $configuration->menu_pos = true;
        }
        $configuration->save();

        $this->generateMesas();

        return [
            'success' => true,
            'configuration' => $configuration->getCollectionData(),
            'message' => 'Configuración actualizada',
        ];
    }

    private function generateMesas()
    {
        RestaurantTable::truncate();
        RestaurantItemOrderStatus::truncate();

        $activeEnvironments = RestaurantTableEnv::where('active', true)->get();

        foreach ($activeEnvironments as $environment) {
            for ($i = 1; $i <= $environment->tables_quantity; $i++) {
                RestaurantTable::create([
                    'status' => 'available',
                    'products' => [],
                    'total' => 0,
                    'personas' => 1,
                    'label' => strval($i),
                    'shape' => 'CUADRADO',
                    'environment' => $environment->name,
                ]);
            }
        }
    }

    /**
     * consulta los roles actuales
     */
    public function getRoles()
    {
        $roles = RestaurantRole::orderBy('name', 'ASC')->get();
        $alls = $roles->transform(function ($item) {
            return $item->getCollectionData();
        });

        return [
            'success' => true,
            'data' => $alls
        ];
    }

    /**
     * consulta los usuarios actuales
     */
    public function getUsers()
    {
        $users = User::orderBy('name')->where('type','<>','client')->get();
        $alls = $users->transform(function ($item) {
            return $item->getCollectionRestaurantData();
        });

        return [
            'success' => true,
            'data' => $alls
        ];
    }

    /**
     * asigna o actualiza un rol a un usuario
     */
    public function setRole(Request $request)
    {
        $user = User::find($request->user_id);
        $user->restaurant_role_id = $request->role_id;
        $user->save();

        return [
            'success' => true,
            'message' => 'Rol asignado a usuario exitosamente',
        ];
    }

    public function deleteRole(Request $request)
    {
        $user = User::find($request->user_id);
        $user->restaurant_role_id = null;
        $user->save();

        return [
            'success' => true,
            'message' => 'Rol quitado a usuario exitosamente',
        ];
    }

    public function saveTable($id, Request $request)
    {
        $table = RestaurantTable::findOrFail($id);
        //$data = $request->all();
        $data = $request->except(['group_id', 'is_main_table']); // Proteger campos de grupo
        $data['status'] = (count($data['products'])<1)?$data['status']:'notavailable';

        if(isset($data['open'])&& $data['open']){
            $data['opening_date'] = Carbon::now();
        }

        if(isset($data['close'])&& $data['close']){
            $data['opening_date'] = null;
        }

        $table->fill($data);
        $table->save();

        if ($table->group_id) {
            \Modules\Restaurant\Models\RestaurantTableGroup::where('id', $table->group_id)
                ->update(['total' => $table->total]);

            // Actualizar estado de todas las mesas del grupo
            RestaurantTable::where('group_id', $table->group_id)
                ->update([
                    'status' => $data['status'],
                    'total' => $table->total
                ]);
        }

        if(count($data['products']) < 1){
            $itemsToDelete = RestaurantItemOrderStatus::where('table_id', $id);
            if ($itemsToDelete->exists()) {
                $itemsToDelete->delete();
            }
        }

        return [
            'success' => true,
            'message' => 'Mesa actualizada.',
        ];
    }

    public function saveLabelTable(Request $request)
    {
        $table = RestaurantTable::findOrFail($request->id);
        $table->label = $request->label ;
        $table->shape = $request->shape ;
        $table->save();
        return [
            'success' => true,
            'message' => 'Mesa actualizada con éxito.',
        ];
    }

    public function getTable($id)
    {
        $row = RestaurantTable::findOrFail($id);

        $table = (object)[
            'id' => $row->id,
            'status' => $row->status,
            'products' => (array)$row->products,
            'total' => (float)$row->total,
            'personas' => $row->personas,
            'cliente' => $row->cliente,
            'label' => $row->label,
            'shape' => $row->shape,
            'environment' => $row->environment,
            'waiter' => $row->waiter,
            'order_status' => $row->order_status,
            'cliente' => $row->cliente,
            'waiter' => $row->waiter,
            'order_status' => $row->order_status,
        ];

        return compact('table');
    }

    public function getSellers()
    {
        $users = User::where('active', 1)
            ->where('restaurant_role_id','<>',null)
            ->select('id','name','email')
            ->get();

        return [
            'success' => true,
            'message' => 'Vendedores disponibles',
            'data' => ($users)?$users:[]
        ];
    }

    public function correctPinCheck($id,$pin)
    {
        $user = User::where('active', 1)
            ->where('id',$id)
            ->where('restaurant_pin',$pin)
            ->select('id','name','email')
            ->first();

        return [
            'success' => ($user)?true:false,
            'message' => 'Verificar pin',
            'data' => ($user)?$user:[]
        ];
    }

    public function userStore(Request $request)
    {
        $id = $request->input('id');
        $email = $request->input('email');
        if (!$id && !$email) {
            $milliseconds = round(microtime(true) * 1000);
            $email = $milliseconds.'@gmail.com';
        }

        if (!$id) {
            $verify = User::where('email', $email)->first();
            if ($verify) {
                return [
                    'success' => false,
                    'message' => 'Email no disponible. Ingrese otro Email'
                ];
            }
        }

        DB::connection('tenant')->transaction(function () use ($request, $id, $email) {

            $user = User::firstOrNew(['id' => $id]);
            $user->name = $request->input('name');
            $user->email = $email;

            if($request->input('restaurant_pin')){
                $user->restaurant_pin = $request->input('restaurant_pin');
            }

            if (!$id){
                $user->type = 'seller';
            }

            if (!$id && $request->restaurant_role_id != 1 && $request->restaurant_role_id != 6) {
                $user->api_token = Str::random(50);
                $password = $request->input('password');
                $user->password = bcrypt($password);
            }

            if($request->restaurant_role_id === 1|| $request->restaurant_role_id === 6){
                $password = $request->input('restaurant_pin');
                $user->password = bcrypt($password);
                if (!$id){
                    $user->api_token = Str::random(50);
                }
            }elseif ($request->has('password')) {
                if (config('tenant.password_change')) {
                    $user->password = bcrypt($request->input('password'));
                }
            }

            $user->establishment_id = ($request->input('establishment_id'))?$request->input('establishment_id'):1;
            $user->restaurant_role_id = $request->input('restaurant_role_id');
            $user->save();

        });

        return [
            'success' => true,
            'message' => ($id) ? 'Usuario actualizado' : 'Usuario registrado'
        ];
    }

    public function userRecord($id)
    {
        $record = new UserResource(User::findOrFail($id));

        return $record;
    }

    public function getEnvs()
    {
        $envs = RestaurantTableEnv::get()->transform(function ($item) {
            $enabled_edit = $item->id === 1 ? false : true;
            return [
                'id' => $item->id,
                'name' => $item->name,
                'original_name' => $item->name,
                'tables_quantity' => $item->tables_quantity,
                'active' => (bool)$item->active,
                'enabled_edit' => $enabled_edit,
                'is_editing' => false,
            ];
        });

        return [
            'success' => true,
            'data' => $envs
        ];
    }

    public function updateTableEnv(Request $request)
    {
        $tableEnvFound = RestaurantTableEnv::where('name',$request->name)->where('id','<>',$request->id)->first();

        if($tableEnvFound){
            return [
                'success' => false,
                'message' => 'Nombre ya existe.',
            ];
        }

        $tableEnv = RestaurantTableEnv::findOrFail($request->id);

        RestaurantTable::where('environment', $tableEnv->name)->update(['environment' => $request->name]);

        $tableEnv->name = $request->name;
        $tableEnv->tables_quantity = $request->tables_quantity;
        $tableEnv->active = $request->active;
        $tableEnv->save();

        $this->generateMesas();

        return [
            'success' => true,
            'data' => $tableEnv,
            'message' => 'Ambiente actualizado con éxito.',
        ];
    }

    public function createTableEnv(Request $request)
    {
        $tableEnvFound = RestaurantTableEnv::where('name', $request->name)->first();

        if($tableEnvFound){
            return [
                'success' => false,
                'message' => 'Nombre ya existe.',
            ];
        }

        $tableEnv = new RestaurantTableEnv();
        $tableEnv->name = $request->name;
        $tableEnv->tables_quantity = $request->tables_quantity;
        $tableEnv->active = true;
        $tableEnv->save();

        $this->generateMesas();

        return [
            'success' => true,
            'data' => $tableEnv,
            'message' => 'Ambiente creado con éxito.',
        ];
    }

    /**
     * Activa o desactiva una mesa
     */
    public function toggleActive(Request $request)
    {
        $request->validate([
            'table_id' => 'required|integer|exists:tenant.restaurant_tables,id'
        ]);

        $table = RestaurantTable::find($request->table_id);

        // No permitir desactivar si tiene pedidos
        if ($table->status === 'NO DISPONIBLE' && $table->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'No puede desactivar una mesa con pedidos activos'
            ], 400);
        }

        // No permitir desactivar si es parte de un grupo
        if ($table->group_id && $table->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Esta mesa ya pertence a un grupo. Separarla primero.'
            ], 400);
        }

        // Toggle estado
        $table->is_active = !$table->is_active;
        $table->save();

        return response()->json([
            'success' => true,
            'is_active' => $table->is_active,
            'message' => $table->is_active
                ? "Mesa {$table->label} activada"
                : "Mesa {$table->label} fuera de servicio"
        ]);
    }

    /**
     * Actualiza el nombre del cliente de una mesa
     */
    public function updateCliente(Request $request)
    {
        $request->validate([
            'table_id' => 'required|integer|exists:tenant.restaurant_tables,id',
            'cliente' => 'nullable|string|max:255'
        ]);

        try {
            $table = RestaurantTable::findOrFail($request->table_id);
            
            // Solo permitir si la mesa está ocupada
            if ($table->status !== 'notavailable') {
                return response()->json([
                    'success' => false,
                    'message' => 'Abrir mesa para editar cliente.'
                ], 400);
            }
            
            // Actualizar cliente
            $table->cliente = $request->cliente ?? '';
            $table->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado correctamente',
                'data' => [
                    'id' => $table->id,
                    'cliente' => $table->cliente,
                    'label' => $table->label
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

}

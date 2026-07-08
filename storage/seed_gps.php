<?php
/**
 * Seed productos, servicios y clientes relacionados al rastreo GPS.
 * Uso: php storage/seed_gps.php
 * Idempotente: salta registros cuyo internal_id (items) o number (persons) ya existen.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('http://demositech.demo.sitech.site/', 'GET');
$request->headers->set('Host', 'demositech.demo.sitech.site');
$app->instance('request', $request);

$app->make(Hyn\Tenancy\Environment::class)->identifyHostname();

use App\Models\Tenant\Item;
use App\Models\Tenant\Person;

$warehouseId = 1;
$currencyPEN = 'PEN';

// ── Productos GPS (item_type_id=01, unit_type_id=NIU) ──────────────────────
$products = [
    ['name' => 'GPS Rastreador Vehicular GT-300',           'desc' => 'Rastreador GPS vehicular con bateria de respaldo, SIM card no incluida', 'price' => 320.00, 'model' => 'GT-300'],
    ['name' => 'GPS Rastreador Personal Mini GT-Mini',       'desc' => 'Rastreador GPS personal portatil, magnetico, waterproof IP67',          'price' => 180.00, 'model' => 'GT-Mini'],
    ['name' => 'GPS Tracker para Motocicleta GT-Moto',       'desc' => 'Rastreador GPS para motocicletas con corte de combustible remoto',     'price' => 250.00, 'model' => 'GT-Moto'],
    ['name' => 'GPS Rastreador de Carga/Flota GT-Heavy',     'desc' => 'Rastreador GPS para carga pesada, antena externa, bateria 10000mAh',  'price' => 480.00, 'model' => 'GT-Heavy'],
    ['name' => 'Antena GPS Externa Magnetica',               'desc' => 'Antena GPS magnetica de 3 metros para rastreadores vehiculares',      'price' => 45.00,  'model' => 'ANT-MAG-3M'],
    ['name' => 'Relay/Corte de Motor para GPS',              'desc' => 'Modulo relay para corte de encendido remoto via GPS',                  'price' => 35.00,  'model' => 'RELAY-12V'],
    ['name' => 'Sensor de Combustible para GPS',             'desc' => 'Sensor capacitivo de nivel de combustible integrable a GPS tracker',  'price' => 220.00, 'model' => 'FUEL-SEN'],
    ['name' => 'Camara Dashcam con GPS Integrado',           'desc' => 'Camara vehicular con GPS, vision nocturna y grabacion en SD',         'price' => 350.00, 'model' => 'DASH-GPS'],
];

// ── Servicios GPS (item_type_id=02, unit_type_id=ZZ) ───────────────────────
$services = [
    ['name' => 'Instalacion de Rastreador GPS Vehicular',    'desc' => 'Servicio de instalacion de GPS tracker en vehiculo, incluye cableado', 'price' => 80.00],
    ['name' => 'Plan Mensual Rastreo GPS - Flota (1 vehiculo)','desc' => 'Suscripcion mensual: plataforma, reportes, geocercas, alertas',     'price' => 45.00],
    ['name' => 'Plan Anual Rastreo GPS - Flota (1 vehiculo)', 'desc' => 'Suscripcion anual: plataforma, reportes, geocercas, alertas (12 meses)','price' => 480.00],
    ['name' => 'Capacitacion Plataforma de Rastreo GPS',      'desc' => 'Capacitacion online de 2 horas para uso de la plataforma GPS',       'price' => 120.00],
    ['name' => 'Mantenimiento Preventivo Flota GPS',          'desc' => 'Revisi\u00f3n y mantenimiento preventivo de dispositivos GPS instalados', 'price' => 60.00],
    ['name' => 'Configuracion de Geocercas y Alertas',        'desc' => 'Configuracion de geocercas, horarios y alertas en plataforma GPS',   'price' => 50.00],
    ['name' => 'Recuperacion de Vehiculo (Servicio Antirobo)','desc' => 'Servicio de seguimiento y apoyo en recuperacion de vehiculo robado', 'price' => 150.00],
];

// ── Clientes (persons type=customers) ──────────────────────────────────────
// identity_document_type_id: 6=RUC, 1=DNI
$customers = [
    ['iddoc' => '6', 'number' => '20512345671', 'name' => 'Transportes Ruta Norte S.A.C.',     'trade' => 'Ruta Norte',     'address' => 'Av. Los Bosques 1200, Lima',     'phone' => '987654321', 'email' => 'admin@rutanorte.com'],
    ['iddoc' => '6', 'number' => '20612345672', 'name' => 'Logistica Express del Peru S.A.C.',  'trade' => 'LogExpress',     'address' => 'Jr. Industrial 456, Arequipa',   'phone' => '955443322', 'email' => 'ops@logexpress.com'],
    ['iddoc' => '6', 'number' => '20412345673', 'name' => 'Distribuidora Andina E.I.R.L.',      'trade' => 'DistAndina',     'address' => 'Av. La Marina 789, Callao',      'phone' => '933221100', 'email' => 'ventas@distandina.com'],
    ['iddoc' => '6', 'number' => '20312345674', 'name' => 'Flota Carga Pesada del Sur S.A.C.',  'trade' => 'CargaSur',       'address' => 'Panamericana Sur Km 12, Ica',   'phone' => '977889900', 'email' => 'flota@cargapesadasur.com'],
    ['iddoc' => '1', 'number' => '70123456',    'name' => 'Carlos Mendoza Ruiz',                'trade' => '',               'address' => 'Av. Javier Prado 300, Lima',     'phone' => '981234567', 'email' => 'carlos.mendoza@gmail.com'],
    ['iddoc' => '1', 'number' => '70876543',    'name' => 'Ana Lucia Flores Salazar',           'trade' => '',               'address' => 'Calle Las Palmeras 55, Trujillo','phone' => '976543210', 'email' => 'ana.flores@hotmail.com'],
    ['iddoc' => '6', 'number' => '20698765432', 'name' => 'Mudanzas y Transportes El Rapido S.A.C.', 'trade' => 'El Rapido', 'address' => 'Av. Argentina 880, Lima',        'phone' => '945678912', 'email' => 'admin@elrapido.com'],
    ['iddoc' => '6', 'number' => '20112345678', 'name' => 'Agroindustrial Valle Verde S.A.C.',  'trade' => 'Valle Verde',    'address' => 'Carretera Central Km 40, Chosica','phone' => '962345678','email' => 'compras@valleverde.com'],
];

$createdItems = 0;
$createdPersons = 0;
$skippedItems = 0;
$skippedPersons = 0;

// Tomar el max internal_id actual para generar los nuevos secuencialmente
$maxId = (int) Item::max('id');
$nextId = $maxId + 1;

foreach ($products as $p) {
    if (Item::where('name', $p['name'])->exists()) {
        $skippedItems++;
        $nextId++;
        continue;
    }
    $internalId = str_pad($nextId, 5, '0', STR_PAD_LEFT);
    Item::create([
        'name' => $p['name'],
        'description' => $p['desc'],
        'item_type_id' => '01',
        'internal_id' => $internalId,
        'barcode' => $internalId,
        'model' => $p['model'],
        'unit_type_id' => 'NIU',
        'currency_type_id' => $currencyPEN,
        'sale_unit_price' => $p['price'],
        'purchase_unit_price' => 0,
        'has_igv' => 1,
        'purchase_has_igv' => 1,
        'sale_affectation_igv_type_id' => '10',
        'purchase_affectation_igv_type_id' => '10',
        'stock' => 0,
        'stock_min' => 1,
        'amount_plastic_bag_taxes' => 0,
        'has_plastic_bag_taxes' => 0,
        'active' => 1,
        'status' => 1,
        'is_set' => 0,
        'is_dish' => 0,
        'apply_store' => 0,
        'apply_restaurant' => 0,
        'warehouse_id' => $warehouseId,
        'calculate_quantity' => 0,
        'lots_enabled' => 0,
        'series_enabled' => 0,
        'has_isc' => 0,
        'has_perception' => 0,
        'exchange_points' => 0,
        'quantity_of_points' => 0,
        'commission_amount' => 0,
        'percentage_of_profit' => 0,
        'percentage_isc' => 0,
        'suggested_price' => 0,
    ]);
    echo "ITEM  (prod) [{$internalId}] {$p['name']} - S/ " . number_format($p['price'], 2) . PHP_EOL;
    $createdItems++;
    $nextId++;
}

foreach ($services as $s) {
    if (Item::where('name', $s['name'])->exists()) {
        $skippedItems++;
        $nextId++;
        continue;
    }
    $internalId = str_pad($nextId, 5, '0', STR_PAD_LEFT);
    Item::create([
        'name' => $s['name'],
        'description' => $s['desc'],
        'item_type_id' => '02',
        'internal_id' => $internalId,
        'barcode' => $internalId,
        'unit_type_id' => 'ZZ',
        'currency_type_id' => $currencyPEN,
        'sale_unit_price' => $s['price'],
        'purchase_unit_price' => 0,
        'has_igv' => 1,
        'purchase_has_igv' => 1,
        'sale_affectation_igv_type_id' => '10',
        'purchase_affectation_igv_type_id' => '10',
        'stock' => 0,
        'stock_min' => 0,
        'amount_plastic_bag_taxes' => 0,
        'has_plastic_bag_taxes' => 0,
        'active' => 1,
        'status' => 1,
        'is_set' => 0,
        'is_dish' => 0,
        'apply_store' => 0,
        'apply_restaurant' => 0,
        'warehouse_id' => $warehouseId,
        'calculate_quantity' => 0,
        'lots_enabled' => 0,
        'series_enabled' => 0,
        'has_isc' => 0,
        'has_perception' => 0,
        'exchange_points' => 0,
        'quantity_of_points' => 0,
        'commission_amount' => 0,
        'percentage_of_profit' => 0,
        'percentage_isc' => 0,
        'suggested_price' => 0,
    ]);
    echo "ITEM  (serv) [{$internalId}] {$s['name']} - S/ " . number_format($s['price'], 2) . PHP_EOL;
    $createdItems++;
    $nextId++;
}

foreach ($customers as $c) {
    if (Person::where('type', 'customers')->where('number', $c['number'])->exists()) {
        $skippedPersons++;
        continue;
    }
    Person::create([
        'type' => 'customers',
        'identity_document_type_id' => $c['iddoc'],
        'number' => $c['number'],
        'name' => $c['name'],
        'trade_name' => $c['trade'],
        'country_id' => 'PE',
        'nationality_id' => 'PE',
        'address' => $c['address'],
        'telephone' => $c['phone'],
        'email' => $c['email'],
        'enabled' => 1,
        'status' => 1,
        'establishment_code' => '0000',
        'accumulated_points' => 0,
        'credit_days' => 0,
        'parent_id' => 0,
        'perception_agent' => false,
        'percentage_perception' => 0,
        'has_discount' => false,
        'discount_type' => '01',
        'discount_amount' => 0,
        'is_agent_retention' => false,
        'contact' => ['full_name' => '', 'phone' => ''],
    ]);
    echo "PERSON (cust) [{$c['number']}] {$c['name']}" . PHP_EOL;
    $createdPersons++;
}

echo PHP_EOL . "── Resumen ──" . PHP_EOL;
echo "Items    creados: {$createdItems} (omitidos: {$skippedItems})" . PHP_EOL;
echo "Clientes creados: {$createdPersons} (omitidos: {$skippedPersons})" . PHP_EOL;

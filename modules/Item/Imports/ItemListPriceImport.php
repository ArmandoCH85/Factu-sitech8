<?php

namespace Modules\Item\Imports;

use App\Models\Tenant\Catalogs\UnitType;
use App\Models\Tenant\Item;
use App\Models\Tenant\Warehouse;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\Item\Models\Category;
use Modules\Item\Models\Brand;
use App\Models\Tenant\ItemUnitType;
use App\Models\Tenant\ItemUnitTypePrice;
use App\Models\Tenant\PriceLabel;

class ItemListPriceImport implements ToCollection
{
    use Importable;

    protected $data;

    public function collection(Collection $rows)
    {
            $total = count($rows);
            $registered = 0;
            unset($rows[0]);

            foreach ($rows as $row)
            {
                $internal_id = ($row[0])?:null; // Codigo interno
                $unit_type_id = $row[1]; // Unidad 
                $factor = $row[2]; // Factor
                // dd($row->slice(3));
                $prices = $row->slice(3)->values();
                $item = null;

                if($internal_id) {
                    $item = Item::where('internal_id', $internal_id)
                                    ->first();
                }
                
                if($item) {
                    $item_unit_type = ItemUnitType::where('item_id', $item->id)
                                                    ->first();
                    if(!$item_unit_type){

                        $description = UnitType::find($unit_type_id)->description;
                        $itemUnitType = $item->item_unit_types()->create([
                            'description' => $description,
                            'unit_type_id' => $unit_type_id,
                            'quantity_unit' => $factor,
                            'price1' => 0,
                            'price2' => 0,
                            'price3' => 0,
                            // 'price_default' => $price_default,
                        ]);

                        foreach ($prices as $index => $price) {
                            $priceLabel = PriceLabel::where('position', ($index + 1));
                            ItemUnitTypePrice::create([
                                'item_unit_type_id' => $itemUnitType->id,
                                'price' => $price,
                                'price_label_id' => $priceLabel->first()->id
                            ]);
                        }
                    }

                    $registered += 1;

                } 

            }

            $this->data = compact('total', 'registered');

    }

    public function getData()
    {
        return $this->data;
    }
}

<?php

namespace Modules\Inventory\Providers;

use Modules\Order\Models\OrderNote;
use App\Models\Tenant\Item;
use App\Models\Tenant\Document;
use Illuminate\Support\ServiceProvider;
use Modules\Inventory\Traits\InventoryTrait;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\Note;

class InventoryVoidedServiceProvider extends ServiceProvider
{
    use InventoryTrait;

    public function register()
    {
    }

    public function boot()
    {
        $this->voided();
        $this->voidedCreditNote();
        $this->voided_order_note();
        $this->voided_dispatch();
        $this->verifyRelatedPrepaymentDocument();
    }

    private function voided()
    {
        //Revisar los tipos de documentos, ello varia el control de stock en las anulaciones.
        Document::updated(function ($document) {
            // if($document['document_type_id'] == '01' || $document['document_type_id'] == '03'){
            if(in_array($document['document_type_id'], ['01', '03', '08'], true))
            {
                if(in_array($document['state_type_id'], [ '09', '11' ], true)){
                    // $warehouse = $this->findWarehouse($document['establishment_id']);

                    foreach ($document['items'] as $detail) {
                        // dd($detail['item']->presentation);

                        if(!$detail->item->is_set){

                            $warehouse = ($detail->warehouse_id) ? $this->findWarehouse($this->findWarehouseById($detail->warehouse_id)->establishment_id) : $this->findWarehouse($document['establishment_id']);

                            $presentationQuantity = $this->getPresentationQuantity($detail['item']);

                            $this->createInventoryKardex($document, $detail['item_id'], $detail['quantity'] * $presentationQuantity, $warehouse->id);

                            if(!$detail->document->sale_note_id && !$detail->document->order_note_id && !$detail->document->dispatch_id && !$detail->document->sale_notes_relateds){

                                $this->updateStock($detail['item_id'], $detail['quantity'] * $presentationQuantity, $warehouse->id);

                            }else{

                                if($detail->document->dispatch){

                                    if(!$detail->document->dispatch->transfer_reason_type->discount_stock){
                                        // $warehouse = $this->findWarehouse($document['establishment_id']);
                                        $this->updateStock($detail['item_id'], $detail['quantity'] * $presentationQuantity, $warehouse->id);
                                    }
                                }
                            }

                            $this->updateDataLots($detail);

                        }
                        else{

                            $this->voidedDocumentItemSet($detail);

                        }

                    }

                    $this->voidedWasDeductedPrepayment($document);

                }
            }
        });
    }


    /**
     *
     * Flujo para nota credito cuando se anula o rechaza
     *
     * @return void
     */
    public function voidedCreditNote()
    {
        Document::updated(function ($document) {

            if($document->isCreditNote() && $document->isVoidedOrRejected())
            {
                // si es nota credito tipo 13, no se asocia a inventario
                if($document->isCreditNoteAndType13()) return;

                foreach ($document->items as $document_item)
                {
                    if(!$document_item->item->is_set)
                    {
                        $warehouse = ($document_item->warehouse_id) ? $this->findWarehouse($this->findWarehouseById($document_item->warehouse_id)->establishment_id) : $this->findWarehouse($document->establishment_id);
                        $presentation_quantity = $this->getPresentationQuantity($document_item->item);

                        $factor = -1;
                        $calculate_quantity = $factor * ($document_item->quantity * $presentation_quantity);

                        $this->createInventoryKardex($document, $document_item->item_id, $calculate_quantity, $warehouse->id);

                        if(!$document_item->document->sale_note_id && !$document_item->document->order_note_id && !$document_item->document->dispatch_id && !$document_item->document->sale_notes_relateds)
                        {
                            $this->updateStock($document_item->item_id, $calculate_quantity, $warehouse->id);
                        }
                    }
                }
            }
        });
    }



    private function voidedWasDeductedPrepayment($document)
    {

        if($document->prepayments){

            foreach ($document->prepayments as $row) {
                $fullnumber = explode('-', $row->number);
                $series = $fullnumber[0];
                $number = $fullnumber[1];

                $doc = Document::where([['series',$series],['number',$number]])->first();
                if($doc){
                    $doc->was_deducted_prepayment = false;
                    $doc->pending_amount_prepayment += $row->total;
                    $doc->save();
                }
            }
        }

    }

    /**
     *
     * Verificar documento relacionado a la nota de credito para liberar el monto del anticipo informado
     *
     * @return void
     */
    private function verifyRelatedPrepaymentDocument()
    {

        Note::created(function ($note) {

            //si es nc y tiene tipo de nc igual a "Anulación de la operación"
            if($note->document->document_type_id === '07' && $note->note_credit_type_id === '01')
            {
                $affected_document = $note->affected_document;

                if($affected_document)
                {
                    //si el cpe relacionado tiene anticipos y el total de la nota es igual al del cpe afectado
                    if($affected_document->prepayments && $note->document->total == $affected_document->total)
                    {
                        foreach($affected_document->prepayments as $row)
                        {
                            $number_full = explode('-', $row->number);
                            $find_document = Document::whereFilterWithOutRelations()->where([['series', $number_full[0]],['number', $number_full[1]]])->first();

                            if($find_document)
                            {
                                $find_document->pending_amount_prepayment += $row->total;

                                if($find_document->pending_amount_prepayment <= $find_document->total)
                                {
                                    $find_document->was_deducted_prepayment = false;
                                    $find_document->save();
                                }
                            }
                        }
                    }

                }
            }

        });

    }





    private function voided_order_note(){

        OrderNote::updated(function ($order_note) {

            if(in_array($order_note->state_type_id, [ '09', '11' ], true)){

                $warehouse = $this->findWarehouse($order_note->establishment_id);

                foreach ($order_note->items as $order_note_item) {

                    $presentationQuantity = $this->getPresentationQuantity($order_note_item->item);

                    $this->createInventoryKardex($order_note, $order_note_item->item_id, $order_note_item->quantity * $presentationQuantity, $warehouse->id);
                    $this->updateStock($order_note_item->item_id, $order_note_item->quantity * $presentationQuantity, $warehouse->id);

                }

            }

        });

    }



    private function voided_dispatch()
    {
        Dispatch::updated(function ($dispatch) {

            // dd($dispatch, $dispatch['state_type_id'],$dispatch->state_type_id);
            if($dispatch->transfer_reason_type == null) {
                $dispatch = Dispatch::where('id', $dispatch->id)->first();
            }
            if(isset($dispatch->transfer_reason_type->discount_stock) && $dispatch->transfer_reason_type->discount_stock){

                if(in_array($dispatch->state_type_id, [ '09', '11' ], true)){

                    $warehouse = $this->findWarehouse($dispatch->establishment_id);

                    foreach ($dispatch->items as $detail) {

                        $this->createInventoryKardex($dispatch, $detail->item_id, $detail->quantity, $warehouse->id);

                        if(!$detail->dispatch->reference_sale_note_id && !$detail->dispatch->reference_order_note_id && !$detail->dispatch->reference_document_id){
                            $this->updateStock($detail->item_id, $detail->quantity, $warehouse->id);
                        }

                        $this->updateDataLots($detail);
                    }
                }
            }
        });
    }


    /**
     * Obtiene de forma segura el factor de presentación (quantity_unit) de un item.
     * Busca en orden: presentation->quantity_unit, item_unit_types[0]->quantity_unit, unit_type[0]->quantity_unit.
     * Devuelve 1 si no encuentra valor.
     *
     * @param mixed $item
     * @return float|int
     */
    private function getPresentationQuantity($item)
    {
        $default = 1;
        if (!$item) return $default;

        try {
            if (isset($item->presentation)) {
                if (is_object($item->presentation) && isset($item->presentation->quantity_unit)) return (float)$item->presentation->quantity_unit;
                if (is_array($item->presentation) && isset($item->presentation['quantity_unit'])) return (float)$item->presentation['quantity_unit'];
            }

            if (isset($item->item_unit_types) && count($item->item_unit_types) > 0) {
                $first = null;
                if (is_array($item->item_unit_types)) {
                    $first = $item->item_unit_types[0] ?? null;
                } elseif (method_exists($item->item_unit_types, 'first')) {
                    $first = $item->item_unit_types->first();
                } else {
                    $first = $item->item_unit_types[0] ?? null;
                }
                if ($first) {
                    if (is_object($first) && isset($first->quantity_unit)) return (float)$first->quantity_unit;
                    if (is_array($first) && isset($first['quantity_unit'])) return (float)$first['quantity_unit'];
                }
            }

            if (isset($item->unit_type) && count($item->unit_type) > 0) {
                $first = null;
                if (is_array($item->unit_type)) {
                    $first = $item->unit_type[0] ?? null;
                } elseif (method_exists($item->unit_type, 'first')) {
                    $first = $item->unit_type->first();
                } else {
                    $first = $item->unit_type[0] ?? null;
                }
                if ($first) {
                    if (is_object($first) && isset($first->quantity_unit)) return (float)$first->quantity_unit;
                    if (is_array($first) && isset($first['quantity_unit'])) return (float)$first['quantity_unit'];
                }
            }
            // Si no encontramos presentación en el objeto proporcionado, intentar cargar el Item desde la base de datos
            $itemId = null;
            if (is_object($item) && isset($item->id)) $itemId = $item->id;
            if (!$itemId && is_object($item) && isset($item->item_id)) $itemId = $item->item_id;
            if (!$itemId && is_array($item) && isset($item['id'])) $itemId = $item['id'];
            if (!$itemId && is_array($item) && isset($item['item_id'])) $itemId = $item['item_id'];

            if ($itemId) {
                try {
                    $dbItem = Item::with(['presentation','item_unit_types','unit_type'])->find($itemId);
                    if ($dbItem) {
                        if (isset($dbItem->presentation) && isset($dbItem->presentation->quantity_unit)) return (float)$dbItem->presentation->quantity_unit;
                        if (isset($dbItem->item_unit_types) && count($dbItem->item_unit_types) > 0) {
                            $first = $dbItem->item_unit_types[0];
                            if (isset($first->quantity_unit)) return (float)$first->quantity_unit;
                        }
                        if (isset($dbItem->unit_type) && is_array($dbItem->unit_type) && count($dbItem->unit_type) > 0) {
                            $first = $dbItem->unit_type[0];
                            if (isset($first->quantity_unit)) return (float)$first['quantity_unit'];
                        }
                    }
                } catch (\Exception $e) {
                    // ignore and return default below
                }
            }
        } catch (\Exception $e) {
            return $default;
        }

        return $default;
    }

}

<?php

namespace App\Repositories\Warehouse;

use App\Models\Warehouse\Container;
use App\Repositories\AbstractRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContainerRepository extends AbstractRepository{

    public function get(Request $request)
    {
        $query = Container::query();

        if ( !Auth::user()->isAdmin() ){
            $query->where('user_id',Auth::id());
        }
        if($request->has('dispatchNumber')){
           $query->where('dispatch_number', 'LIKE', '%' . $request->dispatchNumber . '%');
        } 
        if($request->has('sealNo')){
          $query->where('seal_no', 'LIKE', '%' . $request->sealNo . '%');
        } 
        if($request->has('packetType')){
            $query->where('services_subclass_code', 'LIKE', '%' . $request->packetType . '%');
        } 
        return $query->where(function($query) {
                 $query->where('services_subclass_code','NX')
                        ->orWhere('services_subclass_code','IX')
                        ->orWhere('services_subclass_code','XP');
                })->latest()->paginate(50);
    }

    public function store(Request $request)
    {
        try {
            $container =  Container::create([
                'user_id' => Auth::id(),
                'dispatch_number' => 0,
                'origin_country' => 'US',
                'seal_no' => $request->seal_no,
                'origin_operator_name' => 'HERC',
                'postal_category_code' => 'A',
                'destination_operator_name' => $request->destination_operator_name,
                'unit_type' => $request->unit_type,
                'services_subclass_code' => $request->services_subclass_code
            ]);

            $container->update([
                'dispatch_number' => $container->id
            ]);

            return $container;

        } catch (\Exception $ex) {
            $this->error = $ex->getMessage();
            return null;
        }
    }

    public function update(Container $container, Request $request)
    {
        try {
            return  $container->update([
                'destination_operator_name' => $request->destination_operator_name,
                'seal_no' => $request->seal_no,
                'unit_type' => $request->unit_type
            ]);

        } catch (\Exception $ex) {
            $this->error = $ex->getMessage();
            return null;
        }
    }

    public function delete(Container $container, bool $force = false)
    {
        try {
            if ( $force ){
                $container->forceDelete();
            }

            $container->deliveryBills()->delete();
            $container->orders()->sync([]);
            $container->delete();

            return true;
        } catch (\Exception $ex) {
            $this->error = $ex->getMessage();
            return null;
        }
    }
}

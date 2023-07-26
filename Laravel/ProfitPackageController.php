<?php

namespace App\Http\Controllers\Admin\Rates;

use App\Models\User;
use App\Models\ProfitPackage;
use Illuminate\Http\Request; 
use App\Models\ShippingService;
use App\Http\Controllers\Controller;
use App\Repositories\ProfitPackageRepository;
use App\Http\Requests\Admin\ProfitPackage\CreateRequest;
use App\Http\Requests\Admin\ProfitPackage\UpdateRequest;
use App\Models\ProfitSetting;

class ProfitPackageController extends Controller
{   
    public function __construct()
    {
        $this->authorizeResource(ProfitPackage::class);
    } 
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ProfitPackageRepository $repository)
    {
        $packages = $repository->get();
        return view('admin.rates.profit-packages.index',compact('packages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $shipping_services = ShippingService::all();
        return view('admin.rates.profit-packages.create' , compact('shipping_services'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateRequest $request, ProfitPackageRepository $repository)
    {   
        if ( $repository->store($request) ){
            session()->flash('alert-success',"profitpackage.created");
            return redirect()->route('admin.rates.profit-packages.index');
        }

        return back()->withInput();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(ProfitPackage $profitPackage)
    {
        $shipping_services = ShippingService::all();
        return view('admin.rates.profit-packages.edit',compact('profitPackage', 'shipping_services'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, ProfitPackage $profitPackage, ProfitPackageRepository $repository)
    {   

        if ( $repository->update($request,$profitPackage) ){
            session()->flash('alert-success','profitpackage.updated');
            return redirect()->route('admin.rates.profit-packages.index');
        }

        return back()->withInput();

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProfitPackage $profitPackage, ProfitPackageRepository $repository)
    {   

        if ( $repository->delete($profitPackage) ){
            session()->flash('alert-success','profitpackage.deleted');
        }
  
        return back();
    }

    public function packageUsers(ProfitPackage $package, ProfitPackageRepository $repository)
    {
        $users = $repository->getPackageUsers($package);
        
        return view('admin.modals.package.users', compact('package', 'users'));
    }
}

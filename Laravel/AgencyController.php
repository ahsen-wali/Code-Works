<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Agency;
use App\Models\CustomValue;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiController;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Account\CreateAccountRequest;
use App\Models\AccountPackage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\Types\Null_;

class AgencyController extends ApiController
{

    public function index(Request $request)
    {
        $where = [];
        $query = new Agency();
        if (!empty(request()->keyword)) {
            $query = $query->where('name', 'like', "%{$request->keyword}%");
        }
        if (!empty(request()->manager_id)) {
            $where['manager_id'] = request()->manager_id;
        }
        if (!empty(request()->manager_ids) && request()->manager_ids != 'all') {
            $query = $query->whereIn('manager_id', explode(',', request()->manager_ids));
        }
        if (filled($request->status) && $request->status !== 'all') {
            $query = $query->where('is_active', $request->status);
        }
        $agencies = $query->where($where)->with(['manager', 'owner', 'account_packages', 'account_packages.package'])->get()
            ->filter(function ($item) {
                return $item->name && $item->email;
            });

        return $this->successResponse('Loaded Agencies', $agencies);
    }

    public function getAccountPackage($agencyId)
    {
        try {

            // dd($agencyId);
            $packages = AccountPackage::with('package')
                ->whereHas('package', function ($q) {
                    // $q->where("available", true);
                })
                ->where('agency_id', $agencyId)->get();
            return $this->successResponse('Account Packages Loaded', $packages);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    public function store(CreateAccountRequest $request)
    {
        $user_id = null;
        try {
            // save user
            // $user = new User();
            $user = User::firstOrNew(['email' => $request->email]);
            if ($user->exists) {
                // $user->update();
            } else {
                $user->name         = $request->owner_name;
                $user->email        = $request->location_owner_email;
                if ($request->has('password')) {
                    $user->password = bcrypt($request->password);
                    $user->password_enc = base64_encode($request->password);
                } else {
                    $name = explode(' ', $request->name);
                    $password = ucfirst($name[sizeof($name) - 1]) . date('is', time());
                    $user->password = bcrypt($password);
                    $user->password_enc = base64_encode($password);
                }
                $user->api_token    = hash('md5', Str::random(60));
                $user->user_role_id = 3; // account-admin
                $user->is_active    = true;
                $user->save();
            }
            $user_id = $user->id;
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }



        try {
            $agency = Agency::create(request()->all());
            $agency->owner_id = $user->id;
            $agency->save();

            if ($request->has('account_packages')) {
                foreach ($request->get('account_packages') as $k => $p) {
                    $pack = new AccountPackage;
                    $pack->user_id        = $user_id;
                    $pack->agency_id      = $agency->id;
                    $pack->package_id     = $p['package_id'];
                    $pack->count          = $p['count'] ?? 1;
                    $pack->is_active      = true;
                    $pack->activator_id   = Auth::user()->id;
                    $pack->activation_date = Carbon::parse($p['activation_date']) ?? Carbon::now();
                    if (isset($p['deactivation_date']) && $p['deactivation_date'] != NULL) {
                        $pack->deactivation_date = Carbon::parse($p['deactivation_date']);
                        $pack->deactivator_id = Auth::user()->id;
                    }
                    $pack->save();
                }
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }



        return $this->successResponse('Agency Created Successfully!', ['agency' => $agency, 'user' => $user]);
    }


    public function show($id)
    {
        $agency = Agency::with(['custom_values', 'owner', 'account_packages', 'account_packages.package'])->find($id);
        $agency->is_active = ($agency->is_active) ? true : false;
        return $this->successResponse('Loaded Agency', $agency);
    }

    public function update(Request $request, $id)
    {
        $agency_data = request()->all();
        $agency_data['owner_name'] = $agency_data['owner']['name'];
        $owner_data = isset($agency_data['owner']) ? $agency_data['owner'] : null;
        $agency = Agency::with('custom_values', 'account_packages')->find($id);

        try {
            $website_custom_values = CustomValue::where('agency_id', $agency->id)->where('value', 'LIKE', '%' . $agency->website . '%')->get();
            foreach ($website_custom_values as $key => $cv) {
                $cv->value = str_replace($agency->website, $agency_data["website"], $cv->value);
                $cv->save();
            }
            $agency->update(request()->all());
            $manager = User::find($request->manager_id);
            $custom_value = CustomValue::where('agency_id', '=', $agency->id)->where('name', 'Account Manager')->first();
            if ($custom_value) {
                $custom_value->value = $manager->name;
                $custom_value->save();
            }
            $custom_value = CustomValue::where('agency_id', '=', $agency->id)->where('name', 'AM Email')->first();
            if ($custom_value) {
                $manager = User::find($request->manager_id);
                $custom_value->value = $manager->email;
                $custom_value->save();
            }
            $custom_value = CustomValue::where('agency_id', '=', $agency->id)->where('name', 'Email Signature Name')->first();
            if ($custom_value) {
                $custom_value->value = $agency_data["owner_name"];
                $custom_value->save();
            }
            $custom_value = CustomValue::where('agency_id', '=', $agency->id)->where('name', 'Email Promo Leads To')->first();
            if ($custom_value) {
                $custom_value->value = $agency_data["email"];
                $custom_value->save();
            }
            $custom_value = CustomValue::where('agency_id', '=', $agency->id)->where('name', 'Agency ID')->first();
            if ($custom_value) {
                $custom_value->value = $agency_data["agency_code"];
                $custom_value->save();
            }
            if ($owner_data) {
                if (isset($owner_data['password'])) {
                    $pass = $owner_data['password'];
                    $owner_data['password'] = bcrypt($pass);
                    $owner_data['password_enc'] = base64_encode($pass);
                }
                $agency->owner->update($owner_data);
            }

            $agency->is_active = $agency->is_active ? true : false;
            return $this->successResponse('Agency Updated Successfully!', $agency);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function delete($id)
    {
        $agency = Agency::find($id);
        $agency->is_active = 0;
        $agency->save();
        //$agency->delete();
        return $this->successResponse('Agency Deleted');
    }
    public function statusChange($id)
    {

        $agency = Agency::find($id);
        $agency->is_active = 0;
        $agency->save();
        return $this->successResponse('Status Changed');
    }

    public function designatePackage(Request $request, $id)
    {
        // dd($id, $request->all());
        $pack = null;
        try {
            if ($id && $request->has('package_id')) {

                $pack = AccountPackage::where('agency_id', $request->agency_id)
                    ->where('package_id', $request->get('package_id'))->first();
                if (!$pack) {
                    $pack = new AccountPackage;
                    $pack->agency_id      = $id;
                    $pack->package_id     = $request->get('package_id');
                    $pack->user_id        = $request->get('user_id');
                } else {
                    $pack->agency_id  = $request->get('agency_id');
                }
                $pack->count          = $request->get('count') ?? 1;
                $pack->is_active      = true;
                $pack->activator_id   = Auth::user()->id;
                $pack->activation_date = Carbon::parse($request->get('activation_date')) ?? Carbon::now();

                if ($request->has('deactivation_date') && $request->get('deactivation_date') != NULL) {
                    $pack->deactivation_date = Carbon::parse($request->get('deactivation_date'));
                    $pack->deactivator_id = Auth::user()->id;
                    if (Carbon::createFromFormat('Y-m-d', $request->deactivation_date)->isPast()) {
                        $pack->is_active = 0;
                    }
                }

                $pack->save();

                return $this->successResponse('Success', $pack);
            } else throw new Exception('Required Some Fields');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function removePackage(Request $request, $id, $account_package_id)
    {
        try {
            $account_package = Agency::find($id)->account_packages->find($account_package_id);
            if ($account_package) {
                $account_package->delete();
            } else throw new \Exception("Error Package ref invalid");
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
        return $this->successResponse('Agency Package Deleted');
    }

    public function toggle_account_package_activation(Request $request, $account_package_id)
    {

        try {
            $account_package = AccountPackage::find($account_package_id);
            if ($account_package) {
                $account_package->is_active = !$account_package->is_active;
                if ($account_package->is_active) {
                    $account_package->activator_id = Auth::user()->id;
                    $account_package->activation_date = Carbon::now();
                } else {
                    $account_package->deactivator_id = Auth::user()->id;
                    $account_package->deactivation_date = Carbon::now();
                }
                $account_package->save();
            } else throw new \Exception("Error Package ref invalid");
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->successResponse('Agency Package Status Updated',);
    }

    public function code($code, $token): \Illuminate\Http\JsonResponse
    {
        $agency = Agency::where("agency_code", $code)
            ->with('owner')
            ->whereHas('owner', function (Builder $query) use ($token) {
                $query->where('api_token', $token);
            })
            ->first();

        if ($agency) {
            return $this->successResponse('Success', $agency);
        }

        return $this->errorResponse('Error', $agency);
    }

    public function getManagers()
    {
    }
}

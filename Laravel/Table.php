<?php

namespace App\Http\Livewire\Order;

use App\Models\Order;
use App\Models\Country;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Table extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search;

    public $pageSize = 50;

    private $query;

    /**
     * Searchable Fields.
     */
    public $date = '';
    public $name = '';
    public $pobox = '';
    public $whr_number = '';
    public $merchant = '';
    public $carrier = '';
    public $tracking_id = '';
    public $customer_reference = '';
    public $tracking_code = '';
    public $amount = '';
    public $status = '';
    public $orderType = null;
    public $userType = null;
    public $paymentStatus = null;

    /**
     * Sort Asc.
     */
    public $sortAsc = false;
    public $sortBy = 'id';

    public function mount($userType = null)
    {
        $this->userType = $userType;
        $this->query = $this->getQuery();
    }

    public function render()
    {
        if (! $this->query) {
            $this->query = $this->getQuery();
        }

        return view('livewire.order.table', [
            'orders' => $this->query
            ->orderBy(
                $this->sortBy,
                $this->sortAsc ? 'ASC' : 'DESC'
            )
            ->paginate($this->pageSize)
        ]);
    }

    public function sortBy($name)
    {
        if ($name == $this->sortBy) {
            $this->sortAsc = ! $this->sortAsc;
        } else {
            $this->sortBy = $name;
        }
    }

    public function updatedDate()
    {
        $this->query = $this->getQuery()->where('order_date', 'LIKE', "%{$this->date}%");
    }

    public function updatedName()
    {
        $this->query = $this->getQuery()->whereHas('user', function ($query) {
            return $query->where('name', 'LIKE', "%{$this->name}%");
        });
    }

    public function updatedPobox()
    {
        $this->query = $this->getQuery()->whereHas('user', function ($query) {
            return $query->where('pobox_number', 'LIKE', "%{$this->pobox}%");
        });
    }

    public function updatedWhrNumber()
    {
        if (strlen($this->whr_number) <= 0) {
            return $this->query = $this->getQuery();
        }

        $this->query = $this->getQuery()->where('warehouse_number', 'LIKE', "%{$this->whr_number}%");
    }

    public function updatedMerchant()
    {
        $this->query = $this->getQuery()->where('merchant', 'LIKE', "%{$this->merchant}%");
    }

    public function updatedCarrier()
    {
        $this->query = $this->getQuery()->where('carrier', 'LIKE', "%{$this->carrier}%");
    }
    
    public function updatedAmount()
    {
        $this->query = $this->getQuery()->where('gross_total', 'LIKE', "%{$this->amount}%");
    }

    
    public function updatedTrackingId()
    {
        $this->query = $this->getQuery()->where('tracking_id', 'LIKE', "%{$this->tracking_id}%");
    }

    public function updatedCustomerReference()
    {
        $this->query = $this->getQuery()->where('customer_reference', 'LIKE', "%{$this->customer_reference}%");
    }

    public function updatedTrackingCode()
    {
        $this->query = $this->getQuery()->where('corrios_tracking_code', 'LIKE', "%{$this->tracking_code}%");
    }

    public function updatedStatus()
    {
        if ($this->status !== '') {
            $this->query = $this->getQuery()->where('status',$this->status);
        }
    }

    public function updatedOrderType()
    {
        if ($this->orderType === 'consolidated') {
            $this->query = $this->getQuery()->where('is_consolidated',true);
        }

        if ($this->orderType === 'non-consolidated') {
            $this->query = $this->getQuery()->where('is_consolidated',false);
        }
    }

    public function updatedPaymentStatus()
    {
        if ($this->paymentStatus === 'paid') {
            $this->query = $this->getQuery()->where('is_paid',true);
        }

        if ($this->paymentStatus === 'unpaid') {
            $this->query = $this->getQuery()->where('is_paid',false);
        }
    }

    public function getQuery()
    {
        $orders = Order::query()
            ->where('status','>=',Order::STATUS_ORDER)
            ->has('user')
            ->with([
                'paymentInvoices',
                'user',
                'subOrders',
                'parentOrder'
            ]);
        if (Auth::user()->isUser()) {
            $orders->where('user_id', Auth::id());
        }

        if($this->userType == 'domestic')
        {
            $orders = $orders->where('sender_country_id', Country::US);
            return $orders;
        }

        if ($this->userType == 'pickups') {
            $orders = $orders->where('api_pickup_response' , '!=', null);
            return $orders;
        }
        
        if($this->userType){
            $orders = $orders->whereHas('user', function ($queryUser) {
                $queryUser->whereHas('role', function ($queryRole) {
                    return $queryRole->where('name', $this->userType);
                });
            });
        }

        return $orders;
    }

    public function updating()
    {
        $this->resetPage();
    }
}

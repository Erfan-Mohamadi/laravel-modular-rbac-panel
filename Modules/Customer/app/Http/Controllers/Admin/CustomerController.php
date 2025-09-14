<?php

namespace Modules\Customer\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Customer\Models\Customer;
use Illuminate\Support\Facades\Cache;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        // Collect filter inputs
        $filters = [
            'name'   => $request->get('name'),
            'email'  => $request->get('email'),
            'mobile' => $request->get('mobile'),
            'status' => $request->get('status'),
        ];

        $page = $request->get('page', 1);

        $cacheKey = 'customers_list_' . md5(json_encode($filters) . "_page_{$page}");

        $customers = Cache::remember($cacheKey, 60 * 5, function () use ($filters) { // cache 5 minutes
            $query = Customer::query()->select('id', 'name', 'email', 'mobile', 'status', 'created_at');

            if ($filters['name']) {
                $query->where('name', 'like', "%{$filters['name']}%");
            }

            if ($filters['email']) {
                $query->where('email', 'like', "%{$filters['email']}%");
            }

            if ($filters['mobile']) {
                $query->where('mobile', 'like', "%{$filters['mobile']}%");
            }

            if (!is_null($filters['status']) && $filters['status'] !== '') {
                $query->where('status', $filters['status']);
            }

            return $query->latest('id')->paginate(15)->withQueryString();
        });

        return view('customer::admin.customer.index', compact('customers', 'filters'));
    }
}

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
        $filters = [
            'name'   => $request->get('name'),
            'email'  => $request->get('email'),
            'mobile' => $request->get('mobile'),
            'status' => $request->get('status'),
            'trashed'=> $request->get('trashed'),
        ];

        $page = $request->get('page', 1);

        $cacheKey = 'customers_list_' . md5(json_encode($filters) . "_page_{$page}");

        $customers = Cache::remember($cacheKey, 30, function () use ($filters) {
            $query = Customer::query()->select('id', 'name', 'email', 'mobile', 'status', 'created_at', 'deleted_at');

            if (!empty($filters['trashed']) && $filters['trashed'] == 1) {
                $query->onlyTrashed();
            }

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


    /**
     * Soft delete a customer.
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        Cache::flush();

        return redirect()->route('customers.index')->with('success', 'مشتری با موفقیت حذف شد.');
    }

    /**
     * Restore a soft-deleted customer.
     */
    public function restore($id)
    {
        $customer = Customer::withTrashed()->findOrFail($id);
        $customer->restore();

        Cache::flush();

        return redirect()->route('customers.index')->with('success', 'مشتری با موفقیت بازیابی شد.');
    }
}

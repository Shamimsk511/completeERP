<?php
    
namespace App\Http\Controllers;
    
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use App\Models\Tenant;
use App\Support\TenantContext;

class UserController extends Controller
{
    // Remove this line
    // use HasRoles, Notifiable;
    
    // Add constructor with middleware
    function __construct()
    {
         $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index','store']]);
         $this->middleware('permission:user-create', ['only' => ['create','store']]);
         $this->middleware('permission:user-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:user-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        $tenantId = TenantContext::currentId();
        $selectedTenantId = $request->input('tenant_id');
        if (auth()->user()?->hasRole('Super Admin')) {
            $data = User::with('tenant')
                ->when($selectedTenantId, function ($query) use ($selectedTenantId) {
                    $tenantId = (int) $selectedTenantId;
                    $query->where(function ($sub) use ($tenantId) {
                        $sub->where('tenant_id', $tenantId)
                            ->orWhereHas('tenants', function ($tenantQuery) use ($tenantId) {
                                $tenantQuery->where('tenants.id', $tenantId);
                            });
                    });
                })
                ->latest()
                ->paginate(10)
                ->appends(['tenant_id' => $selectedTenantId]);
        } else {
            $data = User::where('tenant_id', $tenantId)->with('tenant')->latest()->paginate(10);
        }
  
        $selectedTenant = null;
        if (auth()->user()?->hasRole('Super Admin') && $selectedTenantId) {
            $selectedTenant = Tenant::find($selectedTenantId);
        }

        return view('users.index',compact('data', 'selectedTenant', 'selectedTenantId'))
            ->with('i', ($request->input('page', 1) - 1) * 10);
    }
    

    public function create(Request $request): View
    {
        $roles = Role::pluck('name','name')->all();
        $tenants = auth()->user()?->hasRole('Super Admin')
            ? Tenant::orderBy('name')->get()
            : collect();
        $selectedTenantId = $request->input('tenant_id', TenantContext::currentId());

        return view('users.create',compact('roles', 'tenants', 'selectedTenantId'));
    }
    

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required'
        ];

        if (auth()->user()?->hasRole('Super Admin')) {
            $rules['tenant_id'] = 'required|integer|exists:tenants,id';
        }

        $this->validate($request, $rules);
    
        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $tenantId = TenantContext::currentId();
        if (auth()->user()?->hasRole('Super Admin') && $request->filled('tenant_id')) {
            $tenantId = (int) $request->input('tenant_id');
        }
        if ($tenantId) {
            $input['tenant_id'] = $tenantId;
        }
    
        $user = User::create($input);
        $user->assignRole($request->input('roles'));
        if ($tenantId) {
            $user->attachTenant($tenantId, true);
        }
    
        return redirect()->route('users.index')
                        ->with('success','User created successfully');
    }
    

    public function show($id): View
    {
        $user = User::find($id);

        return view('users.show',compact('user'));
    }
    

    public function edit($id): View
    {
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();
        $tenants = auth()->user()?->hasRole('Super Admin')
            ? Tenant::orderBy('name')->get()
            : collect();
        $selectedTenantId = $user->tenant_id;
    
        return view('users.edit',compact('user','roles','userRole', 'tenants', 'selectedTenantId'));
    }
    

    public function update(Request $request, $id): RedirectResponse
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'same:confirm-password',
            'roles' => 'required'
        ];

        if (auth()->user()?->hasRole('Super Admin')) {
            $rules['tenant_id'] = 'required|integer|exists:tenants,id';
        }

        $this->validate($request, $rules);
    
        $input = $request->all();
        if(!empty($input['password'])){ 
            $input['password'] = Hash::make($input['password']);
        }else{
            $input = Arr::except($input,array('password'));    
        }

        if (!auth()->user()?->hasRole('Super Admin')) {
            $input = Arr::except($input, ['tenant_id']);
        }
    
        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id',$id)->delete();
    
        $user->assignRole($request->input('roles'));

        if (auth()->user()?->hasRole('Super Admin') && !empty($input['tenant_id'])) {
            $user->attachTenant((int) $input['tenant_id'], $user->tenant_id === null);
        }
    
        return redirect()->route('users.index')
                        ->with('success','User updated successfully');
    }
    

    public function destroy($id): RedirectResponse
    {
        User::find($id)->delete();
        return redirect()->route('users.index')
                        ->with('success','User deleted successfully');
    }
}

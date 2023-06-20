<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\dashboard\roles\AssignRolesToUserRequest;
use App\Http\Requests\dashboard\roles\ShowRoleRequest;
use App\Http\Requests\dashboard\roles\StoreRoleRequest;
use App\Http\Requests\dashboard\roles\UpdateRoleRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $roles = Role::get();
            if ($roles->isEmpty()) {
                $response = 'No roles found!';
                storeApiResponse($request->api_request_id, ['message' => $response], 404, Auth::id());
                return response()->error($response, 404);
            }
            storeApiResponse($request->api_request_id, ['message' => 'All roles fetched!'], 200, Auth::id());
            return response()->success($roles, 200);
        } catch (\Exception $e) {
            return throwException('RoleController/store', $e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        try {
            $role = Role::create(['name' => $request->name]);
            storeApiResponse($request->api_request_id, $role, 201, Auth::id());
            return response()->success($role, 201);
        } catch (\Exception $e) {
            return throwException('RoleController/store', $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowRoleRequest $request, string $id)
    {
        try {
            $role = Role::find($id);
            storeApiResponse($request->api_request_id, $role, 200, Auth::id());
            return response()->success($role, 200);
        } catch (\Exception $e) {
            return throwException('RoleController/show', $e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, string $id)
    {
        try {
            Role::find($id)->update(['name' => $request->name]);
            $response = ['message' => 'Role with id ' . $id . ' successfully updated!'];
            storeApiResponse($request->api_request_id, $response, 200, Auth::id());
            return response()->success($response, 200);
        } catch (\Exception $e) {
            return throwException('RoleController/update', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShowRoleRequest $request, string $id)
    {
        try {
            Role::find($id)->delete();
            $response = ['message' => 'Role with id ' . $id . ' successfully deleted!'];
            storeApiResponse($request->api_request_id, $response, 200, Auth::id());
            return response()->success($response, 200);
        } catch (\Exception $e) {
            return throwException('RoleController/destroy', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function assignRolesToUser(AssignRolesToUserRequest $request)
    {
        try {
            $user = User::find($request->user_id);
            $role_id = Role::whereIn('id', $request->role_ids)->get();
            $user->assignRole($role_id);

            $response = ['message' => 'Roles has been assigned to ' . $user->first_name . ' ' . $user->last_name . ' successfully!'];
            storeApiResponse($request->api_request_id, $response, 200, Auth::id());
            return response()->success($response, 200);
        } catch (\Exception $e) {
            return throwException('RoleController/assignRolesToUser', $e);
        }
    }
}

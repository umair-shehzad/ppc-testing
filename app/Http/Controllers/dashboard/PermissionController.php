<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\dashboard\permissions\ShowPermissionRequest;
use App\Http\Requests\dashboard\permissions\StorePermissionRequest;
use App\Http\Requests\dashboard\permissions\UpdatePermissionRequest;
use App\Http\Requests\dashboard\permissions\AssignPermissionsToRoleRequest;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $permissions = Permission::get();
            if ($permissions->isEmpty()) {
                $response = 'No permissions found!';
                storeApiResponse($request->api_request_id, ['message' => $response], 404, Auth::id());
                return response()->error($response, 404);
            }
            storeApiResponse($request->api_request_id, ['message' => 'All permissions fetched!'], 200, Auth::id());
            return response()->success($permissions, 200);
        } catch (\Exception $e) {
            return throwException('PermissionController/store', $e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRequest $request)
    {
        try {
            $permission = Permission::create(['name' => $request->name]);
            storeApiResponse($request->api_request_id, $permission, 201, Auth::id());
            return response()->success($permission, 201);
        } catch (\Exception $e) {
            return throwException('PermissionController/store', $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowPermissionRequest $request, string $id)
    {
        try {
            $permission = Permission::find($id);
            storeApiResponse($request->api_request_id, $permission, 200, Auth::id());
            return response()->success($permission, 200);
        } catch (\Exception $e) {
            return throwException('PermissionController/show', $e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePermissionRequest $request, string $id)
    {
        try {
            Permission::find($id)->update(['name' => $request->name]);
            $response = ['message' => 'Permission with id ' . $id . ' successfully updated!'];
            storeApiResponse($request->api_request_id, $response, 200, Auth::id());
            return response()->success($response, 200);
        } catch (\Exception $e) {
            return throwException('PermissionController/update', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShowPermissionRequest $request, string $id)
    {
        try {
            Permission::find($id)->delete();
            $response = ['message' => 'Permission with id ' . $id . ' successfully deleted!'];
            storeApiResponse($request->api_request_id, $response, 200, Auth::id());
            return response()->success($response, 200);
        } catch (\Exception $e) {
            return throwException('PermissionController/destroy', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function assignPermissionsToRole(AssignPermissionsToRoleRequest $request)
    {
        try {
            $role = Role::find($request->role_id);
            $permissions = Permission::whereIn('id', $request->permission_ids)->get();
            $role->givePermissionTo($permissions);

            $response = ['message' => 'Permissions has been given to ' . $role->name . ' successfully!'];
            storeApiResponse($request->api_request_id, $response, 200, Auth::id());
            return response()->success($response, 200);
        } catch (\Exception $e) {
            return throwException('PermissionController/assignPermissionsToRole', $e);
        }
    }
}

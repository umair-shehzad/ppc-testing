<?php

namespace App\Http\Requests\dashboard\permissions;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class UpdatePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $permission_id = $this->route('permission');
        $this->merge(['permission_id' => $permission_id]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $permission_id = $this->route('permission');
        return [
            'permission_id' => 'required|numeric|exists:permissions,id',
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission_id,
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errorMessage = $validator->errors()->first();
        $response = response()->error($errorMessage, 422);
        $apiRequestId = $this->request->get('api_request_id');
        storeApiResponse($apiRequestId, $response->getContent(), 422, Auth::id());
        throw new HttpResponseException($response);
    }
}

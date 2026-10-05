<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\GenericName;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterDataController extends Controller
{
    public function index(string $resource): JsonResponse
    {
        $model = $this->model($resource);
        $fields = match ($resource) {
            'customers' => ['id', 'name', 'customer_type', 'phone', 'email', 'is_active'],
            'suppliers' => ['id', 'name', 'phone', 'email', 'is_active'],
            default => ['id', 'name', 'is_active'],
        };

        return response()->json(['data' => $model::query()->orderBy('name')->get($fields)]);
    }

    public function store(Request $request, string $resource): JsonResponse
    {
        $model = $this->model($resource);
        $data = $request->validate($this->rules($resource));
        $item = $model::query()->create([...$data, 'is_active' => $request->boolean('is_active', true)]);

        return response()->json(['message' => 'Saved successfully.', 'data' => $item], 201);
    }

    public function update(Request $request, string $resource, int $id): JsonResponse
    {
        $model = $this->model($resource);
        $item = $model::query()->findOrFail($id);
        $item->update([...$request->validate($this->rules($resource, $item)), 'is_active' => $request->boolean('is_active', true)]);

        return response()->json(['message' => 'Updated successfully.', 'data' => $item]);
    }

    public function destroy(string $resource, int $id): JsonResponse
    {
        $model = $this->model($resource);
        $model::query()->findOrFail($id)->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }

    private function model(string $resource): string
    {
        return match ($resource) {
            'suppliers' => Supplier::class,
            'brands' => Brand::class,
            'generic-names' => GenericName::class,
            'customers' => Customer::class,
            default => abort(404),
        };
    }

    private function rules(string $resource, ?Model $item = null): array
    {
        $table = (new ($this->model($resource)))->getTable();
        $rules = ['name' => ['required', 'string', 'max:255', Rule::unique($table)->ignore($item)]];
        if (in_array($resource, ['suppliers', 'customers'], true)) {
            $rules['phone'] = ['nullable', 'string', 'max:50'];
            $rules['email'] = ['nullable', 'email', 'max:255'];
        }
        if ($resource === 'customers') {
            $rules['customer_type'] = ['required', Rule::in(['retail', 'wholesale'])];
        }

        return $rules;
    }
}

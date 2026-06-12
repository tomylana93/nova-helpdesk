<?php

namespace App\Http\Requests\Admin\MasterData;

use App\Enums\AssetCategory;
use App\Enums\AssetStatus;
use App\Enums\GeneralStatus;
use App\Enums\UserStatus;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'asset_tag' => ['required', 'string', 'max:255', 'unique:assets,asset_tag'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(AssetCategory::class)],
            'status' => ['required', Rule::enum(AssetStatus::class)],
            'branch_id' => [
                'nullable',
                Rule::exists('branches', 'id')->where('status', GeneralStatus::Active->value),
            ],
            'user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('status', UserStatus::Active->value),
                $this->userBranchRule(),
            ],
        ];
    }

    private function userBranchRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! $value || ! $this->input('branch_id')) {
                return;
            }

            $matches = DB::table('users')
                ->where('id', $value)
                ->where('branch_id', $this->input('branch_id'))
                ->exists();

            if (! $matches) {
                $fail(__('admin.master_data.asset.validation.user_branch'));
            }
        };
    }
}

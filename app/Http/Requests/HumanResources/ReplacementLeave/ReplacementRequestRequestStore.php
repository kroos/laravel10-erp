<?php

namespace App\Http\Requests\HumanResources\ReplacementLeave;

use Illuminate\Foundation\Http\FormRequest;

class ReplacementRequestRequestStore extends FormRequest
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
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array
	{
		return [
			'staff_id' => 'required',
			'date_start' => 'required|date',
			'date_end' => 'required|date',
			'reason' => 'required|string',
		];
	}

	public function attributes(): array
	{
		return [
			'staff_id' => 'Staff',
			'date_start' => 'Date Start',
			'date_end' => 'Date End',
			'reason' => 'Reason',
			'customer_id' => 'Customer',
		];
	}

	public function messages(): array
	{
		return [
			//
		];
	}
}

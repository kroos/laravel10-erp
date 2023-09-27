<?php

namespace App\Http\Requests\HumanResources\ReplacementLeave;

use Illuminate\Foundation\Http\FormRequest;

class ReplacementRequestStore extends FormRequest
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
			'date_start' => 'required|date_format:Y-m-d',
			'date_end' => 'required|date_format:Y-m-d',
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
			'date_start.required' => 'Please select a start date.',
			'date_end.required' => 'Please select an end date.',
			'date_start.date_format' => 'Please insert start date in correct date format.',
			'date_end.date_format' => 'Please insert end date in correct date format.',
			'reason.required' => 'Please insert a reason.',
		];
	}
}

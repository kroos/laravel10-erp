<?php

namespace App\Http\Requests\HumanResources\Disciplinary;

use Illuminate\Foundation\Http\FormRequest;

class DisciplinaryRequestStore extends FormRequest
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
			'date' => 'required|date_format:Y-m-d',
			'disciplinary_action_id' => 'required',
			'violation_id' => 'required',
			'reason' => 'required|string',
		];
	}

	public function attributes(): array
	{
		return [
			'staff_id' => 'staff',
			'date' => 'warning date',
			'disciplinary_action_id' => 'disciplinary action',
			'violation_id' => 'violation',
			'reason' => 'reason',
		];
	}

	public function messages(): array
	{
		return [
			'staff_id.required' => 'Please select a staff.',
			'date.required' => 'Please select a warning date.',
			'date.date_format' => 'Please insert warning date in correct date format.',
			'disciplinary_action_id.required' => 'Please select a disciplinary action.',
			'violation_id.required' => 'Please select a violation.',
			'reason.required' => 'Please insert a reason.',
		];
	}
}

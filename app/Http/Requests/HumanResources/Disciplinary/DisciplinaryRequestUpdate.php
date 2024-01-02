<?php

namespace App\Http\Requests\HumanResources\Disciplinary;

use Illuminate\Foundation\Http\FormRequest;

class DisciplinaryRequestUpdate extends FormRequest
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
			'supervisor_id' => 'required',
			'disciplinary_action_id' => 'required',
			'violation_id' => 'required',
			'infraction_id' => 'required',
			'misconduct_date' => 'required|date_format:Y-m-d',
			'action_taken_date' => 'required|date_format:Y-m-d',
			'reason' => 'required|string',
			'action_to_be_taken' => 'required|string',
		];
	}

	public function attributes(): array
	{
		return [
			'staff_id' => 'staff',
			'supervisor_id' => 'supervisor incharge',
			'disciplinary_action_id' => 'disciplinary action',
			'violation_id' => 'violation',
			'infraction_id' => 'infraction',
			'misconduct_date' => 'misconduct date',
			'action_taken_date' => 'action taken date',
			'reason' => 'incident description',
			'action_to_be_taken' => 'action to be taken',
		];
	}

	public function messages(): array
	{
		return [
			'staff_id.required' => 'Please select staff.',
			'supervisor_id.required' => 'Please select supervisor incharge.',
			'disciplinary_action_id.required' => 'Please select disciplinary action.',
			'violation_id.required' => 'Please select violation.',
			'infraction_id.required' => 'Please select infraction.',
			'misconduct_date.required' => 'Please incert misconduct date.',
			'misconduct_date.date_format' => 'Please insert misconduct date in correct date format.',
			'action_taken_date.required' => 'Please incert action taken date.',
			'action_taken_date.date_format' => 'Please incert action taken date in correct date format.',
			'reason.required' => 'Please insert reason.',
			'action_to_be_taken.required' => 'Please insert action to be taken.',
		];
	}
}

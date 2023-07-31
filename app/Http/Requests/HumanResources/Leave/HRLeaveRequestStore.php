<?php

namespace App\Http\Requests\HumanResources\Leave;

use Illuminate\Foundation\Http\FormRequest;

class HRLeaveRequestStore extends FormRequest
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
			'leave_type_id' => 'required|integer',
			'reason' => 'string',
			'date_time_start' => 'required|date',
			'date_time_end' => 'sometimes|required|',
			'akuan' => 'required|integer',

			// sometimes it needs..
			'staff_id' => 'sometimes|required|integer',

		];
	}

	public function messages()
	{
		return [
			'leave_id.required' => 'Please choose your leave type',
			'date_time_start' => 'Please choose date start leave',
			'date_time_end' => 'Please choose date end leave',
			'akuan.required' => 'Please check as your acknowledgement',

			'staff_leave_replacement_id.required' => 'Please select your replacement leave',
		];
	}
}

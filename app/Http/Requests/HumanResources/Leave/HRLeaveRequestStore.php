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
			'reason' => 'required|string',
			'akuan' => 'accepted',
			'date_time_start' => 'required|date',
			'date_time_end' => 'sometimes|required|date',

			// sometimes it needs..
			'leave_id' => 'sometimes|required|integer',
			'staff_id' => 'sometimes|required|integer',
			'time_start' => 'sometimes|required',
			'time_end' => 'sometimes|required',
			'leave_type' => 'sometimes|required',
			'document' => 'sometimes|required|max:10000|mimes:jpg,bmp,png,png,doc,docx',
			'documentsupport' => 'sometimes|accepted',
		];
	}

	public function attributes(): array
	{
		return [
			'leave_type_id' => 'Leave Type',
			'reason' => 'Leave Reason',
			'date_time_start' => 'Date From',
			'date_time_end' => 'Date To',
			'akuan' => 'Acknowledgement',

			// sometimes it needs..
			'leave_id' => 'Replacement Leave',
			'staff_id' => 'Replacement Person',
			'time_start' => 'AM Time',
			'time_end' => 'PM Time',
			'leave_type' => 'Leave Category',
			'document' => 'Upload Supporting Document',
			'documentsupport' => 'Supporting Document Acknowledgement',
		];
	}

	public function messages(): array
	{
		return [
			// 'leave_type_id' => '',
			// 'reason' => '',
			// 'date_time_start' => '',
			// 'date_time_end' => '',
			// 'akuan' => '',

			// // sometimes it needs..
			// 'leave_id' => '',
			// 'staff_id' => '',
			// 'time_start' => '',
			// 'time_end' => '',
			// 'leave_type' => '',
			// 'document' => '',
			// 'documentsupport' => '',
		];
	}
}

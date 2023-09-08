<?php

namespace App\Http\Requests\HumanResources\Staff;

use Illuminate\Foundation\Http\FormRequest;

class StaffRequestStore extends FormRequest
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
			'name' => 'required',
			'ic' => 'required|unique:staffs,ic',
			'religion_id' => 'nullable',
			'gender_id' => 'required',
			'race_id' => 'nullable',
			'nationality_id' => 'nullable',
			'marital_status_id' => 'required',
			'email' => 'nullable|email|unique:staffs,email',
			'address' => 'required',
			'mobile' => 'required',
			'phone' => 'nullable',
			'dob' => 'nullable',
			'cimb_account' => 'nullable|unique:staffs,cimb_account',
			'epf_account' => 'nullable|unique:staffs,epf_account',
			'income_tax_no' => 'nullable|unique:staffs,income_tax_no',
			'socso_no' => 'nullable|unique:staffs,socso_no',
			'weight' => 'nullable',
			'height' => 'nullable',
			'join' => 'nullable',
			'authorise_id' => 'nullable',
			'status_id' => 'required',
			'category_id' => 'required',
			'branch_id' => 'required',
			'pivot_dept_id' => 'required',
			'leave_flow_id' => 'required',
			'image' => 'nullable|file|max:5120|mimes:jpeg,jpg,png,bmp',
			// 'document' => 'sometimes|file|max:5120|mimes:jpeg,jpg,png,bmp,pdf,doc,docx',

			'username' => 'required|alpha_num:ascii|unique:logins,username',
			'password' => 'required',
			'annual_leave' => 'nullable',
			'mc_leave' => 'nullable',
			'maternity_leave' => 'nullable',

			'staffspouse.*.spouse' => 'sometimes',
			'staffspouse.*.phone' => 'required_with:staffspouse.*.spouse',
			'staffspouse.*.profession' => 'nullable',
			'staffchildren.*.children' => 'sometimes',
			'staffchildren.*.gender_id' => 'required_with:staffchildren.*.children',
			'staffchildren.*.education_level_id' => 'nullable',
			'staffchildren.*.health_status_id' => 'nullable',
			'staffchildren.*.tax_exemption' => 'nullable',
			'staffchildren.*.tax_exemption_percentage_id' => 'nullable',
			'staffemergency.*.contact_person' => 'sometimes',
			'staffemergency.*.phone' => 'required_with:staffemergency.*.contact_person',
			'staffemergency.*.relationship_id' => 'required_with:staffemergency.*.contact_person',
			'staffemergency.*.address' => 'nullable',
		];
	}

	public function attributes(): array
	{
		return [
			'name' => 'Name',
			'ic' => 'Identity Card or Passport',
			'religion_id' => 'Religion',
			'gender_id' => 'Gender',
			'race_id' => 'Race',
			'nationality_id' => 'Nationality',
			'marital_status_id' => 'Marital Status',
			'email' => 'Email Address',
			'address' => 'Address',
			'mobile' => 'Mobile Number',
			'phone' => 'Phone Number',
			'dob' => 'Date Of Birth',
			'cimb_account' => 'CIMB Bank Account',
			'epf_account' => 'EPF Account',
			'income_tax_no' => 'Income Tax Account',
			'socso_no' => 'SOCSO Account',
			'weight' => 'Weight',
			'height' => 'Height',
			'join' => 'Join Date',
			'authorise_id' => 'Administrator Level',
			'status_id' => 'Status',
			'category_id' => 'Category',
			'branch_id' => 'Branch or Location',
			'pivot_dept_id' => 'Department',
			'leave_flow_id' => 'Fleave Flow Process',
			'image' => 'Self Image',

			'username' => 'Work ID No',
			'password' => 'Password',
			'annual_leave' => 'Annual Leave',
			'mc_leave' => 'Medical Certificate Leave',
			'maternity_leave' => 'Maternity Leave',

			'staffspouse.*.spouse' => 'Spouse',
			'staffspouse.*.phone' => 'Spouse Phone No',
			'staffspouse.*.profession' => 'Spouse Profession',
			'staffchildren.*.children' => 'Children Name',
			'staffchildren.*.gender_id' => 'Children Gender',
			'staffchildren.*.education_level_id' => 'Children Education Level',
			'staffchildren.*.health_status_id' => 'Children Status',
			'staffchildren.*.tax_exemption' => 'Children Tax Exemption',
			'staffchildren.*.tax_exemption_percentage_id' => 'Children Tax Exemption Percentage',
			'staffemergency.*.contact_person' => 'Emergency Contact Person',
			'staffemergency.*.phone' => 'Emergency Contact Person Phone No',
			'staffemergency.*.relationship_id' => 'Relationship with Emergency Contact Person',
			'staffemergency.*.address' => 'Emergency Contact Person Address',
		];
	}

	public function messages(): array
	{
		return [
			// 'name' => '',
			// 'ic' => '',
			// 'religion_id' => '',
			// 'gender_id' => '',
			// 'race_id' => '',
			// 'nationality_id' => '',
			// 'marital_status_id' => '',
			// 'email' => '',
			// 'address' => '',
			// 'mobile' => '',
			// 'phone' => '',
			// 'dob' => '',
			// 'cimb_account' => '',
			// 'epf_account' => '',
			// 'income_tax_no' => '',
			// 'socso_no' => '',
			// 'weight' => '',
			// 'height' => '',
			// 'join' => '',
			// 'authorise_id' => '',
			// 'status_id' => '',
			// 'category_id' => '',
			// 'branch_id' => '',
			// 'pivot_dept_id' => '',
			// 'leave_flow_id' => '',
			// 'image' => '',

			// 'username' => '',
			// 'password' => '',
			// 'annual_leave' => '',
			// 'mc_leave' => '',
			// 'maternity_leave' => '',

			// 'staffspouse.*.spouse' => '',
			// 'staffspouse.*.phone' => '',
			// 'staffspouse.*.profession' => '',
			// 'staffchildren.*.children' => '',
			// 'staffchildren.*.gender_id' => '',
			// 'staffchildren.*.education_level_id' => '',
			// 'staffchildren.*.health_status_id' => '',
			// 'staffchildren.*.tax_exemption' => '',
			// 'staffchildren.*.tax_exemption_percentage_id' => '',
			// 'staffemergency.*.contact_person' => '',
			// 'staffemergency.*.phone' => '',
			// 'staffemergency.*.relationship_id' => '',
			// 'staffemergency.*.address' => '',
		];
	}
}

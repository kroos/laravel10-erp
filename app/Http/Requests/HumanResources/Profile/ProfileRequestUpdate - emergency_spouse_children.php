<?php

namespace App\Http\Requests\HumanResources\Profile;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequestUpdate extends FormRequest
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
      'mobile' => 'required|numeric|unique:staffs,mobile,' . $this->profile['id'],
      'email' => 'required|email|unique:staffs,email,' . $this->profile['id'],
      'ic' => 'required|numeric',
      'address' => 'required',
      'dob' => 'required|date_format:Y-m-d',
      'gender_id' => 'required|integer',
      'nationality_id' => 'required|integer',
      'race_id' => 'required|integer',
      'religion_id' => 'required|integer',
      'marital_status_id' => 'required|integer',
      'emer.*.contact_person' => 'required',
      'emer.*.relationship_id' => 'required|integer',
      'emer.*.phone' => 'required|numeric',
      'emer.*.address' => 'required',
      'spou.*.spouse' => 'required',
      'spou.*.id_card_passport' => 'required|numeric',
      'spou.*.phone' => 'required|numeric',
      'spou.*.dob' => 'required|date_format:Y-m-d',
      'spou.*.profession' => 'required',
      'chil.*.children' => 'required',
      'chil.*.dob' => 'required|date_format:Y-m-d',
      'chil.*.gender_id' => 'required|integer',
      'chil.*.health_status_id' => 'required|integer',
      'chil.*.education_level_id' => 'required|integer'
    ];
  }

  public function messages(): array
  {
    return [
      'mobile.required' => 'Please insert mobile number.',
      'mobile.numeric' => 'Please insert valid mobile number.',
      'mobile.unique' => 'This mobile number has been used. Please insert another mobile number.',
      'email.required' => 'Please insert email.',
      'email.email' => 'Please insert valid email.',
      'email.unique' => 'This email has been used. Please insert another email.',
      'ic.required' => 'Please insert ic.',
      'ic.numeric' => 'Please insert valid ic.',
      'address.required' => 'Please insert address.',
      'dob.required' => 'Please insert date of birth.',
      'dob.date_format' => 'Please insert date of birth in correct date format.',
      'gender_id.required' => 'Please select a gender.',
      'nationality_id.required' => 'Please select a nationality.',
      'race_id.required' => 'Please select a race.',
      'religion_id.required' => 'Please select a religion.',
      'marital_status_id.required' => 'Please select a marital status.',
      'emer.*.contact_person.required' => 'Please insert emergency contact person.',
      'emer.*.relationship_id.required' => 'Please select a emergency relationship.',
      'emer.*.phone.required' => 'Please insert emergency phone number.',
      'emer.*.phone.numeric' => 'Please insert valid emergency phone number.',
      'emer.*.address.required' => 'Please insert emergency address.',
      'spou.*.spouse.required' => 'Please insert spouse name.',
      'spou.*.id_card_passport.required' => 'Please insert spouse ic.',
      'spou.*.id_card_passport.numeric' => 'Please insert valid spouse ic.',
      'spou.*.phone.required' => 'Please insert spouse phone number.',
      'spou.*.phone.numeric' => 'Please insert valid spouse phone number.',
      'spou.*.dob.required' => 'Please insert spouse date of birth.',
      'spou.*.dob.date_format' => 'Please insert spouse date of birth in correct date format.',
      'spou.*.profession.required' => 'Please insert spouse profession.',
      'chil.*.children.required' => 'Please insert children name.',
      'chil.*.dob.required' => 'Please insert children date of birth.',
      'chil.*.dob.date_format' => 'Please insert children date of birth in correct date format.',
      'chil.*.gender_id.required' => 'Please select a gender for children.',
      'chil.*.health_status_id.required' => 'Please select a health status for children.',
      'chil.*.education_level_id.required' => 'Please select a educational level for children.'
    ];
  }

  public function attributes(): array
  {
    return [
      'dob' => 'date of birth',
      'gender_id' => 'gender',
      'nationality_id' => 'nationality',
      'race_id' => 'race',
      'religion_id' => 'religion',
      'marital_status_id' => 'marital status',
      'emer.*.contact_person' => 'emergency contact person',
      'emer.*.relationship_id' => 'emergency contact relationship',
      'emer.*.phone' => 'emergency contact phone number',
      'emer.*.address' => 'emergency contact address',
      'spou.*.spouse' => 'spouse name',
      'spou.*.id_card_passport' => 'spouse ic number',
      'spou.*.phone' => 'spouse phone number',
      'spou.*.dob' => 'spouse date of birth',
      'spou.*.profession' => 'spouse profession',
      'chil.*.children' => 'children name',
      'chil.*.dob' => 'children date of birth',
      'chil.*.gender_id' => 'children gender',
      'chil.*.health_status_id' => 'children health status',
      'chil.*.education_level_id' => 'children educational level'
    ];
  }
}

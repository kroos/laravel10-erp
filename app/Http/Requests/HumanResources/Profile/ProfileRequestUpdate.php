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
      'ic' => 'required|integer',
      'mobile' => 'required|numeric',
      'email' => 'required|email',
      'address' => 'required',
      'dob' => 'required|date_format:Y-m-d',
      'gender_id' => 'required|integer',
      'nationality_id' => 'required|integer',
      'race_id' => 'required|integer',
      'religion_id' => 'required|integer',
      'marital_status_id' => 'required|integer',
      ['id'],
    ];
  }

  public function messages(): array
  {
    return [
      // 'ic' =>
      // 'mobile' =>
      // 'email' =>
      // 'address' =>
      // 'dob' => 'required|date_format:Y-m-d',
      // 'gender_id' => 'required|integer',
      // 'nationality_id' => 'required|integer',
      // 'race_id' => 'required|integer',
      // 'religion_id' => 'required|integer',
      // 'marital_status_id' => 'required|integer',
      // 'contact_person' =>
      // 'relationship_id' => 'required|integer',
      // 'phone' => 'required|nullable|numeric',
      // 'address' =>
    ];
  }
}

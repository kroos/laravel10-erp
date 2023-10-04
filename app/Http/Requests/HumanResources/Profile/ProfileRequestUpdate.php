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
      'password' => 'required|confirmed'
    ];
  }

  public function messages(): array
  {
    return [
      'password.required' => 'Please insert new password.'
    ];
  }

  public function attributes(): array
  {
    return [
      'password' => 'password'
    ];
  }
}

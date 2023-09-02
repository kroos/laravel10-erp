<?php

namespace App\Http\Requests\HumanResources\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequestUpdate extends FormRequest
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
      'daytype_id' => 'required',
      'in' => 'required',
      'break' => 'required',
      'resume' => 'required',
      'out' => 'required',
    ];
  }

  public function messages(): array
  {
    return [
      //
    ];
  }

  public function attributes(): array
  {
    return [
      'daytype_id' => 'Day Type',
      'in' => 'In Time',
      'break' => 'Break Time',
      'resume' => 'Resume Time',
      'out' => 'Out Time',
    ];
  }
}

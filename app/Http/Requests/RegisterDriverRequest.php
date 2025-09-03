<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
class RegisterDriverRequest extends FormRequest{  
      /**     * Determine if the user is authorized to make this request.     */
          public function authorize(): bool    
          {        return true;    }    /**     * Get the validation rules that apply to the request.     *   
            * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>     */
                public function rules(): array    {  
                          return [            
                            'name' => 'required|string|max:255',            
                            'phone' => 'required|string|unique:drivers,phone',            
                            'password' => 'required|string|min:6|confirmed',      
                          ];    }    protected function failedValidation(Validator $validator)    { 
                                   $errors = $validator->errors()->toArray();      
                                     // Transform errors to single strings instead of arrays       
                                      $formattedErrors = [];      
                                        foreach ($errors as $field => $messages) {            
             $formattedErrors[$field] = $messages[0]; // Take the first message only 
                    }        throw new HttpResponseException(response()->json([        
                            'message' => 'Validation Error',            
                            'errors' => $formattedErrors,        ], 422));    }}

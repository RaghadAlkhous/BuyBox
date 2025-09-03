<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class RegesterRequest extends FormRequest{    /**     * Determine if the user is authorized to make this request.     */ 
       public function authorize(): bool    {        return true;    }   
        /**     * Get the validation rules that apply to the request.     *     
         * @return array<string, *  \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>     */
            public function rules(): array    {       
                 return [   
                 'first_name'=>'required|alpha:ascii',  
                    'last_name'=>'required|alpha:ascii',
                     'location'=>'required',  
                        'phone_number'=>'required|unique:users|size:10',  
                      'email'=>'required|unique:users|email', 
                     'password'=>'required|confirmed|min:8|max:30',  
                       'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',      
                           'fcm-token'=>'required'       
                                                                                  //  
                                                                                        ];    }   
                    protected function failedValidation(Validator $validator)    {    
                            $errors = $validator->errors()->toArray();      
                              // Transform errors to single strings instead of arrays    
                                  $formattedErrors = [];        foreach ($errors as $field => $messages) {   
                                $formattedErrors[$field] = $messages[0]; // Take the first message only    
                             }        throw new HttpResponseException(response()->json([      
                          'message' => 'Validation Error',        
                               'errors' => $formattedErrors,        ], 422));    }}

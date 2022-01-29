<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Card;
use App\Models\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    //
    public function registerUser(Request $req){

        $respuesta = ["status" => 1, "msg" => ""];

        $validator = Validator::make(json_decode($req->getContent(),true),
        [
            "username"=>["required","unique:App\Models\User,username","max:50"],
            "email"=>["required","email","unique:App\Models\User,email","max:50"],
            "password"=>["required","regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}/"],
            "role"=>["required",Rule::in(['Particular', 'Profesional', 'Administrador'])],
        ]);

        if ($validator->fails()){
            $respuesta['status'] = 0;
            $respuesta['msg'] = $validator->errors();
          
        } else{
            //Generar el nuevo usuario
           
            $data = $req->getContent();
            $data = json_decode($data);
            $user = new User();

            $user->username = $data->username;
            $user->email = $data->email;
            $user->password = Hash::make($data->password);
            $user->role = $data->role;
           
            try{
                $user->save();
                $respuesta['msg'] = "Usuario guardado con id ".$user->id;
                               
            }catch(\Exception $e){
                $respuesta['status'] = 0;
                $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
            }
    
        }
        return response()->json($respuesta);
    }


    public function login(Request $req){

        $respuesta = ["status" => 1, "msg" => ""];
        $data = $req->getContent();
        $data = json_decode($data);

        //Buscar el nombre de usuario
        //$username = $data->username;

        $user = User::where('username', '=', $data->username)->first();

        //Validacion
        
        try{
            //Si el usuario existe
            if($user){
                if(Hash::check($data->password, $user->password)){
                    //Los datos ingresados existen y son validos
                    //Generamos el api_token
                    do{
                        $token = Hash::make($user->id.now());    
                    }while(User::where('api_token', $token)->first());

                    $user->api_token =$token;
                    $user->save();
                    $respuesta['msg'] = "Login exitoso, el token de sesion es ".$user->api_token;

                }else{
                    //El usuario existe pero la contraseña es incorrecta
                    $respuesta['status'] = 0;
                    $respuesta['msg'] = "Contrasena incorrecta, intentelo nuevamente";
                }
                
            }else{
                $respuesta['status'] = 0;
                $respuesta['msg'] = "Usuario no registrado";
            }
            
        }catch(\Exception $e){
            $respuesta['status'] = 0;
            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
        }

        return response()->json($respuesta);

    }

    public function recoverPassword(Request $req){

        $respuesta = ["status" => 1, "msg" => ""];
        //Obtenemos el email
        $data = $req->getContent();
        $data = json_decode($data);

        //Buscar el email
        $user = User::where('email', '=', $data->email)->first();

        //Validacion
        
        try{
            if($user){
                
                $user->api_token = null;
                
                //Generamos nueva contraseña aleatoria
                $characters = "0123456789aAbBcCdDeEfFgFhH";
                $characterLength = strlen($characters);
                $newPassword = '';
                for ($i=0; $i < 6; $i++) { 
                    $newPassword .= $characters[rand(0, $characterLength - 1)];
                } 

                //Le agregamos la nueva contraseña al usuario
                $user->password = Hash::make($newPassword);
                $user->save();

                //Mostramos la contraseña por consola
                $respuesta['msg'] = "La nueva contrasena es ".$newPassword;
                
            }else{
                $respuesta['status'] = 0;
                $respuesta['msg'] = "Usuario no registrado";
            }
            
        }catch(\Exception $e){
            $respuesta['status'] = 0;
            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
        }

        return response()->json($respuesta);

    }
}

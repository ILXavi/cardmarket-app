<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class ApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $req, Closure $next)
    {
        //Buscar al usuario
        if(isset($req->api_token)){
            $apitoken = $req->api_token;
            
            if($user = User::where('api_token',$apitoken)->first()){
                $respuesta["msg"] = "Api token valido";
                $req->user = $user;
                return $next($req);
            }else{
                $respuesta["status"] = 0;
                $respuesta["msg"] = "Token invalido";
            }
            
        }else{
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Token no ingresado";
        }
        
        return response()->json($respuesta);     
    }
}

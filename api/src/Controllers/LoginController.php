<?php
namespace Controllers;

use Models\User;
use Components\GenericResponse;
use Enum\UserRole;
use Components\Token;
use Components\PassManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LoginController
{
    public function login(Request $request, Response $response)
        {
            try {
                $email = $request->getParsedBody()['email'] ?? "";
                $password =  $request->getParsedBody()['password'] ?? "";
                $passwordValid = PassManager::Hash($password);
                $userLogged = User::where('email', $email)->where('password', $passwordValid)->first();

                if(!$email){
                    $response->getBody()->write(GenericResponse::obtain(false, 'Debe especificar el campo email.'));
                    $response->withStatus(401);
                }
                else if(!$password){
                    $response->getBody()->write(GenericResponse::obtain(false, 'Debe especificar el campo password.'));
                    $response->withStatus(401);
                }
                else if(!$passwordValid || !$userLogged){
                    $response->getBody()->write(GenericResponse::obtain(false, 'Credenciales invalidas.'));
                    $response->withStatus(401);
                }
                else{
                    
                    if($userLogged && $userLogged->role == UserRole::USER){
                        $token = Token::getToken($userLogged->id, $email, $userLogged->role);
                        $response->getBody()->write(GenericResponse::obtain(true, 'Bienvenidx:' ." ".$userLogged->name , $token));
                        $response->withStatus(200);
                    }                   
                    if($userLogged && $userLogged->role == UserRole::ADMIN){
                        $token = Token::getToken($userLogged->id, $email, $userLogged->role);
                        $response->getBody()->write(GenericResponse::obtain(true, 'Bienvenidx '.$userLogged->name, $token));
                        $response->withStatus(200);
                    }
                }    
               
            } catch (\Exception $e) {
                $response->getBody()->write(GenericResponse::obtain(false, $e->getMessage()));
                $response->withStatus(500);
            }
    
            return $response;
        }

}

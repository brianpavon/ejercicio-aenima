<?php
namespace Controllers;

use Models\User;
use Models\Product;
use Components\GenericResponse;
use Enum\UserRole;
use Enum\Status;
use Components\Token;
use Components\PassManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController
{

    /**
     * Función que obtiene todos los productos
     */
    public function getAll(Request $request, Response $response, $args)
    {
        try {
            $users = User::all(['id', 'name', 'email', 'created_at', 'updated_at']);
            if(!$users){
                $response->getBody()->write(GenericResponse::obtain(false,"No se encontraron productos, verifique haber dado de alta los mismos."));
                $response->withStatus(400);
            }
            else{
                $response->getBody()->write(GenericResponse::obtain(true,"Se muestran todos los productos.",$users));
                $response->withStatus(200);
            }
        } catch (\Throwable $th) {
            $response->getBody()->write(GenericResponse::obtain(false,$th->getMessage()));
            $response->withStatus(500);
        }
        return $response;
    }
    
    /**
     * Nuevo usuario
     *  */   
    public function addOne(Request $request, Response $response)
    {
        try {
            $name = $request->getParsedBody()['name'] ?? "";
            $email = $request->getParsedBody()['email']  ?? "";
            $password = $request->getParsedBody()['password']  ?? "";            
                       
            if (empty($name)) {
                $response->getBody()->write(GenericResponse::obtain(false, "Error al crear un usuario, debe especificar el nombre."));
                $response->withStatus(400);
            } else if (empty($email)) {
                $response->getBody()->write(GenericResponse::obtain(false, "Error al crear un usuario, debe especificar el email."));
                $response->withStatus(400);
            } else if (empty($password)) {
                $response->getBody()->write(GenericResponse::obtain(false, "Error al crear un usuario, debe especificar el password."));
                $response->withStatus(400);
            }      
            else if (User::where('email', '=', $email)->exists()) {
                $response->getBody()->write(GenericResponse::obtain(false, "Error al crear un usuario, el usuario ya existe.", $email));
                $response->withStatus(400);
            } 
            else {
                $user = new User();                   
                $user->email = $email;
                $user->name = $name;
                    
                $user->password = PassManager::Hash($password);
                $user->role = UserRole::USER;                    
                $user->save();

                $user->password = null;
                $response->getBody()->write(GenericResponse::obtain(true, "Usuario registrado correctamente.", $user));
                $response->withStatus(200);
            }
        } catch (\Exception $e) {
            $response->getBody()->write(GenericResponse::obtain(false, $e->getMessage()));
            $response->withStatus(500);
        }

        return $response;
    }

    /**
     * Función que obtiene los datos de un usuario, puedo simular que el admin quiere ver los datos
     */
    public function getOne(Request $request, Response $response, $args)
    {
        try {
            $id = $args['id'] ?? '';
            $user = User::where('id',$id)->first();
            $token = $request->getHeaderLine('token');            
            $admin = User::where('id',Token::getId($token))->where('users.role',UserRole::ADMIN)->first();
            if(!$token){
                $response->getBody()->write(GenericResponse::obtain(false,"No ingreso su token de identificación. Envíelo por el header."));
                $response->withStatus(400);
            }
            else if(!$admin){
                $response->getBody()->write(GenericResponse::obtain(false,"Solo un administrador puede acceder a esta sección. Verifique que sus credenciales sean correctas."));
                $response->withStatus(400);
            }
            else if(empty($id) || !is_numeric($id)){
                $response->getBody()->write(GenericResponse::obtain(false,"No se ingresó un id o verifique el id sea numérico."));
                $response->withStatus(400);
            }
            else if(empty($id) || !is_numeric($id)){
                $response->getBody()->write(GenericResponse::obtain(false,"No se ingresó un id o verifique el id sea numérico."));
                $response->withStatus(400);
            }
            else if(!$user){
                $response->getBody()->write(GenericResponse::obtain(false,"El id ingresado no corresponde a ningún producto en nuestra base de datos."));
                $response->withStatus(400);
            }
            else{                
                $user->password = null;
                $response->getBody()->write(GenericResponse::obtain(true,"Se muestran todos los productos.",$user));
                $response->withStatus(200);
            }
        } catch (\Throwable $th) {
            $response->getBody()->write(GenericResponse::obtain(false,$th->getMessage()));
            $response->withStatus(500);
        }
        return $response;
    }

}
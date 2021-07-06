<?php
namespace Controllers;

use Models\User;
use Models\Product;
use Components\GenericResponse;
use Enum\UserRole;
use Components\Token;
use stdClass;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductsController
{  
    /**
     * Función que obtiene todos los productos
     */
    public function getAll(Request $request, Response $response, $args)
    {
        try {
            $products = Product::get();
            if(!$products){
                $response->getBody()->write(GenericResponse::obtain(false,"No se encontraron productos, verifique haber dado de alta los mismos."));
                $response->withStatus(400);
            }
            else{
                $response->getBody()->write(GenericResponse::obtain(true,"Se muestran todos los productos.",$products));
                $response->withStatus(200);
            }
        } catch (\Throwable $th) {
            $response->getBody()->write(GenericResponse::obtain(false,$th->getMessage()));
            $response->withStatus(500);
        }
        return $response;
    }

    /**
     * Función que obtiene un producto pasado por url
     */
    public function getOne(Request $request, Response $response, $args)
    {
        try {
            $id = $args['id'] ?? '';
            $product = Product::where('id',$id)->first();
            if(empty($id) || !is_numeric($id)){
                $response->getBody()->write(GenericResponse::obtain(false,"No se ingresó un id o verifique el id sea numérico."));
                $response->withStatus(400);
            }
            else if(!$product){
                $response->getBody()->write(GenericResponse::obtain(false,"El id ingresado no corresponde a ningún producto en nuestra base de datos."));
                $response->withStatus(400);
            }
            else{
                $response->getBody()->write(GenericResponse::obtain(true,"Se muestran todos los productos.",$product));
                $response->withStatus(200);
            }
        } catch (\Throwable $th) {
            $response->getBody()->write(GenericResponse::obtain(false,$th->getMessage()));
            $response->withStatus(500);
        }
        return $response;
    }
    
    /**
     * Función que da de alta un nuevo producto
     */
    public function addOne(Request $request, Response $response)
    {
        try {
            $name = $request->getParsedBody()['name'] ?? '';
            $description = $request->getParsedBody()['description'] ?? '';            
            $token = $request->getHeaderLine('token');//cuando el token iba por header
            
            
            $file_exists = count($_FILES) != 0;
            if($file_exists){
                $arrayNameFile = explode(".",$_FILES['img']['name']);
                $extension = strtolower(end($arrayNameFile));                
            }
            
            //$idAdmin = Token::getId($token);
            //$admin = User::where('id',$idAdmin)->first();

            if($token != 1){
                $response->getBody()->write(GenericResponse::obtain(false,"Solo un administrador puede dar de alta productos. Verifique que sus credenciales sean correctas."));
                $response->withStatus(400);
            }
            else if(!$name){
                $response->getBody()->write(GenericResponse::obtain(false,"Se necesita el nombre del producto para proseguir."));
                $response->withStatus(400);
            }
            else if(!$description){
                $response->getBody()->write(GenericResponse::obtain(false,"Debe ingresar la descripción del producto."));
                $response->withStatus(400);
            }
            else if(!$file_exists){
                $response->getBody()->write(GenericResponse::obtain(false,"Cargue su foto de perfil."));
                $response->withStatus(400);
            }
            else if($extension != "jpg" && $extension != "jpeg" && $extension != 'png'){
                $response->getBody()->write(GenericResponse::obtain(false,"Verifique haber cargado un archivo. O elija un formato permitido de imagen (JPG,JPEG,PNG)."));
                $response->withStatus(400);
            }
            else{
                
                
                $newProduct = new Product();
                $newProduct->name = $name;
                $newProduct->description = $description;
                
                
                $newProduct->save();
                $picturesProducts = __DIR__.'/../../products/'.$newProduct->id.'/';
                $namePicture = $_FILES['img']['name'];
                if(!file_exists($picturesProducts)){
                    mkdir($picturesProducts,0777,true);
                }
                
                $filePath = $picturesProducts . $namePicture;
                                    
                move_uploaded_file($_FILES['img']['tmp_name'],$filePath);
                $newProduct->img_url = $filePath;
                $newProduct->save();

                $response->getBody()->write(GenericResponse::obtain(false,"Alta exitosa.",$newProduct));
                $response->withStatus(200);
            }
        } catch (\Throwable $th) {            
            $response->getBody()->write(GenericResponse::obtain(false,$th->getMessage()));
            $response->withStatus(500);
        }

        return $response;
    }
}
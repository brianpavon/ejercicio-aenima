<?php
namespace Controllers;

use Models\User;
use Models\Product;
use Components\GenericResponse;
use Enum\UserRole;
use Enum\Status;
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
            $token = $request->getHeaderLine('token');
            
            
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
                $picturesProducts = __DIR__.'/../../products/'.'product-'.$newProduct->id.'/';
                $namePicture = date("d-m-Y-U", strtotime('now')).'-'.'product-id-'.$newProduct->id.'.'.$extension;
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

    /**
     * Función que la imagen de un producto
     */
    public function updateImage(Request $request, Response $response,$args)
    {
        try {
            $id = $args['id'] ?? '';
            $modifiedProduct = Product::where('id',$id)->first();
            $token = $request->getHeaderLine('token');

            $file_exists = count($_FILES) != 0;           
            if($token != 1){
                $response->getBody()->write(GenericResponse::obtain(false,"Solo un administrador puede actualizar las imágenes. Verifique que sus credenciales sean correctas."));
                $response->withStatus(400);
            }
            else if(empty($id) || !is_numeric($id)){
                $response->getBody()->write(GenericResponse::obtain(false,"No se ingresó un id o verifique el id sea numérico."));
                $response->withStatus(400);
            }
            else if(!$modifiedProduct){
                $response->getBody()->write(GenericResponse::obtain(false,"El id ingresado no corresponde a ningún producto en nuestra base de datos."));
                $response->withStatus(400);
            }
            else if($modifiedProduct->status == Status::LOCKED){
                $response->getBody()->write(GenericResponse::obtain(false,"El producto seleccionado se encuentra dado de baja."));
                $response->withStatus(400);
            }
            else if(!$file_exists){
                $response->getBody()->write(GenericResponse::obtain(false,"Cargue la nueva imagen para poder proseguir."));
                $response->withStatus(400);
            }           
            else{
                $arrayNameFile = explode(".",$_FILES['img']['name']);
                $extension = strtolower(end($arrayNameFile));
                if($extension != "jpg" && $extension != "jpeg" && $extension != 'png'){
                    $response->getBody()->write(GenericResponse::obtain(false,"Verifique haber cargado un archivo. O elija un formato permitido de imagen (JPG,JPEG,PNG).",'Extension del archivo subido: '.$extension));
                    $response->withStatus(400);
                }
                else{
                    $picturesProducts = __DIR__.'/../../products/'.'product-'.$modifiedProduct->id.'/';
                    $namePicture = date("d-m-Y-U", strtotime('now')).'-'.'product-id-'.$modifiedProduct->id.'.'.$extension;
                    if(!file_exists($picturesProducts)){
                        mkdir($picturesProducts,0777,true);
                    }
                            
                    $filePath = $picturesProducts . $namePicture;
                    if(!empty($modifiedProduct->img_url)){
                        unlink($modifiedProduct->img_url);
                    }
                                            
                    move_uploaded_file($_FILES['img']['tmp_name'],$filePath);
                    $modifiedProduct->img_url = $filePath;

                    $modifiedProduct->save();
                    $response->getBody()->write(GenericResponse::obtain(true,"Se actualizo correctamente la imagen.",$modifiedProduct));
                    $response->withStatus(200);
                }
            }
        } catch (\Throwable $th) {
            $response->getBody()->write(GenericResponse::obtain(false,$th->getMessage()));
            $response->withStatus(500);
        }
        return $response;
    }

    /**
     * Función que actualiza información de un producto
     */
    public function updateOne(Request $request, Response $response,$args)
    {
        try {
            $id = $args['id'] ?? '';
            $modifiedProduct = Product::where('id',$id)->first();
            $token = $request->getHeaderLine('token');
            $data = $request->getParsedBody()['data'] ?? null;
            
            if($token != 1){
                $response->getBody()->write(GenericResponse::obtain(false,"Solo un administrador puede realizar modificaciones. Verifique que sus credenciales sean correctas."));
                $response->withStatus(400);
            }
            else if(empty($id) || !is_numeric($id)){
                $response->getBody()->write(GenericResponse::obtain(false,"No se ingresó un id o verifique el id sea numérico."));
                $response->withStatus(400);
            }
            else if(!$modifiedProduct){
                $response->getBody()->write(GenericResponse::obtain(false,"El id ingresado no corresponde a ningún producto en nuestra base de datos."));
                $response->withStatus(400);
            }
            else if($modifiedProduct->status == Status::LOCKED){
                $response->getBody()->write(GenericResponse::obtain(false,"El producto seleccionado se encuentra dado de baja."));
                $response->withStatus(400);
            }
            else if(empty($data)){
                $response->getBody()->write(GenericResponse::obtain(false,"Debe enviar un JSON con los nuevos valores."));
                $response->withStatus(400);
            }
            else{                
                $modified = false;
                foreach (json_decode($data) as $key => $value) {
                    if($key != 'id'){
                        $modifiedProduct->$key = $value;
                        $modified = true;
                    }
                }
                if(!$modified){
                    $response->getBody()->write(GenericResponse::obtain(false,"Verifique los datos ingresados, no se actualizo ningún campo."));
                    $response->withStatus(400);
                }
                else{
                    $modifiedProduct->save();
                    $response->getBody()->write(GenericResponse::obtain(true,"Producto actualizado exitosamente.",$modifiedProduct));
                    $response->withStatus(200);
                }
            }
        } catch (\Throwable $th) {
            $response->getBody()->write(GenericResponse::obtain(false,$th->getMessage()));
            $response->withStatus(500);
        }

        return $response;
    }


    /**
     * Función que realiza una baja lógica de un producto
     */
    public function deleteOne(Request $request, Response $response,$args)
    {
        try {
            $id = $args['id'] ?? '';
            $product = Product::where('id',$id)->first();
            $token = $request->getHeaderLine('token');
            
            //$admin = User::where('id',Token::getId($token))->first();

            if($token != 1){
                $response->getBody()->write(GenericResponse::obtain(false,"Solo un administrador puede eliminar productos. Verifique que sus credenciales sean correctas."));
                $response->withStatus(400);
            }
            else if(empty($id) || !is_numeric($id)){
                $response->getBody()->write(GenericResponse::obtain(false,"No se ingresó un id o verifique el id sea numérico."));
                $response->withStatus(400);
            }
            else if(!$product){
                $response->getBody()->write(GenericResponse::obtain(false,"El id ingresado no corresponde a ningún producto en nuestra base de datos."));
                $response->withStatus(400);
            }
            else if($product->status == Status::LOCKED){
                $response->getBody()->write(GenericResponse::obtain(false,"El producto seleccionado ya fue dado de baja."));
                $response->withStatus(400);
            }
            else{
                $product->status = Status::LOCKED;
                $product->save();
                $response->getBody()->write(GenericResponse::obtain(true,"Se realizo la baja lógica del producto.",$product));
                $response->withStatus(200);
            }
            
        } catch (\Throwable $th) {
            $response->getBody()->write(GenericResponse::obtain(false,$th->getMessage()));
            $response->withStatus(500);
        }
        return $response;
    }

    /**
     * Función que realiza la baja definitiva del producto, elimina el registro de la base de datos y archivos del producto
     */
    public function deleteDefinitively(Request $request, Response $response,$args)
    {
        try {
            $id = $args['id'] ?? '';
            $product = Product::where('id',$id)->first();
            $token = $request->getHeaderLine('token');
            
            //$admin = User::where('id',Token::getId($token))->first();

            if($token != 1){
                $response->getBody()->write(GenericResponse::obtain(false,"Solo un administrador puede eliminar productos. Verifique que sus credenciales sean correctas."));
                $response->withStatus(400);
            }
            else if(empty($id) || !is_numeric($id)){
                $response->getBody()->write(GenericResponse::obtain(false,"No se ingresó un id o verifique el id sea numérico."));
                $response->withStatus(400);
            }
            else if(!$product){
                $response->getBody()->write(GenericResponse::obtain(false,"El id ingresado no corresponde a ningún producto en nuestra base de datos."));
                $response->withStatus(400);
            }
            else if($product->status != Status::LOCKED){
                $response->getBody()->write(GenericResponse::obtain(false,"Primero debe realizar un baja lógica para eliminar definitivamente un producto."));
                $response->withStatus(400);
            }
            else{                
                
                unlink($product->img_url);
                $product->delete();
                $response->getBody()->write(GenericResponse::obtain(true,"Se elimino de todos nuestros registros el producto indicado.",$product));
                $response->withStatus(200);
            }
            
        } catch (\Throwable $th) {
            $response->getBody()->write(GenericResponse::obtain(false,$th->getMessage()));
            $response->withStatus(500);
        }
        return $response;
    }
}

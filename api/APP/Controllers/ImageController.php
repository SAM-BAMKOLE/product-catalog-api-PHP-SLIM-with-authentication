<?php 
declare(strict_types=1);

namespace App\Controllers;

use App\Services\ImageService;
use App\Services\ProductService;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Valitron\Validator;

class ImageController {
    private $upload_dir = APP_ROOT . "/public/uploads/";
    public function __construct(private Validator $validator, private ImageService $imageService, private ProductService $productService){}
    public function upload(Request $request, Response $response): Response {
        $request_body = $request->getParsedBody();
        $this->validator->mapFieldsRules(['product_id'=>['required', 'integer']]);
        $this->validator = $this->validator->withData($request_body);

        if(!$this->validator->validate()) {
            $response->getBody()->write(json_encode($this->validator->errors()));
            return $response->withStatus(400);
        }

        // check that product exists
        $product_exists = $this->productService->get("id", (int) $request_body['product_id']);
        if(!$product_exists) {
            throw new HttpNotFoundException($request, "Product not found");
        }

        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }

        // fetch uploaded files
        $uploaded_files = $request->getUploadedFiles();

        // check if the image file was uploaded
        if (!isset($uploaded_files['image'])) {
            throw new HttpBadRequestException($request, "No image uploaded");
        }

        $image = $uploaded_files['image'];

        // check for upload errors
        if ($image->getError() !== UPLOAD_ERR_OK) throw new HttpBadRequestException($request, "Image upload failed");

        // validate the file type
        $allowed_types = ['image/jpg', "image/jpeg", "image/png", "image/gif"];
        $file_type = $image->getClientMediaType();

        if (!in_array($file_type, $allowed_types)) {
            $response->getBody()->write(json_encode(['message'=>"Invalid file type. Only PNG, JPEG and GIF are allowed"]));
            return $response->withStatus(415);
        }

        // create a new filename
        $filename = uniqid() . "-" . $image->getClientFilename();
        $target_path = $this->upload_dir . $filename;

        // build the file URL
        $fileURL = "/uploads/" . $filename;

        // add image url to database
        $res = $this->imageService->create($fileURL, $request_body);

        if(!$res) throw new HttpInternalServerErrorException($request, "Unable to upload image");

        // move the file to the designated directory
        $image->moveTo($target_path);

        $response->getBody()->write(json_encode(['message'=>"File uploaded", "file_url"=>$fileURL]));
        return $response->withStatus(201);
    } 
}
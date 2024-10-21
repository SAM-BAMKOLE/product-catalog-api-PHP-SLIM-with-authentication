<?php 
declare(strict_types=1);

namespace App\Middlewares;

use App\Services\ProductService;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Valitron\Validator;

class ImageUpload {
    private $upload_dir = APP_ROOT . "/public/uploads/";
    public function __construct(private Validator $validator, private ProductService $productService){}
    public function __invoke(Request $request, RequestHandlerInterface $handler): Response {
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }

        // fetch uploaded files
        $uploaded_files = $request->getUploadedFiles();

        // check if the image file was uploaded
        if (!isset($uploaded_files['image'])) {
            $response = new Response();
            $response->getBody()->write(json_encode(['message'=>"No image uploaded"]));
            return $response->withStatus(400);
        }

        $image = $uploaded_files['image'];

        // check for upload errors
        if ($image->getError() !== UPLOAD_ERR_OK) {
            $response = new Response();
            $response->getBody()->write(json_encode(['message'=>"Image upload failed"]));
            return $response->withStatus(400);
        }

        // validate the file type
        $allowed_types = ['image/jpg', "image/jpeg", "image/png", "image/gif", "image/webp", "image/avif", "image/HEIC"];
        
        $file_type = $image->getClientMediaType();
        
        if (!in_array($file_type, $allowed_types)) {
            $response = new Response();
            $response->getBody()->write(json_encode(['message'=>"Invalid file type. Only PNG, JPEG and GIF are allowed"]));
            return $response->withStatus(415);
        }

        // create a new filename
        $filename = uniqid() . "-" . $image->getClientFilename();
        $target_path = $this->upload_dir . $filename;

        // build the file URL
        $fileURL = "/uploads/" . $filename;

        // move the file to the designated directory
        $image->moveTo($target_path);

        $request = $request->withAttribute("image_url", $fileURL);

        $response = $handler->handle($request);
        return $response;
    } 
}
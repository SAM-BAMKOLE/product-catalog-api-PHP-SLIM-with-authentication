<?php 
declare(strict_types=1);

namespace App\Controllers;

use App\Services\SaleItemService;
use App\Services\SalesService;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class SalesController {
    public function __construct( private SalesService $salesService, private SaleItemService $saleItemService) {    }
    public function add_sales(Request $request, Response $response): Response {
        $request_body = $request->getParsedBody();

        if (!isset($request_body['payment_method']) || !isset($request_body['total_amount'])) {
            $response->getBody()->write(json_encode(['message'=>'Please specify the payment method and total amount']));
            return $response->withStatus(400);
        }
        
        if (!in_array($request_body['payment_method'], ["cash", 'card', 'transfer'])) {
            $response->getBody()->write(json_encode(['message'=>'Invalid payment method']));
            return $response->withStatus(400);
        }

        $sale_id = $this->salesService->create($request_body['payment_method']);
        
        if (!$sale_id) {
            $response->getBody()->write(json_encode(['message'=>'Unable to add new sales']));
            return $response->withStatus(400);
        }

        foreach($request_body['products'] as $product) {
            $this->saleItemService->create($sale_id, $product);
        }

        $this->salesService->update($sale_id, "total_amount", (float) $request_body['total_amount']);

        $response->getBody()->write(json_encode(['message'=>'Sale registered', 'sale_id'=>$sale_id]));
        return $response;
    }
}
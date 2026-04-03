<?php
// Minimal PHP client for Inventory API
// $api = new InventoryApi('https://app.test', 'token'); $api->listWarehouses();
use GuzzleHttp\Client;

class InventoryApi {
    private Client $http;
    public function __construct(string $baseUrl, string $token) {
        $this->http = new Client([
            'base_uri' => rtrim($baseUrl,'/'),
            'headers' => ['Authorization' => 'Bearer '.$token, 'Accept'=>'application/json']
        ]);
    }
    public function listWarehouses() {
        $r = $this->http->get('/api/inventory/warehouses');
        return json_decode($r->getBody()->getContents(), true);
    }
    public function createWarehouse(array $body) {
        $r = $this->http->post('/api/inventory/warehouses', ['json'=>$body]);
        return json_decode($r->getBody()->getContents(), true);
    }
    public function onHand(int $itemId, ?int $warehouseId=null) {
        $q = $warehouseId ? ['query'=>['warehouse_id'=>$warehouseId]] : [];
        $r = $this->http->get('/api/inventory/stock/'.$itemId.'/on-hand', $q);
        return json_decode($r->getBody()->getContents(), true);
    }
    public function moveStock(array $body) {
        $r = $this->http->post('/api/inventory/stock/move', ['json'=>$body]);
        return json_decode($r->getBody()->getContents(), true);
    }
}

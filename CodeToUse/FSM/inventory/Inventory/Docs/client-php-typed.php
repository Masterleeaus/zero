<?php
// Typed(ish) PHP client for Inventory API from openapi.yaml v0.3.0
use GuzzleHttp\Client;

class InventoryApiTyped {
    private Client $http;
    public function __construct(string $baseUrl, string $token) {
        $this->http = new Client([
            'base_uri' => rtrim($baseUrl,'/'),
            'headers' => ['Authorization' => 'Bearer '.$token, 'Accept'=>'application/json']
        ]);
    }
    // Items
    public function listItems(): array {
        $r = $this->http->get('/api/inventory/items');
        return json_decode($r->getBody()->getContents(), true);
    }
    public function getItem(int $id): array {
        $r = $this->http->get('/api/inventory/items/'.$id);
        return json_decode($r->getBody()->getContents(), true);
    }
    public function createItem(array $body): array {
        $r = $this->http->post('/api/inventory/items', ['json'=>$body]);
        return json_decode($r->getBody()->getContents(), true);
    }
    public function updateItem(int $id, array $body): array {
        $r = $this->http->put('/api/inventory/items/'.$id, ['json'=>$body]);
        return json_decode($r->getBody()->getContents(), true);
    }
    public function deleteItem(int $id): array {
        $r = $this->http->delete('/api/inventory/items/'.$id);
        return json_decode($r->getBody()->getContents(), true);
    }
    // Warehouses
    public function listWarehouses(): array {
        $r = $this->http->get('/api/inventory/warehouses');
        return json_decode($r->getBody()->getContents(), true);
    }
}

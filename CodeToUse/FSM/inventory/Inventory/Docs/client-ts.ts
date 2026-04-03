// Minimal TypeScript client for Inventory API
// Usage: const api = new InventoryAPI(baseURL, token); await api.listWarehouses();
import axios, { AxiosInstance } from 'axios';

export class InventoryAPI {
  private http: AxiosInstance;
  constructor(baseURL: string, token: string) {
    this.http = axios.create({
      baseURL,
      headers: { Authorization: `Bearer ${token}` }
    });
  }
  listWarehouses() { return this.http.get('/api/inventory/warehouses').then(r=>r.data); }
  createWarehouse(body: {name:string; code?:string; location?:string}) { return this.http.post('/api/inventory/warehouses', body).then(r=>r.data); }
  onHand(itemId:number, warehouse_id?:number) { return this.http.get('/api/inventory/stock/'+itemId+'/on-hand', { params: { warehouse_id } }).then(r=>r.data); }
  moveStock(body: {item_id:number; warehouse_id?:number; type:'in'|'out'|'adjust'; qty:number; note?:string}) { return this.http.post('/api/inventory/stock/move', body).then(r=>r.data); }
}

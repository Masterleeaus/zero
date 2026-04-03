// Auto-typed TS client for the Inventory API (handwritten from openapi.yaml v0.3.0)
import axios, { AxiosInstance } from 'axios';

export interface Item {
  id?: number;
  name: string;
  sku?: string | null;
  qty: number;
  category?: string | null;
  unit_price?: number;
  tenant_id?: number | null;
  created_at?: string;
  updated_at?: string;
  deleted_at?: string | null;
}
export interface Warehouse {
  id?: number;
  name: string;
  code?: string | null;
  location?: string | null;
  created_at?: string;
  updated_at?: string;
  deleted_at?: string | null;
}
export type MovementType = 'in'|'out'|'adjust';

export class InventoryAPI {
  private http: AxiosInstance;
  constructor(baseURL: string, token: string) {
    this.http = axios.create({ baseURL, headers: { Authorization: `Bearer ${token}` }});
  }
  // Items
  listItems() { return this.http.get<Item[]>('/api/inventory/items').then(r=>r.data); }
  getItem(id:number) { return this.http.get<Item>('/api/inventory/items/'+id).then(r=>r.data); }
  createItem(body: Item) { return this.http.post<Item>('/api/inventory/items', body).then(r=>r.data); }
  updateItem(id:number, body: Partial<Item>) { return this.http.put<Item>('/api/inventory/items/'+id, body).then(r=>r.data); }
  deleteItem(id:number) { return this.http.delete('/api/inventory/items/'+id).then(r=>r.data); }
  // Warehouses
  listWarehouses() { return this.http.get<Warehouse[]>('/api/inventory/warehouses').then(r=>r.data); }
  getWarehouse(id:number) { return this.http.get<Warehouse>('/api/inventory/warehouses/'+id).then(r=>r.data); }
  createWarehouse(body: Warehouse) { return this.http.post<Warehouse>('/api/inventory/warehouses', body).then(r=>r.data); }
  updateWarehouse(id:number, body: Partial<Warehouse>) { return this.http.put<Warehouse>('/api/inventory/warehouses/'+id, body).then(r=>r.data); }
  deleteWarehouse(id:number) { return this.http.delete('/api/inventory/warehouses/'+id).then(r=>r.data); }
  // Stock
  onHand(itemId:number, warehouse_id?:number) { return this.http.get('/api/inventory/stock/'+itemId+'/on-hand', { params: { warehouse_id }}).then(r=>r.data); }
  moveStock(body: {item_id:number; warehouse_id?:number; type:MovementType; qty:number; note?:string}) { return this.http.post('/api/inventory/stock/move', body).then(r=>r.data); }
}

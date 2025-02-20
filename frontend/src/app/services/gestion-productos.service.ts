import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { HttpClient, HttpParams } from '@angular/common/http';
import { ProductoResponse } from '../interfaces/productoRespond';
import { maxProduct } from '../interfaces/maxProduct';

@Injectable({
  providedIn: 'root'
})
export class GestionProductosService {

  constructor(private http: HttpClient) { }
  // Método para obtener productos paginados
  getProductos(page: number): Observable<ProductoResponse> {
    const params = new HttpParams().set('page', page.toString());  // Pasa el parámetro 'page'
    return this.http.get<ProductoResponse>("http://localhost:8080/Symfony/public/index.php/api/producto", { params });
  }

  // Método para obtener productos paginados por tipo
  getProductosByFilter(page: number,filter: string): Observable<ProductoResponse> {
    //Evita que se sobrescriban los parametros de la url
    const params = new HttpParams()
    .set('page', page.toString())   // Pasa el parámetro 'page'
    .set('filter', filter?.toString() || ""); // Pasa el parámetro 'filter'
    return this.http.get<ProductoResponse>("http://localhost:8080/Symfony/public/index.php/api/producto/tipe", { params });
  }

  // Método para obtener productos paginados por nombre producto
  getProductosByName(page: number,name: string){
    //Evita que se sobrescriban los parametros de la url
    const params = new HttpParams()
    .set('page', page.toString())   // Pasa el parámetro 'page'
    .set('name', name?.toString() || ""); // Pasa el parámetro 'name'
    return this.http.get<ProductoResponse>("http://localhost:8080/Symfony/public/index.php/api/producto/name", { params });
  }

  // Método para obtener productos paginados por precio producto
  getProductosByPrice(page: number,price: string){
    //Evita que se sobrescriban los parametros de la url
    const params = new HttpParams()
    .set('page', page.toString())   // Pasa el parámetro 'page'
    .set('price', price?.toString() || ""); // Pasa el parámetro 'price'
    return this.http.get<ProductoResponse>("http://localhost:8080/Symfony/public/index.php/api/producto/price", { params });
  }

    // Método para obtener productos paginados por precio producto
    getProductosByPriceMax(){
      return this.http.get<maxProduct>("http://localhost:8080/Symfony/public/index.php/api/producto/priceMax");
    }
}

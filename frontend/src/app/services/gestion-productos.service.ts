import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { HttpClient, HttpParams } from '@angular/common/http';
import { ProductoResponse } from '../interfaces/productoRespond';

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
}

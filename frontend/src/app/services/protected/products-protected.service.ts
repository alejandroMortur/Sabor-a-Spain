import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Productos } from '../../interfaces/productos';

@Injectable({
  providedIn: 'root'
})
export class ProductsProtectedService {
  // URL base para la API
  private apiUrl = 'https://localhost:8443/Symfony/public/index.php/api/protected/admin/productos';

  constructor(private http: HttpClient) { }

  // Obtene todos los productos
  getProductos(): Observable<Productos[]> {
    return this.http.get<Productos[]>(this.apiUrl + '/obtener', { withCredentials: true });
  }

  // Obtene un producto por ID
  getProducto(id: number): Observable<Productos> {
    return this.http.get<Productos>(`${this.apiUrl}/obtener/${id}`, { withCredentials: true });
  }

  // Crea un nuevo producto
  createProducto(producto: Productos): Observable<Productos> {
    return this.http.post<Productos>(this.apiUrl + '/crear', producto, { withCredentials: true });
  }

  // Actualiza un producto existente
  updateProducto(id: number, producto: Productos): Observable<Productos> {
    return this.http.put<Productos>(`${this.apiUrl}/actualizar/${id}`, producto, { withCredentials: true });
  }

  // Elimina un producto
  deleteProducto(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/eliminar/${id}`, { withCredentials: true });
  }
}

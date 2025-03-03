import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class TipesProtectedService {
  // URL base para la API de tipos
  private apiUrl = 'https://localhost:8443/Symfony/public/index.php/api/protected/admin/tipos';

  constructor(private http: HttpClient) {}

  // Método para obtener todos los tipos
  getTipos(): Observable<any> {
    return this.http.get(`${this.apiUrl}/obtener`, { withCredentials: true });
  }

  // Método para obtener un tipo específico por ID
  getTipo(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/obtener/${id}`, { withCredentials: true });
  }

  // Método para crear un nuevo tipo
  createTipo(tipoData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/crear`, tipoData, { withCredentials: true });
  }

  // Método para actualizar un tipo existente
  updateTipo(id: number, tipoData: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/actualizar/${id}`, tipoData, { withCredentials: true });
  }

  // Método para eliminar un tipo
  deleteTipo(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/eliminar/${id}`, { withCredentials: true });
  }
}

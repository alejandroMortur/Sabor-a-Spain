import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Usuario } from '../../interfaces/usuario';

@Injectable({
  providedIn: 'root'
})
export class UsersProtectedService {

  // URL base para la API de usuarios
  private apiUrl = 'https://localhost:8443/Symfony/public/index.php/api/protected/admin/usuarios';

  constructor(private http: HttpClient) { }

  // Método para obtener todos los usuarios
  getUsuarios(): Observable<Usuario[]> {
    return this.http.get<Usuario[]>(`${this.apiUrl}/obtener`, { withCredentials: true });
  }

  // Método para obtener un usuario por ID
  getUsuario(id: number): Observable<Usuario> {
    return this.http.get<Usuario>(`${this.apiUrl}/obtener/${id}`, { withCredentials: true });
  }

  // Método para crear un nuevo usuario
  createUsuario(usuarioData: Usuario): Observable<Usuario> {
    return this.http.post<Usuario>(`${this.apiUrl}/crear`, usuarioData, { withCredentials: true });
  }

  // Método para actualizar un usuario existente
  updateUsuario(id: number, usuarioData: Usuario): Observable<Usuario> {
    return this.http.put<Usuario>(`${this.apiUrl}/actualizar/${id}`, usuarioData, { withCredentials: true });
  }

  // Método para eliminar un usuario
  deleteUsuario(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/eliminar/${id}`, { withCredentials: true });
  }
}

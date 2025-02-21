import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/internal/Observable';
import { Tipos } from '../interfaces/tipos';

@Injectable({
  providedIn: 'root'
})
export class GestionTiposService {

  constructor(private http: HttpClient) { }
    // Método para obtener productos paginados
    getListadoTipos(): Observable<Tipos[]> {
      return this.http.get<Tipos[]>("http://localhost:8080/Symfony/public/index.php/api/tipos");
    }
  
}

import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class GrafService {

  private apiUrl = 'https://localhost:8443/Symfony/public/index.php/api/protected/admin/grafico/stock'; // Cambia esto a la URL de tu API Symfony

  constructor(private http: HttpClient) { }

  // Método para obtener los datos del gráfico
  getGraficoStock(): Observable<any[]> {
    return this.http.get<any[]>(this.apiUrl, { withCredentials: true });
  }
}


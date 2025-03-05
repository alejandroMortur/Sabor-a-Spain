import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class SalesBarService {

  private apiUrl = 'https://localhost:8443/Symfony/public/index.php/api/protected/admin/grafico/ventas';

  constructor(private http: HttpClient) { }

  // Método para obtener los datos de ventas
  getVentas(): Observable<any> {
    return this.http.get<any>(this.apiUrl, {
      withCredentials: true, 
      headers: new HttpHeaders({
        'Content-Type': 'application/json'
      })
    });
  }
}

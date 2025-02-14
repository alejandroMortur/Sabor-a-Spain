import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { productos } from '../interfaces/productos';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class GestionProductosService {

  constructor(private http: HttpClient) { }
  getProductos():Observable<productos[]>{
    return this.http.get<productos[]>("http://localhost:8080/Symfony/public/index.php/api/producto");
  }
}

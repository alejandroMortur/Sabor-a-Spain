import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ItemCarousel } from '../interfaces/ItemCarousel';
import { Observable } from 'rxjs/internal/Observable';

const URL = "/";
@Injectable({
  providedIn: 'root'
})
export class CarouserlServiceService {

  constructor(private http: HttpClient) { }
  getItems(): Observable<ItemCarousel[]> {
    return this.http.get<ItemCarousel[]>(URL + "ItemCarousel.json");
  }
}

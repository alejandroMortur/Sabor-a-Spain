import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class LogoutService {
  private apiUrl = 'https://localhost:8443/Symfony/public/index.php/auth/logout';

  constructor(private http: HttpClient) {}

  logout(): Observable<any> {
    return this.http.post(this.apiUrl, {}, { withCredentials: true });
  }
}
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { tap } from 'rxjs/operators';
import { AuthService } from '../protected/auth-service.service';


@Injectable({
  providedIn: 'root',
})
export class LoginService {
  private apiUrl = 'https://localhost:8443/Symfony/public/index.php/auth';

  constructor(private http: HttpClient, private authService: AuthService) {}

  login(email: string, password: string): Observable<any> {
    const body = new URLSearchParams();
    body.set('email', email);
    body.set('password', password);

    const headers = new HttpHeaders({
      'Content-Type': 'application/x-www-form-urlencoded',
    });

    return this.http.post(this.apiUrl, body.toString(), { 
      headers, 
      withCredentials: true 
    }).pipe(
      tap(() => this.authService.isAuthenticated().subscribe())
    );
  }
}
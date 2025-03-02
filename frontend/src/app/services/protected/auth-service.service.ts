import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, throwError } from 'rxjs';
import { catchError, tap } from 'rxjs/operators';
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private authStatus = new BehaviorSubject<boolean>(false);
  authStatus$ = this.authStatus.asObservable();

  constructor(private http: HttpClient, private router: Router) {}

  isAuthenticated(): Observable<{ authenticated: boolean }> {
    return this.http.get<{ authenticated: boolean }>('https://localhost:8443/Symfony/public/index.php/auth/status', { 
      withCredentials: true 
    }).pipe(
      tap((response) => {
        this.authStatus.next(response.authenticated);
        if (!response.authenticated) {
          this.router.navigate(['/auth/login']);
        }
      }),
      catchError((error) => {
        console.error('Error verificando autenticación', error);
        this.authStatus.next(false);
        this.router.navigate(['/auth/login']);
        return throwError(() => error);
      })
    );
  }

  refreshAccessToken(): Observable<any> {
    return this.http.post('https://localhost:8443/Symfony/public/index.php/auth/refresh', {}, {
      withCredentials: true,
    }).pipe(
      tap(() => {
        console.log('Token refrescado correctamente');
        this.authStatus.next(true);
      }),
      catchError((error) => {
        console.error('Error al refrescar el token', error);
        this.authStatus.next(false);
        this.router.navigate(['/auth/login']);
        return throwError(() => error);
      })
    );
  }

  logout(): void {
    // Limpia el estado de autenticación
    this.authStatus.next(false);
    this.router.navigate(['/auth/login']);
  }
}
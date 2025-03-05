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
  private roles: string[] = []; // Almacena los roles en memoria

  constructor(private http: HttpClient, private router: Router) {
    // Al iniciar, intentamos cargar los roles desde localStorage
    const savedRoles = localStorage.getItem('roles');  
    if (savedRoles) {
      this.roles = JSON.parse(savedRoles); // Carga los roles guardados
      this.authStatus.next(true); // Supone que el usuario está autenticado si los roles están presentes
    }
  }

  isAuthenticated(): Observable<{ authenticated: boolean, roles?: string[] }> {
    return this.http.get<{ authenticated: boolean, user?: { roles: string[] } }>('https://localhost:8443/Symfony/public/index.php/auth/status', { 
      withCredentials: true 
    }).pipe(
      tap((response) => {
        this.authStatus.next(response.authenticated);

        if (response.authenticated && response.user && response.user.roles) {
          this.roles = response.user.roles; // Actualiza los roles del usuario
          localStorage.setItem('roles', JSON.stringify(this.roles)); // Guarda los roles en localStorage
        } else {
          this.roles = []; // Limpia los roles si no está autenticado
          localStorage.removeItem('roles'); // Limpia el almacenamiento local
          if (this.router.url !== '/auth/login') {
            this.router.navigate(['/auth/login']);
          }
        }
      }),
      catchError((error) => {
        console.error('Error verificando autenticación', error);
        this.authStatus.next(false);
        this.roles = []; // Limpiam los roles si hay error
        localStorage.removeItem('roles'); // Limpiam el almacenamiento local
        if (this.router.url !== '/auth/login') {
          this.router.navigate(['/auth/login']);
        }
        return throwError(() => error);
      })
    );
  }

  getUserRoles(): string[] {
    return this.roles; // Retorna los roles desde el servicio
  }

  setRoles(roles: string[]): void {
    this.roles = roles;
    localStorage.setItem('roles', JSON.stringify(roles)); // Guardam los roles en localStorage
    this.authStatus.next(true); // Indica que el usuario está autenticado
  }

  logout(): void {
    this.authStatus.next(false);
    this.roles = []; // Limpiam los roles
    localStorage.removeItem('roles'); // Limpia los roles en localStorage
    this.router.navigate(['/auth/login']);
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

}
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
  private roles: string[] = []; // Aquí almacenaremos los roles en memoria

  constructor(private http: HttpClient, private router: Router) {
    // Al iniciar, intentamos cargar los roles desde localStorage
    const savedRoles = localStorage.getItem('roles');  
    if (savedRoles) {
      this.roles = JSON.parse(savedRoles); // Cargar los roles guardados
      this.authStatus.next(true); // Suponemos que el usuario está autenticado si los roles están presentes
    }
  }

  isAuthenticated(): Observable<{ authenticated: boolean, roles?: string[] }> {
    return this.http.get<{ authenticated: boolean, user?: { roles: string[] } }>('https://localhost:8443/Symfony/public/index.php/auth/status', { 
      withCredentials: true 
    }).pipe(
      tap((response) => {
        this.authStatus.next(response.authenticated);

        if (response.authenticated && response.user && response.user.roles) {
          this.roles = response.user.roles; // Actualizamos los roles del usuario
          localStorage.setItem('roles', JSON.stringify(this.roles)); // Guardamos los roles en localStorage
        } else {
          this.roles = []; // Limpiamos los roles si no está autenticado
          localStorage.removeItem('roles'); // Limpiamos el almacenamiento local
          // Redirigimos al login si no estamos ya en esa página
          if (this.router.url !== '/auth/login') {
            this.router.navigate(['/auth/login']);
          }
        }
      }),
      catchError((error) => {
        console.error('Error verificando autenticación', error);
        this.authStatus.next(false);
        this.roles = []; // Limpiamos los roles si hay error
        localStorage.removeItem('roles'); // Limpiamos el almacenamiento local
        // Redirigimos al login si no estamos ya en esa página
        if (this.router.url !== '/auth/login') {
          this.router.navigate(['/auth/login']);
        }
        return throwError(() => error);
      })
    );
  }

  getUserRoles(): string[] {
    return this.roles; // Retornamos los roles desde el servicio
  }

  setRoles(roles: string[]): void {
    this.roles = roles;
    localStorage.setItem('roles', JSON.stringify(roles)); // Guardamos los roles en localStorage
    this.authStatus.next(true); // Indicamos que el usuario está autenticado
  }

  logout(): void {
    this.authStatus.next(false);
    this.roles = []; // Limpiamos los roles
    localStorage.removeItem('roles'); // Limpiamos los roles en localStorage
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
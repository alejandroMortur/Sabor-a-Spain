import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, switchMap } from 'rxjs/operators';
import { AuthService } from '../../protected/auth-service.service';
import { throwError } from 'rxjs';


export const authInterceptor: HttpInterceptorFn = (request, next) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  return next(request).pipe(
    catchError((error) => {
      if (error.status === 401 && !request.url.includes('auth')) {
        // Evitar redirigir si ya estamos en la página de login
        if (router.url !== '/auth/login') {
          return authService.refreshAccessToken().pipe(
            switchMap(() => next(request)), // Reintenta la solicitud original
            catchError((refreshError) => {
              // Solo redirigimos al login si el refresh falla
              router.navigate(['/auth/login']); 
              return throwError(() => refreshError);
            })
          );
        }
      }
      return throwError(() => error);
    })
  );
};
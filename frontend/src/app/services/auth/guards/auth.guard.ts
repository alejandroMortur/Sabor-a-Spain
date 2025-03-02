import { Injectable } from '@angular/core';
import { CanActivate, Router } from '@angular/router';
import { map } from 'rxjs/operators';
import { AuthService } from '../../protected/auth-service.service';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class AuthGuard implements CanActivate {
  constructor(private authService: AuthService, private router: Router) {}

  canActivate(): Observable<boolean> {
    return this.authService.authStatus$.pipe(
      map(authenticated => {
        if (!authenticated) {
          this.router.navigate(['auth/login']); // Redirige al login si no está autenticado
          return false;
        }
        return true;
      })
    );
  }
}
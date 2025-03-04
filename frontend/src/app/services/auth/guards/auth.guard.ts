import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, CanActivate, Router, RouterStateSnapshot } from '@angular/router';
import { map } from 'rxjs/operators';
import { AuthService } from '../../protected/auth-service.service';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class AuthGuard implements CanActivate {
  constructor(private authService: AuthService, private router: Router) {}

  canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Observable<boolean> {
    return this.authService.authStatus$.pipe(
      map(authenticated => {
        if (!authenticated) {
          this.router.navigate(['auth/login']); // Redirigir si no está autenticado
          return false;
        }
  
        // Obtener el rol del usuario desde el servicio de autenticación
        const userRoles = this.authService.getUserRoles(); 
  
        // Si la ruta requiere ROLE_ADMIN y el usuario no lo tiene, lo redirige al home
        if (state.url.startsWith('/admin') && !userRoles.includes('ROLE_ADMIN')) {
          this.router.navigate(['/']);
          return false;
        }
  
        return true; // Permite el acceso si cumple los requisitos
      })
    );
  }  
}
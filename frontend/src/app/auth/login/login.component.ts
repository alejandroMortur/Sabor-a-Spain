import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { LoginService } from '../../services/auth/login.service';
import { Router } from '@angular/router';
import { AuthService } from '../../services/protected/auth-service.service';
import { UserImgService } from '../../services/userImg/user-img.service';


@Component({
  selector: 'app-login',
  imports: [FormsModule, CommonModule],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})
export class LoginComponent {
  model = {
    username: '',
    email: '', // Solo necesitamos email y password
    password: ''
  };

  errorMessage: string = ''; // Para mostrar mensajes de error

  constructor(
    private loginService: LoginService, // Inyecta el servicio de login
    private authService: AuthService, // Inyecta AuthService
    private router: Router, // Inyecta Router
    private userImgService: UserImgService
  ) {}

  onSubmit() {
    if (!this.model.email || !this.model.password) {
      this.errorMessage = 'Por favor, completa todos los campos.';
      return;
    }
  
    this.loginService.login(this.model.email, this.model.password).subscribe({
      next: (response) => {
        console.log('Login exitoso', response);
  
        // Actualiza la imagen del usuario
        const imageUrl = response.imageUrl || "https://localhost:8443/data/imagenes/user.png";
        this.userImgService.updateUserImage(imageUrl);
  
        // Llamar a isAuthenticated solo después del login
        this.authService.isAuthenticated().subscribe({
          next: (authResponse) => {
            if (authResponse.authenticated) {
              const roles = this.authService.getUserRoles(); // Obtener roles desde AuthService
              console.log('Roles del usuario:', roles);
  
              if (roles.includes('ROLE_ADMIN')) {
                console.log("pantalla admin");
                this.router.navigate(['admin/dashboard']);
              } else {
                console.log("home para usuarios");
                this.router.navigate(['/']);
              }
            } else {
              this.errorMessage = 'Error de autenticación después del login.';
            }
          },
          error: (err) => {
            console.error('Error verificando autenticación', err);
            this.errorMessage = 'Error de autenticación.';
          }
        });
  
      },
      error: (err) => {
        console.error('Error en el login', err);
        this.errorMessage = 'Credenciales incorrectas. Inténtalo de nuevo.';
      }
    });
  }
  
}



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
    // Verifica que el email y la contraseña no estén vacíos
    if (!this.model.email || !this.model.password) {
      this.errorMessage = 'Por favor, completa todos los campos.';
      return;
    }

    // Llama al servicio de login
    this.loginService.login(this.model.email, this.model.password).subscribe({
      next: (response) => {
        console.log('Login exitoso', response);

        // Actualiza la imagen del usuario usando UserImgService
        const imageUrl = response.imageUrl || "https://localhost:8443/data/imagenes/user.png";
        this.userImgService.updateUserImage(imageUrl);

        // Guardar los roles en el AuthService y localStorage
        this.authService.isAuthenticated().subscribe(() => {
          const roles = response.roles;
          this.authService.setRoles(roles); // Actualizamos los roles en el servicio de autenticación
        });

        // Verificar roles y redirigir
        if (response.roles.includes('ROLE_ADMIN')) {
          console.log("pantalla admin")
          this.router.navigate(['admin/dashboard']);
        } else {
          console.log("home para usuarios")
          this.router.navigate(['/']); // Si es ROLE_USER al home
        }
      },
      error: (err) => {
        console.error('Error en el login', err);
        this.errorMessage = 'Credenciales incorrectas. Inténtalo de nuevo.';
      }
    });
  }
}



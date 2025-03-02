import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { LoginService } from '../../services/auth/login.service';
import { Router } from '@angular/router';
import { AuthService } from '../../services/protected/auth-service.service';


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
    private router: Router // Inyecta Router
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
        //this.authService.isAuthenticated().subscribe(); // Actualiza el estado de autenticación
        this.router.navigate(['/']); // Redirige al home después del login
      },
      error: (err) => {
        console.error('Error en el login', err);
        this.errorMessage = 'Credenciales incorrectas. Inténtalo de nuevo.';
      }
    });
  }
}
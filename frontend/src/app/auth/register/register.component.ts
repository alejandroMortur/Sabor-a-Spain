import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { RegisterService } from '../../services/auth/register.service';

@Component({
  selector: 'app-register',
  imports: [ FormsModule,CommonModule],
  templateUrl: './register.component.html',
  styleUrl: './register.component.css'
})
export class RegisterComponent {
  activeLink :string = "";
  model = {
    username: '',
    password: '',
    repeatPassword: '',
    email: '',
    role: ['ROLE_USER']
  };

  constructor(private router: Router,private registerService: RegisterService) { }

  passwordFieldType: string = 'password';
  repeatPasswordFieldType: string = 'password';

  togglePasswordVisibility() {
    this.passwordFieldType = this.passwordFieldType === 'password' ? 'text' : 'password';
  }

  toggleRepeatPasswordVisibility() {
    this.repeatPasswordFieldType = this.repeatPasswordFieldType === 'password' ? 'text' : 'password';
  }
  
  onSubmit(event: Event) {
    event.preventDefault(); 
    if (this.model.username && this.model.password && this.model.email) {
      console.log('Formulario enviado', this.model);
  
      // Llamamos al servicio de registro
      this.registerService.registerUser(this.model.username, this.model.password)
        .subscribe(
          (response) => {
            console.log('Respuesta del servidor:', response);
            // Redirigir al HomeComponent después de un registro exitoso
            this.router.navigate(['/']); // Esto debería llevarte al HomeComponent, si tienes una ruta configurada como { path: '', component: HomeComponentComponent }
          },
          (error) => {
            console.error('Error en el registro:', error);
          }
        );
    } else {
      console.log('Formulario no válido');
    }
  }  
  
  route(path: string): void {
    this.router.navigate([path]);
    this.activeLink = path;
  }
}

import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { RegisterService } from '../../services/auth/register.service';
import { UserImgService } from '../../services/userImg/user-img.service';

@Component({
  selector: 'app-register',
  imports: [FormsModule, CommonModule],
  templateUrl: './register.component.html',
  styleUrl: './register.component.css'
})
export class RegisterComponent {
  activeLink: string = "";
  model = {
    username: '',
    password: '',
    repeatPassword: '',
    email: '',
    image: null as File | null  // Especificamos que puede ser File o null
  };  

  constructor(private router: Router, private registerService: RegisterService, private userImgService: UserImgService) { }

  passwordFieldType: string = 'password';
  repeatPasswordFieldType: string = 'password';
  imageInvalid: boolean = false; // Flag para mostrar error si no se selecciona una imagen válida

  togglePasswordVisibility() {
    this.passwordFieldType = this.passwordFieldType === 'password' ? 'text' : 'password';
  }

  toggleRepeatPasswordVisibility() {
    this.repeatPasswordFieldType = this.repeatPasswordFieldType === 'password' ? 'text' : 'password';
  }

  // Método para manejar el cambio en el campo de archivo
  onFileChange(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input?.files?.[0];
  
    if (file && file.type.startsWith('image/')) {
      this.model.image = file;
      this.imageInvalid = false; // Resetear error si la imagen es válida
    } else {
        this.imageInvalid = true; // Mostrar error si no es una imagen
    }
  }

  onSubmit(event: Event) {
    event.preventDefault();
    if (this.model.username && this.model.password && this.model.email) {
      console.log('Formulario enviado', this.model);

      // Llamamos al servicio de registro
      this.registerService.registerUser(this.model.username, this.model.password, this.model.email, this.model.image)
        .subscribe(
          (response) => {
            console.log('Respuesta del servidor:', response);

            // Aquí deberíamos obtener la URL de la imagen generada
            // Suponiendo que la respuesta tenga la URL de la imagen del usuario
            const imageUrl = response.imageUrl || "https://localhost:8443/data/imagenes/user.png";

            // Actualizar la imagen del usuario
            this.userImgService.updateUserImage(imageUrl);

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

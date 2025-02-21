import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';

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
    email: ''
  };

  constructor(private router: Router) { }

  passwordFieldType: string = 'password';
  repeatPasswordFieldType: string = 'password';

  togglePasswordVisibility() {
    this.passwordFieldType = this.passwordFieldType === 'password' ? 'text' : 'password';
  }

  toggleRepeatPasswordVisibility() {
    this.repeatPasswordFieldType = this.repeatPasswordFieldType === 'password' ? 'text' : 'password';
  }
  
  onSubmit() {
    if (this.model.username && this.model.email) {
      console.log('Formulario enviado', this.model);
    }
  }
  route(path: string): void {
    this.router.navigate([path]);
    this.activeLink = path;
  }
}

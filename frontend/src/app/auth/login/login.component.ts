import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-login',
  imports: [FormsModule,CommonModule],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})
export class LoginComponent {
  model = {
      username: '',
      email: '',
      password : ''
  };

  onSubmit() {
    if (this.model.username && this.model.email) {
      console.log('Formulario enviado', this.model);
    }
  }
}

import { Component, OnInit } from '@angular/core';
import { UsersProtectedService } from '../../../../services/protected/users-protected.service';
import { Usuario } from '../../../../interfaces/usuario';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-table-users',
  templateUrl: './table-users.component.html',
  standalone: true, // Asegúrate de que el componente sea standalone
  imports: [CommonModule], // Importa CommonModule aquí
  styleUrls: ['./table-users.component.css']
})
export class TableUsersComponent implements OnInit {
  usuarios: Usuario[] = []; // Array para almacenar los usuarios

  constructor(private usersService: UsersProtectedService) {}

  ngOnInit(): void {
    this.loadUsuarios(); // Cargar los usuarios al inicializar el componente
  }

  // Método para cargar los usuarios desde la API
  loadUsuarios(): void {
    this.usersService.getUsuarios().subscribe(
      (data) => {
        this.usuarios = data.map((usuario: any) => ({
          id: usuario.id,
          email: usuario.email,
          userIdentifier: usuario.userIdentifier,
          roles: usuario.roles,
          foto: usuario.foto,
          ventas: usuario.ventas,
          nombre: usuario.Nombre, // Asignamos el nombre desde el JSON
          refreshToken: usuario.RefreshToken,
          payload: usuario.payload,
          username: usuario.username,
          password: usuario.password,
        })); // Asegura que los datos cumplen con la interfaz
      },
      (error) => {
        console.error('Error al cargar los usuarios:', error);
      }
    );
  }
}


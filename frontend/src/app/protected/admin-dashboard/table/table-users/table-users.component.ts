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
  // Método para editar un usuario
  editarUsuario(id: number): void {
    console.log('Editar usuario con ID:', id);
    // Aquí puedes redirigir a un formulario de edición o abrir un modal
  }

  // Método para eliminar un usuario
  eliminarUsuario(id: number): void {
    if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
      this.usersService.deleteUsuario(id).subscribe(
        () => {
          console.log('Usuario eliminado');
          this.loadUsuarios(); // Recargar la lista de usuarios
        },
        (error) => {
          console.error('Error al eliminar el usuario:', error);
        }
      );
    }
  }

  // Método para crear un nuevo usuario
  crearUsuario(): void {
    console.log('Crear nuevo usuario');
    // Aquí puedes redirigir a un formulario de creación o abrir un modal
  }

  // Método para refrescar la lista de usuarios
  refrescarUsuarios(): void {
    console.log('Refrescando lista de usuarios...');
    this.loadUsuarios(); // Recargar la lista de usuarios
  }
}


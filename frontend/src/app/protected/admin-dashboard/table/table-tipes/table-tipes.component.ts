import { Component, OnInit } from '@angular/core';
import { TipesProtectedService } from '../../../../services/protected/tipes-protected.service';
import { Tipos } from '../../../../interfaces/tipos';
import { CommonModule } from '@angular/common';


@Component({
  selector: 'app-table-tipes',
  templateUrl: './table-tipes.component.html',
  standalone: true,
  imports: [CommonModule], 
  styleUrls: ['./table-tipes.component.css'],
})
export class TableTipesComponent implements OnInit {
  tipos: Tipos[] = []; // Array para almacenar los tipos

  constructor(private tiposService: TipesProtectedService) {}

  ngOnInit(): void {
    this.loadTipos(); // Carga los tipos al inicializar el componente
  }

  // Método para cargar los tipos desde la API
  loadTipos(): void {
    this.tiposService.getTipos().subscribe(
      (data) => {
        this.tipos = data; // Asigna los datos recibidos al array 'tipos'
      },
      (error) => {
        console.error('Error al cargar los tipos:', error);
      }
    );
  }
    // Método para editar un tipo
    editarTipo(id: string): void {
      let idProducto = parseInt(id);
      console.log('Editar tipo con ID:', idProducto);
    }
  
    // Método para eliminar un tipo
    eliminarTipo(id: string): void {
      let idTipo = parseInt(id);
      if (confirm('¿Estás seguro de que deseas eliminar este tipo?')) {
        this.tiposService.deleteTipo(idTipo).subscribe(
          () => {
            console.log('Tipo eliminado');
            this.loadTipos(); // Recarga la lista de tipos
          },
          (error) => {
            console.error('Error al eliminar el tipo:', error);
          }
        );
      }
    }
  
    // Método para crear un nuevo tipo
    crearTipo(): void {
      console.log('Crear nuevo tipo');
    }
  
    // Método para refrescar la lista de tipos
    refrescarTipos(): void {
      console.log('Refrescando lista de tipos...');
      this.loadTipos(); 
    }
}
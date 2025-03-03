import { Component, OnInit } from '@angular/core';
import { TipesProtectedService } from '../../../../services/protected/tipes-protected.service';
import { Tipos } from '../../../../interfaces/tipos';
import { CommonModule } from '@angular/common';


@Component({
  selector: 'app-table-tipes',
  templateUrl: './table-tipes.component.html',
  standalone: true, // Asegúrate de que el componente sea standalone
  imports: [CommonModule], // Importa CommonModule aquí
  styleUrls: ['./table-tipes.component.css'],
})
export class TableTipesComponent implements OnInit {
  tipos: Tipos[] = []; // Array para almacenar los tipos

  constructor(private tiposService: TipesProtectedService) {}

  ngOnInit(): void {
    this.loadTipos(); // Cargar los tipos al inicializar el componente
  }

  // Método para cargar los tipos desde la API
  loadTipos(): void {
    this.tiposService.getTipos().subscribe(
      (data) => {
        this.tipos = data; // Asignar los datos recibidos al array 'tipos'
      },
      (error) => {
        console.error('Error al cargar los tipos:', error);
      }
    );
  }
}
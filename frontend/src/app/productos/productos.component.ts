import { Component } from '@angular/core';
import { Productos } from '../interfaces/productos';
import { GestionProductosService } from '../services/gestion-productos.service';
import { PaginationComponent } from '../pagination/pagination.component';
import { ProductoResponse } from '../interfaces/productoRespond';

@Component({
  selector: 'app-productos',
  imports: [PaginationComponent],
  templateUrl: './productos.component.html',
  styleUrl: './productos.component.css'
})
export class ProductosComponent {
   productos:Productos[] = [];
   totalItems: number = 0;  // Total de productos (para la paginación)
   page: number = 1;  // Página inicial

   constructor(private servicioProductos: GestionProductosService){}

   ngOnInit() {
    this.pintarTarjetas();
   }

   pintarTarjetas(): void {
      this.servicioProductos.getProductos(this.page).subscribe((data: ProductoResponse) => {
        this.productos = data.productos;
        this.totalItems = data.total;
        console.log(this.productos)
        console.log(this.totalItems)
      }); 
  }
   manejarCambioPagina(nuevoValor: number  ) {
      this.page = nuevoValor;
      console.log("estamos en la pagina: "+this.page);
      this.pintarTarjetas();    
   }
}
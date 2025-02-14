import { Component } from '@angular/core';
import { productos } from '../interfaces/productos';
import { GestionProductosService } from '../services/gestion-productos.service';
import { PaginationComponent } from '../pagination/pagination.component';

@Component({
  selector: 'app-productos',
  imports: [PaginationComponent],
  templateUrl: './productos.component.html',
  styleUrl: './productos.component.css'
})
export class ProductosComponent {
   productos:productos[] = [];
   constructor(private servicioPlatos: GestionProductosService){}

   ngOnInit() {
    this.pintarTarjetas();
  }
  pintarTarjetas(): void {
    this.servicioPlatos.getProductos().subscribe(producto => this.productos = producto);
  }
}

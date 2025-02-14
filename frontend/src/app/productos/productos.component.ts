import { ChangeDetectorRef, Component } from '@angular/core';
import { Productos } from '../interfaces/productos';
import { GestionProductosService } from '../services/gestion-productos.service';
import { PaginationComponent } from '../pagination/pagination.component';
import { ProductoResponse } from '../interfaces/productoRespond';
import { CommonModule} from '@angular/common';
import { FilterNameComponent } from '../filters/filter-name/filter-name.component';
import { FilterPriceComponent } from '../filters/filter-price/filter-price.component';
import { FilterTipeComponent } from '../filters/filter-tipe/filter-tipe.component';

@Component({
  selector: 'app-productos',
  imports: [PaginationComponent,CommonModule,FilterNameComponent,FilterPriceComponent,FilterTipeComponent],
  templateUrl: './productos.component.html',
  styleUrl: './productos.component.css'
})
export class ProductosComponent {
   productos:Productos[] = [];
   totalItems: number = 0;  // Total de productos (para la paginación)
   totalPages: number = 0;  // Total de páginas
   page: number = 1;  // Página inicial

   constructor(private servicioProductos: GestionProductosService, private cdr: ChangeDetectorRef){}

   ngOnInit() {
      this.pintarTarjetas();
   }

   pintarTarjetas(): void {
      this.servicioProductos.getProductos(this.page).subscribe((data: ProductoResponse) => {
        this.productos = data.productos;
        this.totalItems = data.total;
        this.page = data.pagina;

        // Forzar detección de cambios para actualizar la vista
         this.cdr.detectChanges();
      }); 
  }
   manejarCambioPagina(nuevoValor: number  ) {
      this.page = nuevoValor;
      console.log("estamos en la pagina: "+this.page);
      this.pintarTarjetas();    
   }

   trackById(index: number, producto: Productos): string {
      return producto.id;
   }
}
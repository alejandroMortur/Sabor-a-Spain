import { ChangeDetectorRef, Component } from '@angular/core';
import { Productos } from '../interfaces/productos';
import { GestionProductosService } from '../services/gestion-productos.service';
import { PaginationComponent } from '../pagination/pagination.component';
import { ProductoResponse } from '../interfaces/productoRespond';
import { CommonModule } from '@angular/common';
import { FilterNameComponent } from '../filters/filter-name/filter-name.component';
import { FilterPriceComponent } from '../filters/filter-price/filter-price.component';
import { FilterTipeComponent } from '../filters/filter-tipe/filter-tipe.component';
import { GestionarCarritoService } from '../services/gestionar-carrito.service';

@Component({
   selector: 'app-productos',
   imports: [PaginationComponent, CommonModule, FilterNameComponent, FilterPriceComponent, FilterTipeComponent],
   templateUrl: './productos.component.html',
   styleUrl: './productos.component.css'
})
export class ProductosComponent {
   productos: Productos[] = [];

   //-----------(paginación)--------------------------------------
   totalItems: number = 0;  // Total de productos (para la paginación)
   totalPages: number = 0;  // Total de páginas
   page: number = 1;  // Página inicial
   //------------(Variables filtros)------------------------------
   tipe: string = "";
   name: string = "";
   price: number = 0;
   //--------------------------------------------------------------

   constructor(private servicioProductos: GestionProductosService, private cdr: ChangeDetectorRef, private carrito: GestionarCarritoService) { }

   ngOnInit() {
      this.pintarTarjetas();
   }

   pintarTarjetas(): void {
      this.servicioProductos.getProductos(this.page).subscribe((data: ProductoResponse) => {
         this.productos = data.productos;
         this.totalItems = data.total;
         this.page = data.pagina;

         // Fuerza detección de cambios para actualizar la vista
         this.cdr.detectChanges();
      });
   }

   pintarTarjetasPorTipo(): void {
      this.servicioProductos.getProductosByFilter(this.page, this.tipe).subscribe((data: ProductoResponse) => {
         this.productos = data.productos;
         this.totalItems = data.total;
         this.page = data.pagina;

         // Fuerza detección de cambios para actualizar la vista
         this.cdr.detectChanges();
      });
   }

   pintarTarjetasPorNombre(): void {
      this.servicioProductos.getProductosByName(this.page, this.name).subscribe((data: ProductoResponse) => {
         this.productos = data.productos;
         this.totalItems = data.total;
         this.page = data.pagina;

         // Fuerza detección de cambios para actualizar la vista
         this.cdr.detectChanges();
      });
   }

   pintarTarjetasPorPrecio(): void {
      this.servicioProductos.getProductosByPrice(this.page, this.price).subscribe((data: ProductoResponse) => {
         this.productos = data.productos;
         this.totalItems = data.total;
         this.page = data.pagina;

         // Fuerza detección de cambios para actualizar la vista
         this.cdr.detectChanges();
      });
   }

   manejarCambioPagina(nuevoValor: number) {
      this.page = nuevoValor;
      if (this.tipe != "" && this.tipe != ' ') {
         this.pintarTarjetasPorTipo();
      } else if (this.name != "" && this.name != ' ') {
         this.pintarTarjetasPorNombre();
      } else if (this.price != 0 && this.price != null) {
         this.pintarTarjetasPorPrecio();
      } else {
         this.pintarTarjetas();
      }
      console.log("estamos en la pagina: " + this.page);
   }

   trackById(index: number, producto: Productos): string {
      return producto.id;
   }

   onTipeChanged(value: any) {
      this.tipe = value;
      console.log('Valor recibido desde input (hijo):', this.tipe);
      this.pintarTarjetasPorTipo();
   }
   onNameChanged(value: any) {
      this.name = value;
      console.log('Valor recibido desde input (hijo):', this.name);
      this.pintarTarjetasPorNombre();
   }
   onPriceChanged(value: any) {
      this.price = value;
      console.log('Valor recibido desde input (hijo):', this.price);
      this.pintarTarjetasPorPrecio();
   }

   addProductCarrito(id: string, event: Event): void {
      event.preventDefault(); // Previene la acción por defecto del clic
      console.log('Producto agregado con ID:', id);

      // Usamos find() para obtener un solo producto
      let producto = this.productos.find(producto => producto.id === id);

      if (producto) {
         // Si el producto fue encontrado, lo agregamos al carrito
         this.carrito.agregarProducto(producto);
      } else {
         console.log('Producto no encontrado');
      }
   }
}
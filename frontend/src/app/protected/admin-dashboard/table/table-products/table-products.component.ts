import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common'; // Importa CommonModule
import { ProductsProtectedService } from '../../../../services/protected/products-protected.service';
import { Productos } from '../../../../interfaces/productos';


@Component({
  selector: 'app-table-products',
  standalone: true, // Asegúrate de que el componente sea standalone
  imports: [CommonModule], // Importa CommonModule aquí
  templateUrl: './table-products.component.html',
  styleUrls: ['./table-products.component.css']
})
export class TableProductsComponent implements OnInit {
  productos: Productos[] = []; // Lista de productos

  constructor(private productosService: ProductsProtectedService) { }

  ngOnInit(): void {
    this.cargarProductos();
  }

  cargarProductos(): void {
    this.productosService.getProductos().subscribe(
      (data) => {
        this.productos = data; // Asigna los datos obtenidos a la propiedad productos
      },
      (error) => {
        console.error('Error al cargar los productos', error);
      }
    );
  }
}
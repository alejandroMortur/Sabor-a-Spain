import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common'; // Importa CommonModule
import { ProductsProtectedService } from '../../../../services/protected/products-protected.service';
import { Productos } from '../../../../interfaces/productos';


@Component({
  selector: 'app-table-products',
  standalone: true, 
  imports: [CommonModule], 
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

  // Método para editar un producto
  editarProducto(id: string): void {
    console.log('Editar producto con ID:', id);
  }

  // Método para eliminar un producto
  eliminarProducto(id: string): void {
    let idProducto = parseInt(id);
    if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
      this.productosService.deleteProducto(idProducto).subscribe(
        () => {
          console.log('Producto eliminado');
          this.cargarProductos(); // Recarga la lista de productos
        },
        (error) => {
          console.error('Error al eliminar el producto:', error);
        }
      );
    }
  }

  // Método para crear un nuevo producto
  crearProducto(): void {
    console.log('Crear nuevo producto');
  }

  // Método para refrescar la lista de usuarios
  refrescarUsuarios(): void {
    console.log('Refrescando lista de usuarios...');
    this.cargarProductos(); // Recarga la lista de usuarios
  }
}
import { Injectable } from '@angular/core';
import { Productos } from '../interfaces/productos';

@Injectable({
  providedIn: 'root'
})
export class GestionarCarritoService {

  private carritoKey = 'carrito';

  constructor() { }

  // Obtener el carrito desde localStorage
  getCarrito(): Productos[] {
    const carrito = localStorage.getItem(this.carritoKey);
    return carrito ? JSON.parse(carrito) : [];
  }

  agregarProducto(producto: Productos): void {
    const carrito = this.getCarrito();

    // Verificamos si el producto ya existe en el carrito
    const productoExistente = carrito.find(p => p.id === producto.id);

    if (productoExistente) {
      // Si el producto ya existe, aumentamos la cantidad
      productoExistente.Stock = productoExistente.Stock + 1;  // O puedes usar productoExistente.Stock++
    } else {
      // Si no existe, agregamos el producto con cantidad 1
      carrito.push({ ...producto, Stock: 1 });
    }

    // Actualizamos el carrito en el localStorage
    localStorage.setItem(this.carritoKey, JSON.stringify(carrito));
  }

  // Eliminar un producto del carrito por su id
  eliminarProductoPorId(id: string): void {
    const carrito = this.getCarrito();
    const carritoFiltrado = carrito.filter(producto => producto.id !== id); // Filtra el producto con ese id
    localStorage.setItem(this.carritoKey, JSON.stringify(carritoFiltrado));
  }

  // Eliminar una unidad del producto, no todo el producto
  eliminarUnidadPorId(id: string): void {
    const carrito = this.getCarrito();
    const productoExistente = carrito.find(producto => producto.id === id);

    if (productoExistente) {
      if (productoExistente.Stock > 1) {
        // Si el producto tiene más de una unidad, reducimos la cantidad
        productoExistente.Stock = productoExistente.Stock - 1;  // O puedes usar productoExistente.Stock++
      } else {
        // Si es la última unidad, eliminamos el producto del carrito
        this.eliminarProductoPorId(id);
      }
    }

    localStorage.setItem(this.carritoKey, JSON.stringify(carrito));
  }

  // Limpiar el carrito
  vaciarCarrito(): void {
    localStorage.removeItem(this.carritoKey);
  }
}

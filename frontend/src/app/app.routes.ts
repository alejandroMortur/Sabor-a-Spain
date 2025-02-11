import { Routes } from '@angular/router';
import { CarouselComponent } from './carousel/carousel.component';
import { ProductosComponent } from './productos/productos.component';
import { TipoProductoComponent } from './productos/tipo-producto/tipo-producto.component';

export const routes: Routes = [
    { path: '', component: CarouselComponent },
    { path: 'productos', component: ProductosComponent },  // Productos generales
    { path: 'productos/:tipo', component: TipoProductoComponent },  // Productos por tipo (por ejemplo, "productos/tipo1")
];

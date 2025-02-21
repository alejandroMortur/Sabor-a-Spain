import { Routes } from '@angular/router';
import { ProductosComponent } from './productos/productos.component';
import { TipoProductoComponent } from './productos/tipo-producto/tipo-producto.component';
import { HomeComponentComponent } from './home-component/home-component.component';

export const routes: Routes = [
    { path: 'productos', component: ProductosComponent },  // Productos generales
    { path: 'productos/:tipo', component: TipoProductoComponent },  // Productos por tipo (por ejemplo, "productos/tipo1")
    { path: '', component: HomeComponentComponent},
];

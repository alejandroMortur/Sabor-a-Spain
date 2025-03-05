import { Routes } from '@angular/router';
import { ProductosComponent } from './productos/productos.component';
import { TipoProductoComponent } from './productos/tipo-producto/tipo-producto.component';
import { HomeComponentComponent } from './home-component/home-component.component';
import { RegisterComponent } from './auth/register/register.component';
import { LoginComponent } from './auth/login/login.component';
import { PaymentGatewayComponent } from './protected/payment-gateway/payment-gateway.component';
import { AdminDashboardComponent } from './protected/admin-dashboard/admin-dashboard.component';
import { AuthGuard } from './services/auth/guards/auth.guard';

export const routes: Routes = [
    { path: 'auth/register', component: RegisterComponent },
    { path: 'auth/login', component: LoginComponent },
    { path: 'productos', component: ProductosComponent },  // Productos generales
    { path: 'productos/:tipo', component: TipoProductoComponent },  // Productos por tipo (por ejemplo, "productos/tipo1")
    { path: 'admin/dashboard', component: AdminDashboardComponent, canActivate: [AuthGuard] },
    { path: 'payment', component: PaymentGatewayComponent, canActivate: [AuthGuard] },
    { path: '', component: HomeComponentComponent},
];

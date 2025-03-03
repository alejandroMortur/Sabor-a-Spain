import { Component } from '@angular/core';
import { TableProductsComponent } from './table/table-products/table-products.component';
import { TableTipesComponent } from './table/table-tipes/table-tipes.component';
import { TableUsersComponent } from './table/table-users/table-users.component';

@Component({
  selector: 'app-admin-dashboard',
  imports: [TableProductsComponent,TableTipesComponent,TableUsersComponent],
  templateUrl: './admin-dashboard.component.html',
  styleUrl: './admin-dashboard.component.css'
})
export class AdminDashboardComponent {

}

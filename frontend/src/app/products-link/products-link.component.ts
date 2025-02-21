import { ChangeDetectorRef, Component } from '@angular/core';
import { Tipos } from '../interfaces/tipos';
import { CommonModule } from '@angular/common';
import { GestionTiposService } from '../services/gestion-tipos.service';

@Component({
  selector: 'app-products-link',
  imports: [CommonModule],
  templateUrl: './products-link.component.html',
  styleUrl: './products-link.component.css'
})
export class ProductsLinkComponent {
  tipos:Tipos[] = [];

  constructor(private servicioProductos: GestionTiposService, private cdr: ChangeDetectorRef){}

  ngOnInit() {
    this.pintarTarjetas();
 }

  pintarTarjetas(): void {
      // Asegúrate de que data sea un array de Tipos[]
      this.servicioProductos.getListadoTipos().subscribe((data: Tipos[]) => {
        this.tipos = data;
        console.log(this.tipos)
        this.cdr.detectChanges();  // Forzar la detección de cambios si es necesario
      });
  }
}




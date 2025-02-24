import { ChangeDetectionStrategy, ChangeDetectorRef, Component } from '@angular/core';
import { Tipos } from '../interfaces/tipos';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { GestionTiposService } from '../services/gestion-tipos.service';

@Component({
  selector: 'app-products-link',
  imports: [CommonModule],
  standalone: true,
  templateUrl: './products-link.component.html',
  styleUrl: './products-link.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ProductsLinkComponent {
  tipos:Tipos[] = [];
  activeLink: string = '';

  constructor(private servicioProductos: GestionTiposService, private cdr: ChangeDetectorRef, private router: Router){}

  ngOnInit() {
    this.pintarTarjetas();
 }

 pintarTarjetas(): void {
    this.servicioProductos.getListadoTipos().subscribe({
      next: (response: any) => {
        // ✅ Verificamos si 'response.tipos' es un array
        if (Array.isArray(response.tipos)) {
          this.tipos = [...response.tipos];  // Forzar cambio de referencia
        } else {
          console.error('Error: La API no devolvió un array de tipos', response);
          this.tipos = []; // Evitar errores en la vista
        }

        this.cdr.markForCheck();
      },
      error: (err) => {
        console.error('Error cargando tipos:', err);
      }
    });
  }

  route(path: string): void {
    this.router.navigate([path]);
    this.activeLink = path;
  }

  // ✅ Función trackBy para mejorar rendimiento
  trackById(index: number, item: Tipos): number {
    return parseInt(item.id);
  }

}






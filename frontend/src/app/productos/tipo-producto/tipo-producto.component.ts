import { Component, OnInit} from '@angular/core';
import { ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-tipo-producto',
  imports: [],
  templateUrl: './tipo-producto.component.html',
  styleUrl: './tipo-producto.component.css'
})
export class TipoProductoComponent {

  tipo: string | null = null;

  constructor(private route: ActivatedRoute) {}

  ngOnInit(): void {
    // Obtener el parámetro 'tipo' de la ruta
    this.route.paramMap.subscribe(params => {
      this.tipo = params.get('tipo');
    });
  }
}


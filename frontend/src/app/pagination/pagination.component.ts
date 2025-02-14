import { NgIf } from '@angular/common';
import { Component, EventEmitter, Input, Output} from '@angular/core';
import { NgbPaginationModule } from '@ng-bootstrap/ng-bootstrap';

const FILTER_PAG_REGEX = /[^0-9]/g;
@Component({
  selector: 'app-pagination',
  imports: [NgbPaginationModule,NgIf],
  templateUrl: './pagination.component.html',
  styleUrl: './pagination.component.css'
})
export class PaginationComponent {
  @Input() page: number = 1;  // Página actual
  @Input() totalItems: number = 0;  // Total de productos
  @Input() pageSize: number = 6;
  @Output() cambio: EventEmitter<number> = new EventEmitter<number>();

  totalPages: number = 0;  // Total de páginas calculado

  ngOnChanges() {
    this.totalPages = Math.ceil(this.totalItems / this.pageSize);  // Calcular total de páginas
    if (this.page > this.pageSize) {
      this.page = this.totalPages;
    }
  }
	selectPage(page: string) {
		this.page = parseInt(page, 10) || 1;
	}
	formatInput(input: HTMLInputElement) {
		input.value = input.value.replace(FILTER_PAG_REGEX, '');
	}
  emitirCambioPagina() {
    this.cambio.emit(this.page); // Emitir el nuevo valor al padre
  }
}
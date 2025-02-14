import { Component, EventEmitter, Input, Output} from '@angular/core';
import { NgbPaginationModule } from '@ng-bootstrap/ng-bootstrap';

const FILTER_PAG_REGEX = /[^0-9]/g;
@Component({
  selector: 'app-pagination',
  imports: [NgbPaginationModule],
  templateUrl: './pagination.component.html',
  styleUrl: './pagination.component.css'
})
export class PaginationComponent {
	selectPage(page: string) {
		this.page = parseInt(page, 10) || 1;
	}
	formatInput(input: HTMLInputElement) {
		input.value = input.value.replace(FILTER_PAG_REGEX, '');
	}

  @Input() page: number = 1;  // Página actual
  @Input() totalItems: number = 0;  // Total de productos
  @Output() cambio: EventEmitter<number> = new EventEmitter<number>();

  emitirCambioPagina() {
    this.cambio.emit(this.page); // Emitir el nuevo valor al padre
  }
}

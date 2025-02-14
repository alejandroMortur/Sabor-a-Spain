import { Component, EventEmitter, Output } from '@angular/core';
import { NgbPaginationModule } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-pagination',
  imports: [NgbPaginationModule],
  templateUrl: './pagination.component.html',
  styleUrl: './pagination.component.css'
})
export class PaginationComponent {
  page = 1;
  // Emitir un evento cuando cambie la página
  @Output() pageChange = new EventEmitter<number>();

  // Este método se llama cuando cambia la página
  onPageChange(page: number): void {
    this.pageChange.emit(page); // Emitimos el cambio de página
  }
}

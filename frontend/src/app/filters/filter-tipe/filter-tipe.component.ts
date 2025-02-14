import { Component } from '@angular/core';
import { NgbTypeaheadModule } from '@ng-bootstrap/ng-bootstrap';
import { Observable, OperatorFunction } from 'rxjs';
import { debounceTime, distinctUntilChanged, map } from 'rxjs/operators';
import { FormsModule } from '@angular/forms';
import { JsonPipe } from '@angular/common';

const items: { name: string }[] = [
	{ name: 'Bebidas' },
	{ name: 'Lácteos' },
	{ name: 'Frutas' },
	{ name: 'Verduras' },
	{ name: 'Pescados' },
	{ name: 'Ropa' },
	{ name: 'Libros' },
	{ name: 'Eventos' },
	{ name: 'Instrumentos' },
	{ name: 'Estrellas' },
	{ name: 'Cárnicos' }
];

@Component({
  selector: 'app-filter-tipe',
  imports: [NgbTypeaheadModule, FormsModule, JsonPipe],
  templateUrl: './filter-tipe.component.html',
  styleUrl: './filter-tipe.component.css'
})
export class FilterTipeComponent {
  model: any;

  formatter = (result: string) => result.toUpperCase();

  search: OperatorFunction<string, readonly string[]> = (text$: Observable<string>) =>
    text$.pipe(
      debounceTime(200),
      distinctUntilChanged(),
      map((term) =>
        term === ''
          ? []
          : items
              .filter((v) => v.name.toLowerCase().includes(term.toLowerCase()))
              .map(v => v.name) // Extrae solo los nombres
              .slice(0, 10),
      ),
    );  
}

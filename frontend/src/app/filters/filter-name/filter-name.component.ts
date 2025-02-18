import { Component, EventEmitter, Output } from '@angular/core';
import { NgbTypeaheadModule } from '@ng-bootstrap/ng-bootstrap';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-filter-name',
  imports: [NgbTypeaheadModule, FormsModule],
  templateUrl: './filter-name.component.html',
  styleUrl: './filter-name.component.css'
})
export class FilterNameComponent {
  model: any;

  @Output() modelChanged: EventEmitter<any> = new EventEmitter<any>();
	formatter = (result: string) => result.toUpperCase();

  logModel() {
    this.modelChanged.emit(this.model);
    console.log('Valor seleccionado (logModel):', this.model);
  }
}
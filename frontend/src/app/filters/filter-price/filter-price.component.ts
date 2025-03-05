import { ChangeDetectorRef, Component, EventEmitter, Output } from '@angular/core';
import { NgxBootstrapSliderModule } from 'ngx-bootstrap-slider';
import { GestionProductosService } from '../../services/gestion-productos.service';
import { maxProduct } from '../../interfaces/maxProduct';

@Component({
  selector: 'app-filter-price',
  templateUrl: './filter-price.component.html',
  imports: [NgxBootstrapSliderModule],
  styleUrls: ['./filter-price.component.css']
})
export class FilterPriceComponent {
  value: number = 10;  
  enabled: boolean = true;  
  min: number = 0; 
  max: number = 100;  

  @Output() dataChanged: EventEmitter<any> = new EventEmitter<any>();

  constructor(private servicioProductos: GestionProductosService, private cdr: ChangeDetectorRef){}

  ngOnInit() {
     this.getMaximo();
  }

  getMaximo(){
    
    this.servicioProductos.getProductosByPriceMax().subscribe((data: maxProduct) => {
      this.max = data.precioMaximo;
      this.max += 10;
        // Forzar detección de cambios para actualizar la vista
         this.cdr.detectChanges();
      }); 
  }

  change() {
    this.dataChanged.emit(this.value); //Emite el valor cambiado
    console.log('Slider value changed to:', this.value);
  }

}
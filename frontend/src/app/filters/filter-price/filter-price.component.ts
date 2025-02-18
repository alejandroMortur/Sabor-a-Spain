import { Component } from '@angular/core';
import { NgxBootstrapSliderModule } from 'ngx-bootstrap-slider';

@Component({
  selector: 'app-filter-price',
  templateUrl: './filter-price.component.html',
  imports: [NgxBootstrapSliderModule],
  styleUrls: ['./filter-price.component.css']
})
export class FilterPriceComponent {
  value: number = 50;  // Initial value of the slider
  enabled: boolean = true;  // Control whether the slider is enabled or disabled
  min: number = 0; //valor minimo slider 
  max: number = 100;  //valor maximo slider
  
  // This function will be triggered on the slider value change
  change() {
    console.log('Slider value changed to:', this.value);
  }
}



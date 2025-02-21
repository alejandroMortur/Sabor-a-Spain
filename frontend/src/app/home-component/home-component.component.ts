import { Component } from '@angular/core';
import { CarouselComponent } from '../carousel/carousel.component';
import { ProductsLinkComponent } from '../products-link/products-link.component';

@Component({
  selector: 'app-home-component',
  imports: [CarouselComponent,ProductsLinkComponent],
  templateUrl: './home-component.component.html',
  styleUrl: './home-component.component.css'
})
export class HomeComponentComponent {

}


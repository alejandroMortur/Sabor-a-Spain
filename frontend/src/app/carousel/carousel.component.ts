import { Component } from '@angular/core';
import { NgbCarouselModule } from '@ng-bootstrap/ng-bootstrap';
import { ItemCarousel } from '../interfaces/ItemCarousel';
import { CarouserlServiceService } from '../services/carouserl-service.service';

@Component({
  selector: 'app-carousel',
  imports: [NgbCarouselModule ],
  templateUrl: './carousel.component.html',
  styleUrl: './carousel.component.css'
})
export class CarouselComponent {
  carouselItems:ItemCarousel [] = [];
  constructor(private servicioCarousel: CarouserlServiceService) {}
  ngOnInit() {
    this.getItems();
  }
  getItems(): void {
    this.servicioCarousel.getItems().subscribe(ItemCarousel => this.carouselItems = ItemCarousel);
  }
}

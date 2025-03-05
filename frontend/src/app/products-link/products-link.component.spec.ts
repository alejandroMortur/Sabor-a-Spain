import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ProductsLinkComponent } from './products-link.component';

describe('ProductsLinkComponent', () => {
  let component: ProductsLinkComponent;
  let fixture: ComponentFixture<ProductsLinkComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ProductsLinkComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ProductsLinkComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

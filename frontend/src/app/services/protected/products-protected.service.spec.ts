import { TestBed } from '@angular/core/testing';

import { ProductsProtectedService } from './products-protected.service';

describe('ProductsProtectedService', () => {
  let service: ProductsProtectedService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(ProductsProtectedService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});

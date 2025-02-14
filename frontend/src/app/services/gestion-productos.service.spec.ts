import { TestBed } from '@angular/core/testing';

import { GestionProductosService } from './gestion-productos.service';

describe('GestionProductosService', () => {
  let service: GestionProductosService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(GestionProductosService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});

import { TestBed } from '@angular/core/testing';

import { GestionTiposService } from './gestion-tipos.service';

describe('GestionTiposService', () => {
  let service: GestionTiposService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(GestionTiposService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});

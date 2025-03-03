import { TestBed } from '@angular/core/testing';

import { TipesProtectedService } from './tipes-protected.service';

describe('TipesProtectedService', () => {
  let service: TipesProtectedService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(TipesProtectedService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});

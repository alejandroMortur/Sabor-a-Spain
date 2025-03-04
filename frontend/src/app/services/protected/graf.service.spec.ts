import { TestBed } from '@angular/core/testing';

import { GrafService } from './graf.service';

describe('GrafService', () => {
  let service: GrafService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(GrafService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});

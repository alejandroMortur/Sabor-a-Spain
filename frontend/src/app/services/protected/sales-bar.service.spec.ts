import { TestBed } from '@angular/core/testing';

import { SalesBarService } from './sales-bar.service';

describe('SalesBarService', () => {
  let service: SalesBarService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(SalesBarService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});

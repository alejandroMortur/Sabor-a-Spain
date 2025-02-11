import { TestBed } from '@angular/core/testing';

import { CarouserlServiceService } from './carouserl-service.service';

describe('CarouserlServiceService', () => {
  let service: CarouserlServiceService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(CarouserlServiceService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});

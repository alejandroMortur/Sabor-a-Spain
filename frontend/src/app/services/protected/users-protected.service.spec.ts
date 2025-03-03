import { TestBed } from '@angular/core/testing';

import { UsersProtectedService } from './users-protected.service';

describe('UsersProtectedService', () => {
  let service: UsersProtectedService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(UsersProtectedService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});

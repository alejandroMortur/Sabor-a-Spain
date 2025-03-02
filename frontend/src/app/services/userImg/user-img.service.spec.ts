import { TestBed } from '@angular/core/testing';

import { UserImgService } from './user-img.service';

describe('UserImgService', () => {
  let service: UserImgService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(UserImgService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});

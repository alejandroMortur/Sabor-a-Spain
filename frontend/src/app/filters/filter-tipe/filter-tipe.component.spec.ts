import { ComponentFixture, TestBed } from '@angular/core/testing';

import { FilterTipeComponent } from './filter-tipe.component';

describe('FilterTipeComponent', () => {
  let component: FilterTipeComponent;
  let fixture: ComponentFixture<FilterTipeComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [FilterTipeComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(FilterTipeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

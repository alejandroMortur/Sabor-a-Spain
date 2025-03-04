import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TableTipesComponent } from './table-tipes.component';

describe('TableTipesComponent', () => {
  let component: TableTipesComponent;
  let fixture: ComponentFixture<TableTipesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [TableTipesComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(TableTipesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

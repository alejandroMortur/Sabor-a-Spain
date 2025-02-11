import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { NgbDropdownModule, NgbModule, NgbNavModule } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-nav-bar',
  imports: [NgbNavModule, NgbDropdownModule,NgbModule],
  templateUrl: './nav-bar.component.html',
  styleUrl: './nav-bar.component.css'
})
export class NavBarComponent {
  isCollapsed = true;
  activeLink: string = '';

  constructor(private router: Router) { }
  
  route(path: string): void {
    this.router.navigate([path]);
    this.activeLink = path;
  }
}


import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { NgbDropdownModule, NgbModule, NgbNavModule } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-nav-bar',
  imports: [NgbNavModule, NgbDropdownModule,NgbModule,FontAwesomeModule],
  templateUrl: './nav-bar.component.html',
  styleUrl: './nav-bar.component.css'
})
export class NavBarComponent {
  isCollapsed = true;
  activeLink: string = '';
  userimg: string = "http://localhost:8080/data/imagenes/user.png";

  constructor(private router: Router) { }
  
  route(path: string): void {
    this.router.navigate([path]);
    this.activeLink = path;
  }
}


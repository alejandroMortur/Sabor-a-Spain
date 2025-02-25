import { Component, TemplateRef, WritableSignal, inject, signal } from '@angular/core';
import { Router } from '@angular/router';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { NgbDatepickerModule, NgbDropdownModule, NgbModule, NgbNavModule, NgbOffcanvas, OffcanvasDismissReasons } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-nav-bar',
  imports: [NgbNavModule, NgbDropdownModule, NgbModule, FontAwesomeModule, NgbDatepickerModule],
  templateUrl: './nav-bar.component.html',
  styleUrl: './nav-bar.component.css'
})
export class NavBarComponent {
  isCollapsed = true;
  activeLink: string = '';
  userimg: string = "http://localhost:8080/data/imagenes/user.png";
  authState: string = "Registro/Login";

  constructor(private router: Router) { }

  route(path: string): void {
    this.router.navigate([path]);
    this.activeLink = path;
  }

  private offcanvasService = inject(NgbOffcanvas);
  closeResult: WritableSignal<string> = signal('');

  open(content: TemplateRef<any>) {
    this.offcanvasService.open(content, { ariaLabelledBy: 'offcanvas-basic-title' }).result.then(
      (result) => {
        this.closeResult.set(`Closed with: ${result}`);
      },
      (reason) => {
        this.closeResult.set(`Dismissed ${this.getDismissReason(reason)}`);
      },
    );
  }

  private getDismissReason(reason: any): string {
    switch (reason) {
      case OffcanvasDismissReasons.ESC:
        return 'by pressing ESC';
      case OffcanvasDismissReasons.BACKDROP_CLICK:
        return 'by clicking on the backdrop';
      default:
        return `with: ${reason}`;
    }
  }
}


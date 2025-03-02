import { Component, TemplateRef, WritableSignal, inject, signal } from '@angular/core';
import { Router } from '@angular/router';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { NgbDatepickerModule, NgbDropdownModule, NgbModule, NgbNavModule, NgbOffcanvas, OffcanvasDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { GestionarCarritoService } from '../services/gestionar-carrito.service';
import { NgFor, NgIf } from '@angular/common';
import { UserImgService } from '../services/userImg/user-img.service';


@Component({
  selector: 'app-nav-bar',
  imports: [NgFor,NgIf,NgbNavModule, NgbDropdownModule, NgbModule, FontAwesomeModule, NgbDatepickerModule],
  templateUrl: './nav-bar.component.html',
  styleUrl: './nav-bar.component.css'
})
export class NavBarComponent {
  isCollapsed = true;
  activeLink: string = '';
  userimg: string = "https://localhost:8443/data/imagenes/user.png";
  authState: string = "Registro/Login";
  
  // Esta propiedad almacenará los productos del carrito
  carrito: any[] = [];

  private offcanvasService = inject(NgbOffcanvas);
  closeResult: WritableSignal<string> = signal('');

  constructor(private router: Router, private carritoService: GestionarCarritoService, private userImgService: UserImgService) { 

      // Escucha cambios en la imagen del usuario
      this.userImgService.userImage$.subscribe(imageUrl => {
        if (imageUrl) {
          this.userimg = imageUrl; // Actualiza la imagen de usuario
        }
      });

  }

  route(path: string): void {
    this.router.navigate([path]);
    this.activeLink = path;
  }

  // Método para cargar los productos del carrito desde el servicio
  private loadCarrito(): void {
    this.carrito = this.carritoService.getCarrito();  // Obtener carrito desde el servicio
  }

  // Método para vaciar el carrito
  vaciarCarrito(): void {
    this.carrito = []; // Limpiar el array del carrito
    this.carritoService.vaciarCarrito();  // También podrías agregar un método en el servicio para limpiar el carrito si es necesario
  }

  open(content: TemplateRef<any>) {
    this.offcanvasService.open(content, { ariaLabelledBy: 'offcanvas-basic-title' }).result.then(
      (result) => {
        this.closeResult.set(`Closed with: ${result}`);
      },
      (reason) => {
        this.closeResult.set(`Dismissed ${this.getDismissReason(reason)}`);
      }
    );
    this.loadCarrito();  // Cargar el carrito cuando se abre el offcanvas
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

  // Método para redirigir a la pasarela de pago
  comprar(): void {
      this.router.navigate(['/payment']); // Redirige a la ruta de la pasarela de pago
  }

}
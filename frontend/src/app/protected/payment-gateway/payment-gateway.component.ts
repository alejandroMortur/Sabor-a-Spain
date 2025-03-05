import { Component, OnInit, OnDestroy } from '@angular/core';
import { PayPalService } from '../../services/protected/paypal/paypal.service';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-payment-gateway',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './payment-gateway.component.html',
  styleUrls: ['./payment-gateway.component.css'],
})
export class PaymentGatewayComponent implements OnInit, OnDestroy {
  amount: number = 100;
  currency: string = 'USD';
  paymentStatus: string | null = null;
  carrito: any[] = [];
  popup: Window | null = null;

  constructor(private payPalService: PayPalService, private router: Router) {
    const carritoGuardado = localStorage.getItem('carrito');
    if (carritoGuardado) {
      this.carrito = JSON.parse(carritoGuardado);
    }
    this.amount = this.calcularTotal();
  }

  // Calcular el total del carrito
  calcularTotal(): number {
    return this.carrito.reduce((total, producto) => {
      return total + producto.Precio * producto.Stock;
    }, 0);
  }

  // Crear el pago y abrir la ventana emergente
  createPayment() {
    this.payPalService.createPayment(this.carrito, this.amount, this.currency).subscribe(
      (response) => {
        if (response.approvalUrl) {
          // Abrir la ventana emergente
          this.popup = window.open(response.approvalUrl, 'PayPal', 'width=600,height=400');

          // Escuchar el cierre de la ventana emergente
          if (this.popup) {
            const interval = setInterval(() => {
              if (this.popup && this.popup.closed) {
                clearInterval(interval);
                this.onPaymentSuccess(); // Llamar a la función cuando se cierre la ventana emergente
              }
            }, 500);
          }
        } else {
          console.error('No se pudo obtener la URL de aprobación de PayPal');
        }
      },
      (error) => {
        console.error('Error al crear el pago', error);
      }
    );
  }

  // Esta función se llama cuando la ventana emergente se cierra
  onPaymentSuccess() {
    // Limpiar el carrito
    this.limpiarCarrito();

    // Redirigir al usuario al home
    this.router.navigate(['/']);
  }

  // Limpiar el carrito
  limpiarCarrito() {
    localStorage.removeItem('carrito');
    this.carrito = [];
  }

  // Escuchar el mensaje cuando la ventana emergente se cierra
  ngOnInit() {
    // No es necesario el listener de 'message', ya que verificamos el estado de la ventana
  }

  // Eliminar el listener cuando el componente se destruye
  ngOnDestroy() {
    // Si agregamos algún listener en ngOnInit, deberíamos removerlo aquí.
    // En este caso, no tenemos listeners adicionales.
  }
}

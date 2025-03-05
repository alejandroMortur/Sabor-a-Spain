import { Component } from '@angular/core';
import { PayPalService } from '../../services/protected/paypal/paypal.service';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';  // Asegúrate de importar FormsModule

@Component({
  selector: 'app-payment-gateway',
  standalone: true,  // Indica que este es un componente independiente
  imports: [CommonModule, FormsModule],  // Agrega FormsModule para usar ngModel
  templateUrl: './payment-gateway.component.html',
  styleUrls: ['./payment-gateway.component.css']  // Asegúrate de usar styleUrls (no styleUrl)
})
export class PaymentGatewayComponent {
  amount: number = 100;  // Define la propiedad amount
  currency: string = 'USD';  // Define la propiedad currency
  paymentStatus: string | null = null;  // Define la propiedad paymentStatus
  carrito: any[] = []; // Define el array carrito

  constructor(private payPalService: PayPalService) {
    // Obtener el carrito desde localStorage (asegurándote de que existe)
    const carritoGuardado = localStorage.getItem('carrito');
    if (carritoGuardado) {
      this.carrito = JSON.parse(carritoGuardado);
    }
    // Calcular el total del carrito
    this.amount = this.calcularTotal();
  }

  // Método para calcular el total del carrito
  calcularTotal(): number {
    return this.carrito.reduce((total, producto) => {
      return total + (producto.Precio * producto.Stock);
    }, 0);
  }

  createPayment() {
    // Llamar al servicio con los parámetros de pago
    this.payPalService.createPayment(this.amount, this.currency).subscribe(
      (response) => {
        // Si la respuesta contiene approvalUrl, redirigir al usuario a PayPal
        if (response.approvalUrl) {
          window.location.href = response.approvalUrl; // Redirigir al usuario a PayPal
        } else {
          console.error('No se pudo obtener la URL de aprobación de PayPal');
        }
      },
      (error) => {
        console.error('Error al crear el pago', error);
      }
    );
  }

  executePayment(paymentId: string, payerId: string) {
    this.payPalService.executePayment(paymentId, payerId).subscribe(
      (response) => {
        console.log('Pago ejecutado correctamente', response);
      },
      (error) => {
        console.error('Error al ejecutar el pago', error);
      }
    );
  }

  cancelPayment() {
    this.payPalService.cancelPayment().subscribe(
      (response) => {
        console.log('Pago cancelado', response);
      },
      (error) => {
        console.error('Error al cancelar el pago', error);
      }
    );
  }
}

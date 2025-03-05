import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class PayPalService {
  private baseUrl = 'https://localhost:8443/Symfony/public/index.php/api/protected/paypal';  // Asegúrate de que la URL sea la correcta

  constructor(private http: HttpClient) {}

  // Llamar al backend Symfony para crear el pago
  createPayment(totalAmount: number, currency: string): Observable<any> {
    // Se envían los datos totalAmount y currency en el cuerpo de la solicitud
    return this.http.post<any>(this.baseUrl, { totalAmount, currency }, { withCredentials: true });
  }

  // Llamar al backend para ejecutar el pago
  executePayment(paymentId: string, payerId: string): Observable<any> {
    return this.http.post<any>(`${this.baseUrl}/pay`, { paymentId, payerId }, { withCredentials: true });
  }

  // Llamar al backend para cancelar el pago
  cancelPayment(): Observable<any> {
    return this.http.get<any>(`${this.baseUrl}/cancel`, { withCredentials: true });
  }
}


import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class PayPalService {
  private baseUrl = 'https://localhost:8443/Symfony/public/index.php/api/protected/paypal';

  constructor(private http: HttpClient) {}

  // Crear el pago
  createPayment(cart: any[], totalAmount: number, currency: string): Observable<any> {
    return this.http.post<any>(this.baseUrl, { cart, totalAmount, currency }, { withCredentials: true });
  }

  // Ejecutar el pago
  executePayment(paymentId: string, payerId: string): Observable<any> {
    return this.http.post<any>(`${this.baseUrl}/execute`, { paymentId, payerId }, { withCredentials: true });
  }

  // Cancelar el pago
  cancelPayment(): Observable<any> {
    return this.http.get<any>(`${this.baseUrl}/cancel`, { withCredentials: true });
  }
}
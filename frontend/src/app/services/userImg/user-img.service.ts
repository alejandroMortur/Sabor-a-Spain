import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class UserImgService {

  // Usamos BehaviorSubject para que todos los componentes que se subscriban reciban actualizaciones.
  private userImageSource = new BehaviorSubject<string>("https://localhost:8443/data/imagenes/user.png");
  userImage$ = this.userImageSource.asObservable();

  constructor() {}

  // Método para actualizar la imagen del usuario
  updateUserImage(imageUrl: string): void {
    this.userImageSource.next(imageUrl); // Emite el nuevo valor
  }
}

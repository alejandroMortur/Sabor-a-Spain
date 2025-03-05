import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class RegisterService {

  private apiUrl = 'https://localhost:8443/Symfony/public/index.php/register'; // Update with your backend URL

  constructor(private http: HttpClient) { }

  registerUser(username: string, password: string, email: string, image: File | null): Observable<any> {
    const formData = new FormData();
    formData.append('username', username);
    formData.append('password', password);
    formData.append('email', email);

    if (image) {
      formData.append('image', image, image.name); // Append the file with a name
    }

    return this.http.post(this.apiUrl, formData, { withCredentials: true });
  }
}

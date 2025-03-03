export interface Usuario {
    id: number;             // ID único del usuario
    email: string;          // Correo electrónico del usuario
    userIdentifier: string; // Identificador del usuario (similar al email)
    roles: string[];        // Array de roles del usuario (ej. ['ROLE_ADMIN', 'ROLE_USER'])
    foto: string;           // URL de la foto del usuario
    ventas: any[];          // Array de ventas (puede estar vacío)
    nombre: string;         // Nombre completo del usuario
    refreshToken: string;   // Refresh token (no usado en la interfaz, pero disponible en el JSON)
    payload: {              // Payload con los mismos datos del usuario
      id: number;
      email: string;
      roles: string[];
    };
    username: string;       // Nombre de usuario
    password: string;
    
  }
  
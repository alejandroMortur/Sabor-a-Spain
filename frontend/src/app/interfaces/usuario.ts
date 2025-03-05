export interface Usuario {
    id: number;             
    email: string;          
    userIdentifier: string; 
    roles: string[];        // Array de roles del usuario (ej. ['ROLE_ADMIN', 'ROLE_USER'])
    foto: string;           
    ventas: any[];          // Array de ventas (puede estar vacío)
    nombre: string;         
    refreshToken: string;   
    payload: {              
      id: number;
      email: string;
      roles: string[];
    };
    username: string;       
    password: string;
    Activo: boolean;
  }
  
// interfaces/productoRespond.ts
import { Productos} from './productos';  // Asegúrate de que la ruta sea correcta

export interface ProductoResponse {
    "productos": Productos[];  // Array de productos
    "total": number;           // Total de productos
    "pagina": number;
}

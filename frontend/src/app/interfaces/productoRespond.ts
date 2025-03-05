// interfaces/productoRespond.ts
import { Productos} from './productos';

export interface ProductoResponse {
    "productos": Productos[];  // Array de productos
    "total": number;           // Total de productos
    "pagina": number;
}

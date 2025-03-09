# ❤️💛❤️ Sabor a España - eCommerce 🇪🇸

## 📌 Descripción
Bienvenido a **Sabor a España**, un eCommerce donde puedes explorar y comprar los productos más icónicos de España 🇪🇸. Desde el exquisito **jamón ibérico** 🥩, el **aceite de oliva virgen extra** 🫒, hasta los mejores **vinos españoles** 🍷, esta tienda te trae los sabores de nuestra tierra directamente a tu hogar.

La plataforma permite a los usuarios:
- Explorar productos 🛍️
- Agregar al carrito 🛒
- Realizar compras seguras 💳
- Seguir el estado de sus pedidos 📦

Además, incluye una sección de **administración** para gestionar productos, usuarios y pedidos.

---

## 🛠️ Tecnologías Utilizadas

| Tecnología  | Descripción |
|-------------|------------|
| **Angular 16** | Framework para el desarrollo del frontend ⚛️ |
| **Symfony 7.2.3** | Framework PHP para la lógica del backend 🖥️ |
| **Bootstrap 5.3** | Biblioteca para diseño responsivo 🎨 |
| **PostgreSQL 15** | Base de datos relacional 🗃️ |
| **pgAdmin 6** | Herramienta para administrar PostgreSQL 🧑‍💻 |
| **Docker** | Contenedores para una fácil implementación 🐳 |

---

## ✨ Características Principales

### 🛒 Usuario Final
- 🔑 **Registro e inicio de sesión**
- 🔍 **Exploración de productos típicos**
- 🛒 **Carrito de compras y pago seguro**
- 🚚 **Seguimiento de pedidos**

### 🔧 Administración
- 🎨 **Gestión de productos**
- 👥 **Administración de usuarios**
- 📦 **Gestión y control de pedidos**

---

## 🚀 Instalación y Ejecución con Docker

### 🔹 Backend (Symfony + PostgreSQL + pgAdmin)
1. **Clona el repositorio**:
   ```bash
   git clone https://github.com/usuario/Sabor-a-Spain.git
   cd Sabor-a-Spain/backend
   ```

2. **Inicia los contenedores**:
   ```bash
   docker-compose up --build -d
   ```

3. **Accede a los servicios**:
   - **Symfony (Backend)** → [https://localhost:8443/Symfony/public/index.php](https://localhost:8443/Symfony/public/index.php)
   - **pgAdmin (Gestión de BD)** → [http://localhost:8081](http://localhost:8081)
     - **Usuario**: `admin@admin.com`
     - **Contraseña**: `admin`

4. **Accede al contenedor del backend (Symfony)**:
   ```bash
   docker exec -it symfony_app bash
   ```

### 🔹 Frontend (Angular)
1. **Ubica el frontend**:
   ```bash
   cd ../frontend
   ```
2. **Instala las dependencias**:
   ```bash
   npm install
   ```
3. **Ejecuta el servidor de Angular**:
   ```bash
   ng serve --open
   ```
4. **Accede a la aplicación**:
   - [http://localhost:4200](http://localhost:4200)

---

## 📂 Estructura del Proyecto
```
Sabor-a-Spain/
│── backend/        # Backend (Symfony + Docker + PostgreSQL)
│── frontend/       # Frontend (Angular)
│── doc/            # Documentación del proyecto
│── .gitignore      # Archivos a ignorar en Git
│── LICENSE         # Licencia del proyecto
│── README.md       # Este archivo 📖
```

---

## ⚡ Notas Adicionales
- Asegúrate de tener **Docker, Node.js y Angular CLI** instalados.
- Si usas WSL2 en Windows, verifica que Docker tenga acceso a los archivos.
- Para detener los contenedores Docker:
  ```bash
  docker-compose down
  ```

---

¡Listo! Ahora puedes disfrutar de **Sabor a España** 🛒🇪🇸


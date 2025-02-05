# ❤️💛❤️**Sabor a España**❤️💛❤️

## Descripción ✨
Bienvenido a **Sabor a España eCommerce**, un proyecto de comercio electrónico que te permite explorar los productos más típicos de España 🇪🇸. Desde el delicioso jamón ibérico 🥩, el aceite de oliva virgen extra 🧴, hasta el exquisito vino 🍷, aquí encontrarás todo lo que necesitas para disfrutar de lo mejor de nuestra tierra. Los usuarios pueden agregar estos productos al carrito y realizar sus compras fácilmente. Además, incluye una sección de administración para gestionar productos, usuarios y pedidos. ¡Disfruta de lo mejor de España en tu hogar!

## Tecnologías Utilizadas 💻


- **Angular**: ⚛️ Framework para el desarrollo del frontend. (Versión: 16.)
- **Symfony**: 🖥️ Framework PHP para la lógica del backend. (Versión: 7.2.3)
- **Bootstrap**: 🎨 Biblioteca para diseño responsivo y estilos. (Versión: 5.3)
- **PostgreSQL**: 🗃️ Base de datos relacional. (Versión: 15)
- **pgAdmin**: 🧑‍💻 Herramienta de administración para PostgreSQL. (Versión: 6)


## Características Principales ✨

### Usuario Final 🌐
- 🔑 **Registro e inicio de sesión**: Accede a tu cuenta para realizar compras fácilmente.
- 🔍 **Exploración de productos españoles**: Descubre una amplia variedad de productos típicos de España.
- 🛒 **Carrito de compras**: Agrega tus productos favoritos al carrito y procede a la compra.
- ⚙️ **Procesamiento de pedidos**: Realiza el pago de tus productos y recibe el envío directamente en tu hogar.

### Administración 💼
- 🎨 **Gestión de productos**: Agrega y organiza productos típicos de España en la tienda.
- 🔒 **Administración de usuarios**: Controla el acceso de los usuarios y sus datos.
- 📦 **Gestión de pedidos**: Administra los pedidos realizados por los usuarios y controla el inventario.

## Instalación del Entorno con Docker 🐳

Para configurar el entorno de desarrollo, puedes usar Docker. A continuación te detallo los pasos:

1. **Clona el repositorio** en tu máquina local.

2. **Ubica el archivo `docker-compose.yml`** en backend/docker-compose.yml

3. **Construye y levanta los contenedores**:

    ```bash
    docker-compose up --build
    ```

4. **Accede a las aplicaciones** a través de las siguientes URLs:

   - **Symfony (Backend)**:  
     URL: [http://localhost:8080](http://localhost:8080)  
     Esta es la URL de acceso al servidor Symfony. Asegúrate de que el contenedor del backend esté corriendo correctamente.

   - **pgAdmin (Gestión de base de datos)**:  
     URL: [http://localhost:8081](http://localhost:8081)  
     Accede a la interfaz de pgAdmin para gestionar la base de datos. Usa las credenciales configuradas en el archivo `docker-compose.yml`:  
     - **Email**: `admin@admin.com`
     - **Contraseña**: `admin`

5. **Acceso a los contenedores**:

   Si necesitas acceder a los contenedores para realizar alguna operación manual (como ejecutar comandos dentro del contenedor del backend), puedes hacerlo con:

   ```bash
   docker exec -it symfony_app bash
   ```

---

Recuerda que las URLs son:

- **Symfony**: [http://localhost:8080](http://localhost:8080)
- **pgAdmin**: [http://localhost:8081](http://localhost:8081)

---

# 🎓 PractiConnect - Plataforma de Prácticas Profesionales

**PractiConnect** es una plataforma web moderna que conecta estudiantes universitarios con empresas para facilitar la búsqueda y gestión de prácticas profesionales. Desarrollada con PHP, MySQL y tecnologías web modernas.

## 🌟 Características Principales

### 👨‍🎓 Para Estudiantes
- **Perfil profesional** completo con información académica
- **Búsqueda inteligente** de prácticas por ubicación, modalidad y sector
- **Sistema de aplicaciones** con seguimiento de estado
- **Dashboard personalizado** con estadísticas y gestión de aplicaciones
- **Mensajes de motivación** para destacar ante las empresas

### 🏢 Para Empresas
- **Panel de gestión** completo de ofertas de prácticas
- **Publicación de ofertas** con formularios intuitivos
- **Gestión de aplicaciones** con actualización de estados en tiempo real
- **Perfil empresarial** con información detallada
- **Estadísticas** de aplicaciones y ofertas activas

### 🔧 Características Técnicas
- **Autenticación segura** con sesiones PHP
- **Base de datos relacional** MySQL optimizada
- **Validación de formularios** en frontend y backend

## 🛠️ Tecnologías Utilizadas

- **Backend:** PHP 7.4+
- **Base de Datos:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **Framework CSS:** Bootstrap 5 (CDN)
- **Iconos:** Font Awesome 6
- **Servidor:** Apache (XAMPP/WAMP)


## 🚀 Instalación

### 1. Clonar o Descargar el Proyecto
```bash
# Si usas Git
git clone https://github.com/jorgeMDK/PractiConnect
cd practiconnect

# O descarga el ZIP y extráelo en tu carpeta de servidor web
```

### 2. Configurar el Servidor Web
```bash
# Para XAMPP
# Copia la carpeta 'practiconnect' a: C:\xampp\htdocs\

# Para WAMP
# Copia la carpeta 'practiconnect' a: C:\wamp64\www\
```

### 3. Configurar la Base de Datos

#### Opción A: Usar phpMyAdmin
1. Abre phpMyAdmin en tu navegador
2. Crea una nueva base de datos llamada `PractiConnect`
3. Importa el archivo `db/db.sql`

#### Opción B: Usar MySQL Command Line
```sql
mysql -u root -p
CREATE DATABASE PractiConnect;
USE PractiConnect;
SOURCE db/db.sql;
```

### 4. Configurar la Conexión a la Base de Datos
Edita los archivos PHP si es necesario:
- `login.php`
- `registro.php`
- `dashboard_estudiante.php`
- `dashboard_empresa.php`
- `nueva_oferta.php`
- `aplicar.php`

```php
// Configuración por defecto (XAMPP)
$host = 'localhost';
$dbname = 'PractiConnect';
$username = 'root';
$password = '';
```

### 5. Iniciar el Servidor
```bash
# Para XAMPP
# Inicia Apache y MySQL desde el panel de control

# Para WAMP
# Inicia WAMP y asegúrate de que Apache y MySQL estén corriendo
```

### 6. Acceder a la Aplicación
Abre tu navegador y ve a:
```
http://localhost/practiconnect/
```

## 📊 Estructura de la Base de Datos

### Tablas Principales

#### `usuarios`
- Tabla base para todos los usuarios (estudiantes, empresas, admins)
- Campos: id, nombre, correo, contraseña, tipo, fecha_creacion

#### `estudiantes`
- Información específica de estudiantes
- Campos: id, universidad, carrera, semestre, cv_url

#### `empresas`
- Información específica de empresas
- Campos: id, nombre_empresa, descripcion, sitio_web, telefono_contacto, direccion

#### `ofertas_practica`
- Ofertas de prácticas publicadas por empresas
- Campos: id, empresa_id, titulo, descripcion, requisitos, modalidad, ubicacion, fechas

#### `postulaciones`
- Aplicaciones de estudiantes a ofertas
- Campos: id, estudiante_id, oferta_id, fecha_postulacion, estado, mensaje

#### `mensajes`
- Sistema de mensajería entre usuarios
- Campos: id, remitente_id, destinatario_id, contenido, fecha_envio

## 👥 Usuarios de Prueba

### Estudiante
- **Email:** ana.perez@uni.edu
- **Contraseña:** 1234
- **Tipo:** estudiante

### Empresa
- **Email:** contacto@techsolutions.com
- **Contraseña:** empresa123
- **Tipo:** empresa

### Administrador
- **Email:** admin@plataforma.com
- **Contraseña:** admin123
- **Tipo:** admin

## 📁 Estructura de Archivos

```
practiconnect/
├── index.html              # Página principal
├── styles.css              # Estilos principales
├── login.php               # Sistema de autenticación
├── registro.php            # Registro de usuarios
├── logout.php              # Cierre de sesión
├── dashboard_estudiante.php # Panel de estudiante
├── dashboard_empresa.php   # Panel de empresa
├── nueva_oferta.php        # Crear oferta de práctica
├── aplicar.php             # Aplicar a práctica
├── db/
│   └── db.sql              # Estructura de base de datos
└── README.md               # Este archivo
```

## 🔐 Seguridad

### Medidas Implementadas
- **Validación de sesiones** en todas las páginas protegidas
- **Preparación de consultas** para prevenir SQL Injection
- **Escape de datos** para prevenir XSS
- **Validación de formularios** en frontend y backend
- **Control de acceso** basado en tipo de usuario

## 👨‍💻 Autor

**Jorge Medel**
- GitHub: [@jorgeMDK](https://github.com/jorgeMDK)
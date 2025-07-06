-- Crear base de datos
CREATE DATABASE IF NOT EXISTS PractiConnect;
USE PractiConnect;

-- Tabla de usuarios (base para estudiantes, empresas y admins)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    correo VARCHAR(100) UNIQUE,
    contraseña VARCHAR(255),
    tipo ENUM('estudiante', 'empresa', 'admin') NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Información específica de estudiantes
CREATE TABLE estudiantes (
    id INT PRIMARY KEY,
    universidad VARCHAR(100),
    carrera VARCHAR(100),
    semestre INT,
    cv_url VARCHAR(255),
    FOREIGN KEY (id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Información específica de empresas
CREATE TABLE empresas (
    id INT PRIMARY KEY,
    nombre_empresa VARCHAR(100),
    descripcion TEXT,
    sitio_web VARCHAR(100),
    telefono_contacto VARCHAR(20),
    direccion TEXT,
    FOREIGN KEY (id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Ofertas de prácticas profesionales
CREATE TABLE ofertas_practica (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT,
    titulo VARCHAR(100),
    descripcion TEXT,
    requisitos TEXT,
    modalidad ENUM('presencial', 'remoto', 'híbrido'),
    ubicacion VARCHAR(100),
    fecha_inicio DATE,
    fecha_fin DATE,
    fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);

-- Postulaciones de estudiantes a prácticas
CREATE TABLE postulaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT,
    oferta_id INT,
    fecha_postulacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'aceptado', 'rechazado') DEFAULT 'pendiente',
    mensaje TEXT,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (oferta_id) REFERENCES ofertas_practica(id) ON DELETE CASCADE
);

-- Mensajes entre usuarios (opcional)
CREATE TABLE mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    remitente_id INT,
    destinatario_id INT,
    contenido TEXT,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (remitente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (destinatario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================
-- 🔽 DATOS DE EJEMPLO
-- ============================

-- Usuarios (2 estudiantes, 1 empresa, 1 admin)
INSERT INTO usuarios (nombre, correo, contraseña, tipo) VALUES
('Ana Pérez', 'ana.perez@uni.edu', '1234', 'estudiante'),
('Luis Gómez', 'luis.gomez@uni.edu', '1234', 'estudiante'),
('Tech Solutions', 'contacto@techsolutions.com', 'empresa123', 'empresa'),
('Admin', 'admin@plataforma.com', 'admin123', 'admin');

-- Estudiantes
INSERT INTO estudiantes (id, universidad, carrera, semestre, cv_url) VALUES
(1, 'Universidad Nacional', 'Ingeniería en Sistemas', 6, 'https://miportafolio.com/ana-perez'),
(2, 'Universidad Autónoma', 'Tecnologías de la Información', 7, 'https://miportafolio.com/luis-gomez');

-- Empresa
INSERT INTO empresas (id, nombre_empresa, descripcion, sitio_web, telefono_contacto, direccion) VALUES
(3, 'Tech Solutions', 'Empresa de desarrollo de software enfocada en soluciones empresariales.', 'https://techsolutions.com', '555-123-4567', 'Calle Tecnológica #42, Ciudad Innovadora');

-- Oferta de práctica
INSERT INTO ofertas_practica (empresa_id, titulo, descripcion, requisitos, modalidad, ubicacion, fecha_inicio, fecha_fin) VALUES
(3, 'Practicante Backend PHP', 'Apoyo en desarrollo de sistemas internos usando PHP y MySQL.', 'Conocimientos básicos de PHP, SQL y Git.', 'remoto', 'Remoto', '2025-08-01', '2025-12-15');

-- Postulación
INSERT INTO postulaciones (estudiante_id, oferta_id, mensaje) VALUES
(1, 1, 'Estoy interesada en la posición. Tengo experiencia en PHP y desarrollo web.');

-- Mensaje
INSERT INTO mensajes (remitente_id, destinatario_id, contenido) VALUES
(1, 3, 'Hola, me gustaría saber más sobre la vacante de Backend PHP.');

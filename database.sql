-- PractiConnect Database Structure
-- Create database
CREATE DATABASE IF NOT EXISTS practiconnect;
USE practiconnect;

-- Students table
CREATE TABLE estudiantes (
    id_estudiante INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    universidad VARCHAR(200) NOT NULL,
    carrera VARCHAR(150) NOT NULL,
    semestre INT NOT NULL,
    telefono VARCHAR(20),
    linkedin VARCHAR(255),
    github VARCHAR(255),
    descripcion TEXT,
    habilidades TEXT,
    experiencia TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estado ENUM('activo', 'inactivo', 'suspendido') DEFAULT 'activo'
);

-- Companies table
CREATE TABLE empresas (
    id_empresa INT AUTO_INCREMENT PRIMARY KEY,
    nombre_empresa VARCHAR(200) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    sector VARCHAR(100) NOT NULL,
    descripcion TEXT,
    telefono VARCHAR(20),
    direccion TEXT,
    website VARCHAR(255),
    linkedin VARCHAR(255),
    tamano_empresa ENUM('pequeña', 'mediana', 'grande', 'multinacional'),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estado ENUM('activa', 'inactiva', 'suspendida') DEFAULT 'activa',
    verificada BOOLEAN DEFAULT FALSE
);

-- Internships table
CREATE TABLE practicas (
    id_practica INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    requisitos TEXT,
    responsabilidades TEXT,
    beneficios TEXT,
    duracion VARCHAR(100),
    modalidad ENUM('presencial', 'remoto', 'hibrido') NOT NULL,
    ubicacion VARCHAR(200),
    salario DECIMAL(10,2),
    fecha_inicio DATE,
    fecha_fin DATE,
    fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('activa', 'cerrada', 'en_revision') DEFAULT 'activa',
    FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa) ON DELETE CASCADE
);

-- Applications table
CREATE TABLE aplicaciones (
    id_aplicacion INT AUTO_INCREMENT PRIMARY KEY,
    id_practica INT NOT NULL,
    id_estudiante INT NOT NULL,
    fecha_aplicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'revisada', 'aceptada', 'rechazada', 'entrevista') DEFAULT 'pendiente',
    cv_url VARCHAR(255),
    carta_motivacion TEXT,
    notas_empresa TEXT,
    fecha_entrevista DATETIME,
    FOREIGN KEY (id_practica) REFERENCES practicas(id_practica) ON DELETE CASCADE,
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id_estudiante) ON DELETE CASCADE,
    UNIQUE KEY unique_application (id_practica, id_estudiante)
);

-- Messages table
CREATE TABLE mensajes (
    id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
    id_remitente INT NOT NULL,
    id_destinatario INT NOT NULL,
    tipo_remitente ENUM('estudiante', 'empresa') NOT NULL,
    tipo_destinatario ENUM('estudiante', 'empresa') NOT NULL,
    asunto VARCHAR(200),
    mensaje TEXT NOT NULL,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    leido BOOLEAN DEFAULT FALSE
);

-- Reviews table
CREATE TABLE evaluaciones (
    id_evaluacion INT AUTO_INCREMENT PRIMARY KEY,
    id_practica INT NOT NULL,
    id_estudiante INT NOT NULL,
    id_empresa INT NOT NULL,
    calificacion INT NOT NULL CHECK (calificacion >= 1 AND calificacion <= 5),
    comentario TEXT,
    fecha_evaluacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_practica) REFERENCES practicas(id_practica) ON DELETE CASCADE,
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id_estudiante) ON DELETE CASCADE,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa) ON DELETE CASCADE
);

-- Skills table
CREATE TABLE habilidades (
    id_habilidad INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE NOT NULL,
    categoria VARCHAR(100)
);

-- Student skills relationship
CREATE TABLE estudiante_habilidades (
    id_estudiante INT NOT NULL,
    id_habilidad INT NOT NULL,
    nivel ENUM('básico', 'intermedio', 'avanzado', 'experto') DEFAULT 'básico',
    PRIMARY KEY (id_estudiante, id_habilidad),
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id_estudiante) ON DELETE CASCADE,
    FOREIGN KEY (id_habilidad) REFERENCES habilidades(id_habilidad) ON DELETE CASCADE
);

-- Insert sample data
INSERT INTO habilidades (nombre, categoria) VALUES
('JavaScript', 'Programación'),
('Python', 'Programación'),
('Java', 'Programación'),
('PHP', 'Programación'),
('HTML/CSS', 'Desarrollo Web'),
('React', 'Frontend'),
('Node.js', 'Backend'),
('MySQL', 'Base de Datos'),
('MongoDB', 'Base de Datos'),
('Git', 'Herramientas'),
('Docker', 'DevOps'),
('AWS', 'Cloud'),
('Marketing Digital', 'Marketing'),
('SEO', 'Marketing'),
('Análisis de Datos', 'Analytics'),
('Excel', 'Herramientas'),
('PowerPoint', 'Herramientas'),
('Word', 'Herramientas'),
('Photoshop', 'Diseño'),
('Illustrator', 'Diseño');

-- Insert sample companies
INSERT INTO empresas (nombre_empresa, email, password, sector, descripcion, telefono, tamano_empresa) VALUES
('TechCorp Solutions', 'info@techcorp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tecnologia', 'Empresa líder en desarrollo de software y soluciones tecnológicas', '+52 55 1234 5678', 'mediana'),
('InnovateLab', 'contacto@innovatelab.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tecnologia', 'Startup innovadora en inteligencia artificial y machine learning', '+52 55 9876 5432', 'pequeña'),
('Global Finance', 'hr@globalfinance.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'finanzas', 'Empresa multinacional de servicios financieros', '+52 55 1111 2222', 'grande');

-- Insert sample students
INSERT INTO estudiantes (nombre, apellido, email, password, universidad, carrera, semestre) VALUES
('María', 'González', 'maria.gonzalez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Universidad Nacional Autónoma de México', 'Ingeniería en Computación', 6),
('Carlos', 'Rodríguez', 'carlos.rodriguez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Instituto Politécnico Nacional', 'Ingeniería en Sistemas Computacionales', 8),
('Ana', 'Martínez', 'ana.martinez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Universidad Autónoma Metropolitana', 'Licenciatura en Informática', 4);

-- Insert sample internships
INSERT INTO practicas (id_empresa, titulo, descripcion, requisitos, responsabilidades, beneficios, duracion, modalidad, ubicacion, salario) VALUES
(1, 'Desarrollador Frontend Junior', 'Buscamos un desarrollador frontend apasionado por crear experiencias de usuario excepcionales', 'Conocimientos en HTML, CSS, JavaScript. React es un plus', 'Desarrollar interfaces de usuario, colaborar con el equipo de diseño', 'Capacitación continua, ambiente dinámico, posibilidad de contrato', '6 meses', 'hibrido', 'Ciudad de México', 8000.00),
(2, 'Practicante en Machine Learning', 'Oportunidad para trabajar en proyectos de IA y aprendizaje automático', 'Conocimientos básicos en Python, matemáticas, estadística', 'Implementar algoritmos de ML, analizar datos, documentar resultados', 'Mentoría personalizada, proyectos reales, networking', '4 meses', 'remoto', 'Remoto', 6000.00),
(3, 'Analista de Datos Financieros', 'Analizar datos financieros y crear reportes para la toma de decisiones', 'Excel avanzado, conocimientos básicos de finanzas', 'Crear dashboards, analizar tendencias, preparar reportes', 'Experiencia en sector financiero, certificaciones', '3 meses', 'presencial', 'Monterrey', 10000.00);

-- Note: The password hash used above is for 'password' - in production, use proper password hashing 
<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'PractiConnect';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$error = '';
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_type = $_POST['user_type'];
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate form data
    if (empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Por favor, completa todos los campos.';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($password) < 4) {
        $error = 'La contraseña debe tener al menos 4 caracteres.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT correo FROM usuarios WHERE correo = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Este email ya está registrado.';
        } else {
            try {
                // Start transaction
                $pdo->beginTransaction();
                
                if ($user_type == 'estudiante') {
                    // Insert student data
                    $nombre = trim($_POST['nombre']);
                    $apellido = trim($_POST['apellido']);
                    $universidad = trim($_POST['universidad']);
                    $carrera = trim($_POST['carrera']);
                    $semestre = $_POST['semestre'];
                    
                    if (empty($nombre) || empty($apellido) || empty($universidad) || empty($carrera)) {
                        $error = 'Por favor, completa todos los campos del estudiante.';
                    } else {
                        // Insert into usuarios table
                        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, contraseña, tipo) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$nombre . ' ' . $apellido, $email, $password, 'estudiante']);
                        $user_id = $pdo->lastInsertId();
                        
                        // Insert into estudiantes table
                        $stmt = $pdo->prepare("INSERT INTO estudiantes (id, universidad, carrera, semestre) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$user_id, $universidad, $carrera, $semestre]);
                        
                        $pdo->commit();
                        $success = '¡Registro exitoso! Ya puedes iniciar sesión.';
                    }
                } else {
                    // Insert company data
                    $nombre_empresa = trim($_POST['nombre_empresa']);
                    $sector = trim($_POST['sector']);
                    $descripcion = trim($_POST['descripcion']);
                    $telefono = trim($_POST['telefono']);
                    
                    if (empty($nombre_empresa) || empty($sector)) {
                        $error = 'Por favor, completa todos los campos de la empresa.';
                    } else {
                        // Insert into usuarios table
                        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, contraseña, tipo) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$nombre_empresa, $email, $password, 'empresa']);
                        $user_id = $pdo->lastInsertId();
                        
                        // Insert into empresas table
                        $stmt = $pdo->prepare("INSERT INTO empresas (id, nombre_empresa, descripcion, telefono_contacto) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$user_id, $nombre_empresa, $descripcion, $telefono]);
                        
                        $pdo->commit();
                        $success = '¡Registro exitoso! Ya puedes iniciar sesión.';
                    }
                }
            } catch(PDOException $e) {
                $pdo->rollback();
                $error = 'Error al registrar: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - PractiConnect</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .register-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #4fc3f7 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-card {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 600px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-header i {
            font-size: 3rem;
            color: #1e3c72;
            margin-bottom: 1rem;
        }
        
        .register-header h1 {
            color: #1e3c72;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .register-header p {
            color: #666;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4fc3f7;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn-register {
            width: 100%;
            background: linear-gradient(135deg, #1e3c72, #4fc3f7);
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(30, 60, 114, 0.3);
        }
        
        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border-left: 4px solid #c62828;
        }
        
        .success-message {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border-left: 4px solid #2e7d32;
        }
        
        .register-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e1e5e9;
        }
        
        .register-footer a {
            color: #4fc3f7;
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-footer a:hover {
            text-decoration: underline;
        }
        
        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .back-home:hover {
            color: #4fc3f7;
        }
        
        .user-type-toggle {
            display: flex;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 0.5rem;
            margin-bottom: 2rem;
        }
        
        .user-type-toggle label {
            flex: 1;
            text-align: center;
            padding: 1rem;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .user-type-toggle input[type="radio"] {
            display: none;
        }
        
        .user-type-toggle input[type="radio"]:checked + label {
            background: #4fc3f7;
            color: white;
        }
        
        .student-fields, .company-fields {
            display: none;
        }
        
        .student-fields.active, .company-fields.active {
            display: block;
        }
        
        .user-type-toggle input[type="radio"]:checked + label {
            background: #4fc3f7;
            color: white;
        }
    </style>
</head>
<body>
    <a href="index.html" class="back-home">
        <i class="fas fa-arrow-left"></i>
        Volver al Inicio
    </a>
    
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <i class="fas fa-user-plus"></i>
                <h1>Crear Cuenta</h1>
                <p>Únete a PractiConnect y conecta tu futuro profesional</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="user-type-toggle">
                    <input type="radio" name="user_type" id="student" value="estudiante" checked>
                    <label for="student">
                        <i class="fas fa-user-graduate"></i>
                        Estudiante
                    </label>
                    <input type="radio" name="user_type" id="company" value="empresa">
                    <label for="company">
                        <i class="fas fa-building"></i>
                        Empresa
                    </label>
                </div>
                
                <!-- Student Fields -->
                <div class="student-fields active" id="student-fields">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre</label>
                            <input type="text" name="nombre" id="nombre" required placeholder="Tu nombre">
                        </div>
                        <div class="form-group">
                            <label for="apellido">Apellido</label>
                            <input type="text" name="apellido" id="apellido" required placeholder="Tu apellido">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="universidad">Universidad</label>
                            <input type="text" name="universidad" id="universidad" required placeholder="Nombre de tu universidad">
                        </div>
                        <div class="form-group">
                            <label for="carrera">Carrera</label>
                            <input type="text" name="carrera" id="carrera" required placeholder="Tu carrera">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="semestre">Semestre</label>
                        <select name="semestre" id="semestre" required>
                            <option value="">Selecciona tu semestre</option>
                            <option value="1">1er Semestre</option>
                            <option value="2">2do Semestre</option>
                            <option value="3">3er Semestre</option>
                            <option value="4">4to Semestre</option>
                            <option value="5">5to Semestre</option>
                            <option value="6">6to Semestre</option>
                            <option value="7">7mo Semestre</option>
                            <option value="8">8vo Semestre</option>
                            <option value="9">9no Semestre</option>
                            <option value="10">10mo Semestre</option>
                            <option value="egresado">Egresado</option>
                        </select>
                    </div>
                </div>
                
                <!-- Company Fields -->
                <div class="company-fields" id="company-fields">
                    <div class="form-group">
                        <label for="nombre_empresa">Nombre de la Empresa</label>
                        <input type="text" name="nombre_empresa" id="nombre_empresa" placeholder="Nombre de tu empresa">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="sector">Sector</label>
                            <select name="sector" id="sector">
                                <option value="">Selecciona el sector</option>
                                <option value="tecnologia">Tecnología</option>
                                <option value="finanzas">Finanzas</option>
                                <option value="salud">Salud</option>
                                <option value="educacion">Educación</option>
                                <option value="manufactura">Manufactura</option>
                                <option value="servicios">Servicios</option>
                                <option value="comercio">Comercio</option>
                                <option value="otros">Otros</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="tel" name="telefono" id="telefono" placeholder="Teléfono de contacto">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción de la Empresa</label>
                        <textarea name="descripcion" id="descripcion" placeholder="Describe brevemente tu empresa y las oportunidades que ofreces"></textarea>
                    </div>
                </div>
                
                <!-- Common Fields -->
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required placeholder="tu@email.com">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" name="password" id="password" required placeholder="Mínimo 6 caracteres">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña</label>
                        <input type="password" name="confirm_password" id="confirm_password" required placeholder="Repite tu contraseña">
                    </div>
                </div>
                
                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i>
                    Crear Cuenta
                </button>
            </form>
            
            <div class="register-footer">
                <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle between student and company fields
        document.querySelectorAll('input[name="user_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const studentFields = document.getElementById('student-fields');
                const companyFields = document.getElementById('company-fields');
                
                if (this.value === 'student') {
                    studentFields.classList.add('active');
                    companyFields.classList.remove('active');
                } else {
                    studentFields.classList.remove('active');
                    companyFields.classList.add('active');
                }
            });
        });
    </script>
</body>
</html> 
<?php
session_start();

// Check if user is logged in and is a company
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'empresa') {
    header('Location: login.php');
    exit();
}

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $requisitos = trim($_POST['requisitos']);
    $modalidad = $_POST['modalidad'];
    $ubicacion = trim($_POST['ubicacion']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    
    // Validate form data
    if (empty($titulo) || empty($descripcion) || empty($modalidad) || empty($ubicacion) || empty($fecha_inicio) || empty($fecha_fin)) {
        $error = 'Por favor, completa todos los campos obligatorios.';
    } elseif (strtotime($fecha_inicio) >= strtotime($fecha_fin)) {
        $error = 'La fecha de inicio debe ser anterior a la fecha de fin.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO ofertas_practica (empresa_id, titulo, descripcion, requisitos, modalidad, ubicacion, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $titulo, $descripcion, $requisitos, $modalidad, $ubicacion, $fecha_inicio, $fecha_fin]);
            $success = '¡Oferta publicada exitosamente!';
        } catch(PDOException $e) {
            $error = 'Error al publicar la oferta: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Oferta - PractiConnect</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .offer-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #4fc3f7 100%);
            padding: 2rem 20px;
        }
        
        .offer-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .back-btn {
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            font-weight: 500;
        }
        
        .back-btn:hover {
            color: #4fc3f7;
        }
        
        .offer-form-card {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .form-header i {
            font-size: 3rem;
            color: #1e3c72;
            margin-bottom: 1rem;
        }
        
        .form-header h1 {
            color: #1e3c72;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .form-header p {
            color: #666;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
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
            min-height: 120px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #1e3c72, #4fc3f7);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-submit:hover {
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
        
        .required {
            color: #c62828;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="offer-container">
        <div class="offer-content">
            <a href="dashboard_empresa.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Volver al Dashboard
            </a>
            
            <div class="offer-form-card">
                <div class="form-header">
                    <i class="fas fa-plus-circle"></i>
                    <h1>Publicar Nueva Oferta</h1>
                    <p>Completa los detalles de tu oferta de práctica para atraer talento universitario</p>
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
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="titulo">Título de la Oferta <span class="required">*</span></label>
                            <input type="text" name="titulo" id="titulo" required 
                                   placeholder="Ej: Practicante Backend PHP" 
                                   value="<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="modalidad">Modalidad <span class="required">*</span></label>
                            <select name="modalidad" id="modalidad" required>
                                <option value="">Selecciona la modalidad</option>
                                <option value="presencial" <?php echo (isset($_POST['modalidad']) && $_POST['modalidad'] == 'presencial') ? 'selected' : ''; ?>>Presencial</option>
                                <option value="remoto" <?php echo (isset($_POST['modalidad']) && $_POST['modalidad'] == 'remoto') ? 'selected' : ''; ?>>Remoto</option>
                                <option value="híbrido" <?php echo (isset($_POST['modalidad']) && $_POST['modalidad'] == 'híbrido') ? 'selected' : ''; ?>>Híbrido</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="ubicacion">Ubicación <span class="required">*</span></label>
                            <input type="text" name="ubicacion" id="ubicacion" required 
                                   placeholder="Ej: Ciudad de México, Remoto, etc." 
                                   value="<?php echo isset($_POST['ubicacion']) ? htmlspecialchars($_POST['ubicacion']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_inicio">Fecha de Inicio <span class="required">*</span></label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" required 
                                   value="<?php echo isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_fin">Fecha de Fin <span class="required">*</span></label>
                            <input type="date" name="fecha_fin" id="fecha_fin" required 
                                   value="<?php echo isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : ''; ?>">
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="descripcion">Descripción de la Práctica <span class="required">*</span></label>
                            <textarea name="descripcion" id="descripcion" required 
                                      placeholder="Describe detalladamente las responsabilidades, proyectos y aprendizaje que ofreces..."><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="requisitos">Requisitos</label>
                            <textarea name="requisitos" id="requisitos" 
                                      placeholder="Especifica los conocimientos, habilidades y requisitos académicos necesarios..."><?php echo isset($_POST['requisitos']) ? htmlspecialchars($_POST['requisitos']) : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i>
                        Publicar Oferta
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Set minimum date for date inputs
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('fecha_inicio').min = today;
        document.getElementById('fecha_fin').min = today;
        
        // Update end date minimum when start date changes
        document.getElementById('fecha_inicio').addEventListener('change', function() {
            document.getElementById('fecha_fin').min = this.value;
        });
    </script>
</body>
</html> 
<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'estudiante') {
    header('Location: login.php');
    exit();
}

// Check if internship ID is provided
if (!isset($_GET['id'])) {
    header('Location: dashboard_estudiante.php');
    exit();
}

$internship_id = $_GET['id'];

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

// Get internship details
$stmt = $pdo->prepare("
    SELECT op.*, em.nombre_empresa, em.descripcion as empresa_descripcion 
    FROM ofertas_practica op 
    JOIN empresas em ON op.empresa_id = em.id 
    WHERE op.id = ?
");
$stmt->execute([$internship_id]);
$internship = $stmt->fetch();

if (!$internship) {
    header('Location: dashboard_estudiante.php');
    exit();
}

// Check if already applied
$stmt = $pdo->prepare("SELECT * FROM postulaciones WHERE estudiante_id = ? AND oferta_id = ?");
$stmt->execute([$_SESSION['user_id'], $internship_id]);
$existing_application = $stmt->fetch();

if ($existing_application) {
    $error = 'Ya has aplicado a esta práctica.';
}

// Handle application form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$existing_application) {
    $mensaje = trim($_POST['mensaje']);
    
    if (empty($mensaje)) {
        $error = 'Por favor, escribe un mensaje de motivación.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO postulaciones (estudiante_id, oferta_id, mensaje) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $internship_id, $mensaje]);
            $success = '¡Aplicación enviada exitosamente!';
        } catch(PDOException $e) {
            $error = 'Error al enviar la aplicación: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplicar a Práctica - PractiConnect</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .apply-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #4fc3f7 100%);
            padding: 2rem 20px;
        }
        
        .apply-content {
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
        
        .internship-details-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .internship-header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .internship-title {
            color: #1e3c72;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .company-name {
            color: #4fc3f7;
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .internship-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
        }
        
        .info-item i {
            color: #4fc3f7;
            width: 20px;
        }
        
        .description-section {
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            color: #1e3c72;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .apply-form-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            resize: vertical;
            min-height: 150px;
            transition: border-color 0.3s ease;
        }
        
        .form-group textarea:focus {
            outline: none;
            border-color: #4fc3f7;
        }
        
        .btn-apply {
            background: linear-gradient(135deg, #1e3c72, #4fc3f7);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-apply:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(30, 60, 114, 0.3);
        }
        
        .btn-apply:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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
        
        .already-applied {
            background: #fff3cd;
            color: #856404;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border-left: 4px solid #856404;
        }
    </style>
</head>
<body>
    <div class="apply-container">
        <div class="apply-content">
            <a href="dashboard_estudiante.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Volver al Dashboard
            </a>
            
            <div class="internship-details-card">
                <div class="internship-header">
                    <h1 class="internship-title"><?php echo htmlspecialchars($internship['titulo']); ?></h1>
                    <div class="company-name"><?php echo htmlspecialchars($internship['nombre_empresa']); ?></div>
                </div>
                
                <div class="internship-info">
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo htmlspecialchars($internship['ubicacion']); ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo date('d/m/Y', strtotime($internship['fecha_inicio'])); ?> - <?php echo date('d/m/Y', strtotime($internship['fecha_fin'])); ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-laptop-house"></i>
                        <span><?php echo ucfirst($internship['modalidad']); ?></span>
                    </div>
                </div>
                
                <div class="description-section">
                    <h3 class="section-title">Descripción de la Práctica</h3>
                    <p><?php echo nl2br(htmlspecialchars($internship['descripcion'])); ?></p>
                </div>
                
                <?php if ($internship['requisitos']): ?>
                <div class="description-section">
                    <h3 class="section-title">Requisitos</h3>
                    <p><?php echo nl2br(htmlspecialchars($internship['requisitos'])); ?></p>
                </div>
                <?php endif; ?>
                
                <div class="description-section">
                    <h3 class="section-title">Sobre la Empresa</h3>
                    <p><?php echo htmlspecialchars($internship['empresa_descripcion']); ?></p>
                </div>
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
            
            <?php if ($existing_application): ?>
                <div class="already-applied">
                    <i class="fas fa-info-circle"></i>
                    Ya has aplicado a esta práctica. Puedes ver el estado de tu aplicación en tu dashboard.
                </div>
            <?php else: ?>
                <div class="apply-form-card">
                    <h2 class="section-title">Aplicar a esta Práctica</h2>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="mensaje">Mensaje de Motivación</label>
                            <textarea name="mensaje" id="mensaje" placeholder="Explica por qué te interesa esta práctica y por qué serías un buen candidato..." required><?php echo isset($_POST['mensaje']) ? htmlspecialchars($_POST['mensaje']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn-apply">
                            <i class="fas fa-paper-plane"></i>
                            Enviar Aplicación
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 
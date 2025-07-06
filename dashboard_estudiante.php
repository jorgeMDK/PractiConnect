<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'estudiante') {
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

// Get student data
$stmt = $pdo->prepare("
    SELECT u.*, e.universidad, e.carrera, e.semestre 
    FROM usuarios u 
    JOIN estudiantes e ON u.id = e.id 
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

// Get available internships
$stmt = $pdo->prepare("
    SELECT op.*, em.nombre_empresa 
    FROM ofertas_practica op 
    JOIN empresas em ON op.empresa_id = em.id 
    ORDER BY op.fecha_publicacion DESC
");
$stmt->execute();
$internships = $stmt->fetchAll();

// Get student's applications
$stmt = $pdo->prepare("
    SELECT p.*, op.titulo, em.nombre_empresa 
    FROM postulaciones p 
    JOIN ofertas_practica op ON p.oferta_id = op.id 
    JOIN empresas em ON op.empresa_id = em.id 
    WHERE p.estudiante_id = ? 
    ORDER BY p.fecha_postulacion DESC
");
$stmt->execute([$_SESSION['user_id']]);
$applications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Estudiante - PractiConnect</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-container {
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 1rem 0;
        }
        
        .dashboard-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: #4fc3f7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .logout-btn {
            background: #4fc3f7;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .dashboard-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 20px;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }
        
        .sidebar {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .main-content {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: #1e3c72;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #1e3c72, #4fc3f7);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .internship-card {
            border: 1px solid #e1e5e9;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .internship-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .internship-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        
        .internship-title {
            color: #1e3c72;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .company-name {
            color: #4fc3f7;
            font-weight: 500;
        }
        
        .internship-details {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            font-size: 0.9rem;
        }
        
        .btn-apply {
            background: #4fc3f7;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-apply:hover {
            background: #29b6f6;
        }
        
        .application-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-pendiente { background: #fff3cd; color: #856404; }
        .status-revisada { background: #d1ecf1; color: #0c5460; }
        .status-aceptada { background: #d4edda; color: #155724; }
        .status-rechazada { background: #f8d7da; color: #721c24; }
        .status-entrevista { background: #e2e3e5; color: #383d41; }
        
        .profile-section {
            margin-bottom: 2rem;
        }
        
        .profile-info {
            margin-bottom: 1rem;
        }
        
        .profile-info label {
            font-weight: 600;
            color: #333;
            display: block;
            margin-bottom: 0.25rem;
        }
        
        .profile-info span {
            color: #666;
        }
        
        .tabs {
            display: flex;
            border-bottom: 2px solid #e1e5e9;
            margin-bottom: 2rem;
        }
        
        .tab {
            padding: 1rem 2rem;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .tab.active {
            border-bottom-color: #4fc3f7;
            color: #4fc3f7;
            font-weight: 600;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="dashboard-nav">
                                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($student['nombre'], 0, 1)); ?>
                        </div>
                        <div>
                            <div>Bienvenido, <?php echo htmlspecialchars($student['nombre']); ?></div>
                            <small><?php echo htmlspecialchars($student['carrera']); ?> - Semestre <?php echo $student['semestre']; ?></small>
                        </div>
                    </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Cerrar Sesión
                </a>
            </div>
        </header>
        
        <div class="dashboard-content">
            <div class="dashboard-grid">
                <aside class="sidebar">
                    <div class="profile-section">
                        <h3 class="section-title">
                            <i class="fas fa-user"></i>
                            Mi Perfil
                        </h3>
                        <div class="profile-info">
                            <label>Universidad:</label>
                            <span><?php echo htmlspecialchars($student['universidad']); ?></span>
                        </div>
                        <div class="profile-info">
                            <label>Carrera:</label>
                            <span><?php echo htmlspecialchars($student['carrera']); ?></span>
                        </div>
                        <div class="profile-info">
                            <label>Semestre:</label>
                            <span><?php echo $student['semestre']; ?></span>
                        </div>
                        <div class="profile-info">
                            <label>Email:</label>
                            <span><?php echo htmlspecialchars($student['correo']); ?></span>
                        </div>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($applications); ?></div>
                            <div>Aplicaciones</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count(array_filter($applications, function($app) { return $app['estado'] === 'aceptado'; })); ?></div>
                            <div>Aceptadas</div>
                        </div>
                    </div>
                </aside>
                
                <main class="main-content">
                    <div class="tabs">
                        <div class="tab active" onclick="showTab('internships')">
                            <i class="fas fa-briefcase"></i>
                            Prácticas Disponibles
                        </div>
                        <div class="tab" onclick="showTab('applications')">
                            <i class="fas fa-clipboard-list"></i>
                            Mis Aplicaciones
                        </div>
                    </div>
                    
                    <div id="internships" class="tab-content active">
                        <h2 class="section-title">
                            <i class="fas fa-search"></i>
                            Prácticas Disponibles
                        </h2>
                        
                        <?php if (empty($internships)): ?>
                            <p>No hay prácticas disponibles en este momento.</p>
                        <?php else: ?>
                            <?php foreach ($internships as $internship): ?>
                                <div class="internship-card">
                                    <div class="internship-header">
                                        <div>
                                            <div class="internship-title"><?php echo htmlspecialchars($internship['titulo']); ?></div>
                                            <div class="company-name"><?php echo htmlspecialchars($internship['nombre_empresa']); ?></div>
                                        </div>
                                        <a href="aplicar.php?id=<?php echo $internship['id']; ?>" class="btn-apply">
                                            Aplicar
                                        </a>
                                    </div>
                                    
                                    <p><?php echo htmlspecialchars(substr($internship['descripcion'], 0, 150)) . '...'; ?></p>
                                    
                                    <div class="internship-details">
                                        <div class="detail-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?php echo htmlspecialchars($internship['ubicacion']); ?>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('d/m/Y', strtotime($internship['fecha_inicio'])); ?> - <?php echo date('d/m/Y', strtotime($internship['fecha_fin'])); ?>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-laptop-house"></i>
                                            <?php echo ucfirst($internship['modalidad']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div id="applications" class="tab-content">
                        <h2 class="section-title">
                            <i class="fas fa-clipboard-check"></i>
                            Mis Aplicaciones
                        </h2>
                        
                        <?php if (empty($applications)): ?>
                            <p>No has aplicado a ninguna práctica aún.</p>
                        <?php else: ?>
                            <?php foreach ($applications as $application): ?>
                                <div class="internship-card">
                                    <div class="internship-header">
                                        <div>
                                            <div class="internship-title"><?php echo htmlspecialchars($application['titulo']); ?></div>
                                            <div class="company-name"><?php echo htmlspecialchars($application['nombre_empresa']); ?></div>
                                        </div>
                                        <span class="application-status status-<?php echo $application['estado']; ?>">
                                            <?php echo ucfirst($application['estado']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="internship-details">
                                        <div class="detail-item">
                                            <i class="fas fa-calendar"></i>
                                            Aplicado: <?php echo date('d/m/Y', strtotime($application['fecha_postulacion'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </main>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }
    </script>
</body>
</html> 
<?php
$host = '127.0.0.1';
$port = '5222';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;port=$port", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create DB if it doesn't exist
    $conn->exec("CREATE DATABASE IF NOT EXISTS adet_db");
    $conn->exec("USE adet_db");
    
    // Check if we need to migrate (major schema change)
    // If the customer table exists but lacks address_id, we'll recreate the DB
    $migrate = false;
    $checkCustomer = $conn->query("SHOW TABLES LIKE 'customer'");
    if($checkCustomer->rowCount() > 0) {
        $cols = $conn->query("SHOW COLUMNS FROM customer")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('address_id', $cols)) {
            $migrate = true;
        }
    } else {
        $migrate = true; // Table doesn't exist, need to run setup
    }

    if($migrate) {
        $sql = file_get_contents(__DIR__ . '/database/adet_db.sql');
        if ($sql) {
            // Disable foreign key checks for clean drop/recreate
            $conn->exec("SET FOREIGN_KEY_CHECKS = 0;");
            
            // Drop tables to apply new schema
            $conn->exec("DROP TABLE IF EXISTS admin_address, address, customer, admin;");
            
            // Re-run full SQL
            $conn->exec($sql);
            
            $conn->exec("SET FOREIGN_KEY_CHECKS = 1;");
        }
    }
} catch(PDOException $e) {
    // Silently continue
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Souveniria Portal - System Access Gateway</title>
  <style>
    :root {
      --primary: #2B3A1C; /* Dark olive green */
      --secondary: #8C7B65; /* Warm earth */
      --accent: #D4AF37; /* Bicol gold */
      --bg-color: #Fdfcf0; /* Cream background */
      --text-dark: #1A1A1A;
      --text-light: #666666;
      --white: #FFFFFF;
      --radius: 12px;
      --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      background-color: var(--bg-color);
      color: var(--text-dark);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .portal-header {
      text-align: center;
      margin-bottom: 3rem;
    }

    .portal-logo {
      font-size: 2.5rem;
      font-weight: 800;
      color: var(--primary);
      text-decoration: none;
      letter-spacing: -1px;
      display: block;
    }

    .portal-logo span {
      display: block;
      font-size: 0.9rem;
      font-weight: 500;
      color: var(--secondary);
      letter-spacing: 2px;
      margin-top: 0.5rem;
      text-transform: uppercase;
    }

    .portal-container {
      display: flex;
      gap: 2rem;
      max-width: 900px;
      width: 90%;
    }

    .portal-card {
      background: var(--white);
      flex: 1;
      border-radius: var(--radius);
      padding: 3rem 2rem;
      text-align: center;
      text-decoration: none;
      color: var(--text-dark);
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      border: 1px solid rgba(140, 123, 101, 0.1);
      transition: var(--transition);
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .portal-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(43, 58, 28, 0.1);
      border-color: var(--accent);
    }

    .card-icon {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: rgba(43, 58, 28, 0.05);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
      margin-bottom: 1.5rem;
      transition: var(--transition);
    }

    .portal-card:hover .card-icon {
      background: var(--primary);
      color: var(--white);
    }

    .card-title {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 1rem;
      color: var(--primary);
    }

    .card-desc {
      font-size: 0.95rem;
      color: var(--text-light);
      line-height: 1.6;
      margin-bottom: 2rem;
    }

    .card-btn {
      margin-top: auto;
      padding: 0.8rem 2rem;
      border-radius: 6px;
      font-weight: 600;
      font-size: 0.9rem;
      transition: var(--transition);
      width: 100%;
    }

    .btn-customer {
      background: var(--primary);
      color: var(--white);
      border: none;
    }

    .btn-admin {
      background: transparent;
      color: var(--primary);
      border: 2px solid var(--primary);
    }

    @media (max-width: 768px) {
      .portal-container {
        flex-direction: column;
      }
    }

  </style>
</head>
<body>

  <header class="portal-header">
    <div class="portal-logo">
      Souveniria
      <span>System Access Gateway</span>
    </div>
  </header>

  <div class="portal-container">
    
    <a href="login.html" class="portal-card">
      <div class="card-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="9" cy="21" r="1"></circle>
          <circle cx="20" cy="21" r="1"></circle>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
      </div>
      <h2 class="card-title">Customer Shop</h2>
      <p class="card-desc">Enter the main storefront to browse the Artisan Luxe catalog, test the checkout flow, and explore products.</p>
      <div class="card-btn btn-customer">Enter Storefront &rarr;</div>
    </a>

    <a href="login.html?admin=true" class="portal-card">
      <div class="card-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
      </div>
      <h2 class="card-title">Admin Dashboard</h2>
      <p class="card-desc">Access the backend portal to manage inventory, track live orders, view stats, and configure dynamic banners.</p>
      <div class="card-btn btn-admin">Secure Login &rarr;</div>
    </a>

  </div>

</body>
</html>

<?php
// Use __DIR__ to get the absolute path of the 'api' folder
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middlewares/JsonMiddleware.php';
require_once __DIR__ . '/controllers/PatientController.php';

// Initialize Middleware
JsonMiddleware::handle();

$database = new Database();
$db = $database->getConnection();  //from db.php 
$controller = new PatientController($db);

// check the request method put,get,...
$method = $_SERVER['REQUEST_METHOD'];
// Use 'request' parameter from .htaccess(split url)
$request = isset($_GET['request']) ? explode('/', trim($_GET['request'], '/')) : [];

$resource = isset($request[0]) ? $request[0] : '';
$id = (isset($request[1]) && is_numeric($request[1])) ? $request[1] : null;

// Handle JSON input for POST/PUT 
$data = json_decode(file_get_contents("php://input"), true);

// Only route if the resource is 'patients' 
if ($resource === 'patients') {
    $controller->handleRequest($method, $id, $data);
} else {
    header("Content-Type: application/json");
    http_response_code(404); // Not Found 
    echo json_encode(["status" => false, "message" => "Endpoint not found: " . $resource, "data" => []]);
}
?>
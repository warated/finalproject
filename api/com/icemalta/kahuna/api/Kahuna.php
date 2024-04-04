<?php
require 'com/icemalta/kahuna/util/ApiUtil.php';
require 'com/icemalta/kahuna/model/AccessToken.php';
require 'com/icemalta/kahuna/model/User.php';
require 'com/icemalta/kahuna/model/Product.php';
require 'com/icemalta/kahuna/model/SupportTicket.php';
require 'com/icemalta/kahuna/model/CustomerSupport.php';
require 'com/icemalta/kahuna/model/Transaction.php';
require 'com/icemalta/kahuna/model/reply.php';


use \com\icemalta\kahuna\model\{Product, User, AccessToken, SupportTicket, CustomerSupport, ReplyTicket, Transaction};


use com\icemalta\kahuna\util\ApiUtil;

cors();

$endPoints = [];
$requestData = [];
header("Content-Type: application/json; charset=UTF-8");

/* BASE URI */
$BASE_URI = '/kahuna/api/';

function sendResponse(mixed $data = null, int $code = 200, mixed $error = null): void
{
    if (!is_null($data)) {
        $response['data'] = $data;
    }
    if (!is_null($error)) {
        $response['error'] = $error;
    }
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    http_response_code($code);
}

function checkToken(array $requestData): bool
{
    if (!isset($requestData['token']) || !isset($requestData['user'])) {
        return false;
    }
    $token = new AccessToken($requestData['user'], $requestData['token']);
    return AccessToken::verify($token);
}


/* Get Request Data */
$requestMethod = $_SERVER['REQUEST_METHOD'];
switch ($requestMethod) {
    case 'GET':
        $requestData = $_GET;
        break;
    case 'POST':
        $requestData = $_POST;
        break;
    case 'PATCH':
        parse_str(file_get_contents('php://input'), $requestData);
        ApiUtil::parse_raw_http_request($requestData);
        $requestData = is_array($requestData) ? $requestData : [];
        break;
    case 'DELETE':
        break;
    default:
        sendResponse(null, 405, 'Method not allowed.');
}

/* Extract EndPoint */
$parsedURI = parse_url($_SERVER["REQUEST_URI"]);
$path = explode('/', str_replace($BASE_URI, "", $parsedURI["path"]));
$endPoint = implode('/', array_filter($path, function ($value) {
    return $value !== '';
}));
if (empty($endPoint)) {
    $endPoint = "/";
}

/* Extract Token */
if (isset($_SERVER["HTTP_X_API_USER"])) {
    $requestData["user"] = $_SERVER["HTTP_X_API_USER"];
}
if (isset($_SERVER["HTTP_X_API_KEY"])) {
    $requestData["token"] = $_SERVER["HTTP_X_API_KEY"];
}

/* EndPoint Handlers */
$endpoints["/"] = function (string $requestMethod, array $requestData): void {
    sendResponse('Welcome to Kahuna API!');
};

/*Endpoints for User*/
$endpoints["user"] = function (string $requestMethod, array $requestData): void {
    if ($requestMethod === 'POST') {
        $email = $requestData['email'];
        $password = $requestData['password'];
        $accessLevel = $requestData['accessLevel'] ?? 'user'; // Default to 'user' if not provided
        $user = new User($email, $password);
        $user->setAccessLevel($accessLevel);
        $user = User::save($user);
        sendResponse($user, 201);
    } else if ($requestMethod === 'PATCH') {
        sendResponse(null, 501, 'Updating a user has not yet been implemented.');
    } else if ($requestMethod === 'DELETE') {
        sendResponse(null, 501, 'Deleting a user has not yet been implemented.');
    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};

$endpoints["login"] = function (string $requestMethod, array $requestData): void {
    if ($requestMethod === 'POST') {
        $email = $requestData['email'];
        $password = $requestData['password'];
        $user = new User($email, $password);
        $user = User::authenticate($user);
        if ($user) {
            $token = new AccessToken($user->getId());
            $token = AccessToken::save($token);
            $accessLevel = $user->getAccessLevel();
            sendResponse(['user' => $user->getId(), 'token' => $token->getToken(), 'accessLevel' => $accessLevel]);

        } else {
            sendResponse(null, 401, 'Login failed.');
        }

    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};

$endpoints["logout"] = function (string $requestMethod, array $requestData): void {
    if ($requestMethod === 'POST') {
        if (checkToken($requestData)) {
            $userId = $requestData['user'];
            $token = new AccessToken($userId);
            $token = AccessToken::delete($token);
            sendResponse('You have been logged out.');
        } else {
            sendResponse(null, 403, 'Missing, invalid or expired token.');
        }
    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};

$endpoints["token"] = function (string $requestMethod, array $requestData): void {
    if ($requestMethod === 'GET') {
        if (checkToken($requestData)) {
            sendResponse(['valid' => true, 'token' => $requestData['token']]);
        } else {
            sendResponse(['valid' => false, 'token' => $requestData['token']]);
        }
    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};
//test for /CustomerSupport endpoint

$endpoints["customerSupport"] = function (string $requestMethod, array $requestData): void {
    if ($requestMethod === 'POST') {
        // Assuming the creation of a customer support representative requires more information
        $email = $requestData['email'];
        $password = $requestData['password'];
        $customerSupport = new CustomerSupport($email, $password);
        $customerSupport = CustomerSupport::save($customerSupport);
        sendResponse($customerSupport, 201);
    } else if ($requestMethod === 'PATCH') {
        sendResponse(null, 501, 'Updating a customer support representative has not yet been implemented.');
    } else if ($requestMethod === 'DELETE') {
        sendResponse(null, 501, 'Deleting a customer support representative has not yet been implemented.');
    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};

$endpoints["supportLogin"] = function (string $requestMethod, array $requestData): void {
    if ($requestMethod === 'POST') {
        $email = $requestData['email'];
        $password = $requestData['password'];
        $customerSupport = new CustomerSupport($email, $password);
        $customerSupport = CustomerSupport::authenticate($customerSupport);
        if ($customerSupport) {
            $token = new AccessToken($customerSupport->getId());
            $token = AccessToken::save($token);
            sendResponse(['supportId' => $customerSupport->getId(), 'token' => $token->getToken()]);
        } else {
            sendResponse(null, 401, 'Login failed.');
        }
    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};

$endpoints["logout"] = function (string $requestMethod, array $requestData): void {
    if ($requestMethod === 'POST') {
        if (checkToken($requestData)) {
            $userId = $requestData['user'];
            $token = new AccessToken($userId);
            $token = AccessToken::delete($token);
            sendResponse('You have been logged out.');
        } else {
            sendResponse(null, 403, 'Missing, invalid or expired token.');
        }
    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }
};




/*This will handle /product*/
$endpoints["product"] = function (string $requestMethod, array $requestData): void {
    if (checkToken($requestData)) {
        if ($requestMethod === 'GET') {
            $products = Product::load();
            sendResponse($products);
        } else if ($requestMethod === 'POST') {
            $serial = $requestData['serial'];
            $name = $requestData['name'];
            $warrantyLength = $requestData['warrantyLength'];

            // Check for serial existence and registration
            if (Product::existsBySerial($serial)) {
                sendResponse(null, 400, 'Serial number already exists.');
                return; // Stop execution if serial exists
            }

            // Call a method to verify if the serial number is allowed
            if (!Product::isAllowedSerial($serial)) {
                sendResponse(null, 400, 'Serial number not allowed.');
                return; // Stop execution if serial is not allowed
            }

            $product = new Product($serial, $name, $warrantyLength);
            $product = Product::save($product);
            sendResponse($product, 201);

        } else {
            sendResponse(null, 405, 'Method not allowed.');
        }
    }
};

//Endpoint for the buying a product *transaction*
$endpoints["product/buy"] = function (string $requestMethod, array $requestData): void {
    if ($requestMethod === 'GET') {
        $transaction = Transaction::load();
        sendResponse($transaction);

}else if ($requestMethod === 'POST') {
        // Extract request data
        $productId = (int) $requestData['productId'];
        $userId = (int) $requestData['userId'];
        $warrantyLength = isset($requestData['warrantyLength']) ? (int) $requestData['warrantyLength'] : 1; // Default to 1 year if not provided
        try {
            // Calculate warranty end date
            // Placeholder for now, adjust based on your business logic
            $warrantyEndDate = date('Y-m-d', strtotime("+$warrantyLength year"));
            

            $transaction = new Transaction(
                0, // Assuming auto-generated ID, not used in DB insert
                $userId,
                $productId,
                date('Y-m-d'), // Today's date for warranty start
                $warrantyEndDate, // Calculated warranty end date
                date('Y-m-d')  // Today's date for purchase date
            );

            // Use Transaction class to perform purchase logic
            if (Transaction::buy($transaction)) {
                sendResponse(['message' => 'Purchase successful.', 'warrantyEndDate' => $warrantyEndDate]);
            } else {
                sendResponse('Purchase failed. Please try again later.', 400);
            }
        } catch (Exception $e) {
            sendResponse('Purchase failed: ' . $e->getMessage(), 500);
        }
    } else {
        sendResponse('Invalid request method.', 405);
    }
};

/*This will handle SupportTicket (EndPoint)*/

$endpoints["SupportTicket"] = function (string $requestMethod, array $requestData): void {
    // if (checkToken($requestData))
    if ($requestMethod === 'GET') {
        $SupportTicket = SupportTicket::load();
        sendResponse($SupportTicket);
    } else if ($requestMethod === 'POST') {
        $name = $requestData['name'];
        $description = $requestData['description'];
        $SupportTicket = new SupportTicket($name, $description);
        $SupportTicket = SupportTicket::save($SupportTicket);
        sendResponse($SupportTicket, 201);

    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }

};

//This will handle ReplyTicket (EndPoint)
$endpoints["ReplyTicket"] = function (string $requestMethod, array $requestData): void {
    // if (checkToken($requestData))
    if ($requestMethod === 'GET') {
        $ReplyTicket = ReplyTicket::load();
        sendResponse($ReplyTicket);
    } else if ($requestMethod === 'POST') {
        $ticket_id = $requestData['ticket_id'];
        $description = $requestData['description'];
        $ReplyTicket = new ReplyTicket($ticket_id, $description);
        $ReplyTicket = ReplyTicket::save($ReplyTicket);
        sendResponse($ReplyTicket, 201);

    } else {
        sendResponse(null, 405, 'Method not allowed.');
    }

};


$endpoints["404"] = function (string $requestMethod, array $requestData): void {
    sendResponse(null, 404, "Endpoint " . $requestData["endPoint"] . " not found.");
};




function cors()
{
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        // you want to allow, and if so:
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            // may also be using PUT, PATCH, HEAD etc
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PATCH, DELETE");

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }
}

try {
    if (isset($endpoints[$endPoint])) {
        $endpoints[$endPoint]($requestMethod, $requestData);
    } else {
        $endpoints["404"]($requestMethod, array("endPoint" => $endPoint));
    }
} catch (Exception $e) {
    sendResponse(null, 500, $e->getMessage());
} catch (Error $e) {
    sendResponse(null, 500, $e->getMessage());
}

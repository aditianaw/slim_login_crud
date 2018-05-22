<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['db']['host']   = "localhost";
$config['db']['user']   = "root";
$config['db']['pass']   = "";
$config['db']['dbname'] = "exampleapp";
function DBConnection(){
	return new PDO('mysql:dbhost=localhost;dbname=exampleapp', 'root', '');
}
//middleware session
$middleware1 = (function ($request, $response, $next) {
	$loggedIn = $_SESSION['isLoggedIn'];
    if ($loggedIn != 'admin') {
        return $response->withRedirect("/");
    }
    $response = $next($request, $response);
    return $response;
});
$middleware2 = (function ($request, $response, $next) {
	$loggedIn = $_SESSION['isLoggedIn'];
    if ($loggedIn != 'user') {
        return $response->withRedirect("/");
    }
    $response = $next($request, $response);
    return $response;
});


$app = new \Slim\App(["settings" => $config]);
			$container = $app->getContainer();

			$container['view'] = new \Slim\Views\PhpRenderer("../templates/");

			$container['logger'] = function($c) {
				$logger = new \Monolog\Logger('my_logger');
				$file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
				$logger->pushHandler($file_handler);
				return $logger;
			};

			$container['db'] = function ($c) {
				$db = $c['settings']['db'];
				$pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
					$db['user'], $db['pass']);
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
				return $pdo;
			};
			//setting tampilan awal
			$app->get('/', function (Request $request, Response $response) {
				$this->logger->addInfo("Menulist");
				$response = $this->view->render($response, "login.php");
				return $response;
			});
			//tabel ticket
			$app->get('/tickets', function (Request $request, Response $response) {
				$this->logger->addInfo("Ticket list");
				$mapper = new TicketMapper($this->db);
				$tickets = $mapper->getTickets();

				$response = $this->view->render($response, "tickets.phtml", ["tickets" => $tickets, "router" => $this->router]);
				return $response;
			});
			//insert ticket
			$app->get('/ticket/new', function (Request $request, Response $response) {
				$component_mapper = new ComponentMapper($this->db);
				$components = $component_mapper->getComponents();
				$response = $this->view->render($response, "ticketadd.phtml", ["components" => $components]);
				return $response;
			});
			$app->post('/ticket/new', function (Request $request, Response $response) {
				$data = $request->getParsedBody();
				$ticket_data = [];
				$ticket_data['title'] = filter_var($data['title'], FILTER_SANITIZE_STRING);
				$ticket_data['description'] = filter_var($data['description'], FILTER_SANITIZE_STRING);

				// work out the component
				$component_id = (int)$data['component'];
				$component_mapper = new ComponentMapper($this->db);
				$component = $component_mapper->getComponentById($component_id);
				$ticket_data['component'] = $component->getName();

				$ticket = new TicketEntity($ticket_data);
				$ticket_mapper = new TicketMapper($this->db);
				$ticket_mapper->save($ticket);

				$response = $response->withRedirect("/tickets");
				return $response;
			});

			//view ticket
			$app->get('/ticket/{id}', function (Request $request, Response $response, $args) {
				$ticket_id = (int)$args['id'];
				$mapper = new TicketMapper($this->db);
				$ticket = $mapper->getTicketById($ticket_id);

				$response = $this->view->render($response, "ticketdetail.phtml", ["ticket" => $ticket]);
				return $response;
			})->setName('ticket-detail');
			
			
			//Component
			$app->get('/components', function (Request $request, Response $response) {
				$this->logger->addInfo("component list");
				$mapper = new ComponentMapper($this->db);
				$components = $mapper->getComponents();

				$response = $this->view->render($response, "components.phtml", ["components" => $components, "router" => $this->router]);
				return $response;
			});

			//insert component
			$app->get('/component/new', function (Request $request, Response $response) {
				$component_mapper = new ComponentMapper($this->db);
				$components = $component_mapper->getComponents();
				$response = $this->view->render($response, "componentadd.phtml", ["components" => $components]);
				return $response;
			});

			$app->post('/component/new', function (Request $request, Response $response) {
				$data = $request->getParsedBody();
				$component_data = [];
				$component_data['id'] = filter_var($data['id'], FILTER_SANITIZE_STRING);
				$component_data['component'] = filter_var($data['component'], FILTER_SANITIZE_STRING);
				$component = new ComponentEntity($component_data);
				$compo_mapper = new ComponentMapper($this->db);
				$compo_mapper->save($component);

				$response = $response->withRedirect("/components");
				return $response;
			});
			
			//view component
			$app->get('/component/{id}', function (Request $request, Response $response, $args) {
				$component_id = (int)$args['id'];
				$mapper = new ComponentMapper($this->db);
				$component = $mapper->getComponentById($component_id);

				$response = $this->view->render($response, "componentdetail.phtml", ["component" => $component]);
				return $response;
			})->setName('component-detail');

			//delete component
			$app->get('/component/{id}/delete', function (Request $request, Response $response, $args ) {
				
				$component_id = (int)$args['id'];
				$mapper = new ComponentMapper($this->db);
				$component = $mapper->getComponentById($component_id);
				$compo_mapper = new ComponentMapper($this->db);
				$compo_mapper->delete($component);

				$response = $response->withRedirect("/components");
				return $response;
			});
			
			//update
			$app->get('/component/{id}/update', function (Request $request, Response $response, $args) {
				$component_id = (int)$args['id'];
				$mapper = new ComponentMapper($this->db);
				$component = $mapper->getComponentById($component_id);
				
				$response = $this->view->render($response, "componentupdate.phtml", ["component" => $component]);
				return $response;
			})->setName('component-update');
			$app->get('/component/{id}/updat', function (Request $request, Response $response, $args) {
				$component_id = (int)$args['id'];
				$mapper = new ComponentMapper($this->db);
				$component = $mapper->getComponentById($component_id);
				$component = $request->getParam('component');
				DBConnection()->exec("update components set component = '".$component."' where id = ".$args['id'].";");
				echo('Data berhasil diupdate !');
				$response = $response->withRedirect("/components");
				return $response;
			})->setName('component-update');

			//home aplikasi
			$app->get("/home", function (Request $request, Response $response, $args) {
				$response = $this->view->render($response, "home.phtml");
				return $response;
			});
		/*	
			//session login
			$app->post('/login', function ($request, $response) {
			$username = $request->getParsedBody()['username'];
			$password = $request->getParsedBody()['password'];
			$stmt = (DBConnection()->query("select password from user where username = '".$username."' LIMIT 1")->fetch());
			$db_pass = $stmt['password'];
			if($password==$db_pass){
				
				$response = $this->view->render($response, "home.phtml");
				return $response;
			}
			else{ 
				$response = $this->view->render($response, "login.php");
				return $response;
			}
			
		});

			$app->get("/logout", function (Request $request, Response $response, $args) {
				
				$response = $this->view->render($response, "login.php");
				return $response;
			}); */

		//LOGIN
		$app->post('/login', function ($request, $response) {
			$username = $request->getParsedBody()['username'];
			$password = $request->getParsedBody()['password'];
			$ps = (DBConnection()->query("select password from user where username = '".$username."' LIMIT 1")->fetch());
			$ut = (DBConnection()->query("select usertype from user where username = '".$username."' LIMIT 1")->fetch());
			$db_ps = $ps['password'];
			$db_ut = $ut['usertype'];
			if($password === $db_ps && $db_ut === "admin"){
				$_SESSION['isLoggedIn'] = 'admin';
				session_regenerate_id();
				$response = $response->withRedirect("/home");
				return $response;
			}else if($password === $db_ps && $db_ut === "user"){
				$_SESSION['isLoggedIn'] = 'user';
				$_SESSION['username'] = $username;
				session_regenerate_id();
				$response = $response->withRedirect("/home");
				return $response;
			}
			
			
			else{
				$message = "Username atau Password Anda Salah !";
				echo "<script type='text/javascript'>alert('$message');</script>";
				$response = $this->view->render($response, "login.php");
						return $response;
			}
		});

		//LOGOUT
		$app->get('/logout', function ($request, $response, $args) {
			unset($_SESSION['isLoggedIn']);
			unset($_SESSION['username']);
			session_regenerate_id();
			$response = $response->withRedirect("/");
			return $response;
		});
		

$app->run();

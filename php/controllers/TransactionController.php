<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TransactionController
{
  //GET /accounts/account:id/transactions -- shows all transactions
  public function allTransactions(Request $request, Response $response, $args){
    //prendo l'id dell'account dai parametri della richiesta
    $accountId = $args['account'];
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'banking');
    $result = $mysqli_connection->query("SELECT * FROM `transaction` WHERE account_id = $accountId");
    $results = $result->fetch_all(MYSQLI_ASSOC);

    $response->getBody()->write(json_encode($results));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }

  //GET /accounts/account:id/transactions/transaction:id -- gets the details of a single transaction
  public function transaction(Request $request, Response $response, $args){
    //prendo l'id dell'account e della transazione dai parametri della richiesta
    $accountId = $args['account'];
    $transactionId = $args['transactionId'];
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'banking');
    $result = $mysqli_connection->query("SELECT * FROM `transaction` WHERE account_id = $accountId AND id = $transactionId");
    $results = $result->fetch_all(MYSQLI_ASSOC);
    if (count($results) === 0) {
      $response->getBody()->write(json_encode(['error' => 'transaction not found']));
      return $response->withHeader("Content-type", "application/json")->withStatus(404);
    }
    $response->getBody()->write(json_encode($results[0]));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }

  private function getRequestData(Request $request){
    $data = $request->getParsedBody();
    if (is_array($data) && count($data) > 0) {
      return $data;
    }
    $body = (string)$request->getBody();
    if ($body !== '') {
      $json = json_decode($body, true);
      if (is_array($json)) return $json;
    }
    if (!empty($_POST)) return $_POST;
    return [];
  }


  //POST /accounts/account:id/deposit -- register a deposit action on a specified account
  public function deposit(Request $request, Response $response, $args){
    //prendo l'id dell'account dai parametri della richiesta
    $accountId = $args['account'];
    //prendo i dati della transazione dal body della richiesta
    $data = $this->getRequestData($request);
    $amount = isset($data['amount']) ? trim($data['amount']) : '';
    $description = isset($data['description']) ? trim($data['description']) : '';
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'banking');
    $mysqli_connection->query("INSERT INTO `transaction` (`account_id`, `amount`, `description`) VALUES ($accountId, $amount, '$description')");
    $response->getBody()->write(json_encode(['message' => 'deposit registered successfully']));
    return $response->withHeader("Content-type", "application/json")->withStatus(201);
  }

  //POST /accounts/account:id/withdrawal -- register a withdrawal action on a specified account
  public function withdraw(Request $request, Response $response, $args){
    //prendo l'id dell'account dai parametri della richiesta
    $accountId = $args['account'];
    //prendo i dati della transazione dal body della richiesta
    $data = $this->getRequestData($request);
    $amount = isset($data['amount']) ? trim($data['amount']) : '';
    $description = isset($data['description']) ? trim($data['description']) : '';
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'banking');
    $mysqli_connection->query("INSERT INTO `transaction` (`account_id`, `amount`, `description`) VALUES ($accountId, -$amount, '$description')");
    $response->getBody()->write(json_encode(['message' => 'withdrawal registered successfully']));
    return $response->withHeader("Content-type", "application/json")->withStatus(201);
  }

  //PUT /accounts/account:id/transactions/transaction:id -- edit the description of a specified transaction
  public function editDesc(Request $request, Response $response, $args){
    //prendo l'id dell'account e della transazione dai parametri della richiesta
    $accountId = $args['account'];
    $transactionId = $args['transactionId'];
    //prendo la nuova descrizione dal body della richiesta
    $data = $this->getRequestData($request);
    $description = isset($data['description']) ? trim($data['description']) : '';
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'banking');
    $mysqli_connection->query("UPDATE `transaction` SET `description` = '$description' WHERE account_id = $accountId AND id = $transactionId");
    $response->getBody()->write(json_encode(['message' => 'transaction description updated successfully']));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }

  //DELETE /accounts/account:id/transactions/transaction:id -- to delete a transaction with specified rules
  public function delete(Request $request, Response $response, $args){
    //prendo l'id dell'account e della transazione dai parametri della richiesta
    $accountId = $args['account'];
    $transactionId = $args['transactionId'];
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'banking');
    //controllo se la transazione da eliminare è l'ultima registrata per quell'account
    $result = $mysqli_connection->query("SELECT id FROM `transaction` WHERE account_id = $accountId ORDER BY id DESC LIMIT 1");
    $lastTransaction = $result->fetch_assoc();
    if ($lastTransaction['id'] != $transactionId) {
      $response->getBody()->write(json_encode(['error' => 'Only the last transaction can be deleted']));
      return $response->withHeader("Content-type", "application/json")->withStatus(400);
    }
    //elimino la transazione
    $mysqli_connection->query("DELETE FROM `transaction` WHERE account_id = $accountId AND id = $transactionId");
    $response->getBody()->write(json_encode(['message' => 'transaction deleted successfully']));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }

  //GET /accounts/account:id/balance -- to get the balance of a specified account
  public function getBalance(Request $request, Response $response, $args){
    //prendo l'id dell'account dai parametri della richiesta
    $accountId = $args['account'];
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'banking');
    $result = $mysqli_connection->query("SELECT SUM(amount) as balance FROM `transaction` WHERE account_id = $accountId");
    $balance = $result->fetch_assoc();
    $response->getBody()->write(json_encode(['balance' => $balance['balance']]));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }


}

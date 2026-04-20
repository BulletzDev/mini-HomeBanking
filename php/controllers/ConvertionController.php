<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TransactionController
{
  /*public function index(Request $request, Response $response, $args){
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
    $result = $mysqli_connection->query("SELECT * FROM alunni");
    $results = $result->fetch_all(MYSQLI_ASSOC);

    $response->getBody()->write(json_encode($results));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }*/

  // /accounts/account:id/balance/convert/fiat?to=USD
  public function toFiat(Request $request, Response $response, $args){
    $id = $args['id'] ?? null;
    $query = $request->getQueryParams();
    $to = strtoupper($query['to'] ?? '');

    if (!$id || !is_numeric($id)) {
        $response->getBody()->write(json_encode(['error' => 'Invalid or missing account id']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    if (!$to) {
        $response->getBody()->write(json_encode(['error' => 'Missing target currency (to)']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $mysqli = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
    if ($mysqli->connect_error) {
        $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    $stmt = $mysqli->prepare("SELECT balance, currency FROM accounts WHERE id = ? LIMIT 1");
    if (!$stmt) {
        $response->getBody()->write(json_encode(['error' => 'Database error']));
        $mysqli->close();
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $account = $result->fetch_assoc();

    $stmt->close();

    if (!$account) {
        $mysqli->close();
        $response->getBody()->write(json_encode(['error' => 'Account not found']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $amount = (float)$account['balance'];
    $from = strtoupper($account['currency'] ?? 'EUR');

    // Call Frankfurter API to convert
    $url = sprintf(
        'https://api.frankfurter.app/latest?amount=%s&from=%s&to=%s',
        urlencode($amount),
        urlencode($from),
        urlencode($to)
    );

    $opts = ['http' => ['timeout' => 5]];
    $context = stream_context_create($opts);
    $apiResponse = @file_get_contents($url, false, $context);

    if ($apiResponse === false) {
        $mysqli->close();
        $response->getBody()->write(json_encode(['error' => 'Currency conversion service unavailable']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(502);
    }

    $data = json_decode($apiResponse, true);
    if (!isset($data['rates'][$to])) {
        $mysqli->close();
        $response->getBody()->write(json_encode(['error' => 'Conversion failed or unsupported currency']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $convertedAmount = (float)$data['rates'][$to];

    // Optional: aggiornare il saldo dell'account con il valore convertito
    // Esempio: aggiornare il saldo con il valore convertito
    $stmtUpdate = $mysqli->prepare("UPDATE accounts SET balance = ? WHERE id = ?");
    if ($stmtUpdate) {
        $stmtUpdate->bind_param('di', $convertedAmount, $id);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }

    $mysqli->close();

    $payload = [
        'account_id' => $id,
        'original' => [
            'amount' => $amount,
            'currency' => $from
        ],
        'target' => [
            'currency' => $to,
            'amount' => $convertedAmount
        ],
        'raw' => $data
    ];

    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
  }

  // /accounts/account:id/balance/convert/crypto?to=BTC
  public function toCrypto(Request $request, Response $response, $args){

  }

}
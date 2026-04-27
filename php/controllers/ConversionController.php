<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ConversionController
{
  // /accounts/account:id/balance/convert/fiat?to=USD
  public function toFiat(Request $request, Response $response, $args){
    $mysqli = new MySQLi('my_mariadb', 'root', 'ciccio', 'banking');
    if ($mysqli->connect_error) {
        $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    $id = $args['account'] ?? null;
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


    $stmt = $mysqli->prepare("SELECT currency, (SELECT SUM(amount) FROM `transaction` WHERE account_id = ?) AS balance FROM account WHERE id = ? LIMIT 1");
    if (!$stmt) {
        $response->getBody()->write(json_encode(['error' => 'Database error']));
        $mysqli->close();
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    $stmt->bind_param('ii', $id, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $account = $result->fetch_assoc();
    if (!$account) {
        $mysqli->close();
        $response->getBody()->write(json_encode(['error' => 'Account not found']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
    $from = strtoupper($account['currency'] ?? 'EUR');
    $old_balance = $account['balance'];
    $stmt->close();

    if($from != $to) {
        $url = "https://api.frankfurter.dev/v2/rate/{$from}/{$to}";
        $opts = ['http' => ['timeout' => 5]];
        $context = stream_context_create($opts);
        $apiResponse = @file_get_contents($url, false, $context);
    
        if ($apiResponse === false) {
            $mysqli->close();
            $response->getBody()->write(json_encode(['error' => 'Currency conversion service unavailable']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(502);
        }
    
        $data = json_decode($apiResponse, true);
        if (!isset($data['rate'])) {
            $mysqli->close();
            $response->getBody()->write(json_encode(['error' => 'Conversion failed or unsupported currency']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        $convertedAmount = $old_balance * $data['rate'];
    }
    else {
        $convertedAmount = $old_balance;
    }
    
    // Optional: aggiornare il saldo dell'account con il valore convertito
    // Esempio: aggiornare il saldo con il valore convertito
    $stmtUpdate = $mysqli->prepare("UPDATE account SET  currency = ?, balance = ? WHERE id = ?");
    if ($stmtUpdate) {
        $stmtUpdate->bind_param('sdi', $to, $convertedAmount, $id);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }

    $mysqli->close();

    $payload = [
        'account_id' => $id,
        'original' => [
            'amount' => $old_balance,
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

  //https://api.binance.com/api/v3/avgPrice?symbol={$crypto}{$currency}
  
  // NOT WORKING STILL NEED TO DO
  public function toCrypto(Request $request, Response $response, $args){
    $mysqli = new MySQLi('my_mariadb', 'root', 'ciccio', 'banking');
    if ($mysqli->connect_error) {
        $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    $id = $args['account'] ?? null;
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


    $stmt = $mysqli->prepare("SELECT currency, (SELECT SUM(amount) FROM `transaction` WHERE account_id = ?) AS balance FROM account WHERE id = ? LIMIT 1");
    if (!$stmt) {
        $response->getBody()->write(json_encode(['error' => 'Database error']));
        $mysqli->close();
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    $stmt->bind_param('ii', $id, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $account = $result->fetch_assoc();
    if (!$account) {
        $mysqli->close();
        $response->getBody()->write(json_encode(['error' => 'Account not found']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
    $from = strtoupper($account['currency'] ?? 'EUR');
    $old_balance = $account['balance'];
    $stmt->close();

    if($from != $to) {
        $url = "https://api.frankfurter.dev/v2/rate/{$from}/{$to}";
        $opts = ['http' => ['timeout' => 5]];
        $context = stream_context_create($opts);
        $apiResponse = @file_get_contents($url, false, $context);
    
        if ($apiResponse === false) {
            $mysqli->close();
            $response->getBody()->write(json_encode(['error' => 'Currency conversion service unavailable']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(502);
        }
    
        $data = json_decode($apiResponse, true);
        if (!isset($data['rate'])) {
            $mysqli->close();
            $response->getBody()->write(json_encode(['error' => 'Conversion failed or unsupported currency']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        $convertedAmount = $old_balance * $data['rate'];
    }
    else {
        $convertedAmount = $old_balance;
    }
    
    // Optional: aggiornare il saldo dell'account con il valore convertito
    // Esempio: aggiornare il saldo con il valore convertito
    $stmtUpdate = $mysqli->prepare("UPDATE account SET  currency = ?, balance = ? WHERE id = ?");
    if ($stmtUpdate) {
        $stmtUpdate->bind_param('sdi', $to, $convertedAmount, $id);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }

    $mysqli->close();

    $payload = [
        'account_id' => $id,
        'original' => [
            'amount' => $old_balance,
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

}
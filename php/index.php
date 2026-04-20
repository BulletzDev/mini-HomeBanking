<?php
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/controllers/Transactions.php';
require __DIR__ . '/controllers/Convertions.php';

$app = AppFactory::create();

//Transactions API's
$app ->get('/accounts/{account}/transactions', 'TransactionController:allTransactions');
$app ->get('/accounts/{account}/transactions/{transactionId}', 'TransactionController:transaction');
$app ->post('/accounts/{account}/deposits', 'TransactionController:deposit');
$app ->post('/accounts/{account}/withdrawals', 'TransactionController:withdraw');
$app ->put('/accounts/{account}/transactions/{transactionId}', 'TransactionController:editDesc');
$app ->delete('/accounts/{account}/transactions/{transactionId}', 'TransactionController:delete');

$app ->get('/accounts/{account}/balance', 'TransactionController:getBalance');

//Convertions API's
$app ->get('/accounts/{account}/balance/convert/fiat?to={currency}', 'ConvertionController:toFiat');
$app ->get('/accounts/{account}/balance/convert/crypto?to={currency}', 'ConvertionController:toCrypto');

$app->run();



//make me some curl requests to test the API's
//curl -X GET http://localhost:3000/accounts/1/transactions
//curl -X GET http://localhost:3000/accounts/1/transactions/5
//curl -X POST http://localhost:3000/accounts/1/deposits -H "Content-Type: application/json" -d '{"amount": 200, "description": "Salary"}'
//curl -X POST http://localhost:3000/accounts/1/withdrawals -H  "Content-Type: application/json" -d '{"amount": 50, "description": "Groceries"}'
//curl -X PUT http://localhost:3000/accounts/1/transactions/5 -H "Content-Type: application/json" -d '{"description": "Updated description"}'
//curl -X DELETE http://localhost:3000/accounts/1/transactions/5
//curl -X GET http://localhost:3000/accounts/1/balance
//curl -X GET http://localhost:3000/accounts/1/balance/convert/fiat?to=USD
//curl -X GET http://localhost:3000/accounts/1/balance/convert/crypto?to=BTC
//172.21.0.3:3000

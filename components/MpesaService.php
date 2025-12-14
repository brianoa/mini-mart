<?php
namespace app\components;

use Yii;

class MpesaService
{
    protected string $baseUrl;
    protected string $consumerKey;
    protected string $consumerSecret;
    protected string $shortCode;
    protected string $passKey;
    protected string $callbackUrl;

    public function __construct()
    {
        $config = Yii::$app->params['mpesa'] ?? [];

        $this->baseUrl        = isset($config['baseUrl']) ? rtrim($config['baseUrl'], '/') : '';
        $this->consumerKey    = $config['consumerKey']    ?? '';
        $this->consumerSecret = $config['consumerSecret'] ?? '';
        $this->shortCode      = $config['shortCode']      ?? '';
        $this->passKey        = $config['passKey']        ?? '';
        $this->callbackUrl    = $config['callbackUrl']    ?? '';

        if (!$this->baseUrl || !$this->consumerKey || !$this->consumerSecret ||
            !$this->shortCode || !$this->passKey || !$this->callbackUrl) {
            throw new \RuntimeException('Mpesa configuration is incomplete in params.php');
        }
    }

    /**
     * Normalize Kenyan phone to 2547XXXXXXXX
     */
    protected function normalizePhone(string $phone): string
    {
        // strip non-digits
        $phone = preg_replace('/\D+/', '', $phone ?? '');

        // 07XXXXXXXX -> 2547XXXXXXXX
        if (preg_match('/^07\d{8}$/', $phone)) {
            $phone = '254' . substr($phone, 1);
        }

        // +2547XXXXXXXX -> 2547XXXXXXXX
        if (preg_match('/^\+2547\d{8}$/', $phone)) {
            $phone = substr($phone, 1);
        }

        if (!preg_match('/^2547\d{8}$/', $phone)) {
            throw new \InvalidArgumentException('Invalid Kenyan phone. Use 07.. or 2547.. format.');
        }

        return $phone;
    }

    /**
     * Step 1: Authorization API – get access token
     */
    public function getAccessToken(): string
    {
        $url = $this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials';

        $encodedCredentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $encodedCredentials,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // for sandbox you can leave verification off; for production you should enable verification.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('Failed to get access token: ' . $error);
        }

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);
        if ($code !== 200 || !isset($data['access_token'])) {
            $desc = $data['error_description'] ?? $response;
            throw new \RuntimeException('Failed to get access token: ' . $desc);
        }

        return $data['access_token'];
    }

    /**
     * Step 2: Lipa Na M-Pesa Online (STK Push)
     *
     * @param string $phone      Customer phone (07.. or 2547..)
     * @param float  $amount     Amount to charge
     * @param string $accountRef Reference (e.g. sale id)
     * @param string $desc       Description
     *
     * @return array Decoded Daraja response
     * @throws \Exception
     */
    public function stkPush(string $phone, float $amount, string $accountRef, string $desc = 'POS Sale'): array
    {
        // Normalize phone to 2547XXXXXXXX
        $phone = $this->normalizePhone($phone);

        // Basic amount validation
        $amount = (float)$amount;
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero.');
        }

        $token     = $this->getAccessToken();
        $timestamp = date('YmdHis');

        // STK password = base64(shortCode + passKey + timestamp)
        $stkPassword = base64_encode($this->shortCode . $this->passKey . $timestamp);

        $url = $this->baseUrl . '/mpesa/stkpush/v1/processrequest';

        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ];

        $payload = [
            'BusinessShortCode' => $this->shortCode,
            'Password'          => $stkPassword,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => (int)round($amount),
            'PartyA'            => $phone,
            'PartyB'            => $this->shortCode,
            'PhoneNumber'       => $phone,
            'CallBackURL'       => $this->callbackUrl,
            'AccountReference'  => $accountRef,
            'TransactionDesc'   => $desc,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // sandbox: false is fine; production: ideally true with proper CA bundle
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('STK push cURL error: ' . $error);
        }

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true) ?? [];

        if ($code !== 200) {
            // bubble up any useful error info from Daraja
            throw new \RuntimeException('STK push HTTP error ' . $code . ': ' . $response);
        }

        if (!isset($data['ResponseCode'])) {
            throw new \RuntimeException('STK push response missing ResponseCode: ' . $response);
        }

        return $data;
    }
}

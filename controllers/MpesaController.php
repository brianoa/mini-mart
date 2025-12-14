<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\components\MpesaService;

class MpesaController extends Controller
{
    /**
     * Quick check: can we obtain an access token from Daraja?
     *
     * GET /mpesa/test-token
     */
    public function actionTestToken()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $mpesa = new MpesaService();
            $token = $mpesa->getAccessToken();

            return [
                'success'      => true,
                'access_token' => $token,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Test STK push with a hard-coded phone and amount.
     *
     * GET /mpesa/test-stk
     */
    public function actionTestStk()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $mpesa = new MpesaService();

            // TODO: replace with your actual Safaricom Mpesa line
            // Can be 07XXXXXXXX or 2547XXXXXXXX; service will normalize it.
            $phone      = '254703119937';
            $amount     = 1;
            $accountRef = 'Test';
            $desc       = 'Test STK';

            $result = $mpesa->stkPush($phone, $amount, $accountRef, $desc);

            return [
                'success' => true,
                'result'  => $result,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * STK callback endpoint – set this URL in params['mpesa']['callbackUrl'].
     *
     * POST /mpesa/stk-callback
     */
    public function actionStkCallback()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Raw JSON body from Safaricom
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        // Log for debugging (runtime/logs/app.log)
        Yii::info('MPESA STK CALLBACK: ' . $raw, 'mpesa');

        // TODO:
        //  - Parse $data['Body']['stkCallback']
        //  - Match CheckoutRequestID / MerchantRequestID to your Sale
        //  - Check ResultCode == 0 for success
        //  - Update your Sales table accordingly

        return [
            'result' => 'ok',
        ];
    }
    
}

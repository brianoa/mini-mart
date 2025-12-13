<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Sales;
use app\models\SaleItems;
use yii\db\Query;


class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => [''],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }


    public function actionClientPurchases()
    {
        $client = Yii::$app->request->get('client');

        $query = Sales::find();

        if ($client) {
            $query->andWhere(['like', 'client_name', $client]);
        }

        $sales = $query->orderBy(['created_at' => SORT_DESC])->all();

        return $this->render('client-purchases', [
            'sales' => $sales,
            'client' => $client
        ]);
    }

    public function actionViewClientPurchase($id)
    {
        $sale = Sales::findOne($id);

        if (!$sale) {
            throw new \yii\web\NotFoundHttpException('Sale not found.');
        }

        $items = SaleItems::find()
            ->where(['sale_id' => $id])
            ->all();

        return $this->render('view-client-purchase', [
            'sale' => $sale,
            'items' => $items
        ]);
    }

    public function actionDashboard()
{
   
    $db = \Yii::$app->db;

    $today = date('Y-m-d');
    $startOfWeek = date('Y-m-d', strtotime('monday this week'));
    $startOfMonth = date('Y-m-01');

    $firstDayLastMonth = date('Y-m-01', strtotime('first day of last month'));
    $lastDayLastMonth  = date('Y-m-t', strtotime('last day of last month'));

    // REVENUE (only PAID sales)
    $todayIncome = (float)$db->createCommand("
        SELECT SUM(total_amount) 
        FROM sales 
        WHERE status = 'PAID' AND DATE(created_at) = :today
    ")->bindValue(':today', $today)->queryScalar();

    $yesterday = date('Y-m-d', strtotime('-1 day'));

    $yesterdayIncome = (float)$db->createCommand("
        SELECT SUM(total_amount) 
        FROM sales 
        WHERE status = 'PAID' AND DATE(created_at) = :yesterday
    ")->bindValue(':yesterday', $yesterday)->queryScalar();

    $weeklyIncome = (float)$db->createCommand("
        SELECT SUM(total_amount) 
        FROM sales 
        WHERE status = 'PAID' AND DATE(created_at) >= :start
    ")->bindValue(':start', $startOfWeek)->queryScalar();

    $monthlyIncome = (float)$db->createCommand("
        SELECT SUM(total_amount) 
        FROM sales 
        WHERE status = 'PAID' AND DATE(created_at) >= :start
    ")->bindValue(':start', $startOfMonth)->queryScalar();

    $lastMonthRevenue = (float)$db->createCommand("
        SELECT SUM(total_amount) 
        FROM sales 
        WHERE status = 'PAID'
          AND DATE(created_at) BETWEEN :start AND :end
    ")->bindValues([
        ':start' => $firstDayLastMonth,
        ':end'   => $lastDayLastMonth,
    ])->queryScalar();

    $totalRevenue = (float)$db->createCommand("
        SELECT SUM(total_amount) 
        FROM sales 
        WHERE status = 'PAID'
    ")->queryScalar();

    // EXPENSES
    $monthlyExpenses = (float)$db->createCommand("
        SELECT SUM(amount) 
        FROM expenses 
        WHERE DATE(created_at) >= :start
    ")->bindValue(':start', $startOfMonth)->queryScalar();

    $lastMonthExpenses = (float)$db->createCommand("
        SELECT SUM(amount) 
        FROM expenses 
        WHERE DATE(created_at) BETWEEN :start AND :end
    ")->bindValues([
        ':start' => $firstDayLastMonth,
        ':end'   => $lastDayLastMonth,
    ])->queryScalar();

    $totalExpenses = (float)$db->createCommand("
        SELECT SUM(amount) 
        FROM expenses
    ")->queryScalar();

    // STOCK VALUES (from products)
    $totalStockValue = (float)$db->createCommand("
        SELECT SUM(buying_price * balance_qty_instock) 
        FROM products
    ")->queryScalar();

    $totalSalesValue = (float)$db->createCommand("
        SELECT SUM(selling_price * sold_qty_instock) 
        FROM products
    ")->queryScalar();

    // NETS
    $netProfit = $totalSalesValue - $totalStockValue;          // stock vs sales approximation
    $netIncome = $totalRevenue - $totalExpenses;               // revenue - expenses

    $monthlyNetIncome    = $monthlyIncome - $monthlyExpenses;
    $lastMonthNetIncome  = $lastMonthRevenue - $lastMonthExpenses;

    // LOW STOCK COUNT (threshold = 10)
    $lowStockCount = (int)(new Query())
        ->from('products')
        ->where(['<', 'balance_qty_instock', 10])
        ->count('*', $db);

    // BEST SELLING ITEMS (top 5 by sold_qty_instock)
    $bestSellingItems = (new Query())
        ->select(['name', 'sold_qty_instock AS total_sold'])
        ->from('products')
        ->orderBy(['sold_qty_instock' => SORT_DESC])
        ->limit(5)
        ->all($db);

    return $this->render('dashboard', [
        'todayIncome'        => $todayIncome ?: 0,
        'yesterdayIncome'    => $yesterdayIncome ?: 0,
        'weeklyIncome'       => $weeklyIncome ?: 0,
        'monthlyIncome'      => $monthlyIncome ?: 0,
        'lastMonthRevenue'   => $lastMonthRevenue ?: 0,
        'monthlyExpenses'    => $monthlyExpenses ?: 0,
        'lastMonthExpenses'  => $lastMonthExpenses ?: 0,
        'monthlyNetIncome'   => $monthlyNetIncome ?: 0,
        'lastMonthNetIncome' => $lastMonthNetIncome ?: 0,
        'totalRevenue'       => $totalRevenue ?: 0,
        'totalExpenses'      => $totalExpenses ?: 0,
        'totalStockValue'    => $totalStockValue ?: 0,
        'totalSalesValue'    => $totalSalesValue ?: 0,
        'netProfit'          => $netProfit ?: 0,
        'netIncome'          => $netIncome ?: 0,
        'bestSellingItems'   => $bestSellingItems,
        'lowStockCount'      => $lowStockCount,
    ]);
}

}

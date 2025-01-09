<?php

namespace App\Http\Controllers;

use Anand\LaravelPaytmWallet\Facades\PaytmWallet;
use App\Models\FileUploader;
use App\Models\payment_gateway\Paystack;
use App\Models\payment_gateway\Ccavenue;
use App\Models\payment_gateway\Pagseguro;
use App\Models\payment_gateway\Xendit;
use App\Models\payment_gateway\Doku;
use App\Models\payment_gateway\Skrill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use paytm\paytmchecksum\PaytmChecksum;

class PaymentController extends Controller
{

    public function index()
    {
        $payment_details = session('payment_details');
        // if (!$payment_details || !is_array($payment_details) || count($payment_details) <= 0) {
        //     Session::flash('error', get_phrase('Payment not configured yet'));
        //     return redirect()->back();
        // }
        // if ($payment_details['payable_amount'] <= 0) {
        //     Session::flash('error', get_phrase("Payable amount cannot be less than 1"));
        //     return redirect()->to($payment_details['cancel_url']);
        // }
        // $page_data['payment_details']  = $payment_details;
        // $page_data['payment_gateways'] = DB::table('payment_gateways')->where('status', 1)->get();
        // return view('payment.index', $page_data);
        
        // $DATAIN = mysqli_query($db, "INSERT INTO `bootcamp_purchases` (`id`, `invoice`, `user_id`, `bootcamp_id`, `price`, `tax`, `payment_method`, `payment_details`, `status`, `created_at`, `updated_at`, `admin_revenue`, `instructor_revenue`) VALUES (NULL, 'hKtbRMFo7PAouoAs8Fyt', '7', '1', '0.00', '0.00', 'offline', NULL, '1', '2024-12-16 14:26:08', '2024-12-16 14:26:08', '0.00', '0.00');");
        
        
        // return redirect()->to('my-bootcamps');

        $pmt_data = json_encode($payment_details);

        $resp = (array) json_decode($pmt_data);

        $items_id = $resp['items'][0]->id;
        $items_title = $resp['items'][0]->title;
        $items_subtitle = $resp['items'][0]->subtitle;
        $items_price = $resp['items'][0]->price;
        $items_discount_price = $resp['items'][0]->discount_price;

        $custom_field_id = $resp['custom_field']->item_type;
        $custom_field_id = $resp['custom_field']->pay_for;

        $success_method_model_name = $resp['success_method']->model_name;
        $success_method_function_name = $resp['success_method']->function_name;

        $payable_amount = $resp['payable_amount'];
        $tax = $resp['tax'];
        $coupon = $resp['coupon'];
        $cancel_url = $resp['cancel_url'];
        $success_url = $resp['success_url'];

        $user = auth()->user();
        $user->id;

        $bt_invoice = md5(time());
        $bt_user_id = $user->id;
        $bt_bootcamp_id = $items_id;
        $bt_price = $items_price;
        $bt_tax = "0.00";
        $bt_payment_method = "offline";
        $bt_payment_details = NULL;
        $bt_status = 1;
        $bt_created_at = NULL;
        $bt_updated_at = NULL;
        $bt_admin_revenue = "0.00";
        $bt_instructor_revenue = "0.00";


        $host =  env('DB_HOST');
        $user =  env('DB_USERNAME');
        $password =  env('DB_PASSWORD');
        $dbname =  env('DB_DATABASE');
        $db = mysqli_connect($host,$user,$password,$dbname);
        if (mysqli_connect_errno())
        {
            //  echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }else
        {

        }
        // echo $timestamp = date('m/d/Y h:i:s a', time());

        $DATAIN = mysqli_query($db, "INSERT INTO `bootcamp_purchases` (`id`, `invoice`, `user_id`, `bootcamp_id`, `price`, `tax`, `payment_method`, `payment_details`, `status`, `admin_revenue`, `instructor_revenue`) VALUES (NULL, '$bt_invoice', '$bt_user_id', '$bt_bootcamp_id', '0.00', '0.00', 'offline', NULL, '1', '0.00', '0.00')");

        return redirect()->to('my-bootcamps');

    }

    public function show_payment_gateway_by_ajax($identifier)
    {
        $page_data['payment_details'] = session('payment_details');
        $page_data['payment_gateway'] = DB::table('payment_gateways')->where('identifier', $identifier)->first();
        return view('payment.' . $identifier . '.index', $page_data);
    }

    public function payment_success($identifier, Request $request)
    {

        $payment_details = session('payment_details');
        $payment_gateway = DB::table('payment_gateways')->where('identifier', $identifier)->first();
        $model_name      = $payment_gateway->model_name;
        $model_full_path = str_replace(' ', '', 'App\Models\payment_gateway\ ' . $model_name);

        $status = $model_full_path::payment_status($identifier, $request->all());
        if ($status === true) {
            $success_model    = $payment_details['success_method']['model_name'];
            $success_function = $payment_details['success_method']['function_name'];

            $model_full_path = str_replace(' ', '', 'App\Models\ ' . $success_model);
            return $model_full_path::$success_function($identifier);
        } else {
            Session::flash('error', get_phrase('Payment failed! Please try again.'));
            return redirect()->to($payment_details['cancel_url']);
        }
    }



    public function payment_create($identifier)
    {
        $payment_details      = session('payment_details');
        $payment_gateway      = DB::table('payment_gateways')->where('identifier', $identifier)->first();
        $model_name           = $payment_gateway->model_name;
        $model_full_path      = str_replace(' ', '', 'App\Models\payment_gateway\ ' . $model_name);
        $created_payment_link = $model_full_path::payment_create($identifier);

        return redirect()->to($created_payment_link);
    }

    public function payment_razorpay($identifier)
    {
        $payment_details = session('payment_details');
        $payment_gateway = DB::table('payment_gateways')->where('identifier', $identifier)->first();
        $model_name      = $payment_gateway->model_name;
        $model_full_path = str_replace(' ', '', 'App\Models\payment_gateway\ ' . $model_name);
        $data            = $model_full_path::payment_create($identifier);

        return view('payment.razorpay.payment', compact('data'));
    }
































    public function make_paytm_order(Request $request)
    {

        //start common code of all payment gateway
        // $identifier = 'paytm';
        // $payment_details = session('payment_details');
        // $model = $payment_details['success_method']['model_name'];
        // $payment_gateway = DB::table('payment_gateways')->where('identifier', $identifier)->first();
        // $user = auth()->user();

        // $paytm_merchant_key = $paytm_merchant_mid = $paytm_merchant_website = $industry_type_id = $channel_id = '';
        // if ($model == 'InstructorPayment') {
        //     $instructor_payment_keys = DB::table('users')
        //         ->where('id', $payment_details['items'][0]['id'])
        //         ->value('paymentkeys');
        //     $keys = isset($instructor_payment_keys) ? json_decode($instructor_payment_keys) : null;
        //     if ($keys) {
        //         $paytm_merchant_key = $keys->paytm->paytm_merchant_key;
        //         $paytm_merchant_mid = $keys->paytm->paytm_merchant_mid;
        //         $paytm_merchant_website = $keys->paytm->paytm_merchant_website;
        //         $industry_type_id = $keys->paytm->industry_type_id;
        //         $channel_id = $keys->paytm->channel_id;
        //     }
        // } else {
        //     $keys = json_decode($payment_gateway->keys);
        //     $paytm_merchant_key = $keys->paytm_merchant_key;
        //     $paytm_merchant_mid = $keys->paytm_merchant_mid;
        //     $paytm_merchant_website = $keys->paytm_merchant_website;
        //     $industry_type_id = $keys->industry_type_id;
        //     $channel_id = $keys->channel_id;
        // }

        // if ($payment_gateway->test_mode == 1) {
        //     $PAYTM_STATUS_QUERY_NEW_URL = 'https://securegw-stage.paytm.in/merchant-status/getTxnStatus';
        //     $PAYTM_TXN_URL = 'https://securegw-stage.paytm.in/theia/processTransaction';
        // } else {
        //     define('PAYTM_ENVIRONMENT', 'PROD'); // PROD or TEST
        //     $PAYTM_STATUS_QUERY_NEW_URL = 'https://securegw.paytm.in/merchant-status/getTxnStatus';
        //     $PAYTM_TXN_URL = 'https://securegw.paytm.in/theia/processTransaction';
        // }
        // $paramList = [];
        // $paramList['MID'] = $paytm_merchant_mid;
        // $paramList['ORDER_ID'] = 'ORDS2123' . $user->id;
        // $paramList['CUST_ID'] = 'CUST' . $user->id;
        // $paramList['INDUSTRY_TYPE_ID'] = $industry_type_id;
        // $paramList['CHANNEL_ID'] = $channel_id;
        // $paramList['TXN_AMOUNT'] = $payment_details['payable_amount'];
        // $paramList['WEBSITE'] = $paytm_merchant_website;
        // $paramList['CALLBACK_URL'] = $payment_details['success_url'] . '/' . $identifier;

        









        // $paytmParams = array();

        // $paytmParams["body"] = array(
        //     "requestType"   => "Payment",
        //     "mid"           => $paytm_merchant_mid,
        //     "websiteName"   => $paytm_merchant_website,
        //     "orderId"       => 'ORDS2123' . $user->id,
        //     "callbackUrl"   => $payment_details['success_url'] . '/' . $identifier,
        //     "txnAmount"     => array(
        //         "value"     => round($payment_details['payable_amount'], 2),
        //         "currency"  => "INR",
        //     ),
        //     "userInfo"      => array(
        //         "custId"    => "CUST_".$user->id,
        //     ),
        // );


        // $checksum = PaytmChecksum::generateSignature(json_encode($paramList, JSON_UNESCAPED_SLASHES), $paytm_merchant_key);
        // echo PaytmChecksum::verifySignature($paramList, $paytm_merchant_key, $checksum);

        // // $checksum = str_replace('/', '', $checksum);
        // // $checksum = str_replace('=', '', $checksum);

        // $paytmParams["head"] = array(
        //     "signature"    => $checksum,
        //     "channelId" => $channel_id
        // );

        // $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

        // /* for Staging */
        // $url = "https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction?mid=$paytm_merchant_mid&orderId=ORDS2123" . $user->id;

        // $ch = curl_init($url);
        // curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        // $response = curl_exec($ch);
        // print_r($response);



































        return view('payment.paytm.paytm_merchant_checkout');
    }

    public function paytm_paymentCallback()
    {
        $transaction = PaytmWallet::with('receive');
        $response    = $transaction->response();
        $order_id    = $transaction->getOrderId(); // return a order id
        $transaction->getTransactionId(); // return a transaction id

        // update the db data as per result from api call
        if ($transaction->isSuccessful()) {
            Paytm::where('order_id', $order_id)->update(['status' => 1, 'transaction_id' => $transaction->getTransactionId()]);
            return redirect(route('initiate.payment'))->with('message', "Your payment is successfull.");
        } else if ($transaction->isFailed()) {
            Paytm::where('order_id', $order_id)->update(['status' => 0, 'transaction_id' => $transaction->getTransactionId()]);
            return redirect(route('initiate.payment'))->with('message', "Your payment is failed.");
        } else if ($transaction->isOpen()) {
            Paytm::where('order_id', $order_id)->update(['status' => 2, 'transaction_id' => $transaction->getTransactionId()]);
            return redirect(route('initiate.payment'))->with('message', "Your payment is processing.");
        }
        $transaction->getResponseMessage(); //Get Response Message If Available

    }

    public function webRedirectToPayFee(Request $request)
    {
        // Check if the 'auth' query parameter is present
        if (!$request->has('auth')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Authentication token is missing.',
            ]);
        }

        // Remove the 'Basic ' prefix
        // $base64Credentials = $request->query('auth');
        // Remove the 'Basic ' prefix
        $base64Credentials = substr($request->query('auth'), 6);

        // Decode the base64-encoded string
        $credentials = base64_decode($base64Credentials);

        // Split the decoded string into email, password, and timestamp
        list($email, $password, $timestamp) = explode(':', $credentials);

        // Get the current timestamp
        $timestamp1 = strtotime(date('Y-m-d'));

        // Calculate the difference
        $difference = $timestamp1 - $timestamp;

        if ($difference < 86400) {
            if (auth()->attempt(['email' => $email, 'password' => $password])) {
                // Authentication passed...
                return redirect(route('cart'));
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Invalid email or password',
            ]);
        } else {
            return redirect()->route('login')->withErrors([
                'email' => 'Token expired!',
            ]);
        }
    }
}

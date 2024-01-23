<?php

namespace App\Http\Controllers\Front;

use App\Service\OrderDetail\OrderDetailServiceInterface;
use App\Service\Order\OrderServiceInterface;
use App\Http\Controllers\Controller;
use App\Utilities\Constant;
use App\Utilities\VNPay;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CheckOutController extends Controller
{
    private $orderService;
    private $orderDetailService;

    public function __construct(OrderServiceInterface $orderService,
    OrderDetailServiceInterface $orderDetailService)
    {
        $this->orderService = $orderService;
        $this->orderDetailService = $orderDetailService;
    }

    public function index() {
        $carts = Cart::content();
        $total = Cart::total();
        $subtotal = Cart::subtotal();

        return view('front.checkout.index', compact('carts', 'total', 'subtotal'));
    }

    public function addOrder(Request $request) {
        //1 Thêm đơn hàng
        $data = $request->all();
        $data['status'] = Constant::order_status_ReceiveOrders;

        $order = $this->orderService->create($data);
        //2 Thêm chi tiết đơn hàng
        $carts = Cart::content();
        foreach ($carts as $cart) {
            $data = [
                'order_id' => $order->id,
                'product_id' => $cart->id,
                'qty' => $cart->qty,
                'amount' => $cart->price,
                'total' => $cart->qty * $cart->price,
                
            ];
            $this->orderDetailService->create($data);
        }
        if($request->payment_type == 'pay_later') {
            // Gui email
            $total = Cart::total();
            $subtotal = Cart::subtotal();
            $this->sendEmail($order, $total, $subtotal);
            //3 Xóa giỏ hàng
            Cart::destroy();
            //4 Trả về kết quả thông báo
            return redirect('checkout/result')
                ->with('notification', 'Success! You will pay on delivery, Please check your email.');
        }
        if($request->payment_type == 'online_payment') {
            // Gui email
            $data_url = VNPay::vnpay_create_payment([
                'vnp_TxnRef' => $order->id,  // ID của đơn hàng
                'vnp_OrderInfo' => 'Mô tả đơn hàng ', // Mô tả đơn hàng
                'vnp_Amount' => Cart::total(0, '', '') * 24385 // Tổng giá trị đơn hàng
            ]);
            // Gui email
            $total = Cart::total();
            $subtotal = Cart::subtotal();
            $this->sendEmail($order, $total, $subtotal);
            //3 Xóa giỏ hàng
            Cart::destroy();
            
        }
        return redirect()->to($data_url);
    }

    public function vnPayCheck(Request $request) {
        // Lấy data từ URL (do VNPay gửi về qua $vnp_Returnurl)
        $vpn_ResponseCode = $request->get('vnp_ResponseCode'); // Mã phản hồi kết quả thanh toán 00 = thành công
        $vpn_TxnRef = $request->get('vnp_TxnRef'); // order id
        $vnp_Amount = $request->get('vnp_Amount'); // Số tiền thanh toán

        if($vpn_ResponseCode != null) {

            if($vpn_ResponseCode == 00) {
                // Nếu thành công
                Cart::destroy();

                return redirect('checkout/result')
            ->with('notification', 'Success! You will pay online, Please check your email.');
            } else {
                $this->orderService->delete($vpn_TxnRef);

                return redirect('checkout/result')
            ->with('notification', 'ERROR: Payment failed or canceled!');
            }
        }
    }
    public function result() {
        $notification = session('notification');
        return view('front.checkout.result', compact('notification'));
    }

    private function sendEmail($order, $total, $subtotal) 
    {
        $email_to = $order->email;

        Mail::send('front.checkout.email', 
            compact('order', 'total', 'subtotal'),
            function ($message) use ($email_to) {
                $message->from('diablo@gmail.com', 'Diablo');
                $message->to($email_to, $email_to);
                $message->subject('Order Notification');
            });
    }
}

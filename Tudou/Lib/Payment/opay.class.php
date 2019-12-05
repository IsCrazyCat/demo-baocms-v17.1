<?php 
class opay
{
 
    /**
     * 生成支付代码
     * @param   array    $logs       订单信息
     * @param   array    $payment     支付方式信息
     */
	 
    function getCode($logs, $payment) {
		
		include('opay/AllPay.Payment.Integration.php');
		
       	$obj = new AllInOne();
   
        //服務參數
        $obj->ServiceURL  = "https://payment.allpay.com.tw/Cashier/AioCheckOut/V4";   
        $obj->HashKey     = $payment['HashKey'];                                          
        $obj->HashIV      = $payment['HashIV'];                                            
        $obj->MerchantID  = $payment['MerchantID'];    
		
	

        //基本參數(請依系統規劃自行調整)
        $obj->Send['ReturnURL']         = __HOST__ . U( 'Home/payment/respond', array('code' =>'opay'));    //付款完成通知回傳的網址
        $obj->Send['MerchantTradeNo']   = $logs['logs_id'].'abc'.time() ;   //訂單編號
        $obj->Send['MerchantTradeDate'] = date('Y/m/d H:i:s');       //交易時間
        $obj->Send['TotalAmount']       = $logs['logs_amount'];       //交易金額
        $obj->Send['TradeDesc']         = $logs['subject'] ;       //交易描述
        $obj->Send['ChoosePayment']     = PaymentMethod::Credit ;     
		
	//p($obj->Send);die;
        //訂單的商品資料
        array_push($obj->Send['Items'], array(
			'Name' => $logs['subject'], 
			'Price' => $logs['logs_amount'],
			'Currency' => "元", 
			'Quantity' => (int) "1", 
			'URL' => __HOST__ . U( 'Home/payment/respond', array('code' =>'opay'))
		));


        //Credit信用卡分期付款延伸參數(可依系統需求選擇是否代入)
        //以下參數不可以跟信用卡定期定額參數一起設定
        $obj->SendExtend['CreditInstallment'] = 0 ;    //分期期數，預設0(不分期)
        $obj->SendExtend['InstallmentAmount'] = 0 ;    //使用刷卡分期的付款金額，預設0(不分期)
        $obj->SendExtend['Redeem'] = false ;           //是否使用紅利折抵，預設false
        $obj->SendExtend['UnionPay'] = false;          //是否為聯營卡，預設false;

        //Credit信用卡定期定額付款延伸參數(可依系統需求選擇是否代入)
        //以下參數不可以跟信用卡分期付款參數一起設定
        // $obj->SendExtend['PeriodAmount'] = '' ;    //每次授權金額，預設空字串
        // $obj->SendExtend['PeriodType']   = '' ;    //週期種類，預設空字串
        // $obj->SendExtend['Frequency']    = '' ;    //執行頻率，預設空字串
        // $obj->SendExtend['ExecTimes']    = '' ;    //執行次數，預設空字串
        
        # 電子發票參數
        /*
        $obj->Send['InvoiceMark'] = InvoiceState::Yes;
        $obj->SendExtend['RelateNumber'] = $MerchantTradeNo;
        $obj->SendExtend['CustomerEmail'] = 'test@allpay.com.tw';
        $obj->SendExtend['CustomerPhone'] = '0911222333';
        $obj->SendExtend['TaxType'] = TaxType::Dutiable;
        $obj->SendExtend['CustomerAddr'] = '台北市南港區三重路19-2號5樓D棟';
        $obj->SendExtend['InvoiceItems'] = array();
        // 將商品加入電子發票商品列表陣列
        foreach ($obj->Send['Items'] as $info)
        {
            array_push($obj->SendExtend['InvoiceItems'],array('Name' => $info['Name'],'Count' =>
                $info['Quantity'],'Word' => '個','Price' => $info['Price'],'TaxType' => TaxType::Dutiable));
        }
        $obj->SendExtend['InvoiceRemark'] = '測試發票備註';
        $obj->SendExtend['DelayDay'] = '0';
        $obj->SendExtend['InvType'] = InvType::General;
        */


        //產生訂單(auto submit至AllPay)
        $button = $obj->CheckOut();
        return $button;
    }

    /**
     * 响应操作
     */
    function respond(){
        /*取返回参数*/

		$data = array();
        $data['MerchantTradeNo'] = $_REQUEST['MerchantTradeNo'];//商店交易編號
        $data['RtnCode'] = $_REQUEST['RtnCode'];//交易狀態1:交易成功，其餘代碼為交易失敗。
        $data['RtnMsg'] = $_REQUEST['RtnMsg'];//交易訊息告知付款結果。Ex.交易成功、付款失敗。
        $data['TradeNo'] = $_REQUEST['TradeNo'];//O'Pay的交易編號請保存O'Pay的交易編號與 MerchantTradeNo的關連。
        $data['PaymentDate'] = $_REQUEST['PaymentDate'];//付款時間日期時間格式：yyyy/MM/dd HH:mm:ss
  		
		//file_put_contents('data.txt', var_export($data, true));
    
        /* 如果pay_result大于0则表示支付失败 */
        if($data['RtnCode'] != 1){
            return false;
        }
		
		$trade = explode('abc',$data['MerchantTradeNo']);//新版回调
		D('Payment')->logsPaid($trade[0]);
        return true;
    }
}

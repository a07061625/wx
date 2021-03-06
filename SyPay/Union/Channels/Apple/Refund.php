<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2020/8/19 0019
 * Time: 8:59
 */
namespace SyPay\Union\Channels\Apple;

use SyConstant\ErrorCode;
use SyException\Pay\UnionException;
use SyPay\Union\Channels\BaseApple;
use SyPay\Union\Channels\Traits\AccessTypeTrait;
use SyPay\Union\Channels\Traits\AccSplitDataTrait;
use SyPay\Union\Channels\Traits\AcqInsCodeTrait;
use SyPay\Union\Channels\Traits\BackUrlTrait;
use SyPay\Union\Channels\Traits\CertIdTrait;
use SyPay\Union\Channels\Traits\ChannelTypeTrait;
use SyPay\Union\Channels\Traits\CtrlRuleTrait;
use SyPay\Union\Channels\Traits\MerInfoTrait;
use SyPay\Union\Channels\Traits\OrderIdTrait;
use SyPay\Union\Channels\Traits\OrigQryIdTrait;
use SyPay\Union\Channels\Traits\ReqReservedTrait;
use SyPay\Union\Channels\Traits\TermIdTrait;
use SyPay\Union\Channels\Traits\TxnAmtTrait;
use SyPay\UtilUnionChannels;

/**
 * 退货接口
 * 对于跨清算日或者当清算日的消费交易,商户可以通过调用SDK向银联全渠道支付平台发起退货交易,从而实现客户的退款需求,支持部分退货、多次退货
 * 该交易参加资金清算,为后台交易
 *
 * @package SyPay\Union\Channels\Apple
 */
class Refund extends BaseApple
{
    use BackUrlTrait;
    use TxnAmtTrait;
    use AccessTypeTrait;
    use ChannelTypeTrait;
    use OrderIdTrait;
    use OrigQryIdTrait;
    use MerInfoTrait;
    use AcqInsCodeTrait;
    use CertIdTrait;
    use AccSplitDataTrait;
    use CtrlRuleTrait;
    use ReqReservedTrait;
    use TermIdTrait;

    public function __construct(string $merId, string $envType)
    {
        $this->reqDomains = [
            self::ENV_TYPE_PRODUCT => 'https://gateway.95516.com',
            self::ENV_TYPE_DEV => 'https://101.231.204.80:5000',
        ];
        parent::__construct($merId, $envType);
        $this->reqDomain .= '/gateway/api/backTransReq.do';
        $this->reqData['bizType'] = '000802';
        $this->reqData['backUrl'] = 'http://www.specialUrl.com';
        $this->reqData['txnType'] = '04';
        $this->reqData['txnSubType'] = '00';
        $this->reqData['accessType'] = 0;
    }

    public function __clone()
    {
    }

    /**
     * @return array
     *
     * @throws \SyException\Pay\UnionException
     */
    public function getDetail() : array
    {
        if (!isset($this->reqData['txnAmt'])) {
            throw new UnionException('交易金额不能为空', ErrorCode::PAY_UNION_PARAM_ERROR);
        }
        if (!isset($this->reqData['channelType'])) {
            throw new UnionException('渠道类型不能为空', ErrorCode::PAY_UNION_PARAM_ERROR);
        }
        if (!isset($this->reqData['orderId'])) {
            throw new UnionException('商户订单号不能为空', ErrorCode::PAY_UNION_PARAM_ERROR);
        }
        if (!isset($this->reqData['origQryId'])) {
            throw new UnionException('原交易查询流水号不能为空', ErrorCode::PAY_UNION_PARAM_ERROR);
        }
        UtilUnionChannels::createSign($this->reqData['merId'], $this->reqData);

        return $this->getChannelsContent();
    }
}

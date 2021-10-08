
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'hitpay',
                component: 'SoftBuild_HitPay/js/hitpay'
            }
        );
        return Component.extend({});
    }
);
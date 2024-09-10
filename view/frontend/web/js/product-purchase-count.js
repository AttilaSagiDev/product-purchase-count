/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

define([
    'jquery',
    'mage/url',
    'mage/storage',
    'domReady!'
], function ($, urlBuilder, storage) {
    'use strict';

    $.widget('mage.productPurchaseCount', {

        defaults: {
            selectorId: '#product-purchase-count'
        },

        /**
         * Product purchase count
         * @private
         */
        _create: function () {
            const productId = this.options.productId || null;
            const storeCode = this.options.storeCode || null;

            if (productId && storeCode) {
                const serviceUrl = urlBuilder.build('/rest/' + storeCode + '/V1/productPurchaseCount/' + productId);
                this._callApi(serviceUrl);
            }
        },

        /**
         * Call API
         *
         * @param {String} serviceUrl
         * @private
         * @return void
         */
        _callApi: function (serviceUrl) {
            storage.get(
                serviceUrl,
                true,
                'application/json',
                {}
            ).done(function (result) {
                this._displayMessage(result);
            }.bind(this)).fail(function (response) {
                // Enable for debug
                //console.log(response);
            });
        },

        /**
         * Display message
         *
         * @param {Object} result
         * @private
         * @return void
         */
        _displayMessage: function (result) {
            const divSelector = this.options.selectorId || this.defaults.selectorId;

            if (typeof $(divSelector) !== "undefined"
                && typeof result === "object"
                && result.hasOwnProperty('count')
                && result.count > 0
            ) {
                $(divSelector).html('test ' + result.count);
            }
        }
    });

    return $.mage.productPurchaseCount;
});

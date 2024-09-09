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
            const serviceUrl = urlBuilder.build('/rest/default/V1/productPurchaseCount/2');
            this._callApi(serviceUrl);
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
                console.log(response);
            });
        },

        /**
         * Display message
         *
         * @param {Object} result
         * @private
         */
        _displayMessage: function (result) {
            if (typeof $(this.defaults.selectorId) !== "undefined") {
                $(this.defaults.selectorId).html('test ' + result.count);
            }
        }
    });

    return $.mage.productPurchaseCount;
});

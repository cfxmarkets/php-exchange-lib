<?php
namespace CFX\SDK\Exchange\Test;

class Order extends \CFX\Exchange\Order
{
    public static $testData = [
        [
            // Active Sell Order
            "orderKey" => "76dc3a75b25a8ce2180faf301f5a40b6",
            "assetSymbol" => "FR008",
            "orderType" => "sell",
            "orderTime" => "2017-08-28 12:41:20",
            "orderPrice" => "2.89",
            "orderQuantity" => "12300",
            "orderFee" => "1244.15",
            "orderStatus" => "1",
            "accountKey" => "e60e10328bf4b136219488d64560cd16",
            "documentKey" => null,
            "documentUrl" => null,
            "documentTime" => null,
            "vaultKey" => null,
            "activities" => [],
        ],
        [
            // Active buy order
            "orderKey" => "91c295e7950923b94057c4a0acdb79d4",
            "assetSymbol" => "FR008",
            "orderType" => "buy",
            "orderTime" => "2017-08-28 12:41:20",
            "orderPrice" => "1.88",
            "orderQuantity" => "12300",
            "orderFee" => "1244.15",
            "orderStatus" => "1",
            "accountKey" => "76b7137d-5555-11e4-8141-003048d9078a",
            "documentKey" => null,
            "documentUrl" => null,
            "documentTime" => null,
            "vaultKey" => null,
            "activities" => [],
        ],
        [
            // Cancelled sell order
            "orderKey" => "64e090def7c3da2d3c4d8081a202a6a7",
            "assetSymbol" => "INVT001",
            "orderType" => "sell",
            "orderTime" => "2017-07-07 13:24:16",
            "orderPrice" => "2.25",
            "orderQuantity" => "100",
            "orderFee" => "150",
            "orderStatus" => "-1",
            "accountKey" => "2bed75b010481af333571b2a15a88953",
            "documentKey" => null,
            "documentUrl" => null,
            "documentTime" => null,
            "vaultKey" => null,
            "activities" => [
                [
                    "activity" => "Canceled order on expiration",
                    "activityData" => null,
                    "createdOn" => "2017-07-21 07:58:40"
                ]
            ]
        ],
        [
            // Canceled buy order
            "orderKey" => "f539f08acb3f9807e00dd367851de1e9",
            "assetSymbol" => "FR008",
            "orderType" => "buy",
            "orderTime" => "2017-09-22 13:45:49",
            "orderPrice" => "1",
            "orderQuantity" => "100",
            "orderFee" => "2.5",
            "orderStatus" => "-1",
            "accountKey" => "a9e1a313e686e159849bd1e0d3e04dc9",
            "documentKey" => null,
            "documentUrl" => null,
            "documentTime" => null,
            "vaultKey" => "undefined",
            "activities" => [],
        ],
        [
            // Matched Sell Order
            "orderKey" => "7b935106cc58489f6576356b7891fa94",
            "assetSymbol" => "INVT001",
            "orderType" => "sell",
            "orderTime" => "2017-06-23 08:45:52",
            "orderPrice" => "2.12",
            "orderQuantity" => "100011",
            "orderFee" => "7420.82",
            "orderStatus" => "2",
            "accountKey" => "cbb99008513123c0dde3c0faf5ca8614",
            "documentKey" => null,
            "documentUrl" => null,
            "documentTime" => null,
            "vaultKey" => null,
            "activities" => [
                [
                    "activity" => "Updated order price on close",
                    "activityData" => "2.12",
                    "createdOn" => "2017-06-28 14:47:08"
                ]
            ]
        ],
        [
            // Matched Buy Order
            "orderKey" => "a0fa966cba5a101a685d5fba1f7bff74",
            "assetSymbol" => "INVT001",
            "orderType" => "buy",
            "orderTime" => "2017-06-15 17:06:18",
            "orderPrice" => "2.02",
            "orderQuantity" => "98753",
            "orderFee" => "4987.03",
            "orderStatus" => "2",
            "accountKey" => "f5345189300251b8e88ad6077a29caba",
            "documentKey" => null,
            "documentUrl" => null,
            "documentTime" => null,
            "vaultKey" => "0",
            "activities" => [
                [
                    "activity" => "Updated order price on smart bid",
                    "activityData" => "2.02",
                    "createdOn" => "2017-06-20 10:45:49"
                ],
                [
                    "activity" => "Updated best bid price on smart bid",
                    "activityData" => "2",
                    "createdOn" => "2017-06-15 17:06:18"
                ]
            ]
        ]
    ];
}

